<?php

namespace App\Controller;

use App\Entity\EmailType;
use App\Form\EmailTypeType;
use App\Repository\EmailTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/email/type')]
class EmailTypeController extends AbstractController
{
    #[Route('/', name: 'admin_email_type_index', methods: ['GET'])]
    public function index(EmailTypeRepository $emailTypeRepository): Response
    {
        return $this->render('back/admin/email_type/index.html.twig', [
            'email_types' => $emailTypeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_email_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $emailType = new EmailType();
        $form = $this->createForm(EmailTypeType::class, $emailType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($emailType);
            $entityManager->flush();

            return $this->redirectToRoute('admin_email_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/admin/email_type/new.html.twig', [
            'email_type' => $emailType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_email_type_show', methods: ['GET'])]
    public function show(EmailType $emailType): Response
    {
        return $this->render('back/admin/email_type/show.html.twig', [
            'email_type' => $emailType,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_email_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EmailType $emailType, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmailTypeType::class, $emailType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_email_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/admin/email_type/edit.html.twig', [
            'email_type' => $emailType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_email_type_delete', methods: ['POST'])]
    public function delete(Request $request, EmailType $emailType, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$emailType->getId(), $request->request->get('_token'))) {
            $entityManager->remove($emailType);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_email_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
