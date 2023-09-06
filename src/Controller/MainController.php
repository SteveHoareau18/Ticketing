<?php

namespace App\Controller;

use App\Entity\MailConfiguration;
use App\Entity\Service;
use App\Entity\Ticket;
use App\Entity\Treatment;
use App\Entity\User;
use App\Service\MailService;
use App\Service\RandomPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 *
 */
class MainController extends AbstractController
{

    /**
     * Route principale -> Vérifie si on est connecté et redirige l'utilisateur vers la vue
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @return Response
     */
    #[Route('/', name: 'app_main')]
    public function index(Request $request, ManagerRegistry $managerRegistry): Response
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login"); //Redirige l'utilisateur vers la page de connexion s'il n'est pas connecté
        $serviceLst = $managerRegistry->getRepository(Service::class)->findAll();//Sert pour le mode admin
        $status = $request->query->has('status') ? str_replace('_', ' ', $request->query->get('status')) : 'EN ATTENTE';//Système de filtre pour les tickets
        $dashboard = false;
        if (in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {//Permet d'afficher ou non le dashboard en administrateur
            $dashboard = true;
            if ($request->query->has('dashboard')) {
                $dashboard = $request->query->getBoolean('dashboard');
            }
        }
        return $this->render('index.html.twig', [
            'serviceLst' => $serviceLst,
            'status' => $status,
            'dashboard' => $dashboard
        ]);//On retourne la vue a afficher
    }

    /**
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @return Response
     */
    #[Route('/mes-tickets', name: 'app_main_my_tickets')]
    public function myTickets(Request $request, ManagerRegistry $managerRegistry): Response
    {
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        $myTickets = $managerRegistry->getRepository(Ticket::class)->findBy(['creator' => $this->getUser()]);
        $status = $request->query->has('status') ? str_replace('_', ' ', $request->query->get('status')) : 'EN ATTENTE';
        return $this->render('my_tickets.html.twig', [
            'myTickets' => $myTickets,
            'status' => $status
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    #[Route('/mot-de-passe-oublie', name: 'app_lost_password')]
    public function lostPassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        if ($this->getUser()) return $this->redirectToRoute("app_main");
        if ($request->request->has('inputEmail') && $request->request->has('_csrf_token') && $this->isCsrfTokenValid('lost-password', $request->request->get('_csrf_token'))) {
            $user = $manager->getRepository(User::class)->findOneBy(['email' => $request->request->get('inputEmail')]);
            if ($user != null) {
                $password = (new RandomPasswordService())->getRandomStrenghPassword();
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
                        ->html("<p>Bonjour, votre mot de passe a été réinitialisé avec l'identifiant " . $user->getUsername() . " et le mot de passe: " . $password . '</p><p>Celui-ci doit rester confidentiel !</p>');

                    foreach (explode(',', $mailConfiguration->getCcAddress()) as $address) {
                        $email->addCc(new Address(trim($address)));
                    }

                    $mailService->getMailer()->send($email);
                    $this->addFlash("success", "Votre nouveau mot de passe vous a été envoyé par mail");
                    return $this->redirectToRoute("app_login");
                } else {
                    $this->addFlash("fail", "Une erreur est intervenue...");
                }
            } else {
                $this->addFlash('fail', "Cet email ne correspond à aucun compte...");
            }
        }
        return $this->render('security/lost_password.html.twig', [

        ]);
    }

//    #[Route('/api/count-tickets/{service}/', name: 'app_api_count_tickets_service', methods: ['POST','GET'])] -> pour tester

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param $service
     * @return JsonResponse
     */
    #[Route('/api/count-tickets/{service}/', name: 'app_api_count_tickets_service', methods: ['POST'])]//on tolère uniquement les requêtes POST
    public function apiCount(Request $request, EntityManagerInterface $manager, $service): JsonResponse
    {
        if ($request->request->has('_csrf_token') && $this->isCsrfTokenValid('api-count' . $service, $request->request->get('_csrf_token'))) {//On vérifie la validité du jeton
            $service = $manager->getRepository(Service::class)->find($service);
            if ($service != null) {
                $nClose = 0;
                $nInProgress = 0;
                $nWaiting = 0;
                $tickets = $manager->getRepository(Ticket::class)->findBy(['service'=>$service]);
                foreach ($tickets as $ticket){
                    //on récupère les tickets qui sont fermés (ayant une date de résultat) ou dont il y a traitements et que le status du dernier traitement est fermé
//                    dd($ticket->getTreatments(),sizeof($ticket->getTreatments()));
                    if(sizeof($ticket->getTreatments()) == 0) {
                        $nWaiting += 1;
                    }else if($ticket->getTreatments()->last()->getStatus() == "EN ATTENTE" || str_contains($ticket->getTreatments()->last()->getStatus(), "EN ATTENTE")) {
                        //les tickets qui n'ont pas encore de traitement sont par défaut en attente, on traite le comptage de ces tickets ci-dessous
                        $nWaiting += 1;
                    }else if($ticket->getTreatments()->last()->getStatus() == "EN COURS"){
                        $nInProgress += 1;
                    }else if($ticket->getResultDate()!=null || $ticket->getTreatments()->last()->getStatus() == "Fermé"){
                        $nClose+=1;
                    }
                }

                dump(array($nWaiting, $nInProgress, $nClose));
                return new JsonResponse(array($nWaiting, $nInProgress, $nClose));//on retourne un JsonResponse d'un array de chaque compteur
            }
        }
        return new JsonResponse(500);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param $user
     * @return JsonResponse
     */
    #[Route('/api/count-tickets-user/{user}/', name: 'app_api_count_tickets_user', methods: ['POST'])]
    public function apiUser(Request $request, EntityManagerInterface $manager, $user): JsonResponse
    {
        if ($request->request->has('_csrf_token') && $this->isCsrfTokenValid('api-count-user' . $user, $request->request->get('_csrf_token'))) {
            $user = $manager->getRepository(User::class)->find($user);
            if ($user != null) {
                $treatments = $manager->getRepository(Treatment::class)->findAll();
                $nOpen = 0;
                foreach ($treatments as $treatment) {
                    if ($treatment->getUser()->getId() == $user->getId()) $nOpen += 1;
                }

                $nCreate = 0;
                foreach ($manager->getRepository(Ticket::class)->findAll() as $ticket) {
                    if ($ticket->getCreator()->getId() == $user->getId()) $nCreate += 1;
                }

                $nClose = 0;
                foreach ($treatments as $treatment) {
                    if (($treatment->getEndDate() == $treatment->getTicket()->getResultDate()) && $treatment->getUser()->getId() == $user->getId()) $nClose += 1;
                }
                return new JsonResponse(array($nOpen, $nCreate, $nClose));
            }
        }
        return new JsonResponse(500);
    }
}
