<?php

namespace App\Entity;

use App\Repository\TeacherProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeacherProfileRepository::class)]
class TeacherProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'teacherProfile')]
    private Collection $user;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\OneToMany(targetEntity: Group::class, mappedBy: 'teacher')]
    private Collection $studyGroups;

    /**
     * @var Collection<int, TeacherComment>
     */
    #[ORM\OneToMany(targetEntity: TeacherComment::class, mappedBy: 'teacher')]
    private Collection $teacherComments;

    public function __construct()
    {
        $this->user = new ArrayCollection();
        $this->studyGroups = new ArrayCollection();
        $this->teacherComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
            $user->setTeacherProfile($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->user->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getTeacherProfile() === $this) {
                $user->setTeacherProfile(null);
            }
        }

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
     * @return Collection<int, Group>
     */
    public function getStudyGroups(): Collection
    {
        return $this->studyGroups;
    }

    public function addStudyGroup(Group $studyGroup): static
    {
        if (!$this->studyGroups->contains($studyGroup)) {
            $this->studyGroups->add($studyGroup);
            $studyGroup->setTeacher($this);
        }

        return $this;
    }

    public function removeStudyGroup(Group $studyGroup): static
    {
        if ($this->studyGroups->removeElement($studyGroup)) {
            // set the owning side to null (unless already changed)
            if ($studyGroup->getTeacher() === $this) {
                $studyGroup->setTeacher(null);
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
            $teacherComment->setTeacher($this);
        }

        return $this;
    }

    public function removeTeacherComment(TeacherComment $teacherComment): static
    {
        if ($this->teacherComments->removeElement($teacherComment)) {
            // set the owning side to null (unless already changed)
            if ($teacherComment->getTeacher() === $this) {
                $teacherComment->setTeacher(null);
            }
        }

        return $this;
    }
}
