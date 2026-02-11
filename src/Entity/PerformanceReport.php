<?php

namespace App\Entity;

use App\Repository\PerformanceReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PerformanceReportRepository::class)]
class PerformanceReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'performanceReports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StudentProfile $student = null;

    #[ORM\ManyToOne(inversedBy: 'performanceReports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lesson $lesson = null;

    #[ORM\Column]
    private ?float $quiz_score = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $weak_topics = null;

    #[ORM\Column(length: 50)]
    private ?string $mastery_status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?StudentProfile
    {
        return $this->student;
    }

    public function setStudent(?StudentProfile $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;

        return $this;
    }

    public function getQuizScore(): ?float
    {
        return $this->quiz_score;
    }

    public function setQuizScore(float $quiz_score): static
    {
        $this->quiz_score = $quiz_score;

        return $this;
    }

    public function getWeakTopics(): ?string
    {
        return $this->weak_topics;
    }

    public function setWeakTopics(?string $weak_topics): static
    {
        $this->weak_topics = $weak_topics;

        return $this;
    }

    public function getMasteryStatus(): ?string
    {
        return $this->mastery_status;
    }

    public function setMasteryStatus(string $mastery_status): static
    {
        $this->mastery_status = $mastery_status;

        return $this;
    }
}
