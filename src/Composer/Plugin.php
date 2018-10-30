<?php

namespace Sioweb\CCEvent\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Script\PackageEvent;

class Plugin implements PluginInterface, EventSubscriberInterface
{

    public function activate(Composer $composer, IOInterface $io)
    {
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            // 'post-install-cmd' => 'installOrUpdate',
            // 'post-update-cmd' => 'installOrUpdate',
            'post-package-install' => 'installOrUpdate',
            'post-package-update' => 'installOrUpdate',
        );
    }
    
    public function installOrUpdate($event)
    {
        $operation = $event->getOperation();
        // $IO = $event->getIO();

        $Type = $event->getName() === 'post-package-install' 
            ? 'install'
            : 'update';

        $package = method_exists($operation, 'getPackage')
            ? $operation->getPackage()
            : $operation->getInitialPackage();

        $Dumper = new ArrayDumper;
        $EventDispatcher = $event->getComposer()->getEventDispatcher();
        
        // $IO->writeln('Run contao package '.$package->getName().': '.$Type);

        // echo "\t\t- root: ".$event->getComposer()->getPackage()."\n";
        // echo "\t\t- getTargetDir: ".$package->getTargetDir()."\n";
        // echo "\t\t- getSourceType: ".$package->getSourceType()."\n";
        // echo "\t\t- getSourceUrl: ".$package->getSourceUrl()."\n";
        // echo "\t\t- getVersion: ".$package->getVersion()."\n";
        // echo "\t\t- getUrls: ".print_r($package->getSourceUrls(),1)."\n";
        // echo "\t\t- getVendorPath: ".$event->getComposer()->getConfig()->get('vendor-dir')."\n";
        // echo "\t\t- ArrayDump: ".print_r($Dumper->dump($package),1)."\n";

        $ComposerJson = $event->getComposer()->getConfig()->get('vendor-dir').'/'.$package->getName().'/composer.json';
        if(is_file($ComposerJson)) {
            $Scripts = [];
            $ComposerArray = json_decode(file_get_contents($ComposerJson), 1);
            if(!empty($ComposerArray['scripts']['post-'.$Type.'-contao'])) {
                $Scripts = $ComposerArray['scripts']['post-'.$Type.'-contao'];
            } else {
                return;
            }

            foreach($Scripts as $script) {
                $EventDispatcher->addListener('post-'.$Type.'-contao', $script);
            }
            
            // $EventDispatcher->dispatch('post-'.$Type.'-contao', new PackageEvent($eventName, $event->getComposer(), $event->getIO(), $devMode, $policy, $pool, $installedRepo, $request, $operations, $operation));
            $EventDispatcher->dispatch('post-'.$Type.'-contao', new PackageEvent(
                'post-'.$Type.'-contao',
                $event->getComposer(),
                $event->getIO(),
                $event->isDevMode(),
                $event->getPolicy(),
                $event->getPool(),
                $event->getInstalledRepo(),
                $event->getRequest(),
                $event->getOperations(),
                $event->getOperation()
            ));
            
            // echo "\t\t- composer.json?: ".($event->getComposer()->getConfig()->get('vendor-dir').'/'.$package->getName().'/composer.json')."\n";
            // echo "\t\t- is_file: ".is_file($event->getComposer()->getConfig()->get('vendor-dir').'/'.$package->getName().'/composer.json')."\n";
            // echo "\t\t".'PLUGIN: '.$package->getName().', method: '.__METHOD__.', class: '.get_class($event).', name: '.$event->getName()."\n";                   
        }
    }
}