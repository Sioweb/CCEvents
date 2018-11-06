<?php

namespace Sioweb\CCEvent\Composer;

use Composer\Composer;
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

    public static function getSubscribedEvents()
    {
        return array(
            'post-package-install' => 'installAndUpdate',
            'post-package-update' => 'installAndUpdate',
            'post-update-cmd' => 'update',
        );
    }

    public function update($event)
    {
        foreach($this->scripts as $Script => $CcEvent) {
            $event->getComposer()->getEventDispatcher()->addListener('package-scripts[' . $Script . ']', $CcEvent->getScript());
            $event->getComposer()->getEventDispatcher()->dispatch('package-scripts[' . $Script . ']', $CcEvent);
        }
    }

    public function installAndUpdate($event)
    {

        $operation = $event->getOperation();

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

            foreach ($Scripts as $script) {
                $_script = $script;
                $arguments = $this->parseArguments($Arguments, $script, $event);

                $CcEvent = new PackageEvent(
                    'package-scripts['.$_script.']',
                    $event->getComposer(),
                    $event->getIO(),
                    $event->isDevMode(),
                    $event->getPolicy(),
                    $event->getPool(),
                    $event->getInstalledRepo(),
                    $event->getRequest(),
                    $event->getOperations(),
                    $event->getOperation()
                );
                $CcEvent->setScript($script);
                $CcEvent->setArguments($arguments);

                $this->scripts[$_script] = $CcEvent;
            }
        }
    }

    private function parseArguments($Arguments = [], &$script, $event)
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
