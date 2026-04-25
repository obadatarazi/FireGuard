<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Device
 *
 * @ORM\Table(name="device",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="idx_unique_device_name", columns={"name"})
 * },
 *     indexes={
 *          @ORM\Index(name="idx_ft_device_name", columns={"name"}),
 *          @ORM\Index(name="forest_id", columns={"forest_id"})
 * })
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("All")
 * @UniqueEntity(
 *        fields={"name"},
 *        message="duplicate_device_name"
 *   )
 * @ORM\HasLifecycleCallbacks()
 */
class Device
{
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
     * @Serializer\Groups({"list", "details"})
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details"})
     */
    private $description;
    
    /**
    * @var string|null
    *
    * @ORM\Column(name="name_address", type="string", length=100, nullable=true)
    * @Serializer\Expose()
    * @OA\Property(example="damas")
    * @Serializer\Groups({"list", "details"})
    */
    private $nameAddress;
    
    
    /**
    * @var string
    *
    * @ORM\Column(name="longitude", type="string", length=255, nullable=false)
    * @Assert\NotBlank()
    * @OA\Property(example="Hamra")
    * @Serializer\Expose()
    * @Serializer\Groups({"list", "details"})
    */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=255, nullable=false)
     * @Serializer\Expose()
     * @OA\Property(example=1)
     * @Serializer\Groups({"list", "details"})
     */
    private $latitude;

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
     * @var Forest
     *
     * @ORM\ManyToOne(targetEntity="Forest")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="forest_id", referencedColumnName="id")
     * })
     * @Assert\NotBlank()
    */
    private $forest;

    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity=DeviceValue::class, mappedBy="device", cascade={"persist"}, orphanRemoval=true)
     */
    private $deviceValues;
    
    
    public function __construct()
    {
        $this->deviceValues = new ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
    
            public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }
    
    public function getNameAddress(): ?string
    {
        return $this->nameAddress;
    }

    public function setNameAddress(string $nameAddress): static
    {
        $this->nameAddress = $nameAddress;

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

    public function getForest(): ?Forest
    {
        return $this->forest;
    }

    public function setForest(?Forest $forest): self
    {
        $this->forest = $forest;

        return $this;
    }

    public function getDeviceValues(): ArrayCollection | Collection
    {
        return $this->deviceValues;
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
     * @Serializer\SerializedName("forest")
     * @Serializer\Type("array")
     * @Serializer\Groups({"list","details"})
     * @return array
    */
    public function getCustomForest(): array
    {
        return [
            'id' => $this->getForest()->getId(),
            'createdAt' => $this->getForest()->getCreatedAt()
        ];
    }
    
    /**
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"details"})
     * @Serializer\SerializedName("deviceValues")
     * @return array
     */
    public function getCustomDeviceValues(): array
    {
        $deviceValues = $this->getDeviceValues();
    
        if ($deviceValues->isEmpty()) {
            return [];
        }
    
        $result = [];
        foreach ($deviceValues as $deviceValue) {
            $result[] = [
                'id' => $deviceValue->getId(),
                'status' => $deviceValue->getStatus(),
                'valueHeat' => $deviceValue->getValueHeat(),
                'valueMoisture' => $deviceValue->getValueMoisture(),
                'valueGas' => $deviceValue->getValueGas(),
                'date' => $deviceValue->getDate(),
            ];
        }
    
        // Return the last item in the collection or an empty array
        $lastDeviceValue = end($result);
        return $lastDeviceValue === false ? [] : $lastDeviceValue;
    }
}
