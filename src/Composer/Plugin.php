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
        file_put_contents('/tmp/composer.log', __METHOD__ . "\n",FILE_APPEND);
    }
    
    public static function getSubscribedEvents()
    {
        return array(
            'post-install-cmd' => 'installOrUpdate',
            'post-update-cmd' => 'installOrUpdate',            
        );
    }    
    
    public function installOrUpdate($event)
    {
        file_put_contents('/tmp/composer.log', __METHOD__ . "\n",FILE_APPEND);
        file_put_contents('/tmp/composer.log', get_class($event) . "\n",FILE_APPEND);            
        file_put_contents('/tmp/composer.log', $event->getName() . "\n",FILE_APPEND);                    
    }
}