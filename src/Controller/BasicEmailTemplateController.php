<?php

namespace App\Controller;

use App\Entity\BasicEmailTemplate;
use App\Entity\EmailType;
use App\Form\BasicEmailTemplateType;
use App\Repository\BasicEmailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/platform/email/template')]
class BasicEmailTemplateController extends AbstractController
{
    #[Route('/', name: 'admin_basic_email_template_index', methods: ['GET'])]
    public function index(BasicEmailTemplateRepository $basicEmailTemplateRepository): Response
    {
        return $this->render('back/admin/basic_email_template/index.html.twig', [
            'basic_email_templates' => $basicEmailTemplateRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_basic_email_template_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {


        $emailTypeCount = $entityManager->getRepository(EmailType::class)->count([]);
        $basicEmailTemplateCount = $entityManager->getRepository(BasicEmailTemplate::class)->count([]);

        // Si chaque EmailType a déjà un BasicEmailTemplate associé
        if ($emailTypeCount <= $basicEmailTemplateCount) {
            // Redirigez l'utilisateur avec un message flash, ou gérez comme nécessaire
            $this->addFlash('error', 'Tous les types d\'email ont déjà un template associé. Pour en créer un nouveau, merci de bien vouloir créer un nouveau type de mail');
            return $this->redirectToRoute('admin_basic_email_template_index');
        }

        $basicEmailTemplate = new BasicEmailTemplate();
        $form = $this->createForm(BasicEmailTemplateType::class, $basicEmailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($basicEmailTemplate);
            $entityManager->flush();

            return $this->redirectToRoute('admin_basic_email_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/admin/basic_email_template/new.html.twig', [
            'basic_email_template' => $basicEmailTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_basic_email_template_show', methods: ['GET'])]
    public function show(BasicEmailTemplate $basicEmailTemplate): Response
    {
        return $this->render('back/admin/basic_email_template/show.html.twig', [
            'basic_email_template' => $basicEmailTemplate,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_basic_email_template_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BasicEmailTemplate $basicEmailTemplate, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BasicEmailTemplateType::class, $basicEmailTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_basic_email_template_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/admin/basic_email_template/edit.html.twig', [
            'basic_email_template' => $basicEmailTemplate,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_basic_email_template_delete', methods: ['POST'])]
    public function delete(Request $request, BasicEmailTemplate $basicEmailTemplate, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$basicEmailTemplate->getId(), $request->request->get('_token'))) {
            $entityManager->remove($basicEmailTemplate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_basic_email_template_index', [], Response::HTTP_SEE_OTHER);
    }
}
