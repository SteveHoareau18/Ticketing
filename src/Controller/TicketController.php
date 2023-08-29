<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ticket')]
class TicketController extends AbstractController
{
    #[Route('/', name: 'app_ticket')]
    public function index(Request $request, ManagerRegistry $registry): Response
    {
        $ticketRepository = $registry->getRepository(Ticket::class);
        if($request->query->has("service")){
            if($ticketRepository->findBy(['service'=>$request->request->get('service')])){
                $ticketsByService = $ticketRepository->findBy(['service'=>$request->request->get('service')]);
                if($request->query->has("status")){
                    $statusRepository = $registry->getRepository(Status::class);
                    if($statusRepository->findBy(['caption'=>$request->request->get("status")])) {
                        $tickets = array();
                        foreach ($ticketsByService as $ticket) {
                            foreach ($ticket->getTreatments() as $treatment) {
                                if ($treatment->getStatus() == $request->request->get('status')) {
                                    array_push($tickets, $ticket);
                                }
                            }
                        }
                    }else{
                        return $this->redirectToRoute("app_main");
                    }
                }else{
                    return $this->render('ticket/index.html.twig', [

                    ]);
                }
            }else{
                return $this->redirectToRoute("app_main");
            }
        }
        return $this->render('ticket/index.html.twig', [

        ]);
    }
}
