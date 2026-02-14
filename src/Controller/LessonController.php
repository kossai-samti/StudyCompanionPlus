<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lesson')]
final class LessonController extends AbstractController
{
    #[Route('/', name: 'lesson_index', methods: ['GET'])]
    public function index(LessonRepository $repo): Response
    {
        return $this->render('lesson/index.html.twig', ['lessons' => $repo->findAll()]);
    }

    #[Route('/new', name: 'lesson_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($lesson);
            $em->flush();

            return $this->redirectToRoute('lesson_index');
        }

        return $this->render('lesson/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'lesson_show', methods: ['GET'])]
    public function show(Lesson $lesson): Response
    {
        return $this->render('lesson/show.html.twig', ['lesson' => $lesson]);
    }

    #[Route('/{id}/edit', name: 'lesson_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Lesson $lesson, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('lesson_index');
        }

        return $this->render('lesson/edit.html.twig', ['form' => $form->createView(), 'lesson' => $lesson]);
    }

    #[Route('/{id}', name: 'lesson_delete', methods: ['POST'])]
    public function delete(Request $request, Lesson $lesson, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->request->get('_token'))) {
            $em->remove($lesson);
            $em->flush();
        }
        return $this->redirectToRoute('lesson_index');
    }
}
