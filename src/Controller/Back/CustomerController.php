<?php

namespace App\Controller\Back;

use App\Entity\Customer;
use App\Form\CustomerType;
use App\Repository\CustomerRepository;
use App\Service\PageAccessService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/platform/customer')]
class CustomerController extends AbstractController
{
    private $pageAccessService;
    private $stripeService;
    private $security;
    private $authorizationChecker;

    public function __construct(Security $security, PageAccessService $pageAccessService, StripeService $stripeService, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->pageAccessService = $pageAccessService;
        $this->stripeService = $stripeService;
        $this->security = $security;
        $this->authorizationChecker = $authorizationChecker;
    }

    #[Route('/', name: 'platform_customer_index', methods: ['GET'])]
    public function index(Request $request, CustomerRepository $customerRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            $customers = $customerRepository->findAll();
        } else {
            $company = $this->security->getUser()->getCompany();
            if ($company) {
                $customers = $customerRepository->findAllByCompanyId($company->getId());
            }
        }

        return $this->render('/back/customer/index.html.twig', [
            'customers' => $customers ?? [],
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
        $response = $this->checkConfidentiality($customer);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

        return $this->render('/back/customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));
        $response = $this->checkConfidentiality($customer);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

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
        $response = $this->checkConfidentiality($customer);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

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

    private function checkConfidentiality(Customer $customer): ?Response
    {
        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            return null; // L'admin a accès à tout, donc pas de redirection
        }
    
        if ($this->security->getUser()->getCompany() == $customer->getCompany()) {
            return null; // L'utilisateur a le droit d'accéder à cette ressource
        }
    
        // L'utilisateur n'a pas le droit d'accéder à cette ressource
        $this->addFlash('error', "Accès non autorisé à la ressource demandée.");
        return new RedirectResponse($this->generateUrl('platform_customer_index'));
    }

}
