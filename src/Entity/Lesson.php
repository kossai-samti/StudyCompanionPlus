<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, StudyMaterial>
     */
    #[ORM\OneToMany(targetEntity: StudyMaterial::class, mappedBy: 'lesson')]
    private Collection $studyMaterials;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(length: 255)]
    private ?string $difficulty = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file_path = null;

    /**
     * @var Collection<int, Quiz>
     */
    #[ORM\OneToMany(targetEntity: Quiz::class, mappedBy: 'lesson')]
    private Collection $quizzes;

    /**
     * @var Collection<int, PerformanceReport>
     */
    #[ORM\OneToMany(targetEntity: PerformanceReport::class, mappedBy: 'lesson')]
    private Collection $performanceReports;

    public function __construct()
    {
        $this->studyMaterials = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
        $this->performanceReports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, StudyMaterial>
     */
    public function getStudyMaterials(): Collection
    {
        return $this->studyMaterials;
    }

    public function addStudyMaterial(StudyMaterial $studyMaterial): static
    {
        if (!$this->studyMaterials->contains($studyMaterial)) {
            $this->studyMaterials->add($studyMaterial);
            $studyMaterial->setLesson($this);
        }

        return $this;
    }

    public function removeStudyMaterial(StudyMaterial $studyMaterial): static
    {
        if ($this->studyMaterials->removeElement($studyMaterial)) {
            // set the owning side to null (unless already changed)
            if ($studyMaterial->getLesson() === $this) {
                $studyMaterial->setLesson(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(?string $file_path): static
    {
        $this->file_path = $file_path;

        return $this;
    }

    /**
     * @return Collection<int, Quiz>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setLesson($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            // set the owning side to null (unless already changed)
            if ($quiz->getLesson() === $this) {
                $quiz->setLesson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PerformanceReport>
     */
    public function getPerformanceReports(): Collection
    {
        return $this->performanceReports;
    }

    public function addPerformanceReport(PerformanceReport $performanceReport): static
    {
        if (!$this->performanceReports->contains($performanceReport)) {
            $this->performanceReports->add($performanceReport);
            $performanceReport->setLesson($this);
        }

        return $this;
    }

    public function removePerformanceReport(PerformanceReport $performanceReport): static
    {
        if ($this->performanceReports->removeElement($performanceReport)) {
            // set the owning side to null (unless already changed)
            if ($performanceReport->getLesson() === $this) {
                $performanceReport->setLesson(null);
            }
        }

        return $this;
    }
}
