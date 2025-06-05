<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Table(name: 'site')]
#[ORM\Index(name: 'Id_Admin', columns: ['Id_Admin'])]
#[ORM\Entity]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'Id_Site')]
    private ?int $id = null;

    #[ORM\Column(name: 'SNom', length: 50)]
    private ?string $nom = null;

    #[ORM\Column(name: 'SLieu', length: 50, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\ManyToOne(targetEntity: Admin::class)]
    #[ORM\JoinColumn(name: 'Id_Admin', referencedColumnName: 'Id_Admin')]
    private ?Admin $idAdmin = null;
    
    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Bunker::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $bunkers;
    
    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Personnel::class)]
    private Collection $personnels;
    
    public function __construct()
    {
        $this->bunkers = new ArrayCollection();
        $this->personnels = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    // Méthode de compatibilité pour le code existant
    public function getIdSite(): ?int
    {
        return $this->id;
    }
    
    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }
    
    // Méthodes de compatibilité pour le code existant
    public function getSNom(): ?string
    {
        return $this->nom;
    }

    public function setSNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }
    
    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }
    
    // Méthodes de compatibilité pour le code existant
    public function getSLieu(): ?string
    {
        return $this->imagePath;
    }

    public function setSLieu(?string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }
    
    public function getIdAdmin(): ?Admin
    {
        return $this->idAdmin;
    }
    
    public function setIdAdmin(?Admin $idAdmin): self
    {
        $this->idAdmin = $idAdmin;
        return $this;
    }
    
    /**
     * @return Collection<int, Bunker>
     */
    public function getBunkers(): Collection
    {
        return $this->bunkers;
    }
    
    public function addBunker(Bunker $bunker): self
    {
        if (!$this->bunkers->contains($bunker)) {
            $this->bunkers[] = $bunker;
            $bunker->setSite($this);
        }
        return $this;
    }
    
    public function removeBunker(Bunker $bunker): self
    {
        if ($this->bunkers->removeElement($bunker)) {
            if ($bunker->getSite() === $this) {
                $bunker->setSite(null);
            }
        }
        return $this;
    }
    
    /**
     * @return Collection<int, Personnel>
     */
    public function getPersonnels(): Collection
    {
        return $this->personnels;
    }
    
    public function addPersonnel(Personnel $personnel): self
    {
        if (!$this->personnels->contains($personnel)) {
            $this->personnels[] = $personnel;
            $personnel->setSite($this);
        }
        return $this;
    }
    
    public function removePersonnel(Personnel $personnel): self
    {
        if ($this->personnels->removeElement($personnel)) {
            // set the owning side to null (unless already changed)
            if ($personnel->getSite() === $this) {
                $personnel->setSite(null);
            }
        }
        return $this;
    }
    
    public function __toString(): string
    {
        return $this->nom ?? 'Site sans nom';
    }
}
