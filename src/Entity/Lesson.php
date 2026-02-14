<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Symfony\Component\Validator\Constraints as Assert;
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
    #[Assert\NotBlank(message: "Title is required")]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Subject is required")]
    #[Assert\Length(max: 255)]
    private ?string $subject = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Difficulty is required")]
    #[Assert\Choice(choices: ['easy','medium','hard'], message: 'Choose a valid difficulty.')]
    private ?string $difficulty = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file_path = null;

    #[ORM\ManyToOne]
    private ?Group $target_group = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $created_by_role = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $created_by_name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

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
        $this->created_at = new \DateTimeImmutable();
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

    public function getTargetGroup(): ?Group
    {
        return $this->target_group;
    }

    public function setTargetGroup(?Group $target_group): static
    {
        $this->target_group = $target_group;

        return $this;
    }

    public function getCreatedByRole(): ?string
    {
        return $this->created_by_role;
    }

    public function setCreatedByRole(?string $created_by_role): static
    {
        $this->created_by_role = $created_by_role;

        return $this;
    }

    public function getCreatedByName(): ?string
    {
        return $this->created_by_name;
    }

    public function setCreatedByName(?string $created_by_name): static
    {
        $this->created_by_name = $created_by_name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

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
