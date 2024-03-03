<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    #[Route('/platform/profil', name: 'app_back_profil')]
    public function index(): Response
    {
        return $this->render('back/profil/index.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }
}
