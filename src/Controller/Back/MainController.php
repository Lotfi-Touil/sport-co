<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/platform')]
class MainController extends AbstractController
{
    #[Route('/', name: 'platform_index')]
    public function index(): Response
    {
        return $this->render('back/main/index.html.twig', []);
    }
}
