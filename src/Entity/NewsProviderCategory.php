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
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var NewsProvider
     * @ORM\ManyToOne(targetEntity="NewsProvider", inversedBy="categories")
     * @ORM\JoinColumn(name="newsprovider_id", referencedColumnName="id")
     */
    private $newsProvider;

    /**
     * @var Category
     * @ORM\Column(type=Category::class, name="key")
     */
    private $category;

    /**
     * @var string
     * @ORM\Column(type="string", name="path")
     */
    private $path;

    public function __construct(Category $category, string $path)
    {
        $this->category = $category;
        $this->path = $path;
    }

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

    public function __toString()
    {
        return "[" . $this->getCategory()->getValue() . " => " . $this->getPath() . "]";
    }
}
