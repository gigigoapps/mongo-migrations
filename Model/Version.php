<?php

namespace Gigigo\Migrations\Model;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(
 * collection="Version",
 * repositoryClass="Gigigo\Migrations\Model\VersionRepository"
 * )
 */
class Version
{
    
    /** @ODM\Id */
    protected $id;
    
    /** @ODM\String */
    protected $version;

    /** @ODM\Date */
    private $createdAt;
    
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function __toString()
    {
        return $this->getVersion();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }
}