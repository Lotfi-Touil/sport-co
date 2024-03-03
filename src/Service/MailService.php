<?php

namespace App\Service;

use App\Entity\EmailType;
use App\Entity\Invoice;
use App\Entity\Quote;
use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\EmailTemplateRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class MailService
{
    private MailerInterface $mailer;
    private EmailTemplateRepository $emailTemplateRepository;
    private CompanyRepository $companyRepository;
    private Security $security;
    private QuoteService $quoteService;
    private InvoiceService $invoiceService;

    private $error;

    public function __construct(MailerInterface $mailer, EmailTemplateRepository $emailTemplateRepository, CompanyRepository $companyRepository, Security $security, QuoteService $quoteService, InvoiceService $invoiceService)
    {
        $this->mailer = $mailer;
        $this->emailTemplateRepository = $emailTemplateRepository;
        $this->companyRepository = $companyRepository;
        $this->security = $security;
        $this->quoteService = $quoteService;
        $this->invoiceService = $invoiceService;
    }

    private function addError(string $error): void
    {
        $this->error = $error;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function sendTestMail(User $user, EmailType $emailType): bool
    {
        $user = $this->security->getUser();
        $company = $user->getCompany();

        $template = $this->emailTemplateRepository->findOneBy([
            'type'    => $emailType,
            'company' => $company,
        ]);

        if (!$template) {
            throw new \Exception("Une erreur est survenue.");
        }

        // Préparation de l'email
        $email = (new Email())
            ->from($company->getEmail())
            ->to($user->getEmail())
            ->subject($template->getSubject())
            ->html($template->getBody());

        // Envoyer l'email
        $this->mailer->send($email);

        return true;
    }

    public function sendQuoteMail(Quote $quote, EmailType $emailType): bool
    {
        if (!$quote->getQuoteUsers()) {
            $this->addError('Erreur lors de la récupération des données, le mail n\'a pas été envoyé.');
            return false;
        }

        $quoteUsers = $quote->getQuoteUsers()[0];
        $company = $quoteUsers->getCreator()->getCompany();
        $customer = $quoteUsers->getCustomer();

        $template = $this->emailTemplateRepository->findOneBy([
            'type'    => $emailType,
            'company' => $company,
        ]);

        if (!$template) {
            throw new \Exception("Une erreur est survenue.");
        }

        $pdfFilePath = $this->quoteService->generatePDF($quote);

        $email = (new Email())
            ->from($company->getEmail())
            ->to($customer->getEmail())
            ->subject($template->getSubject())
            ->html($template->getBody())
            ->attachFromPath($pdfFilePath);

        $this->mailer->send($email);

        unlink($pdfFilePath);

        return true;
    }

    public function sendInvoiceMail(Invoice $invoice, EmailType $emailType): bool
    {
        if (!$invoice->getInvoiceUsers()) {
            $this->addError('Erreur lors de la récupération des données, le mail n\'a pas été envoyé.');
            return false;
        }

        $invoiceUsers = $invoice->getInvoiceUsers()[0];
        $company = $invoiceUsers->getCreator()->getCompany();
        $customer = $invoiceUsers->getCustomer();

        $template = $this->emailTemplateRepository->findOneBy([
            'type'    => $emailType,
            'company' => $company,
        ]);

        if (!$template) {
            throw new \Exception("Une erreur est survenue.");
        }

        $pdfFilePath = $this->invoiceService->generatePDF($invoice);

        $email = (new Email())
            ->from($company->getEmail())
            ->to($customer->getEmail())
            ->subject($template->getSubject())
            ->html($template->getBody())
            ->attachFromPath($pdfFilePath);

        $this->mailer->send($email);

        unlink($pdfFilePath);

        return true;
    }

}