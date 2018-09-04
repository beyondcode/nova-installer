<?php

namespace Beyondcode\NovaInstaller;

use Illuminate\Support\Composer as BaseComposer;

class Composer extends BaseComposer
{
    /**
     * Install a composer package.
     *
     * @param $package
     * @param callable $callback
     * @return bool
     */
    public function install($package, callable $callback)
    {
        $this->setWorkingPath(base_path());

        $process = $this->getProcess();

        $process->setCommandLine(trim($this->findComposer() . ' require ' . $package));

        $process->run($callback);

        return $process->isSuccessful();
    }
}
