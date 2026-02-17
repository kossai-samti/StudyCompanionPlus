<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'First name is required')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Last name is required')]
    private ?string $lastName = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Enter a valid email')]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Role is required')]
    private ?string $userRole = 'student'; // Default role

    #[ORM\ManyToOne(inversedBy: 'user')]
    private ?TeacherProfile $teacherProfile = null;

    public function getId(): ?int { return $this->id; }

    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $firstName): static { $this->firstName = $firstName; return $this; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): static { $this->lastName = $lastName; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }

    /**
     * FIXED: This method now correctly maps your custom 'userRole' 
     * to Symfony's required ROLE_ format.
     */
    public function getRoles(): array {
        $roles = $this->roles;
        
        // Convert 'student' to 'ROLE_STUDENT', 'admin' to 'ROLE_ADMIN', etc.
        if ($this->userRole) {
            $roles[] = 'ROLE_' . strtoupper($this->userRole);
        }

        // Guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getUserRole(): ?string { return $this->userRole; }
    public function setUserRole(string $userRole): static { $this->userRole = $userRole; return $this; }

    public function eraseCredentials(): void {}

    public function getTeacherProfile(): ?TeacherProfile { return $this->teacherProfile; }
    public function setTeacherProfile(?TeacherProfile $teacherProfile): static { $this->teacherProfile = $teacherProfile; return $this; }
}