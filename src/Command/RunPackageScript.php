<?php

namespace Sioweb\CCEvent\Command;

use Composer\Composer;
use Composer\Factory;
use Composer\Console\Application;
use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\IO\NullIO;
use Composer\Package\CompletePackage;
use Composer\Package\Package;
use Composer\Package\Loader\JsonLoader;
use Composer\Repository\CompositeRepository;
use Sioweb\CCEvent\Composer\Installer\PackageEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
/**
 * My own command
 *
 * Demo command for learning
 */
class RunPackageScript extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @var int
     */
    private $statusCode = 0;

    private $_script = [];

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('ccevent:packagescripts')
            ->setDefinition([
                new InputArgument('package', InputArgument::REQUIRED, 'The package name')
            ])
            ->setDescription('Execute scripts of a explicit package.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->rootDir = rtrim($this->getContainer()->getParameter('kernel.project_dir'), '/');
        include $this->rootDir . '/vendor/autoload.php';
        $this->packageName = substr($input->getArgument('package'), 8);
        $this->targetDir = $this->rootDir . '/vendor/' . $this->packageName;


        $ComposerJson = $this->targetDir . '/composer.json';
        $ComposerArray = json_decode(file_get_contents($ComposerJson), 1);


        $package = new CompletePackage($this->packageName, $ComposerArray['version'], $ComposerArray['version']);

        $package->setType($ComposerArray['type']);
        $package->setDescription($ComposerArray['description']);
        $package->setLicense($ComposerArray['license']);
        $package->setAutoload($ComposerArray['autoload']);
        $package->setRequires($ComposerArray['require']);
        
        $ComposerApplication = new Application;

        if (is_file($ComposerJson)) {
            $Scripts = [];
            $ComposerArray = json_decode(file_get_contents($ComposerJson), 1);
            if (!empty($ComposerArray['scripts']['package-scripts'])) {
                $Scripts = $ComposerArray['scripts']['package-scripts'];
            } else {
                return;
            }

            $Config = $ComposerApplication->getComposer()->getConfig();
            foreach ($Scripts as $script) {
                if(strpos($script, '@config') !== false) {
                    if(!$this->checkCondition($script, $Config)) {
                        break;
                    }
                    continue;
                }
                // $script = str_replace('\\', '\\\\', $script);
                $_script = $script;
                $arguments = $this->parseArguments($Arguments, $script);

                $CcEvent = new PackageEvent(
                    'package-scripts['.$_script.']',
                    $ComposerApplication->getComposer(),
                    new NullIO,
                    false, //$event->isDevMode(),
                    new DefaultPolicy, //$event->getPolicy()
                    new Pool, //$event->getPool()
                    new CompositeRepository([]), //$event->getInstalledRepo()
                    new Request, //$event->getRequest()
                    [], //$event->getOperations()
                    new InstallOperation($package)
                );
                $CcEvent->setScript($script);
                $CcEvent->setArguments($arguments);

                $this->scripts[$_script] = $CcEvent;
            }
        }


        foreach($this->scripts as $Script => $CcEvent) {
            $ComposerApplication->getComposer()->getEventDispatcher()->addListener('package-scripts[' . $Script . ']', $CcEvent->getScript());
            $ComposerApplication->getComposer()->getEventDispatcher()->dispatch('package-scripts[' . $Script . ']', $CcEvent);
        }

        return $this->statusCode;
    }

    private function checkCondition($script, $Config) {
        $arrScript = preg_split('|\s*&&\s*|', $script);
    
        $arrConditions = [];
        foreach($arrScript as $condition) {
            $_condition = preg_split('|\s*([\!\=\>\<]{2})\s*|', $condition, -1, PREG_SPLIT_DELIM_CAPTURE);
            $arrConditions[preg_replace('|@*config\.|', '', $_condition[0])] = array_slice($_condition, 1);
        }

        foreach($arrConditions as $configKey => $value) {
            $configValue = $Config->get($configKey);

            if($value[1] === 'true') {
                $value[1] = 1;
            }
            if($value[1] === 'false') {
                $value[1] = 0;
            }
            if(empty($configValue)) {
                $configValue == 0;
            }
            switch($value[0]) {
                case '==':
                    if($configValue != $value[1]) {
                        return false;
                    }
                break;
                case '!=':
                    if($configValue == $value[1]) {
                        return false;
                    }
                break;
                case '<':
                    if($configValue > $value[1]) {
                        return false;
                    }
                break;
                case '<=':
                    if($configValue >= $value[1]) {
                        return false;
                    }
                break;
                case '>':
                    if($configValue < $value[1]) {
                        return false;
                    }
                break;
                case '>=':
                    if($configValue <= $value[1]) {
                        return false;
                    }
                break;
            }
        }

        return true;
    }

    private function parseArguments($Arguments = [], &$script)
    {
        $_script = explode(' ', $script);
        $script = array_shift($_script);
        return $_script;
    }
}