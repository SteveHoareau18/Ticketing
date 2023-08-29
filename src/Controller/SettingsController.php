<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\ByteString;

#[Route('/mon-compte')]
class SettingsController extends AbstractController
{
    #[Route('/', name: 'app_settings')]
    public function settings(): Response
    {
        if(!$this->getUser()) return $this->redirectToRoute("app_login");
        return $this->render('settings.html.twig', []);
    }

    #[Route('/changer-de-mot-passe', name: 'app_settings_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager): Response
    {
        if(!$this->getUser()) return $this->redirectToRoute("app_login");
        if($request->request->has('_csrf_token') && $this->isCsrfTokenValid('random-password'.$this->getUser()->getUserIdentifier(),$request->request->get('_csrf_token'))) {
            $password = ByteString::fromRandom(8, implode('', range('A', 'Z')))->toString(); // uppercase letters only (e.g: sponsor code)
            $password .= ByteString::fromRandom(4, '0123456789')->toString();
            $user = $manager->getRepository(User::class)->findOneBy(['username'=>$this->getUser()->getUserIdentifier()]);
            $user->setPassword($hasher->hashPassword($user, $password));
            if(!$user->isActive())$user->setActive(true);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash("success", "Paramètre enregistré. Votre nouveau mot de passe est : " . $password);
        }
        return $this->redirectToRoute("app_settings");
    }
}
