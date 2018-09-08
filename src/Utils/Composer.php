<?php

namespace Beyondcode\NovaInstaller\Utils;

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

        $process->setCommandLine(trim($this->findComposer().' require '.$package));

        $process->run($callback);

        return $process->isSuccessful();
    }


    /**
     * Remove a composer package.
     *
     * @param $package
     * @param callable $callback
     * @return bool
     */

    public function remove($package, callable $callback)
    {
        $this->setWorkingPath(base_path());

        $process = $this->getProcess();

        $process->setCommandLine(trim($this->findComposer().' remove '.$package));

        $process->run($callback);

        return $process->isSuccessful();
    }


    /**
     * Update a composer package.
     *
     * @param $package
     * @param callable $callback
     * @return bool
     */

    public function update($package, callable $callback)
    {
        $this->setWorkingPath(base_path());

        $process = $this->getProcess();

        $process->setCommandLine(trim($this->findComposer().' update '.$package));

        $process->run($callback);

        return $process->isSuccessful();
    }
}
