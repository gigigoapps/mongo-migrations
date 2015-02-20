<?php

namespace Gigigointernals\Mongomigrations\Versions;


use Gigigointernals\Mongomigrations\VersionBase as VersionBase;

/**
 * Example class
 */
class V0 extends VersionBase
{
    public function getDescription()
    {
        return 'This is the description of the queries that will be executed in the method up()';
    }
    
    public function up()
    {
        $this->db->createQueryBuilder('namespace\classname')
            ->update()
            ->multiple(true)
            ->field('active')->set(true)
            ->getQuery()
            ->execute()
        ;
    }
    
    public function down()
    {
        $this->db->createQueryBuilder('namespace\classname')
            ->update()
            ->multiple(true)
            ->unsetField('active')
            ->getQuery()
            ->execute()
        ;
    }
}
