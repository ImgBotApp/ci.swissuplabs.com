<?php

namespace App\Http\Controllers\Github;

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

        if (!$this->canHandle($pushEvent)) {
            return;
        }

        if ($pushEvent->isTag()) {
            DebouncedJob::dispatch(new UpdateComposerPackages($pushEvent), 600);
        } else {
            ValidateGithubCommit::dispatch($pushEvent);
        }

        EventRepository::add($pushEvent);
    }

    /**
     * Check if we should handle request
     *
     * @return boolean
     */
    protected function canHandle(PushEvent $pushEvent)
    {
        $ignored = config('repositories.ignore');

        return !$pushEvent->isDeleted()
            && !in_array($pushEvent->getRepositoryFullName(), $ignored);
    }
}
