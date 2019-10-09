<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="unparsedposts",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="search_idx", columns={"newsprovider_id", "provider_key"})
 *     }
 * )
 */
class UnparsedPost
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var NewsProvider
     *
     * @ORM\ManyToOne(targetEntity="NewsProvider")
     * @ORM\JoinColumn(name="newsprovider_id", referencedColumnName="id")
     */
    private $provider;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=155)
     */
    private $providerKey;

    /**
     * @var string
     *
     * @ORM\Column(type="string",length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $contents;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): UnparsedPost
    {
        $this->id = $id;

        return $this;
    }

    public function getProvider(): NewsProvider
    {
        return $this->provider;
    }

    public function setProvider(NewsProvider $provider): UnparsedPost
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return string
     */
    public function getProviderKey(): string
    {
        return $this->providerKey;
    }

    public function setProviderKey(string $providerKey): UnparsedPost
    {
        $this->providerKey = $providerKey;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): UnparsedPost
    {
        $this->url = $url;

        return $this;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function setContents(string $contents): UnparsedPost
    {
        $this->contents = $contents;

        return $this;
    }
}