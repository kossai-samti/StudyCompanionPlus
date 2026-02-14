<?php

namespace App\Controller;

use App\Entity\TeacherProfile;
use App\Entity\Group as StudyGroup;
use App\Entity\Group;
use App\Entity\Lesson;
use App\Entity\Quiz;
use App\Entity\StudentProfile;
use App\Entity\User;
use App\Repository\GroupRepository;
use App\Repository\LessonRepository;
use App\Repository\PerformanceReportRepository;
use App\Repository\QuizRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\TeacherProfileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class DashboardController extends AbstractController
{
    #[Route('/student/dashboard', name: 'student_dashboard')]
    public function student(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Student','role' => 'student']);
        return $this->render('dashboard/student.html.twig', ['user' => $user]);
    }

    #[Route('/teacher/dashboard', name: 'teacher_dashboard')]
    public function teacher(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Teacher','role' => 'teacher']);
        return $this->render('dashboard/teacher.html.twig', ['user' => $user]);
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function admin(
        SessionInterface $session,
        UserRepository $users,
        LessonRepository $lessons,
        QuizRepository $quizzes
    ): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);
        $allUsers = $users->findAll();
        $teacherCount = 0;
        $studentCount = 0;

        foreach ($allUsers as $appUser) {
            if ($appUser->getRole() === 'teacher') {
                ++$teacherCount;
            }
            if ($appUser->getRole() === 'student') {
                ++$studentCount;
            }
        }

        return $this->render('dashboard/admin.html.twig', [
            'user' => $user,
            'totalUsers' => count($allUsers),
            'totalLessons' => count($lessons->findAll()),
            'totalQuizzes' => count($quizzes->findAll()),
            'teacherCount' => $teacherCount,
            'studentCount' => $studentCount,
        ]);
    }

    #[Route('/student/performance', name: 'student_performance')]
    public function studentPerformance(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Student','role' => 'student']);
        return $this->render('dashboard/student_performance.html.twig', ['user' => $user]);
    }

    #[Route('/teacher/students', name: 'teacher_students')]
    public function teacherStudents(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Teacher','role' => 'teacher']);
        return $this->render('teacher/students.html.twig', ['user' => $user]);
    }

    #[Route('/teacher/groups', name: 'teacher_groups')]
    public function teacherGroups(
        SessionInterface $session,
        Request $request,
        EntityManagerInterface $em,
        GroupRepository $groups,
        StudentProfileRepository $students,
        PerformanceReportRepository $reports,
        TeacherProfileRepository $teacherProfiles
    ): Response
    {
        $user = $session->get('demo_user', ['name' => 'Teacher','role' => 'teacher']);
        if (strtolower((string) ($user['role'] ?? 'teacher')) !== 'teacher') {
            return $this->redirectToRoute('teacher_dashboard');
        }

        if ($request->isMethod('POST')) {
            $action = (string) $request->request->get('action', '');

            if ($action === 'create_group') {
                $name = trim((string) $request->request->get('group_name', ''));
                if ($name !== '') {
                    $newGroup = new StudyGroup();
                    $newGroup->setName($name);
                    $newGroup->setCreatedAt(new \DateTimeImmutable());
                    $newGroup->setCreatedBy((string) ($user['name'] ?? 'Teacher'));
                    $teacherProfile = $teacherProfiles->findOneBy([]);
                    if ($teacherProfile !== null) {
                        $newGroup->setTeacher($teacherProfile);
                    }
                    $em->persist($newGroup);
                    $em->flush();
                }
            }

            if ($action === 'assign_student') {
                $studentId = (int) $request->request->get('student_id', 0);
                $groupId = (int) $request->request->get('group_id', 0);
                $student = $students->find($studentId);
                $group = $groups->find($groupId);
                if ($student !== null && $group !== null) {
                    $student->setStudentGroup($group);
                    $em->flush();
                }
            }

            if ($action === 'remove_student') {
                $studentId = (int) $request->request->get('student_id', 0);
                $student = $students->find($studentId);
                if ($student !== null) {
                    $student->setStudentGroup(null);
                    $em->flush();
                }
            }

            return $this->redirectToRoute('teacher_groups');
        }

        $groupStudents = [];
        foreach ($students->findAll() as $studentProfile) {
            $group = $studentProfile->getStudentGroup();
            if ($group === null) {
                continue;
            }
            $groupStudents[$group->getId()][] = $studentProfile;
        }

        $studentScores = [];
        foreach ($reports->findAll() as $report) {
            $studentId = $report->getStudent()?->getId();
            if ($studentId === null) {
                continue;
            }
            $studentScores[$studentId][] = (float) $report->getQuizScore();
        }

        $groupsData = [];
        foreach ($groups->findAll() as $group) {
            $items = [];
            foreach ($groupStudents[$group->getId()] ?? [] as $studentProfile) {
                $studentUser = $studentProfile->getUser();
                if ($studentUser === null) {
                    continue;
                }
                $scores = $studentScores[$studentProfile->getId()] ?? [];
                $avg = count($scores) > 0 ? array_sum($scores) / count($scores) : null;
                $items[] = [
                    'studentProfileId' => $studentProfile->getId(),
                    'name' => $studentUser->getName(),
                    'email' => $studentUser->getEmail(),
                    'grade' => $studentProfile->getGrade(),
                    'avgScore' => $avg,
                ];
            }

            $groupsData[] = [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'teacherName' => $this->teacherNameFromProfile($group->getTeacher()),
                'createdAt' => $group->getCreatedAt(),
                'createdBy' => $group->getCreatedBy(),
                'students' => $items,
            ];
        }

        return $this->render('teacher/groups.html.twig', [
            'user' => $user,
            'groupsData' => $groupsData,
            'allStudents' => $students->findAll(),
            'allGroups' => $groups->findAll(),
        ]);
    }

    #[Route('/teacher/lessons', name: 'teacher_lessons')]
    public function teacherLessons(SessionInterface $session, LessonRepository $lessons): Response
    {
        $user = $session->get('demo_user', ['name' => 'Teacher','role' => 'teacher']);
        $lessonRows = [];
        foreach ($lessons->findAll() as $lesson) {
            if (strtolower((string) $lesson->getCreatedByRole()) !== 'teacher') {
                continue;
            }
            $lessonRows[] = [
                'id' => $lesson->getId(),
                'title' => $lesson->getTitle(),
                'subject' => $lesson->getSubject(),
                'difficulty' => $lesson->getDifficulty(),
                'group' => $lesson->getTargetGroup()?->getName(),
                'createdAt' => $lesson->getCreatedAt(),
            ];
        }

        return $this->render('teacher/lessons.html.twig', [
            'user' => $user,
            'lessonRows' => $lessonRows,
        ]);
    }

    #[Route('/teacher/quizzes', name: 'teacher_quizzes')]
    public function teacherQuizzes(SessionInterface $session, QuizRepository $quizzes): Response
    {
        $user = $session->get('demo_user', ['name' => 'Teacher','role' => 'teacher']);
        $teacherName = (string) ($user['name'] ?? 'Teacher');
        $quizRows = [];
        foreach ($quizzes->findAll() as $quiz) {
            if (strtolower((string) $quiz->getCreatedByRole()) !== 'teacher') {
                continue;
            }
            if ($quiz->getCreatedByName() !== null && strcasecmp((string) $quiz->getCreatedByName(), $teacherName) !== 0) {
                continue;
            }
            $quizRows[] = [
                'id' => $quiz->getId(),
                'lessonId' => $quiz->getLesson()?->getId(),
                'lessonTitle' => $quiz->getLesson()?->getTitle(),
                'difficulty' => $quiz->getDifficulty(),
                'questionsCount' => $quiz->getQuestions()->count(),
                'createdAt' => $quiz->getCreatedAt(),
            ];
        }

        return $this->render('teacher/quizzes.html.twig', [
            'user' => $user,
            'quizRows' => $quizRows,
        ]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function adminUsers(
        SessionInterface $session,
        Request $request,
        UserRepository $users,
        StudentProfileRepository $studentProfiles,
        EntityManagerInterface $em
    ): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);
        if (strtolower((string) ($user['role'] ?? 'admin')) !== 'admin') {
            return $this->redirectToRoute('home');
        }

        if ($request->isMethod('POST')) {
            $name = trim((string) $request->request->get('name', ''));
            $email = trim((string) $request->request->get('email', ''));
            $password = (string) $request->request->get('password', '');
            $role = strtolower((string) $request->request->get('role', 'student'));
            $grade = trim((string) $request->request->get('grade', ''));

            if ($name === '' || $email === '' || $password === '') {
                $this->addFlash('error', 'Name, email, and password are required.');
                return $this->redirectToRoute('admin_users');
            }
            if (!in_array($role, ['student', 'teacher', 'admin'], true)) {
                $role = 'student';
            }

            $newUser = new User();
            $newUser->setName($name);
            $newUser->setEmail($email);
            $newUser->setPassword($password);
            $newUser->setRole($role);
            $em->persist($newUser);

            if ($role === 'teacher') {
                $profile = new TeacherProfile();
                $profile->setCreatedAt(new \DateTimeImmutable());
                $profile->addUser($newUser);
                $em->persist($profile);
            }

            if ($role === 'student') {
                $profile = new StudentProfile();
                $profile->setUser($newUser);
                $profile->setGrade($grade !== '' ? $grade : 'Unassigned');
                $em->persist($profile);
            }

            $em->flush();
            $this->addFlash('success', 'User created.');
            return $this->redirectToRoute('admin_users', ['role' => $role]);
        }

        $roleFilter = strtolower((string) $request->query->get('role', ''));
        if (!in_array($roleFilter, ['student', 'teacher', 'admin'], true)) {
            $roleFilter = '';
        }

        $allUsers = $users->findAll();
        $studentProfileByUser = [];
        foreach ($studentProfiles->findAll() as $profile) {
            if ($profile->getUser() !== null) {
                $studentProfileByUser[$profile->getUser()->getId()] = $profile;
            }
        }

        $filteredUsers = [];
        foreach ($allUsers as $appUser) {
            if ($roleFilter !== '' && $appUser->getRole() !== $roleFilter) {
                continue;
            }
            $profile = $studentProfileByUser[$appUser->getId()] ?? null;
            $filteredUsers[] = [
                'id' => $appUser->getId(),
                'name' => $appUser->getName(),
                'email' => $appUser->getEmail(),
                'password' => $appUser->getPassword(),
                'role' => $appUser->getRole(),
                'grade' => $profile?->getGrade(),
                'group' => $profile?->getStudentGroup()?->getName(),
            ];
        }

        $studentsByGradeAndGroup = [];
        foreach ($filteredUsers as $row) {
            if ($row['role'] !== 'student') {
                continue;
            }
            $grade = $row['grade'] ?: 'Unassigned grade';
            $group = $row['group'] ?: 'No group';
            $studentsByGradeAndGroup[$grade][$group][] = $row;
        }
        ksort($studentsByGradeAndGroup);

        return $this->render('admin/users.html.twig', [
            'user' => $user,
            'roleFilter' => $roleFilter,
            'userRows' => $filteredUsers,
            'studentsByGradeAndGroup' => $studentsByGradeAndGroup,
        ]);
    }

    #[Route('/admin/groups', name: 'admin_groups')]
    public function adminGroups(
        SessionInterface $session,
        GroupRepository $groups,
        StudentProfileRepository $students
    ): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);

        $studentCounts = [];
        foreach ($students->findAll() as $studentProfile) {
            $group = $studentProfile->getStudentGroup();
            if ($group === null) {
                continue;
            }
            $studentCounts[$group->getId()] = ($studentCounts[$group->getId()] ?? 0) + 1;
        }

        $groupRows = [];
        foreach ($groups->findAll() as $group) {
            $groupRows[] = [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'teacher' => $this->teacherNameFromProfile($group->getTeacher()),
                'studentsCount' => $studentCounts[$group->getId()] ?? 0,
                'createdAt' => $group->getCreatedAt(),
                'createdBy' => $group->getCreatedBy() ?: $this->teacherNameFromProfile($group->getTeacher()),
            ];
        }

        return $this->render('admin/groups.html.twig', [
            'user' => $user,
            'groupRows' => $groupRows,
        ]);
    }

    private function teacherNameFromProfile(?TeacherProfile $profile): string
    {
        if ($profile === null) {
            return 'Unassigned teacher';
        }

        $teacherUser = $profile->getUser()->first();
        if ($teacherUser === false || $teacherUser === null) {
            return 'Unassigned teacher';
        }

        return $teacherUser->getName() ?: 'Unassigned teacher';
    }

    #[Route('/admin/lessons', name: 'admin_lessons')]
    public function adminLessons(
        SessionInterface $session,
        LessonRepository $lessons,
        PerformanceReportRepository $reports
    ): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);
        $reportByLesson = [];
        foreach ($reports->findAll() as $report) {
            $lessonId = $report->getLesson()?->getId();
            if ($lessonId === null) {
                continue;
            }
            $reportByLesson[$lessonId][] = (float) $report->getQuizScore();
        }

        $lessonRows = [];
        foreach ($lessons->findAll() as $lesson) {
            $scores = $reportByLesson[$lesson->getId()] ?? [];
            $avg = count($scores) > 0 ? array_sum($scores) / count($scores) : null;
            $lessonRows[] = [
                'id' => $lesson->getId(),
                'title' => $lesson->getTitle(),
                'subject' => $lesson->getSubject(),
                'creatorName' => $lesson->getCreatedByName(),
                'creatorRole' => $lesson->getCreatedByRole(),
                'groupName' => $lesson->getTargetGroup()?->getName(),
                'groupStudents' => $lesson->getTargetGroup()?->getStudents()->count() ?? 0,
                'createdAt' => $lesson->getCreatedAt(),
                'avgProgress' => $avg,
            ];
        }

        return $this->render('admin/lessons.html.twig', [
            'user' => $user,
            'lessonRows' => $lessonRows,
        ]);
    }

    #[Route('/admin/quizzes', name: 'admin_quizzes')]
    public function adminQuizzes(SessionInterface $session, QuizRepository $quizzes): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);
        $quizRows = [];
        foreach ($quizzes->findAll() as $quiz) {
            $quizRows[] = [
                'id' => $quiz->getId(),
                'difficulty' => $quiz->getDifficulty(),
                'lesson' => $quiz->getLesson()?->getTitle(),
                'creatorName' => $quiz->getCreatedByName(),
                'creatorRole' => $quiz->getCreatedByRole(),
                'createdAt' => $quiz->getCreatedAt(),
                'questionsCount' => $quiz->getQuestions()->count(),
            ];
        }

        return $this->render('admin/quizzes.html.twig', [
            'user' => $user,
            'quizRows' => $quizRows,
        ]);
    }

    #[Route('/admin/groups/{id}', name: 'admin_group_show', methods: ['GET'])]
    public function adminGroupShow(SessionInterface $session, Group $group, PerformanceReportRepository $reports): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin', 'role' => 'admin']);
        $studentScores = [];
        foreach ($reports->findAll() as $report) {
            $studentId = $report->getStudent()?->getId();
            if ($studentId === null) {
                continue;
            }
            $studentScores[$studentId][] = (float) $report->getQuizScore();
        }

        $members = [];
        foreach ($group->getStudents() as $studentProfile) {
            $scores = $studentScores[$studentProfile->getId()] ?? [];
            $avg = count($scores) > 0 ? array_sum($scores) / count($scores) : null;
            $members[] = [
                'studentProfileId' => $studentProfile->getId(),
                'name' => $studentProfile->getUser()?->getName(),
                'email' => $studentProfile->getUser()?->getEmail(),
                'grade' => $studentProfile->getGrade(),
                'avgScore' => $avg,
            ];
        }

        return $this->render('admin/group_show.html.twig', [
            'user' => $user,
            'group' => $group,
            'members' => $members,
            'teacherName' => $this->teacherNameFromProfile($group->getTeacher()),
        ]);
    }

    #[Route('/admin/groups/{id}/edit', name: 'admin_group_edit', methods: ['POST'])]
    public function adminGroupEdit(
        SessionInterface $session,
        Request $request,
        Group $group,
        EntityManagerInterface $em
    ): Response {
        $user = $session->get('demo_user', ['name' => 'Admin', 'role' => 'admin']);
        if (strtolower((string) ($user['role'] ?? 'admin')) !== 'admin') {
            return $this->redirectToRoute('home');
        }

        if (!$this->isCsrfTokenValid('admin_group_edit_'.$group->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
        }

        $name = trim((string) $request->request->get('name', ''));
        if ($name !== '') {
            $group->setName($name);
            $em->flush();
            $this->addFlash('success', 'Group updated.');
        }

        return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
    }

    #[Route('/admin/groups/{id}/delete', name: 'admin_group_delete', methods: ['POST'])]
    public function adminGroupDelete(
        SessionInterface $session,
        Request $request,
        Group $group,
        EntityManagerInterface $em
    ): Response {
        $user = $session->get('demo_user', ['name' => 'Admin', 'role' => 'admin']);
        if (strtolower((string) ($user['role'] ?? 'admin')) !== 'admin') {
            return $this->redirectToRoute('home');
        }

        if (!$this->isCsrfTokenValid('admin_group_delete_'.$group->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_group_show', ['id' => $group->getId()]);
        }

        foreach ($group->getStudents() as $student) {
            $student->setStudentGroup(null);
        }
        $em->remove($group);
        $em->flush();
        $this->addFlash('success', 'Group deleted.');

        return $this->redirectToRoute('admin_groups');
    }

    #[Route('/admin/lessons/{id}', name: 'admin_lesson_show', methods: ['GET'])]
    public function adminLessonShow(
        SessionInterface $session,
        Lesson $lesson,
        PerformanceReportRepository $reports
    ): Response {
        $user = $session->get('demo_user', ['name' => 'Admin', 'role' => 'admin']);
        $lessonScores = [];
        foreach ($reports->findBy(['lesson' => $lesson]) as $report) {
            $lessonScores[] = (float) $report->getQuizScore();
        }
        $avg = count($lessonScores) > 0 ? array_sum($lessonScores) / count($lessonScores) : null;

        return $this->render('admin/lesson_show.html.twig', [
            'user' => $user,
            'lesson' => $lesson,
            'avgScore' => $avg,
            'teacherName' => $this->teacherNameFromProfile($lesson->getTargetGroup()?->getTeacher()),
        ]);
    }

    #[Route('/admin/lessons/{id}/delete', name: 'admin_lesson_delete', methods: ['POST'])]
    public function adminLessonDelete(
        SessionInterface $session,
        Request $request,
        Lesson $lesson,
        EntityManagerInterface $em,
        QuizRepository $quizzes
    ): Response {
        $user = $session->get('demo_user', ['name' => 'Admin', 'role' => 'admin']);
        if (strtolower((string) ($user['role'] ?? 'admin')) !== 'admin') {
            return $this->redirectToRoute('home');
        }

        if (!$this->isCsrfTokenValid('admin_lesson_delete_'.$lesson->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_lessons');
        }

        foreach ($quizzes->findBy(['lesson' => $lesson]) as $quiz) {
            foreach ($quiz->getQuestions() as $question) {
                $em->remove($question);
            }
            $em->remove($quiz);
        }
        $em->remove($lesson);
        $em->flush();
        $this->addFlash('success', 'Lesson deleted.');

        return $this->redirectToRoute('admin_lessons');
    }

    #[Route('/admin/quizzes/{id}', name: 'admin_quiz_show', methods: ['GET'])]
    public function adminQuizShow(SessionInterface $session, Quiz $quiz): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin', 'role' => 'admin']);
        return $this->render('admin/quiz_show.html.twig', [
            'user' => $user,
            'quiz' => $quiz,
        ]);
    }

    #[Route('/admin/quizzes/{id}/delete', name: 'admin_quiz_delete', methods: ['POST'])]
    public function adminQuizDelete(SessionInterface $session, Request $request, Quiz $quiz, EntityManagerInterface $em): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin', 'role' => 'admin']);
        if (strtolower((string) ($user['role'] ?? 'admin')) !== 'admin') {
            return $this->redirectToRoute('home');
        }

        if (!$this->isCsrfTokenValid('admin_quiz_delete_'.$quiz->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_quizzes');
        }

        foreach ($quiz->getQuestions() as $question) {
            $em->remove($question);
        }
        $em->remove($quiz);
        $em->flush();
        $this->addFlash('success', 'Quiz deleted.');

        return $this->redirectToRoute('admin_quizzes');
    }
}
