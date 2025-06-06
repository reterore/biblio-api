<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['client:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write', 'emprunt:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write', 'emprunt:read'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write', 'emprunt:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 10)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $tel = null;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write'])]
    private ?string $adresse = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['client:read'])]
    private ?\DateTime $date_inscription = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['client:read', 'client:write'])]
    private ?\DateTime $date_naissance = null;

    /**
     * @var Collection<int, Emprunt>
     */
    #[ORM\OneToMany(targetEntity: Emprunt::class, mappedBy: 'client')]
    #[Groups(['client:read'])]
    private Collection $emprunts;

    public function __construct()
    {
        $this->emprunts = new ArrayCollection();
        $this->date_inscription = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string { return $this->prenom; }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string { return $this->email; }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getTel(): ?string { return $this->tel; }

    public function setTel(string $tel): static
    {
        $this->tel = $tel;
        return $this;
    }

    public function getAdresse(): ?string { return $this->adresse; }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getDateInscription(): ?\DateTime { return $this->date_inscription; }

    public function setDateInscription(\DateTime $date_inscription): static
    {
        $this->date_inscription = $date_inscription;
        return $this;
    }

    public function getDateNaissance(): ?\DateTime { return $this->date_naissance; }

    public function setDateNaissance(\DateTime $date_naissance): static
    {
        $this->date_naissance = $date_naissance;
        return $this;
    }

    /**
     * @return Collection<int, Emprunt>
     */
    public function getEmprunts(): Collection
    {
        return $this->emprunts;
    }

    public function addEmprunt(Emprunt $emprunt): static
    {
        if (!$this->emprunts->contains($emprunt)) {
            $this->emprunts->add($emprunt);
            $emprunt->setClient($this);
        }
        return $this;
    }

    public function removeEmprunt(Emprunt $emprunt): static
    {
        if ($this->emprunts->removeElement($emprunt)) {
            if ($emprunt->getClient() === $this) {
                $emprunt->setClient(null);
            }
        }
        return $this;
    }
}
