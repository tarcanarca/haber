<?php

namespace App\Entity;

use App\Types\Category;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="newsprovidercategories")
 */
class NewsProviderCategory
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var NewsProvider
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\NewsProvider", inversedBy="categories")
     * @ORM\JoinColumn(name="newsprovider_id", referencedColumnName="id", nullable=FALSE)
     */
    private $newsProvider;

    /**
     * @var Category
     * @ORM\Column(type=Category::class, name="category_key")
     */
    private $category;

    /**
     * @var string
     * @ORM\Column(type="string", name="path")
     */
    private $path;

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function setCategory(\App\Types\Category $category): NewsProviderCategory
    {
        $this->category = $category;

        return $this;
    }

    public function setPath(string $path): NewsProviderCategory
    {
        $this->path = $path;

        return $this;
    }

    public function setNewsProvider(\App\Entity\NewsProvider $newsProvider): NewsProviderCategory
    {
        $this->newsProvider = $newsProvider;

        return $this;
    }

    public function __toString()
    {
        return "[" . $this->getCategory()->getValue() . " => " . $this->getPath() . "]";
    }
}
