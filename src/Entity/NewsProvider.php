<?php

namespace App\Entity;

use App\Types\ProviderType;
use Doctrine\ORM\Mapping\Entity;

/** @Entity */
class NewsProvider
{
    private $id;

    private $name;

    private $url;

    /**
     * @var \App\Types\ProviderType
     */
    private $type;

    /** @var \App\Entity\NewsProviderCategory[] */
    private $categories;

    public function __construct(ProviderType $type, string $name, string $url)
    {
        $this->type = $type;
        $this->name = $name;
        $this->url = $url;
    }

    public function getType(): ProviderType
    {
        return $this->type;
    }

    /**
     * @return \App\Entity\NewsProviderCategory[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param \App\Entity\NewsProviderCategory[] $categories
     */
    public function setCategories(array $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
