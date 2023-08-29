<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, ManagerRegistry $registry, AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('app_main');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if($request->request->has("username")){
            if($registry->getRepository(User::class)->findBy(['username'=>$request->request->get("username")])){
                $user = $registry->getRepository(User::class)->findOneBy(['username'=>$request->request->get("username")]);
                if(!$user->isActive()){
                    $this->addFlash('fail', "Votre compte n'est pas activé. Contacter l'administrateur.");
                    return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
                }
            }
        }

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->addFlash('success','Vous avez été déconnecté !');
    }
}
