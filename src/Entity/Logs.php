<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Table(name: 'logs')]
#[ORM\Index(name: 'Id_Personnel', columns: ['Id_Personnel'])]
#[ORM\Entity]
class Logs
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'Id_Logs')]
    private ?int $idLogs = null;

    #[ORM\Column(name: 'LAction')]
    private ?string $laction = null;

    #[ORM\Column(name: 'LDate', type: 'datetime', nullable: true, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?DateTime $ldate = null;

    #[ORM\ManyToOne(targetEntity: Personnel::class)]
    #[ORM\JoinColumn(name: 'Id_Personnel', referencedColumnName: 'Id_Personnel')]
    private ?Personnel $idPersonnel = null;

    /**
     * Méthode exigée par EasyAdmin pour accéder à l'identifiant
     */
    public function getId(): ?int
    {
        return $this->idLogs;
    }
    
    public function getIdLogs(): ?int
    {
        return $this->idLogs;
    }
    
    public function getLAction(): ?string
    {
        return $this->laction;
    }
    
    public function setLAction(string $laction): self
    {
        $this->laction = $laction;
        return $this;
    }
    
    public function getLDate(): ?\DateTime
    {
        return $this->ldate;
    }
    
    public function setLDate(?\DateTime $ldate): self
    {
        $this->ldate = $ldate;
        return $this;
    }
    
    public function getIdPersonnel(): ?Personnel
    {
        return $this->idPersonnel;
    }
    
    public function setIdPersonnel(?Personnel $idPersonnel): self
    {
        $this->idPersonnel = $idPersonnel;
        return $this;
    }
}
