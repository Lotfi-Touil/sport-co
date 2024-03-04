<?php

namespace App\Controller\Back;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\PageAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StripeService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/platform/product')]
class ProductController extends AbstractController
{
    private $pageAccessService;
    private $stripeService;
    private $security;
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, Security $security, PageAccessService $pageAccessService, StripeService $stripeService)
    {
        $this->pageAccessService = $pageAccessService;
        $this->stripeService = $stripeService;
        $this->security = $security;
        $this->authorizationChecker = $authorizationChecker;
    }

    #[Route('/', name: 'platform_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            $products = $productRepository->findAll();
        } else {
            $company = $this->security->getUser()->getCompany();
            $products = $productRepository->findAllByCompanyId($company->getId());
        }

        return $this->render('back/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    #[Route('/new', name: 'platform_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $product = new Product();
        $companyId = $security->getUser()->getCompany()->getId();
        $form = $this->createForm(ProductType::class, $product, [
            'company_id' => $companyId, // Passer l'ID de la compagnie comme option
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setCompany($security->getUser()->getCompany());
            $entityManager->persist($product);
            $entityManager->flush();

            $billingType = $product->getIsRecurring() ? 'recurring' : 'one_time';

            $stripeData = $this->stripeService->createStripeProduct($product, $billingType);
            $product->setStripeProductId($stripeData['stripeProductId']);
            $product->setStripePriceId($stripeData['stripePriceId']);

            $entityManager->flush();
            return $this->redirectToRoute('platform_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'platform_product_show', methods: ['GET'])]
    public function show(Request $request, Product $product): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/{id}/edit', name: 'platform_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newBillingType = $product->getIsRecurring() ? 'recurring' : 'one_time';
            $stripeData = $this->stripeService->updateStripeProduct($product, $newBillingType);

            if (!empty($stripeData)) {
                $product->setStripeProductId($stripeData['stripeProductId'] ?? $product->getStripeProductId());
                $product->setStripePriceId($stripeData['stripePriceId'] ?? $product->getStripePriceId());
            }

            $entityManager->flush();

            return $this->redirectToRoute('platform_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $this->stripeService->deleteStripeProduct($product);
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('platform_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search/products', name: 'product_search', methods: ['GET'])]
    public function searchProducts(Request $request, ProductRepository $productRepository, Security $security): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $term = $request->query->get('term');
        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            $products = $productRepository->findByTerm($term);
        } else {
            $company = $security->getUser()->getCompany();
            $products = $productRepository->findByTermAndCompany($term, $company);
        }

        return $this->render('back/product/_search_results.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/view/row', name: 'product_row_view', methods: ['GET'])]
    public function getProductRowView(Request $request, ProductRepository $productRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $productId = $request->query->get('id');
        $product = $productRepository->find($productId);

        return $this->render('back/product/_product_row.html.twig', [
            'product' => $product,
        ]);
    }

}
