<?php

namespace App\Service;

use App\Repository\PageAccessRepository;
use App\Repository\PageRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PageAccessService
{
    private $security;
    private $pageRepository;
    private $pageAccessRepository;
    private $authorizationChecker;

    public function __construct(Security $security, AuthorizationCheckerInterface $authorizationChecker, PageRepository $pageRepository, PageAccessRepository $pageAccessRepository)
    {
        $this->security = $security;
        $this->pageRepository = $pageRepository;
        $this->pageAccessRepository = $pageAccessRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function checkAccess($routeName)
    {
        if (!$this->canAccess($routeName)) {
            throw new AccessDeniedHttpException('Accès refusé.');
        }
    }

    private function canAccess(string $routeName): bool
    {
        if ($this->authorizationChecker->isGranted("ROLE_COMPANY")) {
            return true;
        }

        if($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            return true;
        }

        $user = $this->security->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        $page = $this->pageRepository->findOneBy(['path' => $routeName]);
        if (!$page) {
            // Si la page n'existe pas, on autorise
            return true;
        }

        // Vérifiez les droits d'accès pour cette page et cet utilisateur
        $access = $this->pageAccessRepository->findOneBy([
            'page' => $page,
            'employe' => $user
        ]);

        if (!$access) {
            // Si aucune règle d'accès, on autorise
            return true;
        }

        return $access->getCanAccess();
    }
}