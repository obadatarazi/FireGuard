<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * TaskFireBrigade
 *
 * @ORM\Table(name="task_fire_brigade",
 *      indexes={
 *          @ORM\Index(name="fire_brigade_id", columns={"fire_brigade_id"}),
 *          @ORM\Index(name="fire_id", columns={"fire_id"})
 * })
 * @ORM\Entity
 * @Serializer\ExclusionPolicy("All")
 * @ORM\HasLifecycleCallbacks()
 */
class TaskFireBrigade
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
     * @var string|null
     *
     * @ORM\Column(name="note", type="string", length=255, nullable=true)
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details"})
     */
    private $note;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=100, nullable=false)
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details"})
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details"})
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     * @Serializer\Expose()
     * @Serializer\Groups({"list", "details"})
     */
    private $updatedAt;

    /**
     * @var FireBrigade
     *
     * @ORM\ManyToOne(targetEntity="FireBrigade")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fire_brigade_id", referencedColumnName="id")
     * })
     */
    private $fireBrigade;

    /**
     * @var Fire
     *
     * @ORM\ManyToOne(targetEntity="Fire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fire_id", referencedColumnName="id")
     * })
     */
    private $fire;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }
    
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

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

    public function getFireBrigade(): ?FireBrigade
    {
        return $this->fireBrigade;
    }

    public function setFireBrigade(?FireBrigade $fireBrigade): static
    {
        $this->fireBrigade = $fireBrigade;

        return $this;
    }

    public function getFire(): ?Fire
    {
        return $this->fire;
    }

    public function setFire(?Fire $fire): static
    {
        $this->fire = $fire;

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
    * @Serializer\SerializedName("fire")
    * @Serializer\Type("array")
    * @Serializer\Groups({"list","details"})
    * @return array
    */
    public function getCustomFire(): array
    {
        return [
            'id' => $this->getFire()->getId(),
            'createsAt' => $this->getFire()->getCreatedAt(),
            'longitude' => $this->getFire()->getDevice()->getLongitude(),
            'latitude' => $this->getFire()->getDevice()->getLatitude(),
            'nameAddress' => $this->getFire()->getDevice()->getNameAddress()
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
            'name' => $this->getFireBrigade()->getName()

        ];
    }
}
