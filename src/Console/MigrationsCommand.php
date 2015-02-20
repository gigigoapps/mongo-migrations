<?php

namespace Gigigointernals\Mongomigrations\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Gigigointernals\Mongomigrations\MigrationsManager;


class MigrationsCommand extends Command
{
    /** @var Gigigointernals\Mongomigrations\MigrationsManager */
    protected $mm;
    
    /** @var Symfony\Component\Console\Output\OutputInterface */
    private $output;

    public function __construct(MigrationsManager $migrationsManager, $name = null) {
        parent::__construct($name);
        $this->mm = $migrationsManager;
    }
    
    protected function configure()
    {
        $this->setName("gigigo:migrations:up")
                ->setDescription("Update db to the last version")
                ->addOption('versiondb', null, InputOption::VALUE_OPTIONAL, 'Specific version to update. Must be an integer.')
                ->setHelp(<<<EOT
The <info>gigigo:migrations:up</info> updates the database to the last version.
EOT
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $this->output = $output;
        
        // input option and validation
        $versionToUpdate = $input->getOption('versiondb');
        if (!$this->validateVersion($versionToUpdate)) {
            return;
        }

        // get data and print info
        $this->printMessage("Start update database...", 'comment', true);
        $currentVersion = $this->getCurrentVersion();
        $maxVersion = $this->getMaxVersion();
        $this->printVersionToUpdateInfo($currentVersion, $versionToUpdate, $maxVersion);
        
        
        // update database
        while ($this->mm->up($versionToUpdate)) {
            $this->printUpdatedVersionInfo();
        }
        if ($this->mm->hasLastUpError()) {
            $this->printUpdatedVersionInfo();
            $this->printMessage('');
            $this->printMessage('ABORT', 'error', true);
        }

        
        // info: end
        $end = microtime(true);
        $time = $end - $start;
        $mem = memory_get_usage() / (1024 * 1024);

        $this->printMessage('');
        $this->printMessage('-----------------------', 'comment');
        $this->printMessage("End update database in $time seconds with $mem Mb.", 'comment', true);
    }
    
    private function validateVersion($version)
    {
        if (!$isValid = $this->mm->checkVersionToUpdate($version)) {
            $mess = $this->mm->getCheckVersionToUpdateMessage();
            $this->printMessage('', 'comment');
            $this->printMessage($mess, null, true);
            $this->printMessage('', 'comment');
            return false;
        }
        return true;
    }

    private function getCurrentVersion()
    {
        $currentVersion = $this->mm->getCurrentVersion();
        if ($this->mm->isFirstTime()) {
            $mess = '[Collection "Version" not created. It will be created.]';
        } else {
            $mess = "[Current version: {$currentVersion}]";
        }
        $this->printMessage($mess, 'comment', true);
        
        return $currentVersion;
    }
    
    private function getMaxVersion()
    {
        $maxVersion = $this->mm->getMaxVersion();
        $mess = "[Max version: {$maxVersion}]";
        $this->printMessage($mess, 'comment', true);
        $this->printMessage('-----------------------', 'comment');
        $this->printMessage('', 'comment');
        
        return $maxVersion;
    }
    
    private function printVersionToUpdateInfo($currentVersion, $versionToUpdate, $maxVersion)
    {
        if ($versionToUpdate) {
            $maxVersionMessage = ($versionToUpdate == $maxVersion)
                               ? ' (max version)'
                               : '';
            $mess = "Update database to the version: {$versionToUpdate}{$maxVersionMessage}";
        } else {
            $versionToUpdate = null;
            $mess = "Update database to the max version: {$maxVersion}";
        }
        $this->printMessage($mess, 'comment', true);
        $this->printMessage("Update database since version: " . $currentVersion, 'comment', true);
    }
    
    private function printUpdatedVersionInfo()
    {
        $this->printMessage('');
        $this->printMessage('Running version ' . $this->mm->getLastUpVersion() . '...', 'info', true);
        if ($mess = $this->mm->getLastUpDescription()) {
            $this->printMessage('- ' . $mess, 'info');
        }
        $this->printMessage($this->mm->getLastUpMessage(), null, true);
    }
    
    private function printMessage($mess, $type = null, $logger = false)
    {
        if (!is_null($type)) {
            $mess = "<{$type}>" . $mess . "</{$type}>";
        }
        $this->output->writeln($mess);
    }

}
