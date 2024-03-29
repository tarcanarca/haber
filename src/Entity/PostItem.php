<?php

namespace App\Entity;

class PostItem
{
    /**
     * @var string
     */
    private $providerId;

    /**
     * @var string
     */
    private $heading;

    /**
     * @var string
     */
    private $spot;

    /**
     * @var string
     */
    private $contents;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     */
    private $lastUpdatedAt;

    /**
     * @var array
     */
    private $images;

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function setProviderId(string $providerId): PostItem
    {
        $this->providerId = $providerId;

        return $this;
    }

    public function getHeading(): string
    {
        return $this->heading;
    }

    public function setHeading(string $heading): PostItem
    {
        $this->heading = $heading;

        return $this;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function setContents(string $contents): PostItem
    {
        $this->contents = $contents;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): PostItem
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastUpdatedAt(): \DateTimeImmutable
    {
        return $this->lastUpdatedAt;
    }

    public function setLastUpdatedAt(\DateTimeImmutable $lastUpdatedAt): PostItem
    {
        $this->lastUpdatedAt = $lastUpdatedAt;

        return $this;
    }

    public function getSpot(): string
    {
        return $this->spot;
    }

    public function setSpot(string $spot): PostItem
    {
        $this->spot = $spot;

        return $this;
    }

    public function setImages(array $images): PostItem
    {
        $this->images = $images;

        return $this;
    }

    public function getImages(): array
    {
        return $this->images;
    }
}