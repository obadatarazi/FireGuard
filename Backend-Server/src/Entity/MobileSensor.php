<?php

namespace App\Entity;

use App\Repository\MobileSensorRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MobileSensor
 *
 * @ORM\Table(name="mobile_sensor",
 *      indexes={
 *          @ORM\Index(name="center_id", columns={"center_id"}),
 *          @ORM\Index(name="idx_mobile_sensor_type", columns={"type"}),
 *          @ORM\Index(name="idx_mobile_sensor_status", columns={"status"}),
 *          @ORM\Index(name="idx_mobile_sensor_last_seen_at", columns={"last_seen_at"})
 * })
 * @ORM\Entity(repositoryClass=MobileSensorRepository::class)
 * @Serializer\ExclusionPolicy("All")
 * @ORM\HasLifecycleCallbacks()
 */
class MobileSensor
{
    public const TYPE_DRONE = 'DRONE';
    public const TYPE_CAR_ROBOT = 'CAR_ROBOT';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Serializer\Expose()
     * @OA\Property(example="Drone Alpha")
     * @Serializer\Groups({"list", "details"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=30, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Choice(choices={MobileSensor::TYPE_DRONE, MobileSensor::TYPE_CAR_ROBOT})
     * @Serializer\Expose()
     * @OA\Property(type="string", enum={"DRONE","CAR_ROBOT"}, example="DRONE")
     * @Serializer\Groups({"list", "details"})
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50, nullable=false)
     * @Assert\NotBlank()
     * @Serializer\Expose()
     * @OA\Property(example="ACTIVE")
     * @Serializer\Groups({"list", "details"})
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Serializer\Expose()
     * @OA\Property(example="33.26225")
     * @Serializer\Groups({"list", "details"})
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Serializer\Expose()
     * @OA\Property(example="32.62545")
     * @Serializer\Groups({"list", "details"})
     */
    private $latitude;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_seen_at", type="datetime", nullable=false)
     * @Assert\NotBlank()
     * @OA\Property(example="2000-11-16")
     */
    private $lastSeenAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @Serializer\Expose()
     * @OA\Property(example="2026-03-25T12:00:00+00:00")
     * @Serializer\Groups({"list", "details"})
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     * @Serializer\Expose()
     * @OA\Property(example="2026-03-25T12:00:00+00:00")
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
     * @Assert\NotBlank()
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeInterface
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(\DateTimeInterface $lastSeenAt): static
    {
        $this->lastSeenAt = $lastSeenAt;
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
        if (!$this->lastSeenAt) {
            $this->lastSeenAt = $dateTime;
        }
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
        if (!$this->getCenter()) {
            return [];
        }

        return [
            'id' => $this->getCenter()->getId(),
            'name' => $this->getCenter()->getName(),
            'createdAt' => $this->getCenter()->getCreatedAt(),
        ];
    }
}

