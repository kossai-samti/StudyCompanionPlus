<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\PerformanceReport;
use App\Entity\StudentAnswer;
use App\Form\QuizType;
use App\Repository\LessonRepository;
use App\Repository\PerformanceReportRepository;
use App\Repository\QuizRepository;
use App\Repository\StudentAnswerRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\TeacherCommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quiz')]
final class QuizController extends AbstractController
{
    #[Route('/', name: 'quiz_index', methods: ['GET'])]
    public function index(
        QuizRepository $repo,
        SessionInterface $session,
        StudentProfileRepository $studentProfiles,
        PerformanceReportRepository $reports,
        StudentAnswerRepository $studentAnswers,
        TeacherCommentRepository $teacherComments
    ): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
        $role = strtolower((string) ($viewer['role'] ?? 'student'));

        if ($role === 'student') {
            $studentProfile = null;
            foreach ($studentProfiles->findAll() as $profile) {
                if (strcasecmp((string) $profile->getUser()?->getName(), (string) ($viewer['name'] ?? '')) === 0) {
                    $studentProfile = $profile;
                    break;
                }
            }
            if ($studentProfile === null) {
                $studentProfile = $studentProfiles->findOneBy([]);
            }

            $doneReports = $studentProfile ? $reports->findBy(['student' => $studentProfile]) : [];
            $resultCards = [];
            foreach ($doneReports as $report) {
                $lesson = $report->getLesson();
                if ($lesson === null) {
                    continue;
                }
                $quiz = $repo->findOneBy(['lesson' => $lesson]);
                if ($quiz === null) {
                    continue;
                }

                $allAnswers = [];
                foreach ($studentAnswers->findBy(['student' => $studentProfile]) as $answer) {
                    if ($answer->getQuestion()?->getQuiz()?->getId() === $quiz->getId()) {
                        $allAnswers[] = $answer;
                    }
                }
                $errors = 0;
                $correct = 0;
                foreach ($allAnswers as $a) {
                    if ($a->isCorrect() === false) {
                        ++$errors;
                    }
                    if ($a->isCorrect() === true) {
                        ++$correct;
                    }
                }

                $notesCount = 0;
                foreach ($teacherComments->findBy(['student' => $studentProfile]) as $c) {
                    if (preg_match('/^\[Q(\d+)\]/', (string) $c->getContent(), $m) === 1) {
                        $questionId = (int) $m[1];
                        foreach ($quiz->getQuestions() as $q) {
                            if ($q->getId() === $questionId) {
                                ++$notesCount;
                                break;
                            }
                        }
                    }
                }

                $score = (float) $report->getQuizScore();
                $resultCards[] = [
                    'quizId' => $quiz->getId(),
                    'title' => $lesson->getTitle(),
                    'score' => $score,
                    'color' => $score < 50 ? '#e34f4f' : ($score < 80 ? '#f39a36' : '#37b36b'),
                    'errors' => $errors,
                    'correct' => $correct,
                    'notesCount' => $notesCount,
                ];
            }

            return $this->render('quiz/index.html.twig', [
                'viewerRole' => $role,
                'doneReports' => $doneReports,
                'resultCards' => $resultCards,
                'quizzes' => [],
            ]);
        }

        return $this->render('quiz/index.html.twig', [
            'viewerRole' => $role,
            'doneReports' => [],
            'resultCards' => [],
            'quizzes' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'quiz_new', methods: ['GET','POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        LessonRepository $lessons
    ): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Teacher', 'role' => 'teacher']);
        $role = strtolower((string) ($viewer['role'] ?? 'teacher'));
        if ($role !== 'teacher') {
            $this->addFlash('error', 'Only teachers can create quizzes.');
            return $this->redirectToRoute($role === 'admin' ? 'admin_quizzes' : 'quiz_index');
        }

        if (count($lessons->findAll()) === 0) {
            $this->addFlash('error', 'Create a lesson first, then create a quiz.');
            return $this->redirectToRoute('teacher_lessons');
        }

        $quiz = new Quiz();
        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quiz->setCreatedAt(new \DateTimeImmutable());
            $quiz->setCreatedByRole('teacher');
            $quiz->setCreatedByName((string) ($viewer['name'] ?? 'Teacher'));
            $em->persist($quiz);
            $em->flush();
            return $this->redirectToRoute('quiz_index');
        }

        return $this->render('quiz/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/{id}', name: 'quiz_show', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function show(
        Quiz $quiz,
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        StudentProfileRepository $studentProfiles,
        StudentAnswerRepository $studentAnswers,
        PerformanceReportRepository $reports
    ): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('submit_quiz_'.$quiz->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('error', 'Invalid quiz submission token.');
                return $this->redirectToRoute('quiz_show', ['id' => $quiz->getId()]);
            }

            $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
            $role = strtolower((string) ($viewer['role'] ?? 'student'));

            $studentProfile = null;
            foreach ($studentProfiles->findAll() as $profile) {
                if (strcasecmp((string) $profile->getUser()?->getName(), (string) ($viewer['name'] ?? '')) === 0) {
                    $studentProfile = $profile;
                    break;
                }
            }
            if ($studentProfile === null && $role === 'student') {
                $studentProfile = $studentProfiles->findOneBy([]);
            }
            if ($studentProfile === null) {
                $this->addFlash('error', 'Student profile not found for this account.');
                return $this->redirectToRoute('quiz_show', ['id' => $quiz->getId()]);
            }

            foreach ($studentAnswers->findBy(['student' => $studentProfile]) as $existingAnswer) {
                if ($existingAnswer->getQuestion()?->getQuiz()?->getId() === $quiz->getId()) {
                    $em->remove($existingAnswer);
                }
            }

            $questions = $quiz->getQuestions()->toArray();
            $total = count($questions);
            if ($total === 0) {
                $this->addFlash('error', 'This quiz has no questions.');
                return $this->redirectToRoute('quiz_show', ['id' => $quiz->getId()]);
            }

            $correctCount = 0;
            $wrongTopics = [];
            foreach ($questions as $question) {
                if (!$question instanceof Question) {
                    continue;
                }

                $selected = trim((string) $request->request->get('q_'.$question->getId(), ''));
                $isCorrect = $selected !== '' && $selected === (string) $question->getCorrectAnswer();
                if ($isCorrect) {
                    ++$correctCount;
                } else {
                    $wrongTopics[] = mb_substr((string) $question->getText(), 0, 90);
                }

                $answer = new StudentAnswer();
                $answer->setStudent($studentProfile);
                $answer->setQuestion($question);
                $answer->setAnswer($selected);
                $answer->setIsCorrect($isCorrect);
                $em->persist($answer);
            }

            $score = round(($correctCount / $total) * 100, 2);
            $mastery = $score < 50 ? 'Beginner' : ($score < 80 ? 'Intermediate' : 'Mastered');
            $weakTopics = empty($wrongTopics) ? null : implode('; ', array_slice($wrongTopics, 0, 3));

            $report = $reports->findOneBy(['student' => $studentProfile, 'lesson' => $quiz->getLesson()]);
            if ($report === null) {
                $report = new PerformanceReport();
                $report->setStudent($studentProfile);
                $report->setLesson($quiz->getLesson());
                $em->persist($report);
            }
            $report->setQuizScore((float) $score);
            $report->setMasteryStatus($mastery);
            $report->setWeakTopics($weakTopics);

            $em->flush();
            $this->addFlash('success', 'Quiz submitted. Score: '.$score.'%');
            return $this->redirectToRoute('quiz_index');
        }

        return $this->render('quiz/show.html.twig', ['quiz' => $quiz]);
    }

    #[Route('/{id}/review', name: 'quiz_review', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function review(
        Quiz $quiz,
        SessionInterface $session,
        StudentProfileRepository $studentProfiles,
        StudentAnswerRepository $studentAnswers,
        TeacherCommentRepository $teacherComments
    ): Response {
        $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
        if (strtolower((string) ($viewer['role'] ?? 'student')) !== 'student') {
            return $this->redirectToRoute('quiz_show', ['id' => $quiz->getId()]);
        }

        $studentProfile = null;
        foreach ($studentProfiles->findAll() as $profile) {
            if (strcasecmp((string) $profile->getUser()?->getName(), (string) ($viewer['name'] ?? '')) === 0) {
                $studentProfile = $profile;
                break;
            }
        }
        if ($studentProfile === null) {
            $studentProfile = $studentProfiles->findOneBy([]);
        }
        if ($studentProfile === null) {
            $this->addFlash('error', 'Student profile not found.');
            return $this->redirectToRoute('quiz_index');
        }

        $answerByQuestionId = [];
        foreach ($studentAnswers->findBy(['student' => $studentProfile]) as $a) {
            if ($a->getQuestion()?->getQuiz()?->getId() === $quiz->getId()) {
                $answerByQuestionId[$a->getQuestion()->getId()] = $a;
            }
        }

        $notesByQuestionId = [];
        foreach ($teacherComments->findBy(['student' => $studentProfile]) as $comment) {
            $content = (string) $comment->getContent();
            if (preg_match('/^\[Q(\d+)\]\s*(.*)$/', $content, $m) !== 1) {
                continue;
            }
            $questionId = (int) $m[1];
            $belongsToQuiz = false;
            foreach ($quiz->getQuestions() as $q) {
                if ($q->getId() === $questionId) {
                    $belongsToQuiz = true;
                    break;
                }
            }
            if (!$belongsToQuiz) {
                continue;
            }
            $notesByQuestionId[$questionId][] = trim((string) $m[2]);
        }

        return $this->render('quiz/review.html.twig', [
            'quiz' => $quiz,
            'answerByQuestionId' => $answerByQuestionId,
            'notesByQuestionId' => $notesByQuestionId,
        ]);
    }

    #[Route('/{id}/edit', name: 'quiz_edit', methods: ['GET','POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Quiz $quiz, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Teacher', 'role' => 'teacher']);
        $role = strtolower((string) ($viewer['role'] ?? 'teacher'));
        if ($role !== 'teacher') {
            $this->addFlash('error', 'Only teachers can edit quizzes.');
            return $this->redirectToRoute($role === 'admin' ? 'admin_quizzes' : 'quiz_index');
        }

        $form = $this->createForm(QuizType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('quiz_index');
        }

        return $this->render('quiz/edit.html.twig', ['form' => $form->createView(), 'quiz' => $quiz]);
    }

    #[Route('/{id}', name: 'quiz_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Quiz $quiz, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $viewer = $session->get('demo_user', ['name' => 'Teacher', 'role' => 'teacher']);
        $role = strtolower((string) ($viewer['role'] ?? 'teacher'));

        if ($this->isCsrfTokenValid('delete'.$quiz->getId(), $request->request->get('_token'))) {
            if (!in_array($role, ['teacher', 'admin'], true)) {
                return $this->redirectToRoute('quiz_index');
            }

            foreach ($quiz->getQuestions() as $question) {
                $em->remove($question);
            }
            $em->remove($quiz);
            $em->flush();
        }
        return $this->redirectToRoute($role === 'admin' ? 'admin_quizzes' : 'quiz_index');
    }

    #[Route('/start/{lesson}', name: 'quiz_start_for_lesson', methods: ['GET'], requirements: ['lesson' => '\d+'])]
    public function startForLesson(
        Lesson $lesson,
        QuizRepository $quizRepository,
        EntityManagerInterface $em
    ): Response {
        $quiz = $quizRepository->findOneBy(['lesson' => $lesson]);
        if ($quiz === null) {
            $quiz = new Quiz();
            $quiz->setLesson($lesson);
            $quiz->setDifficulty($lesson->getDifficulty() ?: 'medium');
            $quiz->setCreatedByRole('ai');
            $quiz->setCreatedByName('AI Engine');
            $quiz->setCreatedAt(new \DateTimeImmutable());
            $em->persist($quiz);

            $seed = $lesson->getStudyMaterials()->first();
            $baseText = $seed && $seed->getSummary() ? $seed->getSummary() : $lesson->getTitle().' '.$lesson->getSubject();

            $q1 = new Question();
            $q1->setQuiz($quiz);
            $q1->setText('Which statement best matches this lesson topic?');
            $q1->setOptions([
                'It is about '.$lesson->getSubject(),
                'It is only about geography',
                'It is unrelated to school content',
                'It is only about sports',
            ]);
            $q1->setCorrectAnswer('It is about '.$lesson->getSubject());
            $em->persist($q1);

            $q2 = new Question();
            $q2->setQuiz($quiz);
            $q2->setText('Based on the lesson summary, what is the key focus?');
            $q2->setOptions([
                mb_substr($baseText, 0, 40),
                'No core concept',
                'Only historical dates',
                'Only random facts',
            ]);
            $q2->setCorrectAnswer(mb_substr($baseText, 0, 40));
            $em->persist($q2);

            $em->flush();
        }

        return $this->redirectToRoute('quiz_show', ['id' => $quiz->getId()]);
    }
}
