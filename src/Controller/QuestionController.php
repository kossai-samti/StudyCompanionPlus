<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/question')]
final class QuestionController extends AbstractController
{
    #[Route('/', name: 'question_index', methods: ['GET'])]
    public function index(QuestionRepository $repo): Response
    {
        return $this->render('question/index.html.twig', ['questions' => $repo->findAll()]);
    }

    #[Route('/new', name: 'question_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $q = new Question();
        $form = $this->createForm(QuestionType::class, $q);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($q);
            $em->flush();
            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'question_show', methods: ['GET'])]
    public function show(Question $question): Response
    {
        return $this->render('question/show.html.twig', ['question' => $question]);
    }

    #[Route('/{id}/edit', name: 'question_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Question $question, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/edit.html.twig', ['form' => $form->createView(), 'question' => $question]);
    }

    #[Route('/{id}', name: 'question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->request->get('_token'))) {
            $em->remove($question);
            $em->flush();
        }
        return $this->redirectToRoute('question_index');
    }
}
