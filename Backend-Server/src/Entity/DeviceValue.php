<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

/**
 *  DeviceValue
 *
 * @ORM\Table(name="device_value", 
 *        indexes={
 *          @ORM\Index(name="device_id", columns={"device_id"})
 *     })
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("All")
 * @ORM\HasLifecycleCallbacks()
 */
class DeviceValue
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
    * @ORM\Column(name="status", type="string", length=20, nullable=false)
    * @Serializer\Groups({"list", "details"})
    * @Serializer\Expose()
    */
    private $status;

    /**
     * @var float
     *
     * @ORM\Column(name="value_heat", type="float", precision=10, scale=0, nullable=false)
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details"})
     */
    private $valueHeat;

    /**
    * @var float
    *
    * @ORM\Column(name="value_moisture", type="float", precision=10, scale=0, nullable=false)
    * @Serializer\Expose()
    * @Serializer\Groups({"list", "details"})
    */
    private $valueMoisture;

        /**
     * @var float
     *
     * @ORM\Column(name="value_gas", type="float", precision=10, scale=0, nullable=false)
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details"})
     */
    private $valueGas;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     * @Serializer\Expose()
     * @OA\Property(example="2022-11-16T22:36:33+01:00")
     * @Serializer\Groups({"list", "details"})
     */
    private $date;

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
     * @var Device
     *
     * @ORM\ManyToOne(targetEntity="Device")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="device_id", referencedColumnName="id")
     * })
     */
    private $device;

    public function getId(): ?int
    {
        return $this->id;
    }
    
        public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getValueHeat(): ?float
    {
        return $this->valueHeat;
    }

    public function setValueHeat(float $valueHeat): static
    {
        $this->valueHeat = $valueHeat;

        return $this;
    }

    public function getValueMoisture(): ?float
    {
        return $this->valueMoisture;
    }

    public function setValueMoisture(float $valueMoisture): static
    {
        $this->valueMoisture = $valueMoisture;

        return $this;
    }

    public function getValueGas(): ?float
    {
        return $this->valueGas;
    }

    public function setValueGas(float $valueGas): static
    {
        $this->valueGas = $valueGas;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

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

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): self
    {
        $this->device = $device;

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
    * @Serializer\SerializedName("device")
    * @Serializer\Type("array")
    * @Serializer\Groups({"list","details"})
    * @return array
    */
    public function getCustomDevice(): array
    {
        return [
            'id' => $this->getDevice()->getId(),
            'name' => $this->getDevice()->getName()
        ];
    }
}
