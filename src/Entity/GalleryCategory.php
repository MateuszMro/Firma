<?php

namespace App\Entity;

use App\Repository\GalleryCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GalleryCategoryRepository::class)]
class GalleryCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name;

    /**
     * @var Collection<int, GallerySession>
     */
    #[ORM\ManyToMany(targetEntity: GallerySession::class, mappedBy: 'categories')]
    private Collection $sessions;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
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

    /**
     * @return Collection<int, GallerySession>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(GallerySession $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->addCategory($this);
        }

        return $this;
    }

    public function removeSession(GallerySession $session): static
    {
        if ($this->sessions->removeElement($session)) {
            $session->removeCategory($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
