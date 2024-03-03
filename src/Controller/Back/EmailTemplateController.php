<?php

namespace App\Controller\Back;

use App\Entity\EmailTemplate;
use App\Entity\EmailType;
use App\Form\EmailTemplateType;
use App\Repository\EmailTemplateRepository;
use App\Repository\EmailTypeRepository;
use App\Repository\QuoteRepository;
use App\Service\MailService;
use App\Service\PageAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('platform/email-template/template')]
class EmailTemplateController extends AbstractController
{
    private $pageAccessService;
    private $security;

    public function __construct(Security $security, PageAccessService $pageAccessService)
    {
        $this->pageAccessService = $pageAccessService;
        $this->security = $security;
    }

    #[Route('/', name: 'platform_email_template_index', methods: ['GET'])]
    public function index(EmailTemplateRepository $emailTemplateRepository, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        if ($authorizationChecker->isGranted("ROLE_ADMIN")) {
            $emailTemplates = $emailTemplateRepository->findAll();
        } else {
            $user = $this->security->getUser();
            $company = $user->getCompany();

            if ($company) {
                $emailTemplates = $emailTemplateRepository->findAllByCompanyId($company->getId());
            }
        }

        return $this->render('back/email_template/index.html.twig', [
            'email_templates' => $emailTemplates,
        ]);
    }

    #[Route('/{id}', name: 'platform_email_template_show', methods: ['GET'])]
    public function show(EmailTemplate $emailTemplate): Response
    {
        return $this->render('back/email_template/show.html.twig', [
            'email_template' => $emailTemplate,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_email_template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EmailTemplate $emailTemplate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmailTemplateType::class, $emailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('platform_email_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/email_template/edit.html.twig', [
            'email_template' => $emailTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/test-send-mail/{type}', name: 'platform_email_template_test', methods: ['GET', 'POST'])]
    public function testSendMail(Request $request, MailService $mailService, EmailTypeRepository $emailTypeRepository): Response
    {
        $user = $this->getUser();

        $emailTypeId = $request->get('type');
        $EmailType = $emailTypeRepository->find($emailTypeId);

        if (!$mailService->sendTestMail($user, $EmailType)) {
            $this->addFlash('error', $mailService->getError());
            return $this->redirectToRoute("platform_email_template_index");
        }

        $this->addFlash('success', 'Le Mail a été envoyé avec succès.');
        return $this->redirectToRoute("platform_email_template_index");
    }

}
