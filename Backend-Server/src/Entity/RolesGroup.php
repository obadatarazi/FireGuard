<?php

namespace App\Entity;

use App\Constant\RoleType;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * RolesGroup
 *
 * @ORM\Table(name="roles_group",
 *  uniqueConstraints={
 *     @ORM\UniqueConstraint(name="idx_name", columns={"name"})},
 *  indexes={
 *     @ORM\Index(name="idx_created_at", columns={"created_at"}),
 *     @ORM\Index(name="idx_ft_name", columns={"name"}),
 *     @ORM\Index(name="ft_roles_idx", columns={"roles"})
 * })
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("all")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *      fields={"name"},
 *      message="duplicate_name"
 * )
 * @UniqueEntity(
 *      fields={"identifier"},
 *      message="duplicate_identifier"
 * )
 */
class RolesGroup
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Expose()
     * @OA\Property(example=1)
     * @Serializer\Groups({"list", "details"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Serializer\Expose()
     * @Assert\NotBlank()
     * @OA\Property(example="Super Admin")
     * @Serializer\Groups({"details", "list"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="roles", type="text", length=65555, nullable=false)
     * @OA\Property(type="array",
     *     @OA\Items(type="string",example="ROLE_CATEGORY_LIST")
     *     )
     * @Serializer\Groups({"details", "list"})
     */
    private $roles;

    /**
     * @var bool
     *
     * @ORM\Column(name="standarad", type="boolean", nullable=false)
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details"})
     */
    private $standard = false;


    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=255, nullable=true)
     */
    private $identifier;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = serialize($roles);
        return $this;
    }

    /**
     * @see UserInterface
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @OA\Property(property="rolesBySection",type="object",
     *     @OA\Property(property="section",type="string",example="Bank Account"),
     *     @OA\Property(property="roles",type="object",
     *          @OA\Property(property="role",type="",example="ROLE_BANK_ACCOUNT_ADD"),
     *          @OA\Property(property="name",type="",example="ROLE_BANK_ACCOUNT_ADD"),
     *          @OA\Property(property="description",type="",example="ROLE_BANK_ACCOUNT_ADD")
     *          )
     *     )
     * @Serializer\Groups({"details"})
     * @Serializer\SerializedName("rolesBySection")
     */
    public function getRolesBySection(): array
    {
        $roles = RoleType::cases();
        $myRoles = $this->getRoles();

        $rolesArray = [];
        foreach ($roles as $role) {
            if (in_array($role->getRole(), $myRoles)) {
                $section = $role->getSection();
                if (count($rolesArray) == 0
                    || $rolesArray[count($rolesArray) - 1]['section'] != $section)
                    $rolesArray[] = [
                        'section' => $section,
                        'roles' => []
                    ];
                $rolesArray[count($rolesArray) - 1]['roles'][] = [
                    'role' => $role->getRole(),
                    'name' => $role->getName(),
                    'description' => $role->getDesc(),
                ];
            }
        }
        return $rolesArray;
    }

    /**
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("roles")
     * @Serializer\Groups({"list", "details"})
     * @Serializer\Type("array<string>")
     * @return array
     */
    public function getRoles(): array
    {
        return unserialize($this->roles);
    }

    public function setStandard(bool $standard): self
    {
        $this->standard = $standard;
        return $this;
    }

    public function isStandard(): bool
    {
        return $this->standard;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     */
    public function beforeCreate()
    {
        $dateTime = new \DateTime();
        $this->createdAt = $dateTime;
        $this->updatedAt = $dateTime;
        if (!$this->identifier)
            $this->identifier = str_replace(' ', '_', strtoupper($this->name));
    }

    /**
     * @ORM\PreUpdate()
     */
    public function beforeUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

}
