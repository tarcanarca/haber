<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="unparsedposts")
 */
class UnparsedPost
{
    /**
     * @var int
     *
     * @ORM\Id
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
}