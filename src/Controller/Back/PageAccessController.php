<?php

namespace App\Controller\Back;

use App\Entity\PageAccess;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Repository\PageRepository;
use App\Repository\PageAccessRepository;
use App\Service\PageAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/platform/page-access')]
class PageAccessController extends AbstractController
{
    private $pageAccessService;
    private $authorizationChecker;
    private $security;

    public function __construct(PageAccessService $pageAccessService, AuthorizationCheckerInterface $authorizationChecker, Security $security)
    {
        $this->pageAccessService = $pageAccessService;
        $this->authorizationChecker = $authorizationChecker;
        $this->security = $security;
    }

    #[Route('/{id}', name: 'platform_page_access_edit')]
    public function editAccess(User $user, PageRepository $pageRepository, PageAccessRepository $pageAccessRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));
        $response = $this->checkConfidentiality($user);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

        $pages = $pageRepository->findAll();
        $existingPermissions = $pageAccessRepository->findPermissionsByUser($user);

        $permissionsMap = [];
        foreach ($existingPermissions as $permission) {
            $permissionsMap[$permission->getPage()->getId()] = $permission->getCanAccess();
        }

        if ($request->isMethod('POST')) {
            $submittedPermissions = $request->get('permissions');

            foreach ($submittedPermissions as $pageId => $canAccess) {
                $permission = $pageAccessRepository->findOneBy(['page' => $pageId, 'employe' => $user->getId()]);
                if (!$permission) {
                    $permission = new PageAccess();
                    $permission->setPage($pageRepository->find($pageId));
                    $permission->setEmploye($user);
                }
                $permission->setCanAccess($canAccess);
                $entityManager->persist($permission);
            }

            $entityManager->flush();
            return $this->redirectToRoute('platform_user_index');
        }

        return $this->render('back/page_access/edit.html.twig', [
            'user' => $user,
            'pages' => $pages,
            'permissionsMap' => $permissionsMap,
        ]);
    }

    private function checkConfidentiality(User $user): ?Response
    {
        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            return null; // L'admin a accès à tout, donc pas de redirection
        }
    
        if ($this->security->getUser()->getCompany() == $user->getCompany()) {
            return null; // L'utilisateur a le droit d'accéder à cette ressource
        }
    
        // L'utilisateur n'a pas le droit d'accéder à cette ressource
        $this->addFlash('error', "Accès non autorisé à la ressource demandée.");
        return new RedirectResponse($this->generateUrl('platform_user_index'));
    }
}
