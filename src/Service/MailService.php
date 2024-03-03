<?php

namespace App\Service;

use App\Entity\EmailTemplate;
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
    private $error;

    public function __construct(MailerInterface $mailer, EmailTemplateRepository $emailTemplateRepository, CompanyRepository $companyRepository, Security $security)
    {
        $this->mailer = $mailer;
        $this->emailTemplateRepository = $emailTemplateRepository;
        $this->companyRepository = $companyRepository;
        $this->security = $security;
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

}