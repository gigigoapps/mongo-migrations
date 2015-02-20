<?php

namespace Gigigo\Migrations;

use Doctrine\ODM\MongoDB\DocumentManager;

class VersionBase
{
    protected $db;
    
    public function __construct(DocumentManager $db)
    {
        $this->db = $db;
    }
    
    public function getDescription()
    {
        return 'No description';
    }
}