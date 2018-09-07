<?php

namespace Beyondcode\NovaInstaller\Utils\Manipulation;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;

class ManifestManipulator
{
    public function __construct(Filesystem $filesystem, PackageManifest $manifest)
    {
        $this->filesystem = $filesystem;
        $this->manifest = $manifest;
    }

    public function removeFromManifest($package)
    {
        $newManifestContent = collect($this->getPackagesFromManifest())->except($package)->toArray();

        $this->write($newManifestContent);
    }

    protected function getPackagesFromManifest()
    {
        return file_exists($this->manifest->manifestPath) ? $this->filesystem->getRequire($this->manifest->manifestPath) : [];
    }

    protected function write(array $manifest)
    {
        if (! is_writable(dirname($this->manifest->manifestPath))) {
            throw new Exception('The '.dirname($this->manifest->manifestPath).' directory must be present and writable.');
        }

        $this->filesystem->put(
            $this->manifest->manifestPath,
            '<?php return '.var_export($manifest, true).';',
            true
        );
    }
}
