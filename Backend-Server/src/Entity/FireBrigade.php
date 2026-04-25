<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * FireBrigade
 *
 * @ORM\Table(name="fire_brigade",
 *      indexes={
 *          @ORM\Index(name="center_id", columns={"center_id"})
 * })
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("All")
 * @ORM\HasLifecycleCallbacks()
 */
class FireBrigade implements UserInterface, PasswordAuthenticatedUserInterface
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
     * @ORM\Column(name="name", type="string", length=20, nullable=false)
     * @Serializer\Expose()
     * @OA\Property(example="name fire bridge")
     * @Serializer\Groups({"list", "details"})
     */
    private $name;

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
     * @var int
     *
     * @ORM\Column(name="number_of_team", type="integer", nullable=false)
     * @Serializer\Expose()
     * @OA\Property(example=10)
     * @Serializer\Groups({"list", "details"})
     */
    private $numberOfTeam;

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
     * @var Center
     *
     * @ORM\ManyToOne(targetEntity="Center")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="center_id", referencedColumnName="id")
     * })
     */
    private $center;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getNumberOfTeam(): ?int
    {
        return $this->numberOfTeam;
    }

    public function setNumberOfTeam(int $numberOfTeam): static
    {
        $this->numberOfTeam = $numberOfTeam;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCenter(): ?Center
    {
        return $this->center;
    }

    public function setCenter(?Center $center): static
    {
        $this->center = $center;

        return $this;
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
     * @Serializer\SerializedName("center")
     * @Serializer\Type("array")
     * @Serializer\Groups({"list","details"})
     * @return array
    */
    public function getCustomCenter(): array
    {
        return [
            'id' => $this->getCenter()->getId(),
            'name' => $this->getCenter()->getName(),
            'createdAt' => $this->getCenter()->getCreatedAt()
        ];
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = strtolower($email);
        return $this;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }
}
