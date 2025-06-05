<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Table(name: 'admin')]
#[ORM\Entity]
class Admin implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'Id_Admin', type: 'integer', nullable: false)]
    private ?int $idAdmin = null;

    #[ORM\Column(name: 'ANom', type: 'string', length: 50, nullable: false)]
    private ?string $anom = null;

    #[ORM\Column(name: 'APrenom', type: 'string', length: 50, nullable: false)]
    private ?string $aprenom = null;

    #[ORM\Column(name: 'AMdp', type: 'string', length: 255, nullable: false)]
    private ?string $amdp = null;

    #[ORM\Column(name: 'ACreation', type: 'datetime', nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTime $acreation = null;

    #[ORM\Column(name: 'roles', type: 'json')]
    private array $roles = [];
    
    public function getUserIdentifier(): string
    {
        return $this->anom;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // garantir que chaque admin a au moins ROLE_ADMIN
        $roles[] = 'ROLE_ADMIN';
        
        return array_unique($roles);
    }
    
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->amdp;
    }
    
    public function setPassword(string $pwd): self
    {
        $this->amdp = $pwd;
        return $this;
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
        return $this->idAdmin;
    }
    
    public function getIdAdmin(): ?int
    {
        return $this->idAdmin;
    }

public function getANom(): ?string
{
    return $this->anom;
}

public function setANom(string $anom): self
{
    $this->anom = $anom;
    return $this;
}

public function getAPrenom(): ?string
{
    return $this->aprenom;
}

public function setAPrenom(string $aprenom): self
{
    $this->aprenom = $aprenom;
    return $this;
}

public function getAMdp(): ?string
{
    return $this->amdp;
}

public function setAMdp(string $amdp): self
{
    $this->amdp = $amdp;
    return $this;
}

public function getACreation(): ?\DateTime
{
    return $this->acreation;
}

public function setACreation(?\DateTime $acreation): self
{
    $this->acreation = $acreation;
    return $this;
}

}
