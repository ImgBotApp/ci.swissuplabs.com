<?php

namespace App\Http\Controllers\Pagespeed;

use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CriticalCssController extends Controller
{
    public function index()
    {
        return view('pagespeed/critical-css');
    }

    public function generate()
    {
        $process = new Process('node -v');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            // @todo: log everything!
            return redirect()
                ->action('Pagespeed\CriticalCssController@index')
                ->with('status', 'Something went wrong.');
        }

        return response($process->getOutput(), 200, ['Content-Type' => 'text/plain']);
    }
}
