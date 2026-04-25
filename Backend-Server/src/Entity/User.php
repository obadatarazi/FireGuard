<?php

namespace App\Entity;

use App\Constant\RoleType;
use App\Constant\RolesGroupsIdentifiers;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="user",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="idx_email", columns={"email"}),
 *          @ORM\UniqueConstraint(name="idx_phone_number", columns={"phone_number"}),
 *     },
 *     indexes={
 *              @ORM\Index(name="idx_created_at", columns={"created_at"}),
 *              @ORM\Index(name="idx_date_of_birth", columns={"date_of_birth"}),
 *              @ORM\Index(name="idx_ft_name_email_phone_number", columns={"email","full_name","phone_number"}),
 *              @ORM\Index(name="idx_gender", columns={"gender"}),
 *     })
 * @ORM\Entity
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Serializer\ExclusionPolicy("all")
 * @UniqueEntity(
 *      fields={"phoneNumber"},
 *      message="duplicate_phone_number"
 * )
 * @UniqueEntity(
 *       fields={"email"},
 *       message="duplicate_email"
 *  )
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Expose()
     * @OA\Property(example=1)
     * @Serializer\Groups({"list", "details", "profile"})
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @OA\Property(example="client@email.com")
     * @Assert\Email()
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details", "profile"})
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="full_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Serializer\Expose()
     * @OA\Property(example="superman")
     * @Serializer\Groups({"list", "details", "profile"})
     */
    private $fullName;


    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Serializer\Expose()
     * @Assert\Length(
     *     max=13, maxMessage="max_digits_exceed",
     *     min=4, minMessage="min_digits_exceed"
     * )
     * @Assert\Regex(
     *     pattern="/^[+]?[0-9]+$/",
     *     message="invalid_phone_number"
     * )
     * @OA\Property(example="0944230661")
     * @Serializer\Groups({"details","list","profile"})
     */
    private $phoneNumber;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=false)
     * @OA\Property(example="male")
     *
     */
    #[Assert\Choice(choices: ['FEMALE', 'MALE'], message: 'Choose a valid gender.')]
    private $gender;

    /**
     * @var string
     *
     */
    private $genderValue;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_of_birth", type="datetime", nullable=true)
     * @Serializer\Expose()
     * @OA\Property(example="2000-11-16")
     * @Serializer\Groups({"details", "profile"})
     */
    private $dateOfBirth;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @Serializer\Expose()
     * @OA\Property(example="2022-11-16T22:36:33+01:00")
     * @Serializer\Groups({"list", "details"})
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     * @Serializer\Expose()
     * @OA\Property(example="2022-11-16T22:36:33+01:00")
     * @Serializer\Groups({"list", "details"})
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="verified_at", type="datetime", nullable=true)
     * @Serializer\Expose()
     * @OA\Property(example="2022-11-16T22:36:33+01:00")
     * @Serializer\Groups({"list", "details"})
     */
    private $verifiedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="avatar_file_url", type="text", length=65535, nullable=true)
     * @Serializer\Expose()
     * @OA\Property(example="/uploads/user/Nov-2022/988df180-b30a-412a-bdb1-b8e63344be97.jpg")
     * @Serializer\Groups({"list", "details","profile"})
     */
    private $avatarFileUrl;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="RolesGroup" ,cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinTable(name="user_roles_group",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="roles_group_id", referencedColumnName="id")
     *   }
     * )
     */
    private $rolesGroups;

    public function __construct()
    {
        $this->rolesGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = strtolower($email);
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getRolesGroups(): ArrayCollection | Collection
    {
        return $this->rolesGroups;
    }

    public function addRolesGroup(RolesGroup $rolesGroup): self
    {
        if (!$this->rolesGroups->contains($rolesGroup)) {
            $this->rolesGroups->add($rolesGroup);
        }
        return $this;
    }

    public function removeRolesGroup(RolesGroup $rolesGroup): self
    {
        if ($this->rolesGroups->contains($rolesGroup)) {
            $this->rolesGroups->removeElement($rolesGroup);
        }
        return $this;
    }

    /**
     * @see UserInterface
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\Type("array<string>")
     * @Serializer\Groups({"details", "profile"})
     * @Serializer\SerializedName("roles")
     */
    public function getRoles(): array
    {
        $roles = [];
        $isSuperAdmin = false;
        /** @var RolesGroup $rolesGroup */
        foreach ($this->getRolesGroups() as $rolesGroup) {
            $roles = array_merge($rolesGroup->getRoles(), $roles);
            if ($rolesGroup->getIdentifier() === RolesGroupsIdentifiers::SuperAdmin->value) {
                $isSuperAdmin = true;
            }
        }

        // Keep legacy SUPER_ADMIN accounts fully privileged even if their stored role set is outdated.
        if ($isSuperAdmin) {
            $roles = array_merge(
                $roles,
                array_map(
                    static fn (RoleType $roleType): string => $roleType->getRole(),
                    RoleType::cases()
                )
            );
        }

        return array_values(array_unique($roles));
    }

    /**
     * @see UserInterface
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"details", "profile"})
     * @Serializer\Expose()
     * @Serializer\SerializedName("rolesGroups")
     */
    public function getCustomRolesGroups(): array
    {
        $rolesGroups = [];
        /** @var RolesGroup $rolesGroup */
        foreach ($this->rolesGroups as $rolesGroup) {
            $rolesGroups[] = [
                'id' => $rolesGroup->getId(),
                'name' => $rolesGroup->getName(),
                'roles' => $rolesGroup->getRoles(),
            ];
        }
        return $rolesGroups;
    }

    /**
     * @see UserInterface
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"list"})
     * @Serializer\Expose()
     * @Serializer\SerializedName("rolesGroups")
     */
    public function getCustomRolesGroupsList(): array
    {
        $rolesGroups = [];
        /** @var RolesGroup $rolesGroup */
        foreach ($this->rolesGroups as $rolesGroup) {
            $rolesGroups[] = [
                'id' => $rolesGroup->getId(),
                'name' => $rolesGroup->getName(),
            ];
        }
        return $rolesGroups;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function setAvatarFileUrl(?string $avatarFileUrl): self
    {
        $this->avatarFileUrl = $avatarFileUrl;
        return $this;
    }

    public function getAvatarFileUrl(): ?string
    {
        return $this->avatarFileUrl;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setCreatedAt(?\DateTime $createdAt) : self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setDateOfBirth(?\DateTime $dateOfBirth) : self
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getDateOfBirth(): ?\DateTime
    {
        return $this->dateOfBirth;
    }

    public function setGender(?string $gender) : self
    {
        $this->gender = $gender;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGenderValue(?string $genderValue): self
    {
        $this->genderValue = $genderValue;
        return $this;
    }

    public function getGenderValue(): ?string
    {
        return $this->genderValue;
    }

    public function setUpdatedAt(?\DateTime $updatedAt) : self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setVerifiedAt(?\DateTime $verifiedAt) : self
    {
        $this->verifiedAt = $verifiedAt;
        return $this;
    }

    public function getVerifiedAt(): ?\DateTime
    {
        return $this->verifiedAt;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
    }

    public function getUserIdentifier() : string
    {
        return $this->getEmail();
    }

    /**
     * @ORM\PrePersist()
     */
    public function beforeCreate(): void
    {
        $dateTime = new \DateTime();
        $this->createdAt = $dateTime;
        $this->updatedAt = $dateTime;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function beforeUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("gender")
     * @Serializer\Type("array")
     * @Serializer\Groups({"list", "details", "profile"})
     * @return array
     */
    public function getCustomGender(): array
    {
        return [
            'label' => $this->getGenderValue(),
            'value' => $this->gender,
        ];
    }
}
