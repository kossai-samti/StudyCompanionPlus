<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lesson')]
final class LessonController extends AbstractController
{
    #[Route('/', name: 'lesson_index', methods: ['GET'])]
    public function index(LessonRepository $repo, SessionInterface $session): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
        $role = strtolower((string) ($viewer['role'] ?? 'student'));
        $allLessons = $repo->findAll();

        if ($role === 'teacher') {
            $allLessons = array_values(array_filter($allLessons, static fn (Lesson $lesson): bool =>
                strtolower((string) $lesson->getCreatedByRole()) === 'teacher'
            ));
        }

        return $this->render('lesson/index.html.twig', ['lessons' => $allLessons]);
    }

    #[Route('/new', name: 'lesson_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
        $role = strtolower((string) ($viewer['role'] ?? 'student'));
        if ($role === 'admin') {
            $this->addFlash('error', 'Admin cannot create lessons. Only teachers and students can.');
            return $this->redirectToRoute('admin_lessons');
        }

        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson, ['is_teacher' => $role === 'teacher']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($role === 'teacher' && $lesson->getTargetGroup() === null) {
                $this->addFlash('error', 'Teacher lesson must target a group.');
                return $this->render('lesson/new.html.twig', ['form' => $form->createView()]);
            }

            if ($role === 'student') {
                $lesson->setTargetGroup(null);
            }

            $lesson->setCreatedByRole($role);
            $lesson->setCreatedByName((string) ($viewer['name'] ?? 'Unknown'));
            $lesson->setCreatedAt(new \DateTimeImmutable());
            $em->persist($lesson);
            $em->flush();

            return $this->redirectToRoute('lesson_index');
        }

        return $this->render('lesson/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'lesson_show', methods: ['GET'])]
    public function show(Lesson $lesson): Response
    {
        $material = $lesson->getStudyMaterials()->first();
        $flashcards = [];
        if ($material !== false && $material !== null && $material->getFlashcards() !== null) {
            $decoded = json_decode((string) $material->getFlashcards(), true);
            if (is_array($decoded)) {
                $flashcards = $decoded;
            }
        }

        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
            'material' => $material !== false ? $material : null,
            'flashcards' => $flashcards,
        ]);
    }

    #[Route('/{id}/edit', name: 'lesson_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Lesson $lesson, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
        $role = strtolower((string) ($viewer['role'] ?? 'student'));

        if ($role === 'admin') {
            $this->addFlash('error', 'Admin cannot edit lessons from this screen.');
            return $this->redirectToRoute('admin_lessons');
        }

        $form = $this->createForm(LessonType::class, $lesson, ['is_teacher' => $role === 'teacher']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($role === 'student') {
                $lesson->setTargetGroup(null);
            }
            $em->flush();
            return $this->redirectToRoute('lesson_index');
        }

        return $this->render('lesson/edit.html.twig', ['form' => $form->createView(), 'lesson' => $lesson]);
    }

    #[Route('/{id}', name: 'lesson_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Lesson $lesson,
        EntityManagerInterface $em,
        SessionInterface $session,
        QuizRepository $quizRepository
    ): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
        $role = strtolower((string) ($viewer['role'] ?? 'student'));

        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->request->get('_token'))) {
            if (!in_array($role, ['teacher', 'admin'], true)) {
                return $this->redirectToRoute('lesson_index');
            }

            foreach ($quizRepository->findBy(['lesson' => $lesson]) as $quiz) {
                foreach ($quiz->getQuestions() as $question) {
                    $em->remove($question);
                }
                $em->remove($quiz);
            }
            $em->remove($lesson);
            $em->flush();
        }
        return $this->redirectToRoute($role === 'admin' ? 'admin_lessons' : 'lesson_index');
    }
}
