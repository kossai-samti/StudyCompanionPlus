<?php

namespace App\Entity;

use App\Repository\StudentProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StudentProfileRepository::class)]
class StudentProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Grade is required')]
    private ?string $grade = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Assert\NotNull(message: 'Student must be linked to a User')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Group $studentGroup = null;

    /**
     * @var Collection<int, StudentAnswer>
     */
    #[ORM\OneToMany(targetEntity: StudentAnswer::class, mappedBy: 'student')]
    private Collection $studentAnswers;

    /**
     * @var Collection<int, PerformanceReport>
     */
    #[ORM\OneToMany(targetEntity: PerformanceReport::class, mappedBy: 'student')]
    private Collection $performanceReports;

    /**
     * @var Collection<int, TeacherComment>
     */
    #[ORM\OneToMany(targetEntity: TeacherComment::class, mappedBy: 'student')]
    private Collection $teacherComments;

    public function __construct()
    {
        $this->studentAnswers = new ArrayCollection();
        $this->performanceReports = new ArrayCollection();
        $this->teacherComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(string $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStudentGroup(): ?Group
    {
        return $this->studentGroup;
    }

    public function setStudentGroup(?Group $studentGroup): static
    {
        $this->studentGroup = $studentGroup;

        return $this;
    }

    /**
     * @return Collection<int, StudentAnswer>
     */
    public function getStudentAnswers(): Collection
    {
        return $this->studentAnswers;
    }

    public function addStudentAnswer(StudentAnswer $studentAnswer): static
    {
        if (!$this->studentAnswers->contains($studentAnswer)) {
            $this->studentAnswers->add($studentAnswer);
            $studentAnswer->setStudent($this);
        }

        return $this;
    }

    public function removeStudentAnswer(StudentAnswer $studentAnswer): static
    {
        if ($this->studentAnswers->removeElement($studentAnswer)) {
            // set the owning side to null (unless already changed)
            if ($studentAnswer->getStudent() === $this) {
                $studentAnswer->setStudent(null);
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
            $performanceReport->setStudent($this);
        }

        return $this;
    }

    public function removePerformanceReport(PerformanceReport $performanceReport): static
    {
        if ($this->performanceReports->removeElement($performanceReport)) {
            // set the owning side to null (unless already changed)
            if ($performanceReport->getStudent() === $this) {
                $performanceReport->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TeacherComment>
     */
    public function getTeacherComments(): Collection
    {
        return $this->teacherComments;
    }

    public function addTeacherComment(TeacherComment $teacherComment): static
    {
        if (!$this->teacherComments->contains($teacherComment)) {
            $this->teacherComments->add($teacherComment);
            $teacherComment->setStudent($this);
        }

        return $this;
    }

    public function removeTeacherComment(TeacherComment $teacherComment): static
    {
        if ($this->teacherComments->removeElement($teacherComment)) {
            // set the owning side to null (unless already changed)
            if ($teacherComment->getStudent() === $this) {
                $teacherComment->setStudent(null);
            }
        }

        return $this;
    }
}
