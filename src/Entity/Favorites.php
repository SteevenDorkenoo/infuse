<?php

namespace App\Entity;

use App\Repository\FavoritesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Index(name: "id_fav_idx", columns: ["id"])]
#[ORM\Entity(repositoryClass: FavoritesRepository::class)]
class Favorites
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    private ?User $userId = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    private ?Recipes $recipeId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getRecipeId(): ?Recipes
    {
        return $this->recipeId;
    }

    public function setRecipeId(?Recipes $recipeId): static
    {
        $this->recipeId = $recipeId;

        return $this;
    }
}
