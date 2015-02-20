<?php

namespace Gigigointernals\Mongomigrations\Model;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Category Repository
 *
 * @author gigigo
 *        
 */
class VersionRepository extends DocumentRepository 
{
    /**
     * Get the current version in database
     * 
     * @return object | null
     */
    public function getCurrentVersion()
    {
        $qb = $this->createQueryBuilder()
            ->sort(array('createdAt' => 'desc'))
            ->limit(1);
        
        return $qb->getQuery()->getSingleResult();
    }

}