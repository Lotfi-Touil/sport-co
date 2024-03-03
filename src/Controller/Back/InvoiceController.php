<?php

namespace App\Controller\Back;

use App\Entity\Invoice;
use App\Entity\InvoiceStatus;
use App\Entity\Payment;
use App\Form\InvoiceType;
use App\Repository\EmailTypeRepository;
use App\Repository\InvoiceRepository;
use App\Repository\InvoiceStatusRepository;
use App\Service\InvoiceService;
use App\Service\MailService;
use App\Service\PageAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/platform/invoice')]
class InvoiceController extends AbstractController
{
    private $pageAccessService;

    private $invoiceService;

    public function __construct(PageAccessService $pageAccessService, InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        $this->pageAccessService = $pageAccessService;
    }

    #[Route('/', name: 'platform_invoice_index', methods: ['GET'])]
    public function index(Request $request, InvoiceRepository $invoiceRepository, InvoiceStatusRepository $invoiceStatusRepository): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        return $this->render('back/invoice/index.html.twig', [
            'invoices' => $invoiceRepository->findAll(),
            'invoice_status' => $invoiceStatusRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'platform_invoice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $statusList = $entityManager->getRepository(InvoiceStatus::class)->findAll();

        $invoice = new Invoice();
        $form = $this->createForm(InvoiceType::class, $invoice, ['status_choices' => $statusList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $params = $request->request->all();

            if (!$this->invoiceService->create($invoice, $params))
            {
                $this->addFlash('error', $this->invoiceService->getError());

                return $this->render('back/invoice/new.html.twig', [
                    'invoice' => $invoice,
                    'form' => $form->createView(),
                ]);
            }

            $this->addFlash('success', 'La facture a été enregistré avec succès.');
            return $this->redirectToRoute('platform_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/invoice/new.html.twig', [
            'invoice' => $invoice,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'platform_invoice_show', methods: ['GET'])]
    public function show(Request $request, ?Invoice $invoice): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$invoice) {
            $this->addFlash('error', 'La facture demandé n\'existe pas.');
            return $this->redirectToRoute('platform_invoice_index');
        }

        return $this->render('back/invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_invoice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ?Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$invoice) {
            $this->addFlash('error', 'La facture demandé n\'existe pas.');
            return $this->redirectToRoute('platform_invoice_index');
        }

        if ($invoice->getSubmittedAt()) {
            $this->addFlash('error', 'La facture n\'est plus modifiable car elle a été envoyé le '. $invoice->getSubmittedAt()->format('Y-m-d'));
            return $this->redirectToRoute('platform_invoice_index');
        }

        $statusList = $entityManager->getRepository(InvoiceStatus::class)->findAll();
        $form = $this->createForm(InvoiceType::class, $invoice, ['status_choices' => $statusList]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $params = $request->request->all();

            if (!$this->invoiceService->update($invoice, $params))
            {
                $this->addFlash('error', $this->invoiceService->getError());

                return $this->render('back/invoice/edit.html.twig', [
                    'invoice' => $invoice,
                    'form' => $form->createView(),
                ]);
            }

            $invoice->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'La facture a été mis à jour avec succès.');
            return $this->redirectToRoute('platform_invoice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/invoice/edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'platform_invoice_delete', methods: ['POST'])]
    public function delete(Request $request, ?Invoice $invoice, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$invoice) {
            $this->addFlash('error', 'La facture demandé n\'existe pas.');
            return $this->redirectToRoute('platform_invoice_index');
        }

        if ($invoice->getSubmittedAt()) {
            $this->addFlash('error', 'La facture n\'est pas supprimable car elle a été envoyé le '. $invoice->getSubmittedAt()->format('Y-m-d'));
            return $this->redirectToRoute('platform_invoice_index');
        }

        $payments = $entityManager->getRepository(Payment::class)->findByInvoiceId($invoice->getId());
        if ($payments) {
            $this->addFlash('error', 'Suppression impossible ! Il y a eu un paiement sur la facture.');
            return $this->redirectToRoute('platform_invoice_index');
        }

        if ($this->isCsrfTokenValid('delete'.$invoice->getId(), $request->request->get('_token'))) {
            foreach ($invoice->getInvoiceProducts() as $invoiceProduct) {
                $entityManager->remove($invoiceProduct);
            }

            $entityManager->remove($invoice);
            $entityManager->flush();

            $this->addFlash('success', 'La facture a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('platform_invoice_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/export', name: 'platform_invoice_export', methods: ['GET'])]
    public function export(Request $request, Invoice $invoice, InvoiceService $invoiceService): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$invoice) {
            $this->addFlash('error', 'La facture demandé n\'existe pas.');
            return $this->redirectToRoute('platform_invoice_index');
        }

        return $invoiceService->exportPDF($invoice);
    }

    #[Route('/{id}/send', name: 'platform_invoice_send', methods: ['GET'])]
    public function send(Request $request, Invoice $invoice, MailService $mailService, EmailTypeRepository $emailTypeRepository, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if (!$invoice) {
            $this->addFlash('error', 'La facture demandé n\'existe pas.');
            return $this->redirectToRoute('platform_invoice_index');
        }

        $EmailType = $emailTypeRepository->findOneBy(['type' => 'send_invoice']);

        if (!$mailService->sendInvoiceMail($invoice, $EmailType)) {
            $this->addFlash('error', $mailService->getError());
            return $this->redirectToRoute('platform_invoice_show', ['id' => $invoice->getId()]);
        }

        if (!$invoice->getSubmittedAt()) {
            $invoice->setSubmittedAt(new \DateTime());
            $entityManager->flush();
        }

        $this->addFlash('success', 'Le mail a été envoyé avec succès.');
        return $this->redirectToRoute('platform_invoice_show', ['id' => $invoice->getId()]);
    }

}
