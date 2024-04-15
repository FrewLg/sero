<?php

namespace App\Entity;

use App\Entity\SERO\BoardMember;
use App\Entity\SERO\ReviewAssignment;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Profile $profile = null;

    #[ORM\OneToMany(targetEntity: TrainingMaterial::class, mappedBy: 'uploadedBy', orphanRemoval: true)]
    private Collection $trainingMaterials;

    #[ORM\OneToMany(targetEntity: Feedback::class, mappedBy: 'sentBy')]
    private Collection $feedback;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Directorate $directorate = null;

    #[ORM\OneToMany(targetEntity: TrainingRequest::class, mappedBy: 'requestedBy', orphanRemoval: true)]
    private Collection $trainingRequests;

    #[ORM\OneToMany(targetEntity: Course::class, mappedBy: 'createdBy')]
    private Collection $preparedCourses;

    #[ORM\ManyToMany(targetEntity: UserGroup::class, mappedBy: 'users')]
    private Collection $userGroup;

    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'users')]
    private Collection $permissions;

    #[ORM\OneToMany(targetEntity: Fund::class, mappedBy: 'createdBy')]
    private Collection $regFunds;

    #[ORM\OneToMany(targetEntity: FundTransaction::class, mappedBy: 'createdBy')]
    private Collection $fundTransactions;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $locale = null;

    /**
     * @var Collection<int, ReviewAssignment>
     */
    #[ORM\OneToMany(targetEntity: ReviewAssignment::class, mappedBy: 'irbreviewer')]
    private Collection $reviewAssignments;

    /**
     * @var Collection<int, BoardMember>
     */
    #[ORM\OneToMany(targetEntity: BoardMember::class, mappedBy: 'assignedBy')]
    private Collection $boardMembers;

  
    public function __construct()
    {
        $this->trainingMaterials = new ArrayCollection();
        $this->feedback = new ArrayCollection();
        $this->trainingRequests = new ArrayCollection();
        $this->preparedCourses = new ArrayCollection();
        $this->userGroup = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->regFunds = new ArrayCollection();
        $this->fundTransactions = new ArrayCollection();
        $this->reviewAssignments = new ArrayCollection();
        $this->boardMembers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function addRole(string $role): self
    {
        $roles = $this->roles; 
        $roles[] = $role; 

       
        $this->setRoles(array_unique($roles));
        

        return $this;
    }
    
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = "ROLE_USER";

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        // $roles[] = "ROLE_USER";

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function __toString()
    {
        
   return  "".$this->profile;
    }


    public function setProfile(?Profile $profile): static
    {
        // unset the owning side of the relation if necessary
        if ($profile === null && $this->profile !== null) {
            $this->profile->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($profile !== null && $profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * @return Collection<int, TrainingMaterial>
     */
    public function getTrainingMaterials(): Collection
    {
        return $this->trainingMaterials;
    }

    public function addTrainingMaterial(TrainingMaterial $trainingMaterial): static
    {
        if (!$this->trainingMaterials->contains($trainingMaterial)) {
            $this->trainingMaterials->add($trainingMaterial);
            $trainingMaterial->setUploadedBy($this);
        }

        return $this;
    }

    public function removeTrainingMaterial(TrainingMaterial $trainingMaterial): static
    {
        if ($this->trainingMaterials->removeElement($trainingMaterial)) {
            // set the owning side to null (unless already changed)
            if ($trainingMaterial->getUploadedBy() === $this) {
                $trainingMaterial->setUploadedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Feedback>
     */
    public function getFeedback(): Collection
    {
        return $this->feedback;
    }

    public function addFeedback(Feedback $feedback): static
    {
        if (!$this->feedback->contains($feedback)) {
            $this->feedback->add($feedback);
            $feedback->setSentBy($this);
        }

        return $this;
    }

    public function removeFeedback(Feedback $feedback): static
    {
        if ($this->feedback->removeElement($feedback)) {
            // set the owning side to null (unless already changed)
            if ($feedback->getSentBy() === $this) {
                $feedback->setSentBy(null);
            }
        }

        return $this;
    }

    public function getDirectorate(): ?Directorate
    {
        return $this->directorate;
    }

    public function setDirectorate(?Directorate $directorate): static
    {
        $this->directorate = $directorate;

        return $this;
    }

    /**
     * @return Collection<int, TrainingRequest>
     */
    public function getTrainingRequests(): Collection
    {
        return $this->trainingRequests;
    }

    public function addTrainingRequest(TrainingRequest $trainingRequest): static
    {
        if (!$this->trainingRequests->contains($trainingRequest)) {
            $this->trainingRequests->add($trainingRequest);
            $trainingRequest->setRequestedBy($this);
        }

        return $this;
    }

    public function removeTrainingRequest(TrainingRequest $trainingRequest): static
    {
        if ($this->trainingRequests->removeElement($trainingRequest)) {
            // set the owning side to null (unless already changed)
            if ($trainingRequest->getRequestedBy() === $this) {
                $trainingRequest->setRequestedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Course>
     */
    public function getPreparedCourses(): Collection
    {
        return $this->preparedCourses;
    }

    public function addPreparedCourse(Course $preparedCourse): static
    {
        if (!$this->preparedCourses->contains($preparedCourse)) {
            $this->preparedCourses->add($preparedCourse);
            $preparedCourse->setCreatedBy($this);
        }

        return $this;
    }

    public function removePreparedCourse(Course $preparedCourse): static
    {
        if ($this->preparedCourses->removeElement($preparedCourse)) {
            // set the owning side to null (unless already changed)
            if ($preparedCourse->getCreatedBy() === $this) {
                $preparedCourse->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getUserGroup(): Collection
    {
        return $this->userGroup;
    }

    public function addUserGroup(UserGroup $userGroup): static
    {
        if (!$this->userGroup->contains($userGroup)) {
            $this->userGroup->add($userGroup);
            $userGroup->addUser($this);
        }

        return $this;
    }

    public function removeUserGroup(UserGroup $userGroup): static
    {
        if ($this->userGroup->removeElement($userGroup)) {
            $userGroup->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): static
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }

        return $this;
    }

    public function removePermission(Permission $permission): static
    {
        $this->permissions->removeElement($permission);

        return $this;
    }

    /**
     * @return Collection<int, Fund>
     */
    public function getRegFunds(): Collection
    {
        return $this->regFunds;
    }

    public function addRegFund(Fund $regFund): static
    {
        if (!$this->regFunds->contains($regFund)) {
            $this->regFunds->add($regFund);
            $regFund->setCreatedBy($this);
        }

        return $this;
    }

    public function removeRegFund(Fund $regFund): static
    {
        if ($this->regFunds->removeElement($regFund)) {
            // set the owning side to null (unless already changed)
            if ($regFund->getCreatedBy() === $this) {
                $regFund->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FundTransaction>
     */
    public function getFundTransactions(): Collection
    {
        return $this->fundTransactions;
    }

    public function addFundTransaction(FundTransaction $fundTransaction): static
    {
        if (!$this->fundTransactions->contains($fundTransaction)) {
            $this->fundTransactions->add($fundTransaction);
            $fundTransaction->setCreatedBy($this);
        }

        return $this;
    }

    public function removeFundTransaction(FundTransaction $fundTransaction): static
    {
        if ($this->fundTransactions->removeElement($fundTransaction)) {
            // set the owning side to null (unless already changed)
            if ($fundTransaction->getCreatedBy() === $this) {
                $fundTransaction->setCreatedBy(null);
            }
        }

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return Collection<int, ReviewAssignment>
     */
    public function getReviewAssignments(): Collection
    {
        return $this->reviewAssignments;
    }

    public function addReviewAssignment(ReviewAssignment $reviewAssignment): static
    {
        if (!$this->reviewAssignments->contains($reviewAssignment)) {
            $this->reviewAssignments->add($reviewAssignment);
            $reviewAssignment->setIrbreviewer($this);
        }

        return $this;
    }

    public function removeReviewAssignment(ReviewAssignment $reviewAssignment): static
    {
        if ($this->reviewAssignments->removeElement($reviewAssignment)) {
            // set the owning side to null (unless already changed)
            if ($reviewAssignment->getIrbreviewer() === $this) {
                $reviewAssignment->setIrbreviewer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BoardMember>
     */
    public function getBoardMembers(): Collection
    {
        return $this->boardMembers;
    }

    public function addBoardMember(BoardMember $boardMember): static
    {
        if (!$this->boardMembers->contains($boardMember)) {
            $this->boardMembers->add($boardMember);
            $boardMember->setAssignedBy($this);
        }

        return $this;
    }

    public function removeBoardMember(BoardMember $boardMember): static
    {
        if ($this->boardMembers->removeElement($boardMember)) {
            // set the owning side to null (unless already changed)
            if ($boardMember->getAssignedBy() === $this) {
                $boardMember->setAssignedBy(null);
            }
        }

        return $this;
    }

    
}
