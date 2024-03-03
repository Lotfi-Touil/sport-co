<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\PaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentMethodController extends AbstractController
{
    #[Route('/payment-methods', name: 'payment_methods')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $paymentMethods = $entityManager->getRepository(PaymentMethod::class)->findAll();

        $data = array_map(function ($method) {
            return [
                'id' => $method->getId(),
                'name' => $method->getName(),
            ];
        }, $paymentMethods);

        return new JsonResponse($data);
    }
}
