<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\OneToOne(inversedBy: 'company', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\OneToOne(inversedBy: 'company', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Adress $registrationAddress = null;

    #[ORM\OneToOne(inversedBy: 'company', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Adress $workstationAddress = null;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    private ?User $agent = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoFileName = null;

    #[ORM\OneToMany(targetEntity: Calendar::class, mappedBy: 'client')]
    private Collection $visits;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $workstation = null;

    public function __construct()
    {
        $this->visits = new ArrayCollection();
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getRegistrationAddress(): ?Adress
    {
        return $this->registrationAddress;
    }

    public function setRegistrationAddress(?Adress $registrationAddress): static
    {
        $this->registrationAddress = $registrationAddress;

        return $this;
    }

    public function getWorkstationAddress(): ?Adress
    {
        return $this->workstationAddress;
    }

    public function setWorkstationAddress(Adress $workstationAddress): static
    {
        $this->workstationAddress = $workstationAddress;

        return $this;
    }

    public function getAgent(): ?User
    {
        return $this->agent;
    }

    public function setAgent(?User $agent): static
    {
        $this->agent = $agent;

        return $this;
    }



    public function getPhotoFileName(): ?string
    {
        return $this->photoFileName;
    }

    public function setPhotoFileName(?string $photoFileName): static
    {
        $this->photoFileName = $photoFileName;

        return $this;
    }

    /**
     * @return Collection<int, Calendar>
     */
    public function getVisits(): Collection
    {
        return $this->visits;
    }

    public function addVisit(Calendar $visit): static
    {
        if (!$this->visits->contains($visit)) {
            $this->visits->add($visit);
            $visit->setClient($this);
        }

        return $this;
    }

    public function removeVisit(Calendar $visit): static
    {
        if ($this->visits->removeElement($visit)) {
            // set the owning side to null (unless already changed)
            if ($visit->getClient() === $this) {
                $visit->setClient(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }
    public function active(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getWorkstation(): ?string
    {
        return $this->workstation;
    }

    public function setWorkstation(?string $workstation): static
    {
        $this->workstation = $workstation;

        return $this;
    }
}
