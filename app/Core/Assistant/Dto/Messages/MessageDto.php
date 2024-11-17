<?php

namespace App\Core\Assistant\Dto\Messages;

/**
 * Basic class representing a message in chatbox assistant
 */
abstract class MessageDto
{
    protected ?int $id = null;
    protected ?string $name = null;
    protected ?string $type = null;
    protected ?string $imageUrl = null;
    protected ?string $message = null;
    protected ?bool $loaded = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getLoaded(): ?bool
    {
        return $this->loaded;
    }

    public function setLoaded(?bool $loaded): self
    {
        $this->loaded = $loaded;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'imageUrl' => $this->imageUrl,
            'message' => $this->message,
            'loaded' => $this->loaded,
        ];
    }
}
