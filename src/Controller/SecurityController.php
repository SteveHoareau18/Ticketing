<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\ByteString;

class SecurityController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, EntityManagerInterface $registry, UserPasswordHasherInterface $hasher, MailerInterface $mailer): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('app_main');
         }


         $userRepository = $registry->getRepository(User::class);
         if(sizeof($userRepository->findAll())==0){
             $serviceRepository = $registry->getRepository(Service::class);
             if(sizeof($serviceRepository->findAll())>0) {
                 $user = new User();
                 $user->setActive(0);
                 $user->setRoles(array("ROLE_ADMIN"));
                 $user->setEmail("steve.hoareau1@gmail.com");
                 $user->setFirstname("Steve");
                 $user->setName("HOAREAU");
                 $user->setUsername("hsteve");
                 $user->setService($serviceRepository->findAll()[0]);
                 $password =  ByteString::fromRandom(8, implode('', range('A', 'Z')))->toString(); // uppercase letters only (e.g: sponsor code)
                 $password .= ByteString::fromRandom(4, '0123456789')->toString();
                 $user->setPassword($hasher->hashPassword($user, $password));

//                 try {
//                     $email = (new Email())
//                         ->from('ac63f97c3f-aa64d3@inbox.mailtrap.io')
//                         ->to($user->getEmail())
//                         ->subject('Votre mot de passe de la plateforme ticketing')
//                         ->html('<p>Votre mot de passe est <strong>' . $password . '</strong></p><p>Attention ! Il doit rester confidentiel.</p>');
//                     $mailer->send($email);
//                     dd($mailer);
//                 }catch (\Exception $e){
//                     dd($e);
//                 }//TODO mailer

                 $this->addFlash('success',$password);

                 $registry->persist($user);
                 $registry->flush();
             }
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
