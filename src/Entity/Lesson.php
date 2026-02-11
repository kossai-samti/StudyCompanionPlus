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

    public function __construct()
    {
        $this->studyMaterials = new ArrayCollection();
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
}
