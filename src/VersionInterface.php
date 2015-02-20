<?php

namespace Gigigointernals\Mongomigrations;

interface VersionInterface
{
    /**
     * 
     * @param \Doctrine\ODM\MongoDB\DocumentManager $db
     */
    public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $db);
    
    /**
     * Get description of the version
     */
    public function getDescription();
    
    /**
     * Queries to update db
     */
    public function up();
    
    /**
     * Queries to revert the changes made for the method "up()"
     */
    public function down();
}
