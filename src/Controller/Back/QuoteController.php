<?php

namespace App\Controller\Back;

use App\Entity\Quote;
use App\Entity\QuoteStatus;
use App\Form\QuoteType;
use App\Repository\EmailTypeRepository;
use App\Repository\QuoteRepository;
use App\Repository\QuoteStatusRepository;
use App\Service\MailService;
use App\Service\PageAccessService;
use App\Service\QuoteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/platform/quote')]
class QuoteController extends AbstractController
{
    private $pageAccessService;
    private $quoteService;
    private $security;

    public function __construct(PageAccessService $pageAccessService, QuoteService $quoteService, Security $security)
    {
        $this->pageAccessService = $pageAccessService;
        $this->quoteService = $quoteService;
        $this->security = $security;
    }

    #[Route('/', name: 'platform_quote_index', methods: ['GET'])]
    public function index(Request $request, AuthorizationCheckerInterface $authorizationChecker, QuoteRepository $quoteRepository, QuoteStatusRepository $quoteStatusRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($authorizationChecker->isGranted("ROLE_ADMIN")) {
            $quotes = $quoteRepository->findAll();
            $quote_status = $quoteStatusRepository->findAll();
        } else {
            $company = $this->security->getUser()->getCompany();
            $quotes = $quoteRepository->findAllByCompanyId($company->getId());
            $quote_status = $quoteStatusRepository->findAllByCompanyId($company->getId());
        }

        return $this->render('back/quote/index.html.twig', [
            'quotes' => $quotes,
            'quote_status' => $quote_status,
        ]);
    }

    #[Route('/new', name: 'platform_quote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($authorizationChecker->isGranted("ROLE_ADMIN")) {
            $statusList = $entityManager->getRepository(QuoteStatus::class)->findAll();
        } else {
            $company = $this->security->getUser()->getCompany();
            $statusList = $entityManager->getRepository(QuoteStatus::class)->findAllByCompanyId($company->getId());
        }

        $quote = new Quote();
        $form = $this->createForm(QuoteType::class, $quote, ['status_choices' => $statusList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $params = $request->request->all();

            if (!$this->quoteService->create($quote, $params))
            {
                $this->addFlash('error', $this->quoteService->getError());

                return $this->render('back/quote/new.html.twig', [
                    'quote' => $quote,
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'Le devis a été enregistré avec succès.');
            return $this->redirectToRoute('platform_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/quote/new.html.twig', [
            'quote' => $quote,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'platform_quote_show', methods: ['GET'])]
    public function show(Request $request, ?Quote $quote): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$quote) {
            $this->addFlash('error', 'Le devis demandé n\'existe pas.');
            return $this->redirectToRoute('platform_quote_index');
        }

        return $this->render('back/quote/show.html.twig', [
            'quote' => $quote,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_quote_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Quote $quote, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$quote) {
            $this->addFlash('error', 'Le devis demandé n\'existe pas.');
            return $this->redirectToRoute('platform_quote_index');
        }

        $statusList = $entityManager->getRepository(QuoteStatus::class)->findAll();
        $form = $this->createForm(QuoteType::class, $quote, ['status_choices' => $statusList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $params = $request->request->all();

            if (!$this->quoteService->update($quote, $params))
            {
                $this->addFlash('error', $this->quoteService->getError());

                return $this->render('back/quote/edit.html.twig', [
                    'quote' => $quote,
                    'form' => $form->createView(),
                ]);
            }

            $quote->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Le devis a été mis à jour avec succès.');
            return $this->redirectToRoute('platform_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/quote/edit.html.twig', [
            'quote' => $quote,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'platform_quote_delete', methods: ['POST'])]
    public function delete(Request $request, ?Quote $quote, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$quote) {
            $this->addFlash('error', 'Le devis demandé n\'existe pas.');
            return $this->redirectToRoute('platform_quote_index');
        }

        if ($quote->getSubmittedAt()) {
            $this->addFlash('error', 'Suppression impossible ! le devis a été soumis au client.');
            return $this->redirectToRoute('platform_invoice_index');
        }

        if ($this->isCsrfTokenValid('delete'.$quote->getId(), $request->request->get('_token'))) {
            foreach ($quote->getQuoteProducts() as $quoteProduct) {
                $entityManager->remove($quoteProduct);
            }

            $entityManager->remove($quote);
            $entityManager->flush();

            $this->addFlash('success', 'Le devis a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('platform_quote_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/export', name: 'platform_quote_export', methods: ['GET'])]
    public function export(Request $request, Quote $quote, QuoteService $quoteService): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$quote) {
            $this->addFlash('error', 'Le devis demandé n\'existe pas.');
            return $this->redirectToRoute('platform_quote_index');
        }

        return $quoteService->exportPDF($quote);
    }

    #[Route('/{id}/send', name: 'platform_quote_send', methods: ['GET'])]
    public function send(Request $request, Quote $quote, MailService $mailService, EmailTypeRepository $emailTypeRepository, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$quote) {
            $this->addFlash('error', 'Le devis demandé n\'existe pas.');
            return $this->redirectToRoute('platform_quote_index');
        }

        $EmailType = $emailTypeRepository->findOneBy(['type' => 'send_quote']);

        if (!$mailService->sendQuoteMail($quote, $EmailType)) {
            $this->addFlash('error', $mailService->getError());
            return $this->redirectToRoute('platform_quote_show', ['id' => $quote->getId()]);
        }

        $quote->setSubmittedAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Le mail a été envoyé avec succès.');
        return $this->redirectToRoute('platform_quote_show', ['id' => $quote->getId()]);
    }

}
