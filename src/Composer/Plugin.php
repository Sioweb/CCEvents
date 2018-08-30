<?php

namespace Sioweb\CCEvent\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;

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
        );
    }
    
    public function installOrUpdate($event)
    {
        echo 'PLUGIN: method: '.__METHOD__.', class: '.get_class($event).', name: '.$event->getName();                   
    }
}