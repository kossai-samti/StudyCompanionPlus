<?php

namespace App\Controller;

use App\Entity\StudyMaterial;
use App\Form\StudyMaterialType;
use App\Repository\StudyMaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/study/material')]
final class StudyMaterialController extends AbstractController
{
    #[Route(name: 'app_study_material_index', methods: ['GET'])]
    public function index(StudyMaterialRepository $studyMaterialRepository): Response
    {
        return $this->render('study_material/index.html.twig', [
            'study_materials' => $studyMaterialRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_study_material_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $studyMaterial = new StudyMaterial();
        $form = $this->createForm(StudyMaterialType::class, $studyMaterial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($studyMaterial);
            $entityManager->flush();

            return $this->redirectToRoute('app_study_material_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('study_material/new.html.twig', [
            'study_material' => $studyMaterial,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_study_material_show', methods: ['GET'])]
    public function show(StudyMaterial $studyMaterial): Response
    {
        return $this->render('study_material/show.html.twig', [
            'study_material' => $studyMaterial,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_study_material_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StudyMaterial $studyMaterial, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StudyMaterialType::class, $studyMaterial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_study_material_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('study_material/edit.html.twig', [
            'study_material' => $studyMaterial,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_study_material_delete', methods: ['POST'])]
    public function delete(Request $request, StudyMaterial $studyMaterial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$studyMaterial->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($studyMaterial);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_study_material_index', [], Response::HTTP_SEE_OTHER);
    }
}
