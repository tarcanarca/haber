<?php

namespace App\Entity;

use App\Types\ProviderType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="newsproviders")
 */
class NewsProvider
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string",length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string",length=155)
     */
    private $url;

    /**
     * @var ProviderType
     *
     * @ORM\Column(type=ProviderType::class, length=25)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="NewsProviderCategory", mappedBy="newsProvider", cascade={"persist", "remove", "merge"})
     * @var \App\Entity\NewsProviderCategory[]
     */
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
