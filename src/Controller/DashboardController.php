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

final class DashboardController extends AbstractController
{
    /**
     * THIS IS THE CRITICAL FIX: This route handles the redirect logic 
     * immediately after the user logs in successfully.
     */
    #[Route('/login-redirect', name: 'app_login_redirect')]
    public function loginRedirect(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $role = $user->getUserRole(); 

        if ($role === 'admin') {
            return $this->redirectToRoute('admin_dashboard');
        } elseif ($role === 'teacher') {
            return $this->redirectToRoute('teacher_dashboard');
        } else {
            return $this->redirectToRoute('student_dashboard');
        }
    }

    #[Route('/student/dashboard', name: 'student_dashboard')]
    public function student(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('dashboard/student.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/teacher/dashboard', name: 'teacher_dashboard')]
    public function teacher(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('dashboard/teacher.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function admin(
        UserRepository $users,
        LessonRepository $lessons,
        QuizRepository $quizzes
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $allUsers = $users->findAll();
        $teacherCount = 0;
        $studentCount = 0;

        foreach ($allUsers as $appUser) {
            if ($appUser->getUserRole() === 'teacher') {
                ++$teacherCount;
            }
            if ($appUser->getUserRole() === 'student') {
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

    private function teacherNameFromProfile(?TeacherProfile $profile): string
    {
        if ($profile === null) {
            return 'Unassigned teacher';
        }

        $teacherUser = $profile->getUser()->first();
        if (!$teacherUser) {
            return 'Unassigned teacher';
        }

        return $teacherUser->getFirstName() . ' ' . $teacherUser->getLastName();
    }
}