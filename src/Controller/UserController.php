<?php

namespace App\Controller;

use App\Form\UserGeneralFormType;
use App\Form\UserSecurityFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em)
    {
        
    }

    #[Route(path: '/profile', name: 'profile.index')]
    public function profile(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {

        /** @var User */
        $user = $this->getUser();
        $route = $request->headers->get('referer');

        $generalForm = $this->createForm(UserGeneralFormType::class, $user);
        $securityForm = $this->createForm(UserSecurityFormType::class, null);

        /** General form */
        $generalForm->handleRequest($request);
        if($generalForm->isSubmitted() && $generalForm->isValid()){
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', 'You\'re profile has been updated');
            return $this->redirect($route);
        }

        /** Security form */
        $securityForm->handleRequest($request);
        if($securityForm->isSubmitted() && $securityForm->isValid()){

            $datas = $securityForm->getData();
            
            $actualPassword = $datas['actualPassword'];
            $newPassword = $datas['newPassword'];

            if(!$passwordHasher->isPasswordValid($user, $actualPassword)){
                $this->addFlash('danger', 'Your actual password is not correct');
                
                return $this->redirect($this->generateUrl('profile.index', ['tab' => 'security']));

            }

            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

            $this->em->persist($user);
            $this->em->flush();
            return $this->redirect($route);
        }

        return $this->render('profile/index.html.twig', [
            'generalForm' => $generalForm->createView(),
            'securityForm' => $securityForm->createView(),
            'tab' => $request->query->get('tab') ?? "general",
        ]);
    }
}
