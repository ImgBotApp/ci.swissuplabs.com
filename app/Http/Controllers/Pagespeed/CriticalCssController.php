<?php

namespace App\Http\Controllers\Pagespeed;

use App;
use Activity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CriticalCssController extends Controller
{
    const CRITICAL_CLI_PATH = 'node_modules/critical/cli.js';

    public function index()
    {
        return view('pagespeed/critical-css');
    }

    public function generate(Request $request)
    {
        $this->validate($request, [
            'website' => 'required|url'
        ]);

        $command = sprintf(
            "node %s/%s %s -m -b %s",
            App::basePath(),
            self::CRITICAL_CLI_PATH,
            escapeshellarg($request->input('website')),
            App::basePath() . '/storage/app'
        );
        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            $status = 'Something went wrong.';
            if (App::environment(['local', 'staging', 'testing'])) {
                $status = $process->getErrorOutput();
            }

            Activity::log(sprintf(
                'CriticalCSS: Failure. Input: %s; Output: %s',
                $command,
                $process->getErrorOutput()
            ));

            return redirect()
                ->action('Pagespeed\CriticalCssController@index')
                ->withErrors([$status]);
        } else {
            Activity::log('CriticalCSS: Success. ' . $request->input('website'));
        }

        return response($process->getOutput(), 200, ['Content-Type' => 'text/plain']);
    }
}
