<?php

namespace App\Controller;

use App\Entity\MailConfiguration;
use App\Entity\Relance;
use App\Entity\Service;
use App\Entity\Ticket;
use App\Entity\Treatment;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/historic')]
class HistoricController extends AbstractController
{
    #[Route('/by-service/{service}', name: 'app_historic_by_service')]
    public function historicByService(EntityManagerInterface $registry, $service): Response
    {
        if ($registry->getRepository(Service::class)->find($service) == null) return $this->redirectToRoute("app_main");
        $service = $registry->getRepository(Service::class)->find($service);


        return $this->render('admin/tickets/historic_by_service.html.twig', [
            "service" => $service
        ]);
    }

    #[Route('/by-service/{service}/see/{id}', name: 'app_historic_by_service_see_ticket')]
    public function historicByServiceSeeTicket(EntityManagerInterface $registry, $service, $id): Response
    {
        if ($registry->getRepository(Service::class)->find($service) == null) return $this->redirectToRoute("app_main");
        if ($registry->getRepository(Ticket::class)->find($id) == null) return $this->redirectToRoute('app_main');
        $service = $registry->getRepository(Service::class)->find($service);
        $ticket = $registry->getRepository(Ticket::class)->find($id);

        return $this->render('admin/tickets/historic_see_ticket.html.twig', [
            "service" => $service,
            'ticket' => $ticket
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    #[Route('/by-service/{service}/relance/{id}/treatment/{treatment}', name: 'app_historic_by_service_relance_treatment_for_ticket')]
    public function historicRelanceTreatmentForTicket(Request $request, EntityManagerInterface $registry, $service, $id, $treatment): Response
    {
        if ($registry->getRepository(Service::class)->find($service) == null) return $this->redirectToRoute("app_main");
        if ($registry->getRepository(Ticket::class)->find($id) == null) return $this->redirectToRoute('app_main');
        if ($registry->getRepository(Treatment::class)->find($treatment) == null) return $this->redirectToRoute("app_main");
        $treatment = $registry->getRepository(Treatment::class)->find($treatment);
        if ($request->request->has("_csrf_token") && $request->request->has('relanceReason') && $this->isCsrfTokenValid('relance-treatment' . $treatment->getId(), $request->request->get("_csrf_token"))) {
            $service = $registry->getRepository(Service::class)->find($service);
            $ticket = $registry->getRepository(Ticket::class)->find($id);

            $mailConfiguration = $registry->getRepository(MailConfiguration::class)->findAll()[0];
            if ($mailConfiguration != null) {
                $mailService = new MailService($mailConfiguration);
                $relance = new Relance();
                $relance->setTreatment($treatment)->
                setUser($this->getUser())->
                setReason($request->request->get('relanceReason'))->
                setRelanceDate(new \DateTime("now", new \DateTimeZone($_ENV['DATETIMEZONE'])));
                if ($request->request->has('reopen')) {
                    $relance->setReopen(true);
                    $newTreatment = (new Treatment())->
                    setTicket($ticket)->
                    setUser($this->getUser())->
                    setStartDate(new \DateTime("now", new \DateTimeZone($_ENV['DATETIMEZONE'])))->
                    setStatus("Ré-ouvert (admin)")->
                    setObservations($relance->getReason())->
                    setEndDate(null);

                    $registry->persist($newTreatment);
                    $registry->flush();
                    $ticket->addTreatment($newTreatment)->setResultDate(null)->setResult("");
                    $registry->persist($ticket);
                    $registry->flush();
                }

                $registry->persist($relance);
                $registry->flush();
                $email = (new Email())
                    ->from(new Address($mailConfiguration->getLogin(), $mailConfiguration->getSubject()))
                    ->to($treatment->getUser()->getEmail())
                    ->subject('Votre traitement a eu une relance // PLATEFORME TICKETING')
                    ->html($this->renderView('admin/tickets/email.historic_relance_ticket_treatment.twig', ['treatment' => $treatment, 'ticket' => $ticket, 'relance' => $relance]));
                if ($request->request->has('email')) {
                    $email->addTo($request->request->get('email'));
                }
                foreach (explode(',', $mailConfiguration->getCcAddress()) as $address) {
                    $email->addCc(new Address(trim($address)));
                }

                $mailService->getMailer()->send($email);
                $this->addFlash("success", "Une relance a été crée et envoyé par mail (Référence TR°" . $treatment->getId() . " T°" . $treatment->getTicket()->getId() . ") à l'utilisateur " . $treatment->getUser()->getFirstname() . " " . $treatment->getUser()->getName());
                if ($request->request->has('email')) {
                    $this->addFlash("success", "Elle a aussi été envoyée par mail (Référence TR°" . $treatment->getId() . " T°" . $treatment->getTicket()->getId() . ") à " . $request->request->get('email'));

                }
                return $this->redirectToRoute('app_historic_by_service', ['service' => $service->getId()]);
            } else {
                $this->addFlash('fail', 'Une erreur est suvenue...');
            }
        }
        return $this->redirectToRoute("app_main");
    }
}
