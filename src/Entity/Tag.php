<?php

namespace App\Entity;

use App\Entity\Interfaces\EntityInterface;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(
    fields: ['name'], 
    message: 'entity.tag.uniqueEntity.name', 
    errorPath: 'name',
)]
#[ORM\Index(columns: ['name'])]
#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag implements EntityInterface
{
    #[Groups(['tag', 'tag_extended'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['tag', 'tag_extended'])]
    #[Assert\NotBlank(message: 'entity.tag.name.notBlank')]
    #[Assert\Length(max: 140, maxMessage: 'entity.tag.name.length.max')]
    #[Assert\Unique]
    #[ORM\Column(length: 140)]
    private ?string $name = null;

    #[Groups('tag_extended')]
    #[ORM\ManyToMany(targetEntity: Video::class, inversedBy: 'tags', fetch: 'EXTRA_LAZY')]
    private Collection $videos;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        $this->videos->removeElement($video);

        return $this;
    }
}
