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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('platform/email-template/template')]
class EmailTemplateController extends AbstractController
{
    private $pageAccessService;
    private $security;
    private $authorizationChecker;

    public function __construct(Security $security, PageAccessService $pageAccessService, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->pageAccessService = $pageAccessService;
        $this->security = $security;
        $this->authorizationChecker = $authorizationChecker;
    }

    #[Route('/', name: 'platform_email_template_index', methods: ['GET'])]
    public function index(Request $request, EmailTemplateRepository $emailTemplateRepository, AuthorizationCheckerInterface $authorizationChecker): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        if ($authorizationChecker->isGranted("ROLE_ADMIN")) {
            $emailTemplates = $emailTemplateRepository->findAll();
        } else {
            $user = $this->security->getUser();
            $company = $user->getCompany();
            $emailTemplates = $emailTemplateRepository->findAllByCompanyId($company->getId());
        }

        return $this->render('back/email_template/index.html.twig', [
            'email_templates' => $emailTemplates,
        ]);
    }

    #[Route('/{id}', name: 'platform_email_template_show', methods: ['GET'])]
    public function show(Request $request, EmailTemplate $emailTemplate): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $response = $this->checkConfidentiality($emailTemplate);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }
    
        return $this->render('back/email_template/show.html.twig', [
            'email_template' => $emailTemplate,
        ]);
    }

    #[Route('/{id}/edit', name: 'platform_email_template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EmailTemplate $emailTemplate, EntityManagerInterface $entityManager): Response
    {
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

        $response = $this->checkConfidentiality($emailTemplate);
        if ($response !== null) {
            return $response; // Redirection si l'utilisateur n'a pas accès
        }

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
        $this->pageAccessService->checkAccess($request->attributes->get('_route'));

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

    private function checkConfidentiality(EmailTemplate $emailTemplate): ?Response
    {
        if ($this->authorizationChecker->isGranted("ROLE_ADMIN")) {
            return null; // L'admin a accès à tout, donc pas de redirection
        }
    
        if ($user->getCompany() === $emailTemplate->getCompany()) {
            return null; // L'utilisateur a le droit d'accéder à cette ressource
        }
    
        // L'utilisateur n'a pas le droit d'accéder à cette ressource
        $this->addFlash('error', "Accès non autorisé à la ressource demandée.");
        return new RedirectResponse($this->generateUrl('platform_email_template_index'));
    }
}
