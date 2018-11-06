<?php

namespace Sioweb\CCEvent\Composer\Installer;

use Composer\Installer\PackageEvent as BaseEvent;

class PackageEvent extends BaseEvent
{
    private $arguments = [];
    private $script;

    public function setArguments($args)
    {
        $this->arguments = $args;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setScript($script)
    {
        $this->script = $script;
    }

    public function getScript()
    {
        return $this->script;
    }
}
