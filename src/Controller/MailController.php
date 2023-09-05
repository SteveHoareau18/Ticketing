<?php

namespace App\Controller;

use App\Entity\MailConfiguration;
use App\Form\MailConfigurationType;
use App\Repository\MailConfigurationRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted("ROLE_ADMIN")]
#[Route('/admin/parametre')]
class MailController extends AbstractController
{
    #[Route('/mail', name: 'app_configuration_mail')]
    public function configurationMail(MailConfigurationRepository $repository): Response
    {
        $mailConfigs = $repository->findAll();
        if (sizeof($mailConfigs) == 0) {
            return $this->redirectToRoute("app_configuration_mail_new");
        } else {
            return $this->redirectToRoute("app_configuration_mail_edit", ['id' => $mailConfigs[0]->getId()]);
        }
    }

    #[Route('/mail/new', name: 'app_configuration_mail_new')]
    public function configurationMailNew(Request $request, EntityManagerInterface $repository): Response
    {
        $mailConfig = new MailConfiguration();

        $form = $this->createForm(MailConfigurationType::class, $mailConfig);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $repository->persist($mailConfig);
                $repository->flush();
                $this->addFlash('success', 'Paramétrage enregistré.');
                return $this->redirectToRoute('app_main');
            } catch (Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render('admin/settings/email.html.twig', [
            'emailConfig' => $form,
            'alreadyExist' => false
        ]);
    }

    #[Route('/mail/edit', name: 'app_configuration_mail_edit')]
    public function configurationMailEdit(Request $request, EntityManagerInterface $repository): Response
    {
        $mailConfig = $repository->getRepository(MailConfiguration::class)->findAll()[0];
        $form = $this->createForm(MailConfigurationType::class, $mailConfig);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $repository->persist($mailConfig);
                $repository->flush();
                $this->addFlash('success', 'Paramétrage mail enregistré.');
                return $this->redirectToRoute('app_main');
            } catch (Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render('admin/settings/email.html.twig', [
            'emailConfig' => $form,
            'alreadyExist' => true
        ]);
    }

    #[Route('/mail/test', name: 'app_configuration_mail_test', methods: ['POST'])]
    public function configurationMailTest(Request $request, EntityManagerInterface $repository): Response
    {
        if ($request->request->has('_csrf_token') && $this->isCsrfTokenValid('test-parameter', $request->request->get('_csrf_token'))) {
            $mailConfiguration = $repository->getRepository(MailConfiguration::class)->findAll()[0];
            try {
                $mailService = new MailService($mailConfiguration);
                $email = (new Email())
                    ->from(new Address($mailConfiguration->getLogin(), $mailConfiguration->getSubject()))
                    ->subject('Mail de test - Configuration email')
                    ->text('Bonjour, si vous recevez cet e-mail, la configuration e-mail fonctionne !');

                foreach (explode(',', $mailConfiguration->getCcAddress()) as $address) {
                    $email->addTo(new Address(trim($address)));
                }

                $mailService->getMailer()->send($email);
                $this->addFlash('success', 'L\'e-mail de test a été envoyé.');
            } catch (Exception $exception) {
                $this->addFlash('fail', $exception->getMessage());
            }
        }
        return $this->redirectToRoute('app_main');
    }
}
