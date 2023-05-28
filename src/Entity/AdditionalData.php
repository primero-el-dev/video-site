<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Repository\AdditionalDataRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdditionalDataRepository::class)]
class AdditionalData implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @param array<string, mixed> $content
     */
    #[Groups(['additional_data'])]
    #[ORM\Column(type: 'json')]
    protected array $content = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setContent(array $content): static
    {
        $this->content = $content;

        return $this;
    }
}
