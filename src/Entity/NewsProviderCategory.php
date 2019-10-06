<?php

namespace App\Entity;

use App\Types\Category;

class NewsProviderCategory
{
    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $path;

    public function __construct(Category $category, string $path)
    {
        $this->category = $category->getValue();
        $this->path = $path;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return new Category($this->category);
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
