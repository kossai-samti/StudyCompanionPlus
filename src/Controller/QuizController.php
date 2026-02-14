<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quiz')]
final class QuizController extends AbstractController
{
    #[Route('/', name: 'quiz_index', methods: ['GET'])]
    public function index(QuizRepository $repo): Response
    {
        return $this->render('quiz/index.html.twig', ['quizzes' => $repo->findAll()]);
    }

    #[Route('/new', name: 'quiz_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($quiz);
            $em->flush();
            return $this->redirectToRoute('quiz_index');
        }

        return $this->render('quiz/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'quiz_show', methods: ['GET'])]
    public function show(Quiz $quiz): Response
    {
        return $this->render('quiz/show.html.twig', ['quiz' => $quiz]);
    }

    #[Route('/{id}/edit', name: 'quiz_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Quiz $quiz, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('quiz_index');
        }

        return $this->render('quiz/edit.html.twig', ['form' => $form->createView(), 'quiz' => $quiz]);
    }

    #[Route('/{id}', name: 'quiz_delete', methods: ['POST'])]
    public function delete(Request $request, Quiz $quiz, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quiz->getId(), $request->request->get('_token'))) {
            $em->remove($quiz);
            $em->flush();
        }
        return $this->redirectToRoute('quiz_index');
    }
}
