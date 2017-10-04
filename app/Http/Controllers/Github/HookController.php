<?php

namespace App\Http\Controllers\Github;

use App\PushEvent;
use Illuminate\Http\Request;
use App\Jobs\ValidateGithubCommit;
use App\Http\Controllers\Controller;

class HookController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->header('X-GitHub-Event') !== 'push') {
            return;
        }

        $pushEvent = new PushEvent($request->getContent());
        ValidateGithubCommit::dispatch($pushEvent);
    }
}
