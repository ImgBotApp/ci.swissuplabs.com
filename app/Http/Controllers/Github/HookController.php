<?php

namespace App\Http\Controllers\Github;

use App\PushEvent;
use Illuminate\Http\Request;
use App\Jobs\DebouncedJob;
use App\Jobs\ValidateGithubCommit;
use App\Jobs\UpdateComposerPackages;
use App\Http\Controllers\Controller;

class HookController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->header('X-GitHub-Event') !== 'push') {
            return;
        }

        $pushEvent = new PushEvent($request->getContent());

        if ($pushEvent->isDeleted()) {
            return;
        }

        if ($pushEvent->isTag()) {
            DebouncedJob::dispatch(new UpdateComposerPackages($pushEvent), 10);
        } else {
            ValidateGithubCommit::dispatch($pushEvent);
        }
    }
}
