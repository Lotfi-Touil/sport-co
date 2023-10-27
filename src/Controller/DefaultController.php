<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_index')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    #[Route('/say-my-name/{name}', name: 'default_name', requirements: ['say-my-name' => '\w{1,10}'], defaults: ['name' => 'PRENOM'])]
    public function name($name): Response
    {
        return $this->render('default/say-my-name.html.twig', [
            'controller_name' => $name,
        ]);
    }
}
