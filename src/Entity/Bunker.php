<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Table(name: 'bunker')]
#[ORM\Index(name: 'Id_Site', columns: ['Id_Site'])]
#[ORM\Index(name: 'BDerniereModifPar', columns: ['BDerniereModifPar'])]
#[ORM\Entity]
class Bunker
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'Id_Bunker')]
    private ?int $id = null;

    #[ORM\Column(name: 'BNom', length: 50)]
    private ?string $nom = null;

    #[ORM\Column(name: 'BEnum', length: 50, nullable: true)]
    private ?string $benum = null;

    #[ORM\Column(name: 'BEtat', options: ['default' => 'Pas de retard'])]
    private ?string $etat = 'Pas de retard';

    #[ORM\Column(name: 'BCreation', type: 'datetime', nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?DateTime $creation = null;

    #[ORM\ManyToOne(targetEntity: Site::class, inversedBy: 'bunkers')]
    #[ORM\JoinColumn(name: 'Id_Site', referencedColumnName: 'Id_Site', nullable: false, onDelete: 'CASCADE')]
    private ?Site $site = null;

    #[ORM\ManyToOne(targetEntity: Personnel::class)]
    #[ORM\JoinColumn(name: 'BDerniereModifPar', referencedColumnName: 'Id_Personnel')]
    private ?Personnel $derniereModifPar = null;

    public function getId(): ?int
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
    public function getBNom(): ?string
    {
        return $this->getNom();
    }
    
    public function setBNom(?string $nom): self
    {
        return $this->setNom($nom);
    }
    
    public function getBEnum(): ?string
    {
        return $this->benum;
    }
    
    public function setBEnum(?string $benum): self
    {
        $this->benum = $benum;
        return $this;
    }
    
    public function getEtat(): ?string
    {
        return $this->etat;
    }
    
    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }
    
    // Méthodes de compatibilité pour le code existant
    public function getBEtat(): ?string
    {
        return $this->etat;
    }
    
    public function setBEtat(?string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }
    
    public function getCreation(): ?DateTime
    {
        return $this->creation;
    }
    
    public function setCreation(?DateTime $creation): self
    {
        $this->creation = $creation;
        return $this;
    }
    
    // Méthodes de compatibilité pour le code existant
    public function getBCreation(): ?DateTime
    {
        return $this->creation;
    }
    
    public function setBCreation(?DateTime $creation): self
    {
        $this->creation = $creation;
        return $this;
    }
    
    public function getSite(): ?Site
    {
        return $this->site;
    }
    
    public function setSite(?Site $site): self
    {
        $this->site = $site;
        return $this;
    }
    
    // Méthodes de compatibilité pour le code existant
    public function getIdSite(): ?Site
    {
        return $this->getSite();
    }
    
    public function setIdSite(?Site $site): self
    {
        return $this->setSite($site);
    }
    
    public function getDerniereModifPar(): ?Personnel
    {
        return $this->derniereModifPar;
    }
    
    public function setDerniereModifPar(?Personnel $derniereModifPar): self
    {
        $this->derniereModifPar = $derniereModifPar;
        return $this;
    }
    
    // Méthodes de compatibilité pour le code existant
    public function getBDerniereModifPar(): ?Personnel
    {
        return $this->derniereModifPar;
    }
    
    public function setBDerniereModifPar(?Personnel $derniereModifPar): self
    {
        $this->derniereModifPar = $derniereModifPar;
        return $this;
    }
    
    public function __toString(): string
    {
        return $this->nom ?? 'Bunker sans nom';
    }
}
