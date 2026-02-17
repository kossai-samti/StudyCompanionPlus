<?php

namespace App\Controller;

use App\Entity\Lesson; 
use App\Entity\Quiz;   
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student')]
final class StudentProfileController extends AbstractController
{
    #[Route('/dashboard', name: 'student_dashboard', methods: ['GET'])]
    public function dashboard(EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        
        // Fetching real data
        $recentLessons = $em->getRepository(Lesson::class)->findBy([], ['id' => 'DESC'], 3);
        $availableQuizzes = $em->getRepository(Quiz::class)->findBy([], ['id' => 'DESC'], 2);

        return $this->render('dashboard/student.html.twig', [
            'user' => $user,
            'lessons' => $recentLessons,
            'quizzes' => $availableQuizzes,
            'core_id' => '#' . str_pad($user->getId() ?? 0, 4, '0', STR_PAD_LEFT),
            'total_lessons_count' => count($recentLessons), 
            'avg_score' => '84%',
            'mastered_count' => 6
        ]);
    }

    #[Route('/profile/list', name: 'app_student_profile_list', methods: ['GET'])]
    public function index(StudentProfileRepository $studentProfileRepository): Response
    {
        return $this->render('student_profile/index.html.twig', [
            'student_profiles' => $studentProfileRepository->findAll(),
        ]);
    }
}