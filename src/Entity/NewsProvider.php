<?php

namespace App\Entity;

use App\Types\ProviderType;
use App\ValueObject\Url;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NewsProviderRepository")
 * @ORM\Table(name="newsproviders")
 */
class NewsProvider
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * @var \App\Entity\NewsProviderCategory[]
     *
     * @ORM\OneToMany(targetEntity="NewsProviderCategory", mappedBy="newsProvider", cascade={"persist", "remove", "merge"})
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
    public function getCategories(): Collection
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

    public function getUrl(): Url
    {
        return new Url($this->url);
    }
}
