<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\StudyMaterial;
use App\Entity\TeacherComment;
use App\Repository\GroupRepository;
use App\Repository\LessonRepository;
use App\Repository\PerformanceReportRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Repository\StudentAnswerRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\TeacherCommentRepository;
use App\Repository\TeacherProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class LearningFlowController extends AbstractController
{
    #[Route('/student/lessons/upload', name: 'student_lesson_upload', methods: ['GET', 'POST'])]
    public function studentUploadLesson(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em
    ): Response|RedirectResponse {
        $viewer = $session->get('demo_user', ['name' => 'Student', 'role' => 'student']);
        if (strtolower((string) ($viewer['role'] ?? 'student')) !== 'student') {
            return $this->redirectToRoute('student_dashboard');
        }

        if ($request->isMethod('POST')) {
            $input = trim((string) $request->request->get('content', ''));
            if ($input === '') {
                $this->addFlash('error', 'Please upload/paste lesson content first.');
                return $this->redirectToRoute('student_lesson_upload');
            }

            $lesson = new Lesson();
            $lesson->setTitle($this->inferTitle($input));
            $lesson->setSubject($this->inferSubject($input));
            $lesson->setDifficulty($this->inferDifficulty($input));
            $lesson->setFilePath((string) $request->request->get('file_path', ''));
            $lesson->setCreatedByRole('student');
            $lesson->setCreatedByName((string) ($viewer['name'] ?? 'Student'));
            $lesson->setCreatedAt(new \DateTimeImmutable());
            $lesson->setTargetGroup(null);
            $em->persist($lesson);

            $material = new StudyMaterial();
            $material->setLesson($lesson);
            $material->setType('ai_generated');
            $material->setContent($input);
            $material->setSummary($this->generateSummary($input));
            $material->setFlashcards($this->generateFlashcards($input));
            $em->persist($material);

            $em->flush();

            $this->addFlash('success', 'Lesson uploaded. AI generated title, summary, and flashcards.');
            return $this->redirectToRoute('lesson_show', ['id' => $lesson->getId()]);
        }

        return $this->render('student/upload_lesson.html.twig');
    }

    #[Route('/teacher/lessons/create-ai', name: 'teacher_lesson_create_ai', methods: ['GET', 'POST'])]
    public function teacherCreateAiLesson(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        GroupRepository $groups
    ): Response|RedirectResponse {
        $viewer = $session->get('demo_user', ['name' => 'Teacher', 'role' => 'teacher']);
        if (strtolower((string) ($viewer['role'] ?? 'teacher')) !== 'teacher') {
            return $this->redirectToRoute('teacher_dashboard');
        }

        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title', ''));
            $subject = trim((string) $request->request->get('subject', ''));
            $difficulty = (string) $request->request->get('difficulty', 'medium');
            $groupId = (int) $request->request->get('group_id', 0);
            $content = trim((string) $request->request->get('content', ''));

            $group = $groups->find($groupId);
            if ($group === null) {
                $this->addFlash('error', 'Choose a target group.');
                return $this->redirectToRoute('teacher_lesson_create_ai');
            }
            if ($title === '' || $subject === '' || $content === '') {
                $this->addFlash('error', 'Title, subject, and lesson content are required.');
                return $this->redirectToRoute('teacher_lesson_create_ai');
            }

            $lesson = new Lesson();
            $lesson->setTitle($title);
            $lesson->setSubject($subject);
            $lesson->setDifficulty(in_array($difficulty, ['easy', 'medium', 'hard'], true) ? $difficulty : 'medium');
            $lesson->setTargetGroup($group);
            $lesson->setCreatedByRole('teacher');
            $lesson->setCreatedByName((string) ($viewer['name'] ?? 'Teacher'));
            $lesson->setCreatedAt(new \DateTimeImmutable());
            $em->persist($lesson);

            $material = new StudyMaterial();
            $material->setLesson($lesson);
            $material->setType('teacher_ai_generated');
            $material->setContent($content);
            $material->setSummary($this->generateSummary($content));
            $material->setFlashcards($this->generateFlashcards($content));
            $em->persist($material);

            $em->flush();
            $this->addFlash('success', 'Teacher lesson published to group with AI summary + flashcards.');
            return $this->redirectToRoute('teacher_lessons');
        }

        return $this->render('teacher/create_lesson_ai.html.twig', ['groups' => $groups->findAll()]);
    }

    #[Route('/teacher/lessons/create-manual', name: 'teacher_lesson_create_manual', methods: ['GET', 'POST'])]
    public function teacherCreateManualLesson(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        GroupRepository $groups
    ): Response|RedirectResponse {
        $viewer = $session->get('demo_user', ['name' => 'Teacher', 'role' => 'teacher']);
        if (strtolower((string) ($viewer['role'] ?? 'teacher')) !== 'teacher') {
            return $this->redirectToRoute('teacher_dashboard');
        }

        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title', ''));
            $subject = trim((string) $request->request->get('subject', ''));
            $difficulty = (string) $request->request->get('difficulty', 'medium');
            $groupId = (int) $request->request->get('group_id', 0);
            $summary = trim((string) $request->request->get('summary', ''));
            $flashcards = trim((string) $request->request->get('flashcards', ''));

            $group = $groups->find($groupId);
            if ($group === null) {
                $this->addFlash('error', 'Choose a target group.');
                return $this->redirectToRoute('teacher_lesson_create_manual');
            }
            if ($title === '' || $subject === '') {
                $this->addFlash('error', 'Title and subject are required.');
                return $this->redirectToRoute('teacher_lesson_create_manual');
            }

            $lesson = new Lesson();
            $lesson->setTitle($title);
            $lesson->setSubject($subject);
            $lesson->setDifficulty(in_array($difficulty, ['easy', 'medium', 'hard'], true) ? $difficulty : 'medium');
            $lesson->setTargetGroup($group);
            $lesson->setCreatedByRole('teacher');
            $lesson->setCreatedByName((string) ($viewer['name'] ?? 'Teacher'));
            $lesson->setCreatedAt(new \DateTimeImmutable());
            $em->persist($lesson);

            $material = new StudyMaterial();
            $material->setLesson($lesson);
            $material->setType('teacher_manual');
            $material->setContent('Manual teacher lesson');
            $material->setSummary($summary !== '' ? $summary : null);
            $material->setFlashcards($flashcards !== '' ? $flashcards : null);
            $em->persist($material);

            $em->flush();
            return $this->redirectToRoute('teacher_quiz_builder', ['lessonId' => $lesson->getId()]);
        }

        return $this->render('teacher/create_lesson_manual.html.twig', ['groups' => $groups->findAll()]);
    }

    #[Route('/teacher/lessons/{lessonId}/quiz-builder', name: 'teacher_quiz_builder', methods: ['GET', 'POST'])]
    public function teacherQuizBuilder(
        int $lessonId,
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        LessonRepository $lessons,
        QuestionRepository $questions
    ): Response|RedirectResponse {
        $viewer = $session->get('demo_user', ['name' => 'Teacher', 'role' => 'teacher']);
        if (strtolower((string) ($viewer['role'] ?? 'teacher')) !== 'teacher') {
            return $this->redirectToRoute('teacher_dashboard');
        }

        $lesson = $lessons->find($lessonId);
        if ($lesson === null) {
            $this->addFlash('error', 'Lesson not found.');
            return $this->redirectToRoute('teacher_lessons');
        }

        $quiz = $lesson->getQuizzes()->first();
        if ($quiz === false || $quiz === null) {
            $quiz = new Quiz();
            $quiz->setLesson($lesson);
            $quiz->setDifficulty($lesson->getDifficulty() ?: 'medium');
            $quiz->setCreatedByRole('teacher');
            $quiz->setCreatedByName((string) ($viewer['name'] ?? 'Teacher'));
            $quiz->setCreatedAt(new \DateTimeImmutable());
            $em->persist($quiz);
            $em->flush();
        }

        if ($request->isMethod('POST')) {
            $action = (string) $request->request->get('action', 'add');

            if ($action === 'delete') {
                $questionId = (int) $request->request->get('question_id', 0);
                $question = $questions->find($questionId);
                if ($question !== null && $question->getQuiz()?->getId() === $quiz->getId()) {
                    $em->remove($question);
                    $em->flush();
                    $this->addFlash('success', 'Question deleted.');
                } else {
                    $this->addFlash('error', 'Question not found.');
                }
                return $this->redirectToRoute('teacher_quiz_builder', ['lessonId' => $lesson->getId()]);
            }

            $text = trim((string) $request->request->get('question_text', ''));
            $correct = trim((string) $request->request->get('correct_answer', ''));
            $optionsRaw = trim((string) $request->request->get('options_raw', ''));
            $options = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $optionsRaw) ?: [])));
            if ($text === '' || $correct === '' || count($options) < 2) {
                $this->addFlash('error', 'Question text, correct answer, and at least 2 options are required.');
                return $this->redirectToRoute('teacher_quiz_builder', ['lessonId' => $lesson->getId()]);
            }

            if ($action === 'update') {
                $questionId = (int) $request->request->get('question_id', 0);
                $question = $questions->find($questionId);
                if ($question === null || $question->getQuiz()?->getId() !== $quiz->getId()) {
                    $this->addFlash('error', 'Question not found.');
                    return $this->redirectToRoute('teacher_quiz_builder', ['lessonId' => $lesson->getId()]);
                }
                $question->setText($text);
                $question->setOptions($options);
                $question->setCorrectAnswer($correct);
                $em->flush();
                $this->addFlash('success', 'Question updated.');
                return $this->redirectToRoute('teacher_quiz_builder', ['lessonId' => $lesson->getId()]);
            }

            $question = new Question();
            $question->setQuiz($quiz);
            $question->setText($text);
            $question->setOptions($options);
            $question->setCorrectAnswer($correct);
            $em->persist($question);
            $em->flush();

            $this->addFlash('success', 'Question added to quiz.');
            return $this->redirectToRoute('teacher_quiz_builder', ['lessonId' => $lesson->getId()]);
        }

        return $this->render('teacher/quiz_builder.html.twig', [
            'lesson' => $lesson,
            'quiz' => $quiz,
        ]);
    }

    #[Route('/teacher/lessons/{lessonId}/quiz-builder/finish', name: 'teacher_quiz_builder_finish', methods: ['POST'])]
    public function finishTeacherQuizBuilder(
        int $lessonId,
        SessionInterface $session,
        LessonRepository $lessons,
        QuizRepository $quizzes
    ): RedirectResponse {
        $viewer = $session->get('demo_user', ['name' => 'Teacher', 'role' => 'teacher']);
        if (strtolower((string) ($viewer['role'] ?? 'teacher')) !== 'teacher') {
            return $this->redirectToRoute('teacher_dashboard');
        }

        $lesson = $lessons->find($lessonId);
        if ($lesson === null) {
            $this->addFlash('error', 'Lesson not found.');
            return $this->redirectToRoute('teacher_lessons');
        }

        $quiz = $quizzes->findOneBy(['lesson' => $lesson]);
        if ($quiz === null || $quiz->getQuestions()->count() === 0) {
            $this->addFlash('error', 'Add at least one question before finishing the quiz.');
            return $this->redirectToRoute('teacher_quiz_builder', ['lessonId' => $lessonId]);
        }

        $this->addFlash('success', 'Quiz finished and ready for students.');
        return $this->redirectToRoute('teacher_quizzes');
    }

    #[Route('/teacher/quizzes/monitor', name: 'teacher_quiz_monitor', methods: ['GET'])]
    public function teacherQuizMonitor(
        SessionInterface $session,
        GroupRepository $groups,
        PerformanceReportRepository $reports,
        StudentAnswerRepository $answers,
        QuestionRepository $questions,
        TeacherCommentRepository $comments
    ): Response {
        $viewer = $session->get('demo_user', ['name' => 'Teacher', 'role' => 'teacher']);
        if (strtolower((string) ($viewer['role'] ?? 'teacher')) !== 'teacher') {
            return $this->redirectToRoute('teacher_dashboard');
        }

        $questionById = [];
        foreach ($questions->findAll() as $question) {
            $questionById[$question->getId()] = $question;
        }

        $answersByStudentLesson = [];
        foreach ($answers->findAll() as $answer) {
            $student = $answer->getStudent();
            $question = $answer->getQuestion();
            $lesson = $question?->getQuiz()?->getLesson();
            if ($student === null || $question === null || $lesson === null) {
                continue;
            }
            $key = $student->getId().'_'.$lesson->getId();
            $answersByStudentLesson[$key][] = $answer;
        }

        $commentMap = [];
        foreach ($comments->findAll() as $comment) {
            $studentId = $comment->getStudent()?->getId();
            if ($studentId === null) {
                continue;
            }
            $content = (string) $comment->getContent();
            if (preg_match('/^\[Q(\d+)\]\s*(.*)$/', $content, $m) !== 1) {
                continue;
            }
            $questionId = (int) $m[1];
            $commentMap[$studentId.'_'.$questionId][] = trim((string) $m[2]);
        }

        $groupRows = [];
        foreach ($groups->findAll() as $group) {
            $lessonsData = [];
            foreach ($reports->findAll() as $report) {
                $student = $report->getStudent();
                if ($student === null || $student->getStudentGroup()?->getId() !== $group->getId()) {
                    continue;
                }
                $lesson = $report->getLesson();
                if ($lesson === null) {
                    continue;
                }

                $lessonId = $lesson->getId();
                if (!isset($lessonsData[$lessonId])) {
                    $lessonsData[$lessonId] = [
                        'lesson' => $lesson,
                        'students' => [],
                    ];
                }

                $wrongAnswers = [];
                foreach ($answersByStudentLesson[$student->getId().'_'.$lessonId] ?? [] as $studentAnswer) {
                    if ($studentAnswer->isCorrect()) {
                        continue;
                    }
                    $question = $questionById[$studentAnswer->getQuestion()->getId()] ?? null;
                    $wrongAnswers[] = [
                        'questionId' => $studentAnswer->getQuestion()->getId(),
                        'question' => $question?->getText() ?? '',
                        'answer' => $studentAnswer->getAnswer(),
                        'correct' => $question?->getCorrectAnswer() ?? '',
                        'comments' => $commentMap[$student->getId().'_'.$studentAnswer->getQuestion()->getId()] ?? [],
                    ];
                }

                $lessonsData[$lessonId]['students'][] = [
                    'profileId' => $student->getId(),
                    'name' => $student->getUser()?->getName(),
                    'email' => $student->getUser()?->getEmail(),
                    'score' => $report->getQuizScore(),
                    'mastery' => $report->getMasteryStatus(),
                    'weakTopics' => $report->getWeakTopics(),
                    'wrongAnswers' => $wrongAnswers,
                ];
            }

            if (!empty($lessonsData)) {
                $groupRows[] = [
                    'group' => $group,
                    'lessons' => array_values($lessonsData),
                ];
            }
        }

        return $this->render('teacher/quiz_monitor.html.twig', ['groupRows' => $groupRows]);
    }

    #[Route('/teacher/quizzes/comment', name: 'teacher_quiz_comment', methods: ['POST'])]
    public function addTeacherComment(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        StudentProfileRepository $students,
        TeacherProfileRepository $teachers
    ): RedirectResponse {
        $viewer = $session->get('demo_user', ['name' => 'Teacher', 'role' => 'teacher']);
        if (strtolower((string) ($viewer['role'] ?? 'teacher')) !== 'teacher') {
            return $this->redirectToRoute('teacher_dashboard');
        }

        $studentId = (int) $request->request->get('student_id', 0);
        $questionId = (int) $request->request->get('question_id', 0);
        $commentText = trim((string) $request->request->get('comment', ''));

        $student = $students->find($studentId);
        $teacher = $teachers->findOneBy([]);
        if ($student !== null && $teacher !== null && $commentText !== '') {
            $comment = new TeacherComment();
            $comment->setStudent($student);
            $comment->setTeacher($teacher);
            $comment->setContent('[Q'.$questionId.'] '.$commentText);
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success', 'Comment saved for this wrong answer.');
        } else {
            $this->addFlash('error', 'Unable to save comment.');
        }

        return $this->redirectToRoute('teacher_quiz_monitor');
    }

    private function inferTitle(string $content): string
    {
        $firstLine = trim((string) (preg_split('/\r\n|\r|\n/', $content)[0] ?? ''));
        return $firstLine !== '' ? mb_substr($firstLine, 0, 120) : 'AI Generated Lesson';
    }

    private function inferSubject(string $content): string
    {
        $text = strtolower($content);
        if (str_contains($text, 'algebra') || str_contains($text, 'equation') || str_contains($text, 'geometry')) {
            return 'Mathematics';
        }
        if (str_contains($text, 'cell') || str_contains($text, 'biology') || str_contains($text, 'photosynthesis')) {
            return 'Biology';
        }
        return 'General Studies';
    }

    private function inferDifficulty(string $content): string
    {
        $length = mb_strlen($content);
        if ($length > 3000) {
            return 'hard';
        }
        if ($length > 1200) {
            return 'medium';
        }
        return 'easy';
    }

    private function generateSummary(string $content): string
    {
        $snippet = trim(mb_substr($content, 0, 420));
        return "AI Summary:\n- Key concepts extracted\n- Structured explanation\n- Quick revision focus\n\n".$snippet;
    }

    private function generateFlashcards(string $content): string
    {
        $words = array_values(array_filter(array_unique(preg_split('/\W+/', strtolower($content)) ?: [])));
        $top = array_slice($words, 0, 6);
        $cards = [];
        foreach ($top as $word) {
            $cards[] = ['q' => 'Define '.$word, 'a' => 'AI-generated explanation for '.$word];
        }
        return json_encode($cards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
