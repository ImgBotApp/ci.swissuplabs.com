<?php

namespace App\Http\Controllers\Github;

use App\Events\PushRecieved;
use App\Push;
use App\CommitRepository;
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

        $push = new Push($request->getContent());

        if (in_array($push->getRepositoryFullName(), config('repositories.ignore'))) {
            return;
        }

        event(new PushRecieved($push));
    }
}
