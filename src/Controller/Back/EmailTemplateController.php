<?php

namespace App\Controller\Back;

use App\Entity\EmailTemplate;
use App\Entity\EmailType;
use App\Form\EmailTemplateType;
use App\Repository\EmailTemplateRepository;
use App\Repository\EmailTypeRepository;
use App\Repository\QuoteRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('platform/email/template')]
class EmailTemplateController extends AbstractController
{
    #[Route('/', name: 'platform_email_template_index', methods: ['GET'])]
    public function index(EmailTemplateRepository $emailTemplateRepository): Response
    {
        return $this->render('back/email_template/index.html.twig', [
            'email_templates' => $emailTemplateRepository->findAll(),
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
}