<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EmergencyRequest
 *
 * @ORM\Table(name="emergency_request",
 *          indexes={@ORM\Index(name="fire_brigade_id", columns={"fire_brigade_id"}), @ORM\Index(name="center_id", columns={"center_id"}), @ORM\Index(name="fire_id", columns={"fire_id"})})
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("All")
 * @ORM\HasLifecycleCallbacks()
 */
class EmergencyRequest
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
     * @ORM\Column(name="status", type="string", length=50, nullable=false)
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
     * @var \Fire
     *
     * @ORM\ManyToOne(targetEntity="Fire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fire_id", referencedColumnName="id")
     * })
     * @Assert\NotBlank()
     */
    private $fire;

    /**
     * @var \FireBrigade
     *
     * @ORM\ManyToOne(targetEntity="FireBrigade")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fire_brigade_id", referencedColumnName="id")
     * })
     * @Assert\NotBlank()
     */
    private $fireBrigade;

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
     * @var string
     *
     */
    private $statusValue;
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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
    
    public function getFire(): ?Fire
    {
        return $this->fire;
    }

    public function setFire(?Fire $fire): self
    {
        $this->fire = $fire;

        return $this;
    }
    
    public function getFireBrigade(): ?FireBrigade
    {
        return $this->fireBrigade;
    }

    public function setFireBrigade(?FireBrigade $fireBrigade): self
    {
        $this->fireBrigade = $fireBrigade;

        return $this;
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
    
    /**
    * @Serializer\Expose()
    * @Serializer\VirtualProperty()
    * @Serializer\SerializedName("fire")
    * @Serializer\Type("array")
    * @Serializer\Groups({"list","details"})
    * @return array
    */
    public function getCustomFire(): array
    {
        return [
            'id' => $this->getFire()->getId(),
            'status' => $this->getFire()->getCustomStatus(),
            'createdAt' => $this->getFire()->getCreatedAt()
        ];
    }
    
        /**
    * @Serializer\Expose()
    * @Serializer\VirtualProperty()
    * @Serializer\SerializedName("fireBrigade")
    * @Serializer\Type("array")
    * @Serializer\Groups({"list","details"})
    * @return array
    */
    public function getCustomFireBrigade(): array
    {
        return [
            'id' => $this->getFireBrigade()->getId(),
            'name' => $this->getFireBrigade()->getName(),
            'createdAt' => $this->getFireBrigade()->getCreatedAt()
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

}
