<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\UserEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilController extends AbstractController
{
    #[Route('/platform/profil', name: 'app_back_profil')]
    public function profil(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {   
        $user = $this->getUser();
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
                if ($newPassword) {
                    $hashedPassword = $userPasswordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('platform_dashboard');
        }

        // Rendre la vue avec le formulaire et les informations utilisateur
        return $this->render('back/profil/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
