<?php

namespace App\Http\Controllers\Github;

use App\Events\PushRecieved;
use App\PushEvent;
use App\EventRepository;
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

        if (in_array($pushEvent->getRepositoryFullName(), config('repositories.ignore'))) {
            return;
        }

        event(new PushRecieved($pushEvent));
    }
}
