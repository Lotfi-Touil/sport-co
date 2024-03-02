<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Service\PageAccessService;

#[Route('/platform/user')]
class UserController extends AbstractController
{
    private $pageAccessService;
    private $security;

    public function __construct(Security $security, PageAccessService $pageAccessService)
    {
        $this->pageAccessService = $pageAccessService;
        $this->security = $security;
    }

    #[Route('/', name: 'platform_user_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($authorizationChecker->isGranted("ROLE_ADMIN")) {
            $users = $userRepository->findAll();
        } else {
            $user = $this->security->getUser();
            $company = $user->getCompany();

            if ($company) {
                $users = $userRepository->findAllByCompanyId($company->getId());
            }
        }

        return $this->render('back/user/index.html.twig', [
            'users' => $users ?? [],
        ]);
    }

    #[Route('/new', name: 'platform_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher, AuthorizationCheckerInterface $authorizationChecker, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            if ($authorizationChecker->isGranted("ROLE_ADMIN")) {
                $role = $request->request->get('user_role', 'ROLE_USER');
            } else {
                $currentUser = $this->security->getUser();
                if ($currentUser instanceof User) {
                    $user->setCompany($currentUser->getCompany());
                }

                $role = "ROLE_EMPLOYE";
            }

            $user->setRoles([$role]);

            if (!in_array($role, ["ROLE_USER", "ROLE_EMPLOYE", "ROLE_COMPANY", "ROLE_ADMIN"])) {
                $this->addFlash('error', 'Erreur ! le role est invalide.');
                return $this->redirectToRoute('platform_invoice_index');
            }
 
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('platform_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_user_show', methods: ['GET'])]
    public function show(Request $request, User $user): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserPasswordHasherInterface $userPasswordHasher, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            if ($newPassword) {
                $hashedPassword = $userPasswordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }
    
            $this->addFlash('success', 'Enregistré avec succès.');

            $entityManager->flush();

            return $this->redirectToRoute('platform_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('platform_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
