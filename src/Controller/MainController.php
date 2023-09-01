<?php

namespace App\Controller;

use App\Entity\MailConfiguration;
use App\Entity\Service;
use App\Entity\Ticket;
use App\Entity\Treatment;
use App\Entity\User;
use App\Service\MailService;
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
use Symfony\Component\String\ByteString;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(ManagerRegistry $managerRegistry): Response
    {
        if(!$this->getUser()) return $this->redirectToRoute("app_login");
        $user = $managerRegistry->getRepository(User::class)->findOneBy(['username'=>$this->getUser()->getUserIdentifier()]);
        //nombre de ticket pour le service qui a comme dernier status 'en cours'
//        $tickets = array();
        $serviceLst = $managerRegistry->getRepository(Service::class)->findAll();
        return $this->render('index.html.twig', [
            'serviceLst'=>$serviceLst,
//            'tickets'=>$tickets
        ]);
    }

    #[Route('/mot-de-passe-oublie', name: 'app_lost_password')]
    public function lostPassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        if($this->getUser()) return $this->redirectToRoute("app_main");
        if($request->request->has('inputEmail') && $request->request->has('_csrf_token') && $this->isCsrfTokenValid('lost-password',$request->request->get('_csrf_token'))){
            $user = $manager->getRepository(User::class)->findOneBy(['email'=>$request->request->get('inputEmail')]);
            if($user != null){
                $password =  ByteString::fromRandom(8, implode('', range('A', 'Z')))->toString(); // uppercase letters only (e.g: sponsor code)
                $password .= ByteString::fromRandom(4, '0123456789')->toString();
                $mailConfiguration = $manager->getRepository(MailConfiguration::class)->findAll()[0];
                if($mailConfiguration != null) {
                    $user->setPassword($hasher->hashPassword($user, $password));
                    if(!$user->isActive())$user->setActive(true);
                    $manager->persist($user);
                    $manager->flush();

                    $mailService = new MailService($mailConfiguration);
                    $email = (new Email())
                        ->from(new Address($mailConfiguration->getLogin(), $mailConfiguration->getSubject()))
                        ->to($user->getEmail())
                        ->subject('Un parametre a ete change dans votre compte // PLATEFORME TICKETING')
                        ->html("<p>Bonjour, votre mot de passe a été réinitialisé avec l'identifiant ".$user->getUsername()." et le mot de passe ".$password.'</p><p>Celui-ci doit rester confidentiel !</p>');

                    foreach (explode(',', $mailConfiguration->getCcAddress()) as $address) {
                        $email->addCc(new Address(trim($address)));
                    }

                    $mailService->getMailer()->send($email);
                    $this->addFlash("success", "Votre nouveau mot de passe vous a été envoyé par mail");
                    return $this->redirectToRoute("app_login");
                }else {
                    $this->addFlash("fail", "Une erreur est intervenue...");
                }
            }else{
                $this->addFlash('fail',"Cet email ne correspond à aucun compte...");
            }
        }
        return $this->render('security/lost_password.html.twig', [

        ]);
    }

    #[Route('/api/count-tickets/{service}/', name: 'app_api_count_tickets_service', methods: ['POST'])]
    public function apiCount(Request $request, EntityManagerInterface $manager,$service): JsonResponse
    {
        if($request->request->has('_csrf_token')&&$this->isCsrfTokenValid('api-count'.$service,$request->request->get('_csrf_token'))){
            $service = $manager->getRepository(Service::class)->find($service);
            if($service != null){
               $treatments = $manager->getRepository(Treatment::class)->findBy(['status'=>'Fermé']);
               $nClose = 0;
               foreach ($treatments as $treatment){
                   if($treatment->getTicket()->getService()->getId() == $service->getId()) $nClose+=1;
               }

               $treatments = $manager->getRepository(Treatment::class)->findBy(['status'=>'EN COURS']);
               $nInProgress = 0;
               foreach ($treatments as $treatment){
                   if($treatment->getTicket()->getService()->getId() == $service->getId()) $nInProgress+=1;
               }

               $treatments = $manager->getRepository(Treatment::class)->findBy(['status'=>'EN ATTENTE']);
               $nWaiting = 0;
               foreach ($treatments as $treatment){
                   if($treatment->getTicket()->getService()->getId() == $service->getId()) $nWaiting+=1;
               }
               if($nWaiting == 0){
                   $tickets = $manager->getRepository(Ticket::class)->findBy(['service'=>$service]);
                   foreach ($tickets as $ticket){
                       if(sizeof($ticket->getTreatments())==0||sizeof($manager->getRepository(Treatment::class)->findBy(['status'=>'EN ATTENTE', 'ticket'=>$ticket]))==0)$nWaiting+=1;
                   }
               }
              return new JsonResponse(array($nWaiting,$nInProgress,$nClose));
            }
        }
        return new JsonResponse(500);
    }
}
