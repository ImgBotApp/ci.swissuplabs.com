<?php

namespace App\Http\Controllers\Pagespeed;

use App;
use Activity;
use App\Lib\Terminal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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

        try {
            $output = Terminal::exec(sprintf(
                "node %s %s -m -b %s --timeout 60000",
                App::basePath() . '/' . self::CRITICAL_CLI_PATH,
                escapeshellarg($request->input('website')),
                storage_path('app')
            ));
        } catch (\Exception $e) {
            report($e);

            return redirect()
                ->action('Pagespeed\CriticalCssController@index')
                ->withErrors(['Something went wrong.']);
        }

        // @todo: replace Activity with plain Log
        Activity::log('CriticalCSS: Success. ' . $request->input('website'));

        return response($output, 200, ['Content-Type' => 'text/plain']);
    }
}
