<?php

namespace App\Controller;

use App\Entity\StudentProfile;
use App\Form\StudentProfileType;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student/profile')]
final class StudentProfileController extends AbstractController
{
    #[Route(name: 'app_student_profile_index', methods: ['GET'])]
    public function index(StudentProfileRepository $studentProfileRepository): Response
    {
        return $this->render('student_profile/index.html.twig', [
            'student_profiles' => $studentProfileRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_student_profile_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $studentProfile = new StudentProfile();
        $form = $this->createForm(StudentProfileType::class, $studentProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($studentProfile);
            $entityManager->flush();

            return $this->redirectToRoute('app_student_profile_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student_profile/new.html.twig', [
            'student_profile' => $studentProfile,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_profile_show', methods: ['GET'])]
    public function show(StudentProfile $studentProfile): Response
    {
        return $this->render('student_profile/show.html.twig', [
            'student_profile' => $studentProfile,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_student_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StudentProfile $studentProfile, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StudentProfileType::class, $studentProfile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_student_profile_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('student_profile/edit.html.twig', [
            'student_profile' => $studentProfile,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_student_profile_delete', methods: ['POST'])]
    public function delete(Request $request, StudentProfile $studentProfile, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$studentProfile->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($studentProfile);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_student_profile_index', [], Response::HTTP_SEE_OTHER);
    }
}
