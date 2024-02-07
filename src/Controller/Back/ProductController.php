<?php

namespace App\Controller\Back;

use App\Entity\Image;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\PageAccessService;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\StripeService;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/platform/product')]
class ProductController extends AbstractController
{
    private $pageAccessService;

    private $stripeService;

    public function __construct(PageAccessService $pageAccessService, StripeService $stripeService)
    {
        $this->pageAccessService = $pageAccessService;

        $this->stripeService = $stripeService;
    }

    #[Route('/', name: 'platform_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'platform_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,ImageService $imageService): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();

            foreach ($images as $image){
                $folder = 'products';
                $file = $imageService->add($image,$folder,150,150);
                $img = new Image();
                $img->setName($file);
                $product->addImage($img);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $stripeData = $this->stripeService->createStripeProduct($product);
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

    #[Route('/{id}/edit', name: 'platform_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager,ImageService $imageService): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();

            foreach ($images as $image){
                $folder = 'products';
                $file = $imageService->add($image,$folder,150,150);
                $img = new Image();
                $img->setName($file);
                $product->addImage($img);
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
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('platform_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search/products', name: 'product_search', methods: ['GET'])]
    public function searchProducts(Request $request, ProductRepository $productRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $searchTerm = $request->query->get('term');
        $products = $productRepository->findBySearchTerm($searchTerm);

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

    #[Route('/delete/image/{id}', name: 'platform_image_delete', methods: ['DELETE'])]
    public function deleteImage(Request $request, Image $image, EntityManagerInterface $entityManager,ImageService $imageService): JsonResponse
    {
        $data = json_decode($request->getContent(),true);

        if($this->isCsrfTokenValid('delete'.$image->getId(),$data['_token'])){
            $name = $image->getName();

            if($imageService->delete($name,'products',150,150)){
                $entityManager->remove($image);
                $entityManager->flush();
                return new JsonResponse(['success'=>true],200);
            }
            return new JsonResponse(['error'=>"error delete"],400);
        }
        return new JsonResponse(['error'=>"token invalid"],400);
    }

}
