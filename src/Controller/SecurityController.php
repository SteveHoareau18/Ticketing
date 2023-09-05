<?php

namespace App\Controller;

use App\Entity\MailConfiguration;
use App\Entity\Service;
use App\Entity\User;
use App\Service\MailService;
use App\Service\RandomPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 *
 */
class SecurityController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, EntityManagerInterface $registry, UserPasswordHasherInterface $hasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_main');
        }


        $userRepository = $registry->getRepository(User::class);
        if (sizeof($userRepository->findAll()) == 0) {
            $serviceRepository = $registry->getRepository(Service::class);
            if (sizeof($serviceRepository->findAll()) > 0) {
                $user = new User();
                $user->setActive(0);
                $user->setRoles(array("ROLE_ADMIN"));
                $user->setEmail("steve.hoareau1@gmail.com");
                $user->setFirstname("Steve");
                $user->setName("HOAREAU");
                $user->setUsername("hsteve");
                $user->setService($serviceRepository->findAll()[0]);
                $password = (new RandomPasswordService())->getRandomStrenghPassword();
                $user->setPassword($hasher->hashPassword($user, $password));
                $mailConfiguration = $registry->getRepository(MailConfiguration::class)->findAll()[0];
                if ($mailConfiguration != null) {
                    $mailService = new MailService($mailConfiguration);
                    $email = (new Email())
                        ->from(new Address($mailConfiguration->getLogin(), $mailConfiguration->getSubject()))
                        ->to($user->getEmail())
                        ->subject('Creation de votre compte // PLATEFORME TICKETING')
                        ->html("<p>Bonjour, votre compte a été crée avec l'identifiant " . $user->getUsername() . " et le mot de passe: " . $password . '</p><p>Celui-ci doit rester confidentiel !</p>');

                    foreach (explode(',', $mailConfiguration->getCcAddress()) as $address) {
                        $email->addCc(new Address(trim($address)));
                    }

                    $mailService->getMailer()->send($email);

                    $this->addFlash('success', 'La génération de compte a été effectuée. Contactez steve.hoareau1@gmail.com.');

                    $registry->persist($user);
                    $registry->flush();
                } else {
                    $this->addFlash('fail', "Une erreur est survenue, la génération de compte n'a pas été effectuée. Contactez steve.hoareau1@gmail.com.");
                }
            }
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @return void
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
