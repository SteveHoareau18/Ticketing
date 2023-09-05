<?php

namespace App\Controller;

use App\Entity\MailConfiguration;
use App\Entity\Service;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\RandomPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\ByteString;

#[IsGranted("ROLE_ADMIN")]
#[Route('/admin/gestion/utilisateur')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user')]
    public function index(UserRepository $repository): Response
    {
        $users = $repository->findAll();
        $usersArr = array();
        foreach ($users as $user) {
            if(!in_array("ROLE_ADMIN",$user->getRoles())) array_push($usersArr, $user);
        }
        return $this->render('admin/users/list.html.twig', [
            'users'=>$usersArr
        ]);
    }

    /**
     * @throws NotSupported
     * @throws ORMException
     */
    #[Route('/new', name: 'app_user_new')]
    public function new(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if($manager->getRepository(User::class)->findBy(['username'=>$user->getUsername()])){
                $this->addFlash("fail","Ce nom n'utilisateur est déjà pris...");
            }else {
                $service = $manager->getRepository(Service::class)->find($form->get("service")->getData());
                if ($service == null) {
                    $this->addFlash("fail", "Ce service n'existe pas...");
                } else {
                    $user->setRoles(array("ROLE_USER"));
                    $user->setService($service);
                    $user->setActive(false);
                    $password = (new RandomPasswordService())->getRandomStrenghPassword();
                    $mailConfiguration = $manager->getRepository(MailConfiguration::class)->findAll()[0];
                    if($mailConfiguration != null) {
                        $user->setPassword($hasher->hashPassword($user, $password));
                        if(!$user->isActive())$user->setActive(true);
                        $manager->persist($user);
                        $manager->flush();
                        $this->addFlash("success", "Paramètre enregistré.");

                        $mailService = new MailService($mailConfiguration);
                        $email = (new Email())
                            ->from(new Address($mailConfiguration->getLogin(), $mailConfiguration->getSubject()))
                            ->to($user->getEmail())
                            ->subject('Creation de votre compte // PLATEFORME TICKETING')
                            ->html("<p>Bonjour, votre compte a été crée avec l'identifiant ".$user->getUsername()." et le mot de passe: ".$password.'</p><p>Celui-ci doit rester confidentiel !</p>');

                        foreach (explode(',', $mailConfiguration->getCcAddress()) as $address) {
                            $email->addCc(new Address(trim($address)));
                        }

                        $mailService->getMailer()->send($email);
                        $this->addFlash("success", "Utilisateur " . $user->getUsername() . " crée avec succès. Le mot de passe lui a été envoyé par mail");
                        return $this->redirectToRoute("app_user");
                    }else {
                        $this->addFlash("fail", "Une erreur est intervenue...");
                    }
                }
            }
        }
        $serviceLst = $manager->getRepository(Service::class)->findAll();
        return $this->render("admin/users/new.html.twig",[
            "form"=>$form,
            'serviceLst'=>$serviceLst
        ]);
    }

    #[Route('/see/{username}', name: 'app_user_see')]
    public function see(EntityManagerInterface $manager, $username): Response
    {
        $user = $manager->getRepository(User::class)->findOneBy(['username'=>$username]);
        if($user==null){
            return $this->redirectToRoute("app_user");
        }
        return $this->render("admin/users/see.html.twig",[
            "user"=>$user,
        ]);
    }

    #[Route('/edit/{username}', name: 'app_user_edit')]
    public function edit(Request $request, EntityManagerInterface $manager, $username): Response
    {
        $user = $manager->getRepository(User::class)->findOneBy(['username'=>$username]);
        if($user==null){
            return $this->redirectToRoute("app_user");
        }
        $form = $this->createForm(UserType::class, $user);
        $form->get("service")->setData($user->getService()->getId());
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $service = $manager->getRepository(Service::class)->find($form->get("service")->getData());
            if ($service == null) {
                $this->addFlash("fail", "Ce service n'existe pas...");
            } else {
                $user->setService($service);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash("success", "Utilisateur " . $user->getUsername() . " modifié avec succès.");
                return $this->redirectToRoute("app_user_see",['username'=>$username]);

            }
        }
        $serviceLst = $manager->getRepository(Service::class)->findAll();
        return $this->render("admin/users/edit.html.twig",[
            "form"=>$form,
            "user"=>$user,
            'serviceLst'=>$serviceLst
        ]);
    }

    #[Route('/delete/{username}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $manager, $username): Response
    {
        $user = $manager->getRepository(User::class)->findOneBy(['username'=>$username]);
        if($user!=null) {
            if ($request->request->has("_csrf_token") && $this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_csrf_token'))) {
                $manager->remove($user);
                $manager->flush();
                $this->addFlash("success", "Utilisateur " . $user->getUsername() . " supprimé avec succès.");
            }
        }
        return $this->redirectToRoute("app_user");
    }
}
