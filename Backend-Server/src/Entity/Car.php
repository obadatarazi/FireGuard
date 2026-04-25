<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Car
 *
 * @ORM\Table(name="car",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="idx_unique_number_plate", columns={"number_plate"})
 *     },
 *     indexes={
 *          @ORM\Index(name="idx_ft_name", columns={"name"}),
 *          @ORM\Index(name="model", columns={"model"}),
 *          @ORM\Index(name="center_id", columns={"center_id"})
 *     })
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("All")
 * @UniqueEntity(
 *      fields={"numberPlate"},
 *      message="duplicate_number_plate"
 *  )
 * @ORM\HasLifecycleCallbacks()
 */
class Car
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
     * @OA\Property(example="RIO")
     * @Serializer\Groups({"list", "details"})
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="model", type="string", length=50, nullable=true)
     * @Serializer\Expose()
     * @OA\Property(example="Kia")
     * @Serializer\Groups({"list", "details"})
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="number_plate", type="string", length=10, nullable=false)
     * @Serializer\Expose()
     * @OA\Property(example="565256")
     * @Serializer\Groups({"list", "details"})
     */
    private $numberPlate;

    /**
     * @var \Center
     *
     * @ORM\ManyToOne(targetEntity="Center")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="center_id", referencedColumnName="id")
     * })
     * @Assert\NotBlank()
     */
    private $center;

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

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getNumberPlate(): ?string
    {
        return $this->numberPlate;
    }

    public function setNumberPlate(string $numberPlate): static
    {
        $this->numberPlate = $numberPlate;

        return $this;
    }

    public function getCenter(): ?Center
    {
        return $this->center;
    }

    public function setCenter(?Center $center): self
    {
        $this->center = $center;

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
            'name' => $this->getCenter()?->getName(),
            'phoneNumber' => $this->getCenter()?->getPhoneNumber()
        ];
    }
}
