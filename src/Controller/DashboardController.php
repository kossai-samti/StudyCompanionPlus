<?php

namespace App\Controller;

use App\Repository\LessonRepository;
use App\Repository\GroupRepository;
use App\Repository\StudentProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

final class DashboardController extends AbstractController
{
    /**
     * This route MUST be open to all logged-in users.
     * It detects the role and sends them to the correct "Home".
     */
    #[Route('/login-redirect', name: 'app_login_redirect')]
    public function loginRedirect(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Use the same logic as your User Entity
        $role = $user->getUserRole(); 

        if ($role === 'admin') {
            return $this->redirectToRoute('admin_dashboard');
        }
        
        if ($role === 'teacher') {
            return $this->redirectToRoute('teacher_dashboard');
        }

        // If they aren't Admin or Teacher, they MUST be a student.
        // Make sure this route name exists in your StudentController!
        return $this->redirectToRoute('student_dashboard'); 
    }

    #[Route('/teacher/dashboard', name: 'teacher_dashboard')]
    // Ensure you DON'T have an #[IsGranted('ROLE_TEACHER')] at the very top of the CLASS
    // if you want the redirect to work for everyone.
    public function teacher(
        StudentProfileRepository $studentRepo,
        GroupRepository $groupRepo,
        LessonRepository $lessonRepo
    ): Response {
        // Double check authority inside the method for safety
        if ($this->getUser()->getUserRole() !== 'teacher') {
            throw $this->createAccessDeniedException('Neural Access Denied: Faculty Clearance Required.');
        }

        return $this->render('dashboard/teacher.html.twig', [
            'user' => $this->getUser(),
            'studentCount' => count($studentRepo->findAll()),
            'groupCount' => count($groupRepo->findAll()),
            'lessonCount' => count($lessonRepo->findAll()),
            'students' => $studentRepo->findAll()
        ]);
    }
}