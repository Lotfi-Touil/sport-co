<?php

namespace App\Controller\Back;


use App\Entity\InvoiceStatus;
use App\Repository\PaymentRepository;
use App\Service\StripeService;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\Webhook;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\StripeClient;
use App\Entity\Payment;
use App\Entity\Invoice;
use App\Entity\PaymentStatus;
use App\Entity\PaymentMethod;
use App\Service\PageAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @method createPaymentSession(Request $request, int $paymentId)
 */
#[Route('/platform')]
class PaymentController extends AbstractController
{
    private $pageAccessService;

    private $stripeClient;
    private $entityManager;
    private $stripeSecretKey;
    private $stripeWebhookSecret;
    private $authorizationChecker;

    private $stripeService;

    public function __construct(PageAccessService $pageAccessService, string $stripeSecretKey, string $stripeWebhookSecret,  EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker, StripeService $stripeService)
    {
        $this->pageAccessService = $pageAccessService;

        $this->stripeSecretKey = $stripeSecretKey;
        $this->stripeWebhookSecret = $stripeWebhookSecret;
        $this->stripeClient = new StripeClient($stripeSecretKey);
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->stripeService = $stripeService;
    }


    #[Route('/payment/create/{invoiceId}', name: 'payment_create')]
    public function create(Request $request, int $invoiceId): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$this->authorizationChecker->isGranted('ROLE_COMPANY') &&
            !$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        try {
            $invoice = $this->entityManager->getRepository(Invoice::class)->find($invoiceId);
            if (!$invoice) {
                throw new \Exception("Facture non trouvée");
            }

            $invoiceUsers = $invoice->getInvoiceUsers();


            if (count($invoiceUsers) != 1) {

                return new Response('Erreur: Plus d\'un utilisateur ou aucun utilisateur associé à cette facture.', Response::HTTP_BAD_REQUEST);
            }


            $customer = $invoiceUsers->first()->getCustomer();
            if (!$customer) {
                throw new \Exception("Client non trouvé pour cette facture.");
            }

            $existingPayment = $this->entityManager->getRepository(Payment::class)->findOneBy(['invoice' => $invoice]);
            if ($existingPayment) {
                throw new \Exception("Un paiement a déjà été initié pour cette facture.");
            }



            $amount = $invoice->getTotalAmount();

            $content = json_decode($request->getContent(), true);
            $paymentType = $content['payment_type'] ?? 'unique'; // Remplacer ici
            $isRecurring = $paymentType === 'recurring';

            $paymentMethodId = $content['payment_method_id'] ?? null;
            if (!$paymentMethodId) {
                throw new \Exception("Méthode de paiement non trouvée.");
            }

            $payment = new Payment();
            $payment->setAmount($amount);
            $payment->setInvoice($invoice);
            $payment->setIsRecurring($isRecurring);
            $payment->setCreatedAt(new \DateTime());
            $defaultPaymentStatus = $this->entityManager->getRepository(PaymentStatus::class)->findOneBy(['name' => 'En attente']);
            if (!$defaultPaymentStatus) {
                throw new \Exception("Statut de paiement par défaut introuvable.");
            }
            $payment->setPaymentStatus($defaultPaymentStatus);
            $paymentMethod = $this->entityManager->getRepository(PaymentMethod::class)->find($paymentMethodId);
            if (!$paymentMethod) {
                throw new \Exception("Méthode de paiement non trouvée.");
            }
            $payment->setPaymentMethod($paymentMethod);
            dump($paymentMethod->getName());


            if (!$isRecurring) {
                if ($paymentMethod->getName() == 'Carte de crédit') {
                    $paymentIntent = $this->stripeClient->paymentIntents->create([
                        'amount' => $amount * 100,
                        'currency' => 'eur',
                        'payment_method_types' => ['card'],
                    ]);
                } elseif ($paymentMethod->getName() == 'PayPal') {
                    $paymentIntent = $this->stripeClient->paymentIntents->create([
                        'amount' => $amount * 100,
                        'currency' => 'eur',
                        'payment_method_types' => ['paypal'],
                    ]);
                } elseif ($paymentMethod->getName() == 'Virement bancaire') {
                    $paymentIntent = $this->stripeClient->paymentIntents->create([
                        'amount' => $amount * 100,
                        'currency' => 'eur',
                        'payment_method_types' => ['sepa_debit'],
                    ]);
                } else {
                    return new Response('Erreur: Méthode de paiement non reconnue.', Response::HTTP_BAD_REQUEST);
                }

                $payment->setStripePaymentIntentId($paymentIntent->id);
                $this->entityManager->persist($payment);
                $this->entityManager->flush();
                return $this->json(['clientSecret' => $paymentIntent->client_secret]);
            } else {
                $invoiceProducts = $invoice->getInvoiceProducts();
                $stripeItems = array_map(function ($invoiceProduct) {
                    return ['price' => $invoiceProduct->getProduct()->getStripePriceId(), 'quantity' => 1];
                }, $invoiceProducts->toArray());

                if (empty($stripeItems)) {
                    return new Response('Erreur: Aucun produit Stripe disponible pour la facture.', Response::HTTP_BAD_REQUEST);
                }

                $stripeCustomer = $this->stripeClient->customers->retrieve($customer->getStripeCustomerId());
                $session = $this->stripeClient->checkout->sessions->create([
                    'customer' => $stripeCustomer->id,
                    'payment_method_types' => ['card'],
                    'line_items' => $stripeItems,
                    'mode' => 'subscription',
                    'success_url' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $this->generateUrl('payment_failed', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ]);

                $payment->setStripeSubscriptionId($session->subscription);
                $this->entityManager->persist($payment);
                $this->entityManager->flush();
                return $this->json(['url' => $session->url]);
            }

        } catch (\Exception $e) {
            return new Response('Erreur: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @throws ApiErrorException
     * @throws TransportExceptionInterface
     */
    #[Route('/payment/send-email/{paymentId}', name: 'payment_send_email')]
    public function sendPaymentEmail(MailerInterface $mailer, int $paymentId): Response
    {
        $payment = $this->entityManager->getRepository(Payment::class)->find($paymentId);
        if (!$payment) {
            return new Response('Paiement non trouvé.', Response::HTTP_NOT_FOUND);
        }

        $invoice = $payment->getInvoice();
        $customerEmail = $invoice->getInvoiceUsers()->first()->getCustomer()->getEmail();

        $paymentLink = $this->stripeService->createPaymentSession($payment->getId());

        $email = (new TemplatedEmail())
            ->from(new Address('no.reply.sportco@gmail.com', 'SportCo Bot'))
            ->to($customerEmail)
            ->subject('Votre lien de paiement')
            ->htmlTemplate('back/payment/email.html.twig')
            ->context([
                'paymentLink' => $paymentLink,
            ]);

        $mailer->send($email);

        return new Response('Email envoyé avec succès.', Response::HTTP_OK);
    }

    /**
     * @throws ApiErrorException
     * @throws TransportExceptionInterface
     */
    #[Route('/send-overdue-payment-reminders', name: 'send_overdue_payment_reminders')]
    public function sendOverduePaymentReminders(PaymentRepository $paymentRepository, MailerInterface $mailer, UrlGeneratorInterface $urlGenerator): Response
    {
        $paymentStatus = $this->entityManager->getRepository(PaymentStatus::class);
        $overduePayments = $paymentRepository->findOverduePayments($paymentStatus);

        foreach ($overduePayments as $payment) {
            $customerEmail = $payment->getInvoice()->getInvoiceUsers()->first()->getCustomer()->getEmail();
            $paymentLink = $this->stripeService->createPaymentSession($payment->getId());

            $email = (new TemplatedEmail())
                ->from(new Address('no.reply.sportco@gmail.com', 'SportCo Bot'))
                ->to($customerEmail)
                ->subject('Rappel : Paiement 😭 ')
                ->htmlTemplate('back/payment/email_payment_reminder.html.twig')
                ->context([
                    'paymentLink' => $paymentLink,
                ]);

            $mailer->send($email);
        }

        return new Response('Reminders sent for ' . count($overduePayments) . ' overdue payments.');
    }

    #[Route('/payment/checkout/{paymentId}', name: 'payment_checkout')]
    public function checkout(int $paymentId, StripeService $stripeService): Response
    {
        try {
            $paymentSessionUrl = $stripeService->createPaymentSession($paymentId);
            return $this->redirect($paymentSessionUrl);
        } catch (\Exception $e) {
            throw $this->createNotFoundException('Impossible de créer la session de paiement.');
        }
    }



    #[Route('/payment/webhook', name: 'payment_webhook', methods: ['POST'])]
    public function stripeWebhook(Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager): JsonResponse
    {
        $logger->info('Webhook received');
        $endpoint_secret = $this->stripeWebhookSecret;
        $payload = $request->getContent();
        $sig_header = $request->headers->get('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );

            $logger->info('Stripe webhook received', ['event_type' => $event->type]);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $paymentId = $session->metadata['payment_id'];
                    $logger->info('Processing checkout.session.completed', ['payment_id' => $paymentId]);

                    $payment = $entityManager->getRepository(Payment::class)->find($paymentId);
                    if (!$payment) {
                        $logger->error('Payment not found', ['payment_id' => $paymentId]);
                        return new JsonResponse(['error' => 'Payment not found', 'status' => Response::HTTP_NOT_FOUND]);
                    }

                    $completedPaymentStatus = $entityManager->getRepository(PaymentStatus::class)->findOneBy(['name' => 'Complété']);
                    $completedInvoiceStatus = $entityManager->getRepository(InvoiceStatus::class)->findOneBy(['title' => 'Payée']);

                    if (!$completedPaymentStatus || !$completedInvoiceStatus) {
                        $logger->error('Completed status not found', ['payment_status' => $completedPaymentStatus, 'invoice_status' => $completedInvoiceStatus]);
                        return new JsonResponse(['error' => 'Status not found', 'status' => Response::HTTP_OK]);
                    }

                    $payment->setPaymentStatus($completedPaymentStatus);
                    $invoice = $payment->getInvoice();
                    $invoice->setInvoiceStatus($completedInvoiceStatus);
                    $entityManager->flush();

                    $logger->info('Payment and invoice status updated to completed', ['payment_id' => $paymentId]);
                    return new JsonResponse(['message' => 'Webhook Handled for session completed', 'status' => Response::HTTP_OK]);
                default:
                    $logger->info('Received another type of event', ['event_type' => $event->type]);
                    return new JsonResponse(['message' => 'Received another type of event', 'status' => Response::HTTP_OK]);
            }
        } catch (\UnexpectedValueException $e) {
            $logger->error('Invalid payload', ['exception' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Invalid payload', 'status' => Response::HTTP_BAD_REQUEST]);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $logger->error('Invalid signature', ['exception' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Invalid signature', 'status' => Response::HTTP_BAD_REQUEST]);
        } catch (\Exception $e) {
            $logger->error('Internal error', ['exception' => $e->getMessage()]);
            return new JsonResponse(['error' => 'Internal error: ' . $e->getMessage(), 'status' => Response::HTTP_INTERNAL_SERVER_ERROR]);
        }
    }

    #[Route('/payment/delete/{paymentId}', name: 'payment_delete')]
    public function delete(Request $request, int $paymentId): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Accès refusé.');
        }

        $payment = $this->entityManager->getRepository(Payment::class)->find($paymentId);
        if (!$payment) {
            $this->addFlash('error', 'Paiement non trouvé.');
            return $this->redirectToRoute('app_payment');
        }

        try {
            $this->entityManager->remove($payment);
            $this->entityManager->flush();
            $this->addFlash('success', 'Le paiement a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression du paiement: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_payment');
    }



    #[Route('/payment/success', name: 'payment_success')]
    public function success(Request $request): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/payment/success.html.twig');
    }


    #[Route('/payment/failed', name: 'payment_failed')]
    public function failed(Request $request): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/payment/failed.html.twig');
    }

    #[Route('/payment', name: 'app_payment')]
    public function index(Request $request): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $payments = $this->entityManager->getRepository(Payment::class)->findAll();
        $allInvoices = $this->entityManager->getRepository(Invoice::class)->findAll();

        $invoicesWithoutPayments = array_filter($allInvoices, function ($invoice) {
            return $invoice->getPayments()->isEmpty();
        });
        return $this->render('back/payment/index.html.twig', [
            'payments' => $payments,
            'invoices' => $invoicesWithoutPayments,
        ]);
    }
}
