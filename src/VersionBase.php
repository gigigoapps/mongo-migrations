<?php

namespace Gigigointernals\Mongomigrations;

use Doctrine\ODM\MongoDB\DocumentManager;

class VersionBase implements VersionInterface
{
    protected $db;
    
    public function __construct(DocumentManager $db)
    {
        $this->db = $db;
    }
    
    public function getDescription()
    {
        throw new Exception('Method getDescription() must be implemented');
    }
    
    public function up()
    {
        throw new Exception('Method up() must be implemented');
    }
    
    public function down()
    {
        // functionality not developed yet
        //throw new Exception('Method down() must be implemented');
    }
}