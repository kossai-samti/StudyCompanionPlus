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
    /**
     * Updated Index logic: Filters view based on the session role.
     */
    #[Route('/', name: 'lesson_index', methods: ['GET'])]
    public function index(LessonRepository $repo, SessionInterface $session): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
        $role = strtolower((string) ($viewer['role'] ?? 'student'));
        
        // Per your logic: Teachers only see teacher-created lessons.
        // Students and Admins see all available modules.
        if ($role === 'teacher') {
            $lessons = $repo->findBy(['created_by_role' => 'teacher']);
        } else {
            $lessons = $repo->findAll();
        }

        return $this->render('lesson/index.html.twig', [
            'lessons' => $lessons,
            'viewer_role' => $role
        ]);
    }

    #[Route('/new', name: 'lesson_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
        $role = strtolower((string) ($viewer['role'] ?? 'student'));
        
        if ($role === 'admin') {
            $this->addFlash('error', 'Admin cannot create lessons directly.');
            return $this->redirectToRoute('lesson_index');
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
            $lesson->setCreatedByName((string) ($viewer['name'] ?? 'Unknown User'));
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
        
        if ($material && $material->getFlashcards()) {
            $decoded = json_decode((string) $material->getFlashcards(), true);
            $flashcards = is_array($decoded) ? $decoded : [];
        }

        return $this->render('lesson/show.html.twig', [
            'lesson' => $lesson,
            'material' => $material ?: null,
            'flashcards' => $flashcards,
        ]);
    }

    #[Route('/{id}/edit', name: 'lesson_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Lesson $lesson, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $viewer = $session->get('demo_user', ['role' => 'student']);
        $role = strtolower((string) ($viewer['role'] ?? 'student'));

        if ($role === 'admin') {
            $this->addFlash('error', 'Admin restricted access.');
            return $this->redirectToRoute('lesson_index');
        }

        $form = $this->createForm(LessonType::class, $lesson, ['is_teacher' => $role === 'teacher']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($role === 'student') { $lesson->setTargetGroup(null); }
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
    ): Response {
        $viewer = $session->get('demo_user', ['role' => 'student']);
        $role = strtolower((string) ($viewer['role'] ?? 'student'));

        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->request->get('_token'))) {
            // Permission check: Only Teachers/Admins can delete, or a student deleting their own.
            if (in_array($role, ['teacher', 'admin'], true) || ($role === 'student' && $lesson->getCreatedByName() === $viewer['name'])) {
                
                // Manual cascade delete for linked quizzes and questions
                foreach ($quizRepository->findBy(['lesson' => $lesson]) as $quiz) {
                    foreach ($quiz->getQuestions() as $question) {
                        $em->remove($question);
                    }
                    $em->remove($quiz);
                }
                
                $em->remove($lesson);
                $em->flush();
                $this->addFlash('success', 'Neural module purged.');
            }
        }
        
        return $this->redirectToRoute($role === 'admin' ? 'admin_lessons' : 'lesson_index');
    }
}