<?php

namespace Sioweb\CCEvent\Composer\Script;

use Composer\Script\PackageEvent as BasePackageEvent;

/**
 * The Package Event.
 *
 * @deprecated Use Composer\Installer\PackageEvent instead
 */
class PackageEvent extends BasePackageEvent
{

    private $arguments = [];
    
    public function setArguments($args) {
        $this->arguments = $args;
    }

    public function getAttributes() {
        return $this->arguments;
    }
}
