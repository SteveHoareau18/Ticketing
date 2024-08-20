<?php

namespace App\Controller;

use App\Entity\MailConfiguration;
use App\Entity\Service;
use App\Entity\Ticket;
use App\Entity\Treatment;
use App\Entity\User;
use App\Service\MailService;
use App\Service\RandomPasswordService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
        set_time_limit(0);
        if (!$this->getUser()) return $this->redirectToRoute("app_login"); //Redirige l'utilisateur vers la page de connexion s'il n'est pas connecté
        $serviceLst = $managerRegistry->getRepository(Service::class)->findAll();//Sert pour le mode admin
        $status = $request->query->has('status') ? str_replace('_', ' ', $request->query->get('status')) : 'EN ATTENTE';//Système de filtre pour les tickets
        if ($status != "EN ATTENTE" && $status != "EN COURS" && $status != "Fermé") $status = "EN ATTENTE";
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
        set_time_limit(0);
        if (!$this->getUser()) return $this->redirectToRoute("app_login");
        $myTickets = $managerRegistry->getRepository(Ticket::class)->findBy(['creator' => $this->getUser()]);
        $status = $request->query->has('status') ? str_replace('_', ' ', $request->query->get('status')) : 'EN ATTENTE';
        if ($status != "EN ATTENTE" && $status != "EN COURS" && $status != "Fermé") $status = "EN ATTENTE";
        $dashboard = false;
        if (in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {//Permet d'afficher ou non le dashboard en administrateur
            $dashboard = true;
            if ($request->query->has('dashboard')) {
                $dashboard = $request->query->getBoolean('dashboard');
            }
        }
        return $this->render('ticket/see.my_tickets.html.twig', [
            'myTickets' => $myTickets,
            'status' => $status,
            'dashboard' => $dashboard
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     * @throws TransportExceptionInterface
     */
    #[Route('/mot-de-passe-oublie', name: 'app_lost_password')]
    public function lostPassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        set_time_limit(0);
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
     * @throws Exception
     */
    #[Route('/api/count-tickets/{service}/', name: 'app_api_count_tickets_service', methods: ['POST'])]//on tolère uniquement les requêtes POST
    public function apiCount(Request $request, EntityManagerInterface $manager, $service): JsonResponse
    {
        set_time_limit(0);
        if ($request->request->has('_csrf_token') && $this->isCsrfTokenValid('api-count' . $service, $request->request->get('_csrf_token'))) {//On vérifie la validité du jeton
            $conn = $manager->getConnection();
            $stmt = $conn->prepare('CALL count_tickets_service(:serviceId)');
            $result = $stmt->executeQuery(['serviceId' => $service])->fetchAssociative();
            return new JsonResponse([
                $result['in_waiting'],
                $result['in_progress'],
                $result['closed']]
            );
        }
        return new JsonResponse(array(500, "CSRF invalid"));
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param $user
     * @return JsonResponse
     * @throws Exception
     */
    #[Route('/api/count-tickets-user/{user}/', name: 'app_api_count_tickets_user', methods: ['POST'])]
    public function apiUser(Request $request, EntityManagerInterface $manager, $user): JsonResponse
    {
        set_time_limit(0);
        if ($request->request->has('_csrf_token') && $this->isCsrfTokenValid('api-count-user' . $user, $request->request->get('_csrf_token'))) {
            $conn = $manager->getConnection();
            $stmt = $conn->prepare('CALL count_tickets_user(:userId)');
            $result = $stmt->executeQuery(['userId' => $user])->fetchAssociative();
            return new JsonResponse([
                $result['n_open'],
                $result['n_create'],
                $result['n_close']
            ]);
        }
        return new JsonResponse(array(500, "CSRF invalid"));
    }
}
