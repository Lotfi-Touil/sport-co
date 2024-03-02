<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Service\PageAccessService;
use Symfony\Bundle\SecurityBundle\Security;

class AccessExtension extends AbstractExtension
{
    private $pageAccessService;
    private $security;

    public function __construct(PageAccessService $pageAccessService, Security $security)
    {
        $this->pageAccessService = $pageAccessService;
        $this->security = $security;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('can_access', [$this, 'canAccess']),
        ];
    }

    public function canAccess(string $routeName): bool
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return false;
        }

        return $this->pageAccessService->canAccess($routeName);
    }
}
