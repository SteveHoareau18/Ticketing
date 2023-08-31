<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ticket')]
class TicketController extends AbstractController
{
    #[Route('/new', name: 'app_ticket_new')]
    public function new(Request $request, ManagerRegistry $registry): Response
    {
        if(!$this->getUser()) $this->redirectToRoute("app_login");
        $user = $registry->getRepository(User::class)->findOneBy(['username'=>$this->getUser()->getUserIdentifier()]);


    }
}
