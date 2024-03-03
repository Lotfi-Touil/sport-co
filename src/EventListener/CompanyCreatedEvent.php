<?php

namespace App\EventListener;
use App\Entity\Company;
use App\Entity\EmailTemplate;
use App\Repository\BasicEmailTemplateRepository;
use App\Repository\EmailTypeRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;


#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Company::class)]
class CompanyCreatedEvent
{
    private EmailTypeRepository $emailTypeRepository;
    private BasicEmailTemplateRepository $basicEmailTemplateRepository;
    public function __construct(EmailTypeRepository $emailTypeRepository,EntityManagerInterface $entityManager,BasicEmailTemplateRepository $basicEmailTemplateRepository)
    {
        $this->emailTypeRepository = $emailTypeRepository;
        $this->basicEmailTemplateRepository = $basicEmailTemplateRepository;

    }

    public function postPersist(Company $company,PostPersistEventArgs $event) : void
    {
        $emailTypes = $this -> getDefaultEmailType();
        $baseEmailTemplates = $this->getDefaultTemplate();

        foreach ($baseEmailTemplates as $baseEmailTemplate) {

            $emailTemplate = new EmailTemplate();
            $emailTemplate->setCompany($company);
            $emailTemplate->setType($baseEmailTemplate->getType());
            $emailTemplate->setSubject($baseEmailTemplate->getSubjet());
            $emailTemplate->setBody($baseEmailTemplate->getBody());

            $event->getObjectManager()->persist($emailTemplate);
        }
        $event->getObjectManager()->flush();

    }

    public function getDefaultEmailType() : array
    {
        return $this->emailTypeRepository->findAll();
    }

    public function getDefaultTemplate(): array
    {
        return $this->basicEmailTemplateRepository->findAll();
    }
}