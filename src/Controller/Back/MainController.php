<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/panel', name: 'panel_index')]
    public function index(): Response
    {
        return $this->render('back/main/index.html.twig', []);
    }
}
