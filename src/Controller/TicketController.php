<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\Treatment;
use App\Entity\User;
use App\Form\TicketType;
use App\Form\TreatmentType;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ticket')]
class TicketController extends AbstractController
{
    /**
     * @throws \Exception
     */
    #[Route('/new', name: 'app_ticket_new')]
    public function new(Request $request, EntityManagerInterface $registry): Response
    {
        if(!$this->getUser()) $this->redirectToRoute("app_login");
        $user = $registry->getRepository(User::class)->findOneBy(['username'=>$this->getUser()->getUserIdentifier()]);

        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class,$ticket);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $ticket->setCreator($user);
            $service = $registry->getRepository(Service::class)->find($form->get('service')->getData());
            if($service == null){
                $this->addFlash('fail',"Ce service n'existe pas...");
            }else {
                $ticket->setService($service);
                $ticket->setCreateDate(new \DateTime("now", new DateTimeZone($_ENV["DATETIMEZONE"])));
                $registry->persist($ticket);
                $registry->flush();
                $this->addFlash('success',"Vous avez ouvert un nouveau ticket pour le service ".$service->getName());
                return $this->redirectToRoute("app_main");
            }
        }
        return $this->render("ticket/new.html.twig",[
            'form'=>$form,
            'serviceLst'=>$registry->getRepository(Service::class)->findAll()
        ]);
    }

    #[Route('/see/{id}', name: 'app_ticket_see')]
    public function see(Request $request, EntityManagerInterface $registry, $id): Response
    {
        if(!$this->getUser()) $this->redirectToRoute("app_login");
        $user = $registry->getRepository(User::class)->findOneBy(['username'=>$this->getUser()->getUserIdentifier()]);

        $ticket = $registry->getRepository(Ticket::class)->find($id);
        if($ticket == null) return $this->redirectToRoute("app_main");
        if($ticket->getCreator()->getId() == $user->getId() || $ticket->getService()->getId() == $user->getService()->getId()) {
            return $this->render("ticket/see.html.twig", [
                'ticket'=>$ticket,
                'serviceLst'=>$registry->getRepository(Service::class)->findAll()
            ]);
        }else{
            return $this->redirectToRoute("app_main");
        }
    }

    /**
     * @throws \Exception
     */
    #[Route('/open/{id}', name: 'app_ticket_open')]
    public function open(Request $request, EntityManagerInterface $registry, $id): Response
    {
        if(!$this->getUser()) $this->redirectToRoute("app_login");
        $user = $registry->getRepository(User::class)->findOneBy(['username'=>$this->getUser()->getUserIdentifier()]);

        $ticket = $registry->getRepository(Ticket::class)->find($id);
        if($ticket == null) return $this->redirectToRoute("app_main");
        if($ticket->getCreator()->getId() == $user->getId() || $ticket->getService()->getId() == $user->getService()->getId()) {
            $treatment = $registry->getRepository(Treatment::class)->findOneBy(['caterer'=>$user]);
            if($treatment != null && $treatment->getStatus() == "EN COURS") {
                $this->addFlash('fail','Vous avez déjà ouvert un traitement...');
            }else{
                $treatment = new Treatment();
                $form = $this->createForm(TreatmentType::class, $treatment);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $treatment->setTicket($ticket);
                    $treatment->setUser($user);
                    $treatment->setStartDate(new \DateTime("now", new DateTimeZone($_ENV['DATETIMEZONE'])));
                    $treatment->setStatus("EN COURS");
                    $registry->persist($treatment);
                    $registry->flush();

                    $ticket->addTreatment($treatment);
                    return $this->redirectToRoute("app_ticket_see", ['id' => $ticket->getId()]);
                }
                return $this->render("ticket/treatment/new.html.twig", [
                    'ticket' => $ticket,
                    'form' => $form
                ]);
            }
        }else{
            return $this->redirectToRoute("app_main");
        }
    }
    /**
     * @throws \Exception
     */
    #[Route('/open/{id}/relayed/{treatment}', name: 'app_ticket_relayed')]
    public function relayed(Request $request, EntityManagerInterface $registry, $id, $treatment): Response
    {
        if(!$this->getUser()) $this->redirectToRoute("app_login");
        $user = $registry->getRepository(User::class)->findOneBy(['username'=>$this->getUser()->getUserIdentifier()]);

        $ticket = $registry->getRepository(Ticket::class)->find($id);
        if($ticket == null) return $this->redirectToRoute("app_main");
        if($ticket->getCreator()->getId() == $user->getId() || $ticket->getService()->getId() == $user->getService()->getId()) {
            $treatment = $registry->getRepository(Treatment::class)->find($treatment);
            if($treatment==null) return $this->redirectToRoute("app_ticket_see",['id'=>$id]);
            $treatment->setStatus("RELAYÉ");
            $treatment->setEndDate(new \DateTime("now", new DateTimeZone($_ENV['DATETIMEZONE'])));
            $registry->persist($treatment);
            $registry->flush();
            $this->addFlash('success','Vous avez pris le relais pour le traitement du ticket.');
            return $this->redirectToRoute("app_ticket_open",['id'=>$ticket->getId()]);
        }else{
            return $this->redirectToRoute("app_main");
        }
    }

    /**
     * @throws \Exception
     */
    #[Route('/close/{id}', name: 'app_ticket_close')]
    public function close(Request $request, EntityManagerInterface $registry, $id): Response
    {
        if(!$this->getUser()) $this->redirectToRoute("app_login");
        $user = $registry->getRepository(User::class)->findOneBy(['username'=>$this->getUser()->getUserIdentifier()]);

        $ticket = $registry->getRepository(Ticket::class)->find($id);
        if($ticket == null) return $this->redirectToRoute("app_main");
        if($ticket->getService()->getId() == $user->getService()->getId()) {
            if($request->request->has('closeReason') && $request->request->has("_csrf_token") && $this->isCsrfTokenValid('close-ticket'.$ticket->getId(),$request->request->get('_csrf_token'))){
                $ticket->getTreatments()->last()->setStatus('Fermé');
                $ticket->getTreatments()->last()->setEndDate(new \DateTime("now", new DateTimeZone($_ENV['DATETIMEZONE'])));
                $ticket->setResult($request->request->get('closeReason'));
                $ticket->setResultDate(new \DateTime("now", new DateTimeZone($_ENV['DATETIMEZONE'])));
                $registry->persist($ticket);
                $registry->flush();
                $this->addFlash('success',"Ticket fermé avec succès ! Merci d'avoir pris le temps de le résoudre !");
            }
            return $this->redirectToRoute("app_main");
        }else{
            return $this->redirectToRoute("app_main");
        }
    }

    #[Route('/transfer/{id}/for/{service}', name: 'app_ticket_transfer')]
    public function transfer(Request $request, EntityManagerInterface $registry, $id, $service): Response
    {
        if(!$this->getUser()) $this->redirectToRoute("app_login");
        $user = $registry->getRepository(User::class)->findOneBy(['username'=>$this->getUser()->getUserIdentifier()]);

        $ticket = $registry->getRepository(Ticket::class)->find($id);
        $service = $registry->getRepository(Service::class)->find($service);
        if($ticket == null) return $this->redirectToRoute("app_main");
        if($service == null) return $this->redirectToRoute("app_main");
        if($ticket->getService()->getId() == $service->getId()) return $this->redirectToRoute("app_main");
        if($ticket->getService()->getId() == $user->getService()->getId()) {
            $ticket->setService($service);
            $ticket->getTreatments()->last()->setStatus("TRANSFÉRÉ // EN ATTENTE");
            $registry->persist($ticket);
            $registry->flush();
            $this->addFlash('success',"Ticket transféré avec succès au service ".$service->getName());
            return $this->redirectToRoute("app_main");
        }else{
            return $this->redirectToRoute("app_main");
        }
    }
}