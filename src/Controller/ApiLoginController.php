<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiLoginController extends AbstractController
  {
      #[Route('/api/login', name: 'api_login')]

     public function index(#[CurrentUser] ?User $user): Response
      {
        if(!$user){
            return $this->json([
                'error' => "Vous n'étes pas co"
            ]);
        };
        //  if (null === $user) {
        //      return $this->json([
        //          'message' =>$user,
        //      ], Response::HTTP_UNAUTHORIZED);
        //  }

         $token ="salut"; // somehow create an API token for $user

          return $this->json([
             
             'user'  => $user->getUserIdentifier(),
             'token' => $user,
          ]);
      }
  }