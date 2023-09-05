<?php

namespace App\Controller;

use App\Entity\MailConfiguration;
use App\Entity\User;
use App\Service\MailService;
use App\Service\RandomPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mon-compte')]
class SettingsController extends AbstractController
{
    #[Route('/', name: 'app_settings')]
    public function settings(): Response
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        return $this->render('settings.html.twig', []);
    }

    #[Route('/changer-de-mot-passe', name: 'app_settings_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        if ($request->request->has('_csrf_token') && $this->isCsrfTokenValid('random-password' . $this->getUser()->getUserIdentifier(), $request->request->get('_csrf_token'))) {
            $password = (new RandomPasswordService())->getRandomStrenghPassword();
            $user = $manager->getRepository(User::class)->findOneBy(['username' => $this->getUser()->getUserIdentifier()]);
            $mailConfiguration = $manager->getRepository(MailConfiguration::class)->findAll()[0];
            if ($mailConfiguration != null) {
                $user->setPassword($hasher->hashPassword($user, $password));
                if (!$user->isActive()) $user->setActive(true);
                $manager->persist($user);
                $manager->flush();

                $mailService = new MailService($mailConfiguration);
                $email = (new Email())
                    ->from(new Address($mailConfiguration->getLogin(), $mailConfiguration->getSubject()))
                    ->to($user->getEmail())
                    ->subject('Un parametre a ete change dans votre compte // PLATEFORME TICKETING')
                    ->html('<p>Bonjour, voici votre nouveau mot de passe: ' . $password . '</p><strong>Celui-ci doit rester confidentiel !</strong>');

                foreach (explode(',', $mailConfiguration->getCcAddress()) as $address) {
                    $email->addCc(new Address(trim($address)));
                }

                $mailService->getMailer()->send($email);
                $this->addFlash('success', 'Votre nouveau mot de passe vous a été en envoyé par mail');
            } else {
                $this->addFlash("fail", "Une erreur est intervenue...");
            }
        } else {
            $this->addFlash("fail", "Une erreur est intervenue...");
        }
        return $this->redirectToRoute("app_settings");
    }
}
