<?php

namespace App\Entity;

use App\Repository\PersonnelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Table(name: 'personnel')]
#[ORM\Index(name: 'Id_Site', columns: ['Id_Site'])]
#[ORM\Entity(repositoryClass: PersonnelRepository::class)]
#[UniqueEntity('pNom')]
class Personnel implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'Id_Personnel')]
    private ?int $idPersonnel = null;

    #[ORM\Column(name: 'PNom', length: 50)]
    private ?string $pNom = null;

    #[ORM\Column(name: 'PPrenom', length: 50)]
    private ?string $pPrenom = null;

    #[ORM\Column(name: 'PMdp', length: 255)]
    private ?string $password = null;

    #[ORM\ManyToOne(targetEntity: Site::class, inversedBy: 'personnels')]
    #[ORM\JoinColumn(name: 'Id_Site', referencedColumnName: 'Id_Site', nullable: false)]
    private ?Site $site = null;
    
    #[ORM\Column(name: 'roles', type: 'json')]
    private array $roles = [];
    
    public function getUserIdentifier(): string
    {
        return $this->pNom;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // garantir que chaque personnel a au moins ROLE_PERSONNEL
        $roles[] = 'ROLE_PERSONNEL';
        
        return array_unique($roles);
    }
    
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    
    public function setPassword(string $hashedPassword): self
    {
        $this->password = $hashedPassword;
        return $this;
    }
    
    public function getSalt(): ?string
    {
        return null; // Non utilisé avec les algorithmes modernes de hachage
    }

    public function eraseCredentials()
    {
        // Rien à faire ici
    }
    
    /**
     * Méthode exigée par EasyAdmin pour accéder à l'identifiant
     */
    public function getId(): ?int
    {
        return $this->idPersonnel;
    }
    
    public function getIdPersonnel(): ?int
    {
        return $this->idPersonnel;
    }
    
    public function getPNom(): ?string
    {
        return $this->pNom;
    }
    
    public function setPNom(string $pnom): self
    {
        $this->pNom = $pnom;
        return $this;
    }
    
    public function getPPrenom(): ?string
    {
        return $this->pPrenom;
    }
    
    public function setPPrenom(string $pprenom): self
    {
        $this->pPrenom = $pprenom;
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
    
    /**
     * Méthode de compatibilité pour le code existant
     */
    public function getIdSite(): ?Site
    {
        return $this->getSite();
    }
    
    /**
     * Méthode de compatibilité pour le code existant
     */
    public function setIdSite(?Site $idSite): self
    {
        return $this->setSite($idSite);
    }
    
    public function __toString(): string
    {
        return $this->pNom ?? 'Personnel';
    }
}
