<?php

namespace Beyondcode\NovaInstaller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Beyondcode\NovaInstaller\Utils\ComposerStatus;

class InstallationStatusController extends Controller
{

    /**
     * The ComposerStatus object.
     *
     * @var \Beyondcode\NovaInstaller\Utils\ComposerStatus
     */

    protected $status;


    /**
     * Create a new controller instance.
     *
     * @param  \Beyondcode\NovaInstaller\Utils\ComposerStatus  $status
     * @return void
     */

    public function __construct(ComposerStatus $status)
    {
        $this->status = $status;
    }


    /**
     * Show the current status of the action being run.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function show(Request $request)
    {
        return $this->status->show();
    }


    /**
     * Reset the status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function reset(Request $request)
    {
        return $this->status->reset();
    }
}
