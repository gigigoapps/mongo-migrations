<?php

namespace Gigigointernals\Mongomigrations;

use Doctrine\ODM\MongoDB\DocumentManager;
use Gigigointernals\Mongomigrations\Model\Version;

/**
 * Database update services
 *
 */
class MigrationsManager
{
    const VERSION_MODEL = '\Gigigointernals\Mongomigrations\Model\Version';
    
    protected $db;
    protected $firstTime = false;
    protected $currentVersion = null;
    protected $maxVersion;
    protected $versionsClasses = [];
    protected $versionsClassesToUpdate = [];
    protected $versionsNamespace;
    
    protected $checkVersionToUpdateMessage = '';
    protected $lastUpDescription = '';
    protected $lastUpMessage = '';
    protected $lastUpVersion = '';
    protected $lastUpError = false;
    
    public function __construct(DocumentManager $db, $versionsPath, $versionsNamespace)
    {
        $this->db = $db;
        $this->setVersionNamespace($versionsNamespace);
        
        if ($handle = opendir($versionsPath)) {
            while (false !== ($file = readdir($handle))) {
                if ($this->isValidFile($file)) {
                    $version = substr($file, 0, strpos($file, '.'));
                    $this->versionsClasses[] = $version;
                }
            }
            sort($this->versionsClasses, SORT_NATURAL);
            closedir($handle);
        }
        
        $this->setMaxVersion();
        $this->setCurrentVersionFromDatabase();
        $this->setVersionsClassesToUpdate();
        
        
        // ToDo:
        // bloquear acciones si no hay versiones consecutivas en el directorio /Versions
        // bloquear acciones si no se puede saltar de la versión actual en base de datos a la que se solicita
        
        
    }

    /**
     * Update database to the version given
     * If version is null, this method try to update database to the max version
     * 
     * @param integer $versionToUpdate
     */
    public function up($versionToUpdate = null)
    {
        if (null === $versionToUpdate) {
            $versionToUpdate = $this->getMaxVersion();
        }
        
        if (empty($this->versionsClassesToUpdate)) {
            return false;
        }
        
        $versionClassName = array_shift($this->versionsClassesToUpdate);
        $this->lastUpVersion = $versionNumber = $this->getVersionNumber($versionClassName);
        if ($versionNumber > $versionToUpdate) {
            return false;
        }
        
        $classNS = $this->versionsNamespace . '\\' . ucfirst($versionClassName);
        if (class_exists($classNS)) {
            $versionClass = new $classNS($this->db);
        } else {
            $this->lastUpError = true;
            $this->lastUpDescription = '';
            $this->lastUpMessage = '<error>Error. Class not found "' . $classNS . '"</error>';
            return false;
        }
        
        $this->lastUpDescription = $versionClass->getDescription();
        try {
            $versionClass->up();
            $this->lastUpMessage = '<info>Done.</info>';
        } catch (\Exception $e) {
            $this->lastUpError = true;
            $this->lastUpMessage = '<error>Error running the query: ' . $e->getMessage() . '</error>';
            return false;
        }
        
        $this->db->getDocumentCollection(self::VERSION_MODEL)->drop();
        $versionObject = new Version();
        $versionObject->setVersion($versionNumber);
        $this->db->persist($versionObject);
        $this->db->flush($versionObject);
        
        return true;
    }
    
    /**
     * Check if version is numeric
     * Check if version given is smaller than current version (from database)
     * Check if version given is greater than the max version
     * 
     * @param integer $versionToUpdate
     * @return boolean
     */
    public function checkVersionToUpdate($versionToUpdate)
    {
        if (is_null($versionToUpdate)) {
            if ($this->getCurrentVersion() == $this->getMaxVersion()) {
                $this->checkVersionToUpdateMessage = '<info>The database is already in the max version ' . $this->getMaxVersion() . '</info>';
                return false;
            }
            return true;
        }
        
        if (!is_numeric($versionToUpdate)) {
            $this->checkVersionToUpdateMessage = '<error>Version must be an integer.</error>';
            return false;
        }
        
        if ($versionToUpdate < $this->getCurrentVersion()) {
            $this->checkVersionToUpdateMessage = '<error>The version given is smaller than the current version: ' . $this->getCurrentVersion() . '</error>';
            return false;
        }
        
        if ($versionToUpdate == $this->getCurrentVersion()) {
            $this->checkVersionToUpdateMessage = '<info>The database is already in the version ' . $versionToUpdate . '</info>';
            return false;
        }
        
        if ($versionToUpdate > $this->getMaxVersion()) {
            $this->checkVersionToUpdateMessage = '<error>The version given is too high (max version: ' . $this->getMaxVersion() . ')</error>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Returns the message made by the method checkVersionToUpdate()
     * 
     * @return string
     */
    public function getCheckVersionToUpdateMessage()
    {
        return $this->checkVersionToUpdateMessage;
    }
    
    /**
     * Returns max possible version
     * 
     * @return integer
     */
    public function getMaxVersion()
    {
        return $this->maxVersion;
    }
    
    /**
     * Returns current version or false if not in database
     * 
     * @return integer | false
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }
    
    /**
     * Returns if the database already has the collection "Version"
     * 
     * @return bool
     */
    public function isFirstTime()
    {
        return $this->firstTime;
    }
    
    /**
     * Returns true if the file name is valid
     * regex: ^V(\d+)$
     * 
     * @param string $filename
     * @return bool
     */
    private function isValidFile($filename)
    {
        return (bool)preg_match('/^V(\d+)\.php$/', $filename);
    }
    
    /**
     * Get version number of the version given
     * 
     * @param string $version
     * @return integer
     */
    private function getVersionNumber($version)
    {
        return (int)substr($version, 1);
    }
    
    /**
     * Set the namespace of the version classes
     * 
     * @param string $versionsNamespace
     */
    private function setVersionNamespace($versionsNamespace)
    {
        if (substr($versionsNamespace, 0, 1) != '\\') {
            $versionsNamespace = '\\' . $versionsNamespace;
        }
        if (substr($versionsNamespace, -1, 1) == '\\') {
            $versionsNamespace = substr($versionsNamespace, 0, -1);
        }
        $this->versionsNamespace = $versionsNamespace;
    }
    
    /**
     * Set current version from database.
     * Set false if the collection "Version" is not created
     */
    private function setCurrentVersionFromDatabase()
    {
        $currentVersion = 0;
        if ($version = $this->db->getRepository(self::VERSION_MODEL)->getCurrentVersion()) {
            $currentVersion = $version->getVersion();
        } else {
            $this->firstTime = true;
        }
        $this->currentVersion = $currentVersion;
    }
    
    /**
     * Set max possible version
     */
    private function setMaxVersion()
    {
        $maxVersion = $this->versionsClasses[count($this->versionsClasses)-1];
        $this->maxVersion = $this->getVersionNumber($maxVersion);
    }
    
    /**
     * Set an array with the possible versions without previous versions to the current
     */
    private function setVersionsClassesToUpdate()
    {
        foreach ($this->versionsClasses as $versionClassName) {
            $versionNumber = $this->getVersionNumber($versionClassName);
            if ($versionNumber > $this->getCurrentVersion()) {
                $this->versionsClassesToUpdate[] = $versionClassName;
            }
        }
    }
    
    public function getLastUpDescription()
    {
        return $this->lastUpDescription;
    }
    
    public function getLastUpMessage()
    {
        return $this->lastUpMessage;
    }
    
    public function getLastUpVersion()
    {
        return $this->lastUpVersion;
    }
    
    public function hasLastUpError()
    {
        return $this->lastUpError;
    }
}
