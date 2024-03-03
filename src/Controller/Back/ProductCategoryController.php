<?php

namespace App\Controller\Back;

use App\Entity\ProductCategory;
use App\Form\ProductCategoryType;
use App\Repository\ProductCategoryRepository;
use App\Service\PageAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/platform/product/category')]
class ProductCategoryController extends AbstractController
{
    private $pageAccessService;

    public function __construct(PageAccessService $pageAccessService)
    {
        $this->pageAccessService = $pageAccessService;
    }

    #[Route('/', name: 'platform_product_category_index', methods: ['GET'])]
    public function index(Request $request, ProductCategoryRepository $productCategoryRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/product_category/index.html.twig', [
            'product_categories' => $productCategoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'platform_product_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $productCategory = new ProductCategory();
        $form = $this->createForm(ProductCategoryType::class, $productCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($productCategory);
            $entityManager->flush();

            return $this->redirectToRoute('platform_product_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/product_category/new.html.twig', [
            'product_category' => $productCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_product_category_show', methods: ['GET'])]
    public function show(Request $request, ProductCategory $productCategory): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/product_category/show.html.twig', [
            'product_category' => $productCategory,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_product_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductCategory $productCategory, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $form = $this->createForm(ProductCategoryType::class, $productCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('platform_product_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/product_category/edit.html.twig', [
            'product_category' => $productCategory,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'platform_product_category_delete', methods: ['POST'])]
    public function delete(Request $request, ProductCategory $productCategory, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($this->isCsrfTokenValid('delete'.$productCategory->getId(), $request->request->get('_token'))) {
            $entityManager->remove($productCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('platform_product_category_index', [], Response::HTTP_SEE_OTHER);
    }
}
