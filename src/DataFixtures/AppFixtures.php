<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\TeacherProfile;
use App\Entity\StudentProfile;
use App\Entity\Group as GroupEntity;
use App\Entity\Lesson;
use App\Entity\StudyMaterial;
use App\Entity\Quiz;
use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Create admin
        $admin = new User();
        $admin->setName('Admin User')->setEmail('admin@example.test')->setPassword('adminpass')->setRole('admin');
        $manager->persist($admin);

        // Teacher
        $teacherUser = new User();
        $teacherUser->setName('Jane Teacher')->setEmail('jane.teacher@example.test')->setPassword('teacherpass')->setRole('teacher');
        $manager->persist($teacherUser);

        $teacherProfile = new TeacherProfile();
        $teacherProfile->setUser($teacherUser);
        $manager->persist($teacherProfile);

        // Group
        $group = new GroupEntity();
        $group->setName('Group A');
        $group->setTeacher($teacherProfile);
        $manager->persist($group);

        // Student
        $studentUser = new User();
        $studentUser->setName('Sam Student')->setEmail('sam.student@example.test')->setPassword('studentpass')->setRole('student');
        $manager->persist($studentUser);

        $studentProfile = new StudentProfile();
        $studentProfile->setUser($studentUser)->setGrade('10')->setStudentGroup($group);
        $manager->persist($studentProfile);

        // Lessons
        $lesson1 = new Lesson();
        $lesson1->setTitle('Intro to Biology')->setSubject('Biology')->setDifficulty('easy')->setFilePath(null);
        $manager->persist($lesson1);

        $lesson2 = new Lesson();
        $lesson2->setTitle('Algebra Basics')->setSubject('Mathematics')->setDifficulty('medium')->setFilePath(null);
        $manager->persist($lesson2);

        // StudyMaterial
        $sm = new StudyMaterial();
        $sm->setType('summary')->setContent('Short summary content')->setSummary('This is a summary')->setFlashcards('[]')->setLesson($lesson1);
        $manager->persist($sm);

        // Quiz for lesson1
        $quiz = new Quiz();
        $quiz->setLesson($lesson1)->setDifficulty('easy');
        $manager->persist($quiz);

        // Questions
        $q1 = new Question();
        $q1->setQuiz($quiz)->setText('What is the powerhouse of the cell?')->setOptions(['Nucleus','Mitochondria','Ribosome'])->setCorrectAnswer('Mitochondria');
        $manager->persist($q1);

        $q2 = new Question();
        $q2->setQuiz($quiz)->setText('Which gas do plants produce?')->setOptions(['Oxygen','Nitrogen','Carbon dioxide'])->setCorrectAnswer('Oxygen');
        $manager->persist($q2);

        $manager->flush();
    }
}
