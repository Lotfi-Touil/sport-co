<?php

namespace App\Service;

use App\Entity\EmailTemplate;
use App\Entity\EmailType;
use App\Entity\Invoice;
use App\Entity\Quote;
use App\Repository\EmailTemplateRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
class MailService
{
    private MailerInterface $mailer;
    private EmailTemplateRepository $emailTemplateRepository;


    public function __construct(MailerInterface $mailer, EmailTemplateRepository $emailTemplateRepository)
    {
        $this->mailer = $mailer;
        $this->emailTemplateRepository = $emailTemplateRepository;
    }


    public function sendCustomEmail($entity,EmailType $emailType,?string $pdfPath = null): void
    {

        if ($entity instanceof Quote) {
            $company = $entity->getQuoteUsers()->first()->getCustomer()->getCompany();
            $mailCompany = $company->getEmail();
            $customerEmail = $entity->getQuoteUsers()->first()->getCustomer()->getEmail();

        } elseif ($entity instanceof Invoice) {
            // a faire
        } else {
            throw new \Exception("Type d'entité non pris en charge.");
        }

        $template = $this->emailTemplateRepository->findOneBy([
            'type' => $emailType,
            'company' => $company,
        ]);

        if (!$template) {
            throw new \Exception("Aucun template d'email trouvé pour le type spécifié et la company.");
        }

        // Préparation de l'email
        $email = (new Email())
            ->from($mailCompany)
            ->to($customerEmail)
            ->subject($template->getSubject())
            ->html($template->getBody());

        // Joindre le PDF
        if($pdfPath){
            $email->attachFromPath($pdfPath, 'document.pdf', 'application/pdf');
        }


        // Envoyer l'email
        $this->mailer->send($email);
    }


}