<?php

namespace App\Controller\Back;

use App\Service\PageAccessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/platform')]
class MainController extends AbstractController
{
    private $pageAccessService;

    public function __construct(PageAccessService $pageAccessService)
    {
        $this->pageAccessService = $pageAccessService;
    }

    #[Route('/', name: 'platform_index')]
    public function index(Request $request): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/main/index.html.twig', []);
    }
}
