<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\EntityListeners(['App\EntityListener\MediaListener'])]
#[Vich\Uploadable]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Vich\UploadableField(mapping: "media_file", fileNameProperty: "path")]
    private ?File $mediaFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $extension = null;

    #[ORM\Column(nullable: true)]
    private ?int $size = null;

    public function construct(){
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __serialize()
    {
        $this->path = base64_encode($this->path);

        return [
            'path' => $this->path,
        ];
    }

    public function __unserialize($serialized)
    {
        $this->path = base64_decode($serialized['path']);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setMediaFile(File $mediaFile = null)
    {
        $this->mediaFile = $mediaFile;
    }

    public function getMediaFile()
    {
        return $this->mediaFile;
    }

    public function getPath(): ?string
    {
        return "/images/medias/" . $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function __toString()
    {
        return $this->getPath() ?? "";
    }
}
