<?php

namespace Sioweb\CCEvent\Composer;

use Composer\Composer;
use Composer\Installer\PackageEvent as ComposerPackageEvent;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Plugin\PluginInterface;
use Sioweb\CCEvent\Composer\Installer\PackageEvent;

class Plugin implements PluginInterface, EventSubscriberInterface
{

    private $scripts = [];

    public function activate(Composer $composer, IOInterface $io)
    {
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    public static function getSubscribedEvents()
    {
        return array(
            'post-package-install' => 'registratePackageScripts',
            'post-package-update' => 'registratePackageScripts',
            'post-install-cmd' => ['runPackageScripts', -999],
            'post-update-cmd' => ['runPackageScripts', -999],
        );
    }

    public function runPackageScripts($event)
    {
        foreach($this->scripts as $Script => $CcEvent) {
            $event->getComposer()->getEventDispatcher()->addListener('package-scripts[' . $Script . ']', $CcEvent->getScript());
            $event->getComposer()->getEventDispatcher()->dispatch('package-scripts[' . $Script . ']', $CcEvent);
        }
    }

    public function registratePackageScripts(ComposerPackageEvent $event)
    {
        $operation = $event->getOperation();

        // die('<pre>' . __METHOD__ . ":\n" . print_r(get_class_methods($event), true) . "\n#################################\n\n" . '</pre>');

        $package = method_exists($operation, 'getPackage')
        ? $operation->getPackage()
        : $operation->getInitialPackage();

        $Dumper = new ArrayDumper;
        $EventDispatcher = $event->getComposer()->getEventDispatcher();
        
        $ComposerJson = $event->getComposer()->getConfig()->get('vendor-dir') . '/' . $package->getName() . '/composer.json';
        
        if (is_file($ComposerJson)) {
            $Scripts = [];
            $Arguments = [];
            $ComposerArray = json_decode(file_get_contents($ComposerJson), 1);
            if (!empty($ComposerArray['scripts']['package-scripts'])) {
                $Scripts = $ComposerArray['scripts']['package-scripts'];
            } else {
                return;
            }

            $Config = $event->getComposer()->getConfig();
            foreach ($Scripts as $script) {
                if(strpos($script, '@config') !== false) {
                    if(!$this->checkCondition($script, $Config)) {
                        break;
                    }
                    continue;
                }
                $_script = $script;
                $arguments = $this->parseArguments($script, $event, $Arguments);

                $CcEvent = new PackageEvent(
                    'package-scripts['.$_script.']',
                    $event->getComposer(),
                    $event->getIO(),
                    $event->isDevMode(),
                    $event->getLocalRepo(),
                    $event->getOperations(),
                    $event->getOperation()
                );
                $CcEvent->setScript($script);
                $CcEvent->setArguments($arguments);

                $this->scripts[$_script] = $CcEvent;
            }
        }
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

    private function parseArguments(&$script, $event, $Arguments = [])
    {
        $_script = explode(' ', $script);
        $script = array_shift($_script);
        return $_script;
    }

    /**
     * Checks if string given references a class path and method
     *
     * @param  string $callable
     * @return bool
     */
    protected function isPhpScript($callable)
    {
        return false === strpos($callable, ' ') && false !== strpos($callable, '::');
    }
}
