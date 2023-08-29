<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
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
                    $password =  ByteString::fromRandom(8, implode('', range('A', 'Z')))->toString(); // uppercase letters only (e.g: sponsor code)
                    $password .= ByteString::fromRandom(4, '0123456789')->toString();
                    $user->setPassword($hasher->hashPassword($user, $password));
                    $manager->persist($user);
                    $manager->flush();
                    $this->addFlash("success", "Utilisateur " . $user->getUsername() . " crée avec succès avec le mot de passe par défaut : ".$password);
                    return $this->redirectToRoute("app_user");
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
