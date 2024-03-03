<?php

namespace App\Controller\Back;

use App\Entity\Customer;
use App\Form\CustomerType;
use App\Repository\CustomerRepository;
use App\Service\PageAccessService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/platform/customer')]
class CustomerController extends AbstractController
{
    private $pageAccessService;
    private $stripeService;

    public function __construct(PageAccessService $pageAccessService, StripeService $stripeService)
    {
        $this->pageAccessService = $pageAccessService;
        $this->stripeService = $stripeService;
    }

    #[Route('/', name: 'platform_customer_index', methods: ['GET'])]
    public function index(Request $request, CustomerRepository $customerRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('/back/customer/index.html.twig', [
            'customers' => $customerRepository->findAll(),
        ]);
    }

    /**
     */
    #[Route('/new', name: 'platform_customer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stripeCustomerId = $this->stripeService->createStripeCustomer($customer);
            $customer->setStripeCustomerId($stripeCustomerId);
            $entityManager->persist($customer);
            $entityManager->flush();

            if ($stripeCustomerId === null) {
                $this->addFlash('warning', 'Le client a été créé, mais l\'enregistrement sur Stripe a échoué.');
            }

            return $this->redirectToRoute('platform_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('/back/customer/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_customer_show', methods: ['GET'])]
    public function show(Request $request, Customer $customer): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('/back/customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->stripeService->updateStripeCustomer($customer);
            $entityManager->flush();

            return $this->redirectToRoute('platform_customer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('/back/customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_customer_delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->request->get('_token'))) {
            $stripeDeletionSuccess = $this->stripeService->deleteStripeCustomer($customer);

            $entityManager->remove($customer);
            $entityManager->flush();

            if ($stripeDeletionSuccess) {
                $this->addFlash('success', 'Le client a été supprimé avec succès de la base de données et de Stripe.');
            } else {
                $this->addFlash('warning', 'Le client a été supprimé de la base de données, mais la suppression sur Stripe a échoué.');
            }
        }

        return $this->redirectToRoute('platform_customer_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search/customers', name: 'customer_search', methods: ['GET'])]
    public function customerSearch(Request $request, CustomerRepository $customerRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $term = $request->query->get('term');
        $customers = $customerRepository->findByTerm($term);

        return $this->render('back/customer/_search_results.html.twig', [
            'customers' => $customers,
        ]);
    }
}
