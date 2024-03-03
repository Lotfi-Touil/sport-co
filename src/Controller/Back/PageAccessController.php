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
use Symfony\Component\HttpFoundation\Request;

#[Route('/platform/page-access')]
class PageAccessController extends AbstractController
{
    private $pageAccessService;

    public function __construct(PageAccessService $pageAccessService)
    {
        $this->pageAccessService = $pageAccessService;
    }

    #[Route('/{id}', name: 'platform_page_access_edit')]
    public function editAccess(User $user, PageRepository $pageRepository, PageAccessRepository $pageAccessRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $pages = $pageRepository->findAll();
        $existingPermissions = $pageAccessRepository->findPermissionsByUser($user);

        $permissionsMap = [];
        foreach ($existingPermissions as $permission) {
            $permissionsMap[$permission->getPage()->getId()] = $permission->getCanAccess();
        }

        if ($request->isMethod('POST')) {
            $submittedPermissions = $request->get('permissions');

            foreach ($submittedPermissions as $pageId => $canAccess) {
                // Check if there is an existing permission for the page
                $permission = $pageAccessRepository->findOneBy(['page' => $pageId, 'employe' => $user->getId()]);
                if (!$permission) {
                    // Create a new permission if it doesn't exist
                    $permission = new PageAccess();
                    $permission->setPage($pageRepository->find($pageId));
                    $permission->setEmploye($user);
                }
                // Update the canAccess value
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
}
