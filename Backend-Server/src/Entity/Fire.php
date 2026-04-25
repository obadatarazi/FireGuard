<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Fire
 *
 * @ORM\Table(name="fire",
 *     indexes={
 *          @ORM\Index(name="forest_id", columns={"forest_id"}),
 *          @ORM\Index(name="device_id", columns={"device_id"}),
 *          @ORM\Index(name="status", columns={"status"})
 * })
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("All")
 * @ORM\HasLifecycleCallbacks()
 */
class Fire
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
     * @ORM\Column(name="status", type="string", length=20, nullable=false)
     * @Serializer\Expose()
     */
    private $status;

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
    * @var Device
    *
    * @ORM\ManyToOne(targetEntity="Device")
    * @ORM\JoinColumns({
    *   @ORM\JoinColumn(name="device_id", referencedColumnName="id")
    * })
    * @Assert\NotBlank()
    */
    private $device;
    
        /**
     * @var string
     *
     */
    private $statusValue;

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
    
        public function setStatusValue(?string $statusValue): self
    {
        $this->statusValue = $statusValue;
        return $this;
    }

    public function getStatusValue(): ?string
    {
        return $this->statusValue;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
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
    * @Serializer\SerializedName("forest")
    * @Serializer\Type("array")
    * @Serializer\Groups({"list","details"})
    * @return array
    */
    public function getCustomForest(): array
    {
        return [
            'id' => $this->getForest()->getId(),
            'name' => $this->getForest()->getName(),
            'createdAt' => $this->getForest()->getCreatedAt()
        ];
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
            'name' => $this->getDevice()->getName(),
            'longitude ' => $this->getDevice()->getLongitude(),
            'latitude' => $this->getDevice()->getLatitude(),
            'nameAddress' => $this->getDevice()->getNameAddress(),
            'createdAt' => $this->getDevice()->getCreatedAt()
        ];
    }
    
        /**
     * @Serializer\Expose()
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("status")
     * @Serializer\Type("array")
     * @Serializer\Groups({"list", "details"})
     * @return array
     */
    public function getCustomStatus(): array
    {
        return [
            'label' => $this->getStatusValue(),
            'value' => $this->status,
        ];
    }
}
