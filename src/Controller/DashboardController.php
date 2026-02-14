<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
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
    public function admin(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);
        return $this->render('dashboard/admin.html.twig', ['user' => $user]);
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
    public function teacherGroups(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Teacher','role' => 'teacher']);
        return $this->render('teacher/groups.html.twig', ['user' => $user]);
    }

    #[Route('/teacher/lessons', name: 'teacher_lessons')]
    public function teacherLessons(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Teacher','role' => 'teacher']);
        return $this->render('teacher/lessons.html.twig', ['user' => $user]);
    }

    #[Route('/teacher/quizzes', name: 'teacher_quizzes')]
    public function teacherQuizzes(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Teacher','role' => 'teacher']);
        return $this->render('teacher/quizzes.html.twig', ['user' => $user]);
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function adminUsers(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);
        return $this->render('admin/users.html.twig', ['user' => $user]);
    }

    #[Route('/admin/groups', name: 'admin_groups')]
    public function adminGroups(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);
        return $this->render('admin/groups.html.twig', ['user' => $user]);
    }

    #[Route('/admin/lessons', name: 'admin_lessons')]
    public function adminLessons(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);
        return $this->render('admin/lessons.html.twig', ['user' => $user]);
    }

    #[Route('/admin/quizzes', name: 'admin_quizzes')]
    public function adminQuizzes(SessionInterface $session): Response
    {
        $user = $session->get('demo_user', ['name' => 'Admin','role' => 'admin']);
        return $this->render('admin/quizzes.html.twig', ['user' => $user]);
    }
}
