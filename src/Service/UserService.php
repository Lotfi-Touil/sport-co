<?php

// src/Service/UserService.php

namespace App\Service;

use App\Entity\Company;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class UserService
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getCurrentUserCompany(): ?Company
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \LogicException('No user found');
        }

        $company = $user->getCompany();
        if (!$company) {
            throw new AccessDeniedException('L\'utilisateur n\'est associé à aucune entreprise.');
        }

        return $company;
    }
}
