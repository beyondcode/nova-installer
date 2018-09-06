<?php

namespace Beyondcode\NovaInstaller\Http\Controllers;

use Illuminate\Routing\Controller;
use Beyondcode\NovaInstaller\Utils\ComposerStatus;

class InstallationStatusController extends Controller
{
    protected $status;

    public function __construct(ComposerStatus $status)
    {
        $this->status = $status;
    }

    public function show()
    {
        return $this->status->show();
    }

    public function reset()
    {
        return $this->status->reset();
    }
}
