<?php

namespace App\Controller\Back;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use App\Entity\Payment;
use App\Entity\Invoice;
use App\Entity\PaymentStatus;
use App\Entity\PaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/platform')]
class PaymentController extends AbstractController
{
    private $stripeClient;
    private $entityManager;
    private $stripeSecretKey;
    private $stripeWebhookSecret;
    private $authorizationChecker;

    public function __construct(string $stripeSecretKey, string $stripeWebhookSecret,  EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->stripeSecretKey = $stripeSecretKey;
        $this->stripeWebhookSecret = $stripeWebhookSecret;
        $this->stripeClient = new StripeClient($stripeSecretKey);
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    // #[Route('/payment/create/{invoiceId}', name: 'payment_create')]
    // public function create(Request $request, int $invoiceId): Response
    // {
    //     // Vérifie que l'utilisateur est bien une entreprise ou un admin
    //     if (
    //         !$this->authorizationChecker->isGranted('ROLE_COMPANY') &&
    //         !$this->authorizationChecker->isGranted('ROLE_ADMIN')
    //     ) {
    //         throw new AccessDeniedException('Accès refusé.');
    //     }

    //     try {
    //         $invoice = $this->entityManager->getRepository(Invoice::class)->find($invoiceId);
    //         if (!$invoice) {
    //             throw new \Exception("Facture non trouvée");
    //         }

    //         // Vérifie si un paiement n'a pas déjà été initié pour cette facture
    //         $existingPayment = $this->entityManager->getRepository(Payment::class)->findOneBy(['invoice' => $invoice]);
    //         if ($existingPayment) {
    //             throw new \Exception("Un paiement a déjà été initié pour cette facture.");
    //         }

    //         $amount = $invoice->getTotalAmount();

    //         $paymentIntent = $this->stripeClient->paymentIntents->create([
    //             'amount' => $amount * 100, // Convertit le montant en centimes
    //             'currency' => 'eur',
    //             'payment_method_types' => ['card'],
    //         ]);

    //         $payment = new Payment();
    //         $payment->setAmount($amount);
    //         $payment->setInvoice($invoice);
    //         $payment->setStripePaymentIntentId($paymentIntent->id); // Stocke l'ID du PaymentIntent
    //         $defaultPaymentStatus = $this->entityManager->getRepository(PaymentStatus::class)->findOneBy(['name' => 'En attente']);
    //         if (!$defaultPaymentStatus) {
    //             throw new \Exception("Statut de paiement par défaut introuvable.");
    //         }

    //         $payment->setPaymentStatus($defaultPaymentStatus);
    //         $defaultPaymentMethod = $this->entityManager->getRepository(PaymentMethod::class)->findOneBy(['name' => 'Carte de crédit']);
    //         if (!$defaultPaymentMethod) {
    //             throw new \Exception("Méthode de paiement par défaut introuvable.");
    //         }
    //         $payment->setPaymentMethod($defaultPaymentMethod);
    //         $this->entityManager->persist($payment);
    //         $this->entityManager->flush();

    //         // TODO : Rediriger vers la page de paiement ou le tableau de bord des paiements
    //         return $this->json(['clientSecret' => $paymentIntent->client_secret]);
    //     } catch (\Exception $e) {
    //         return new Response('Erreur: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
    //     }
    // }

    #[Route('/payment/create/{invoiceId}', name: 'payment_create')]
    public function create(Request $request, int $invoiceId): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_COMPANY') &&
            !$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Accès refusé.');
        }
    
        try {
            $invoice = $this->entityManager->getRepository(Invoice::class)->find($invoiceId);
            if (!$invoice) {
                throw new \Exception("Facture non trouvée");
            }

            // Récupérer le client lié à la facture
            $customer = $invoice->getCustomer();
            if (!$customer) {
                throw new \Exception("Client non trouvé pour cette facture.");
            }
    
            $existingPayment = $this->entityManager->getRepository(Payment::class)->findOneBy(['invoice' => $invoice]);
            if ($existingPayment) {
                throw new \Exception("Un paiement a déjà été initié pour cette facture.");
            }
    
            $amount = $invoice->getTotalAmount();
            $paymentType = $request->request->get('payment_type', 'unique');
            $isRecurring = $paymentType === 'recurring';
    
            $payment = new Payment();
            $payment->setAmount($amount);
            $payment->setInvoice($invoice);
            $payment->setIsRecurring($isRecurring);
    
            if (!$isRecurring) {
                $paymentIntent = $this->stripeClient->paymentIntents->create([
                    'amount' => $amount * 100,
                    'currency' => 'eur',
                    'payment_method_types' => ['card'],
                ]);
                $payment->setStripePaymentIntentId($paymentIntent->id);
            } else {
                $stripeCustomer = $this->stripeClient->customers->create([
                    'email' => $customer->getEmail(),
                    'name' => $customer->getFirstName() . ' ' . $customer->getLastName(),
                ]);

                $subscription = $this->stripeClient->subscriptions->create([
                    'customer' => $stripeCustomer->id,
                    // Todo corriger pour implementer la logique de création de produit et de plan
                    'items' => [['price' => 'price_id']],
                ]);
                $payment->setStripeSubscriptionId($subscription->id);
            }
    
            $defaultPaymentStatus = $this->entityManager->getRepository(PaymentStatus::class)->findOneBy(['name' => 'En attente']);
            if (!$defaultPaymentStatus) {
                throw new \Exception("Statut de paiement par défaut introuvable.");
            }
            $payment->setPaymentStatus($defaultPaymentStatus);
    
            $defaultPaymentMethod = $this->entityManager->getRepository(PaymentMethod::class)->findOneBy(['name' => 'Carte de crédit']);
            if (!$defaultPaymentMethod) {
                throw new \Exception("Méthode de paiement par défaut introuvable.");
            }
            $payment->setPaymentMethod($defaultPaymentMethod);
    
            $this->entityManager->persist($payment);
            $this->entityManager->flush();
    
            $responseContent = $isRecurring ? ['subscriptionId' => $subscription->id] : ['clientSecret' => $paymentIntent->client_secret];
            return $this->json($responseContent);
    
        } catch (\Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
    



    #[Route('/payment/checkout/{paymentId}', name: 'payment_checkout')]
    public function checkout(int $paymentId): Response
    {
        $payment = $this->entityManager->getRepository(Payment::class)->find($paymentId);
        if (!$payment) {
            throw $this->createNotFoundException('Paiement non trouvé.');
        }

        $invoice = $payment->getInvoice();
        $amount = $payment->getAmount();

        $session = $this->stripeClient->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Paiement pour Facture #' . $invoice->getId(),
                    ],
                    'unit_amount' => $amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('payment_success', ['paymentId' => $paymentId], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('payment_failed', ['paymentId' => $paymentId], UrlGeneratorInterface::ABSOLUTE_URL),
            'metadata' => ['payment_id' => $paymentId],
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/payment/webhook', name: 'payment_webhook')]
    public function stripeWebhook(Request $request): Response
    {
        // Remplacer par la clé secrète du webhook endpoint
        $endpoint_secret = $this->stripeWebhookSecret;
        $payload = $request->getContent();
        $sig_header = $request->headers->get('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;

                // Récupére l'ID de paiement depuis les métadonnées
                $paymentId = $session->metadata->payment_id;
                $payment = $this->entityManager->getRepository(Payment::class)->find($paymentId);

                if ($payment) {
                    // Mise à jour du statut de paiement dans la base de données
                    $payment->setPaymentStatus('Paid');
                    $this->entityManager->flush();
                }
            }

            return new Response('Webhook Handled', Response::HTTP_OK);
        } catch (\UnexpectedValueException $e) {
            // Payload invalide
            return new Response('Invalid payload', Response::HTTP_BAD_REQUEST);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Signature invalide
            return new Response('Invalid signature', Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            // Autres erreurs
            return new Response('Internal error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/payment/success', name: 'payment_success')]
    public function success(): Response
    {
        return $this->render('back/payment/success.html.twig');
    }


    #[Route('/payment/failed', name: 'payment_failed')]
    public function failed(): Response
    {

        return $this->render('back/payment/failed.html.twig');
    }

    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        $payments = $this->entityManager->getRepository(Payment::class)->findAll();
        // Récupération de toutes les factures
        $allInvoices = $this->entityManager->getRepository(Invoice::class)->findAll();

        // Filtrer pour obtenir seulement les factures sans paiements
        $invoicesWithoutPayments = array_filter($allInvoices, function ($invoice) {
            return $invoice->getPayments()->isEmpty();
        });
        return $this->render('back/payment/index.html.twig', [
            'payments' => $payments,
            'invoices' => $invoicesWithoutPayments,
        ]);
    }
}
