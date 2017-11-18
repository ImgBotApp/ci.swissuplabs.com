<?php

namespace App\Listeners;

use App\Commit;
use App\Events\PushValidated;

class UpdateCommitStatus
{
    /**
     * Handle the event.
     *
     * @param  PushValidated  $event
     * @return void
     */
    public function handle(PushValidated $event)
    {
        /**
         * @var string  $status
         * @var array   $errors
         * @var array   $result
         * @var string  $resultUrl
         */
        extract($event->data);

        // Create Github commit status
        if ($status === 'failure') {
            $description = 'Internal server error';
        } else {
            $description = sprintf(
                "%s / %s checks OK",
                count($result) - count($errors),
                count($result)
            );
        }
        $event->push->createCommitStatus(
            $status,
            $description,
            $resultUrl
        );

        // Create commit comment if previous commit passed all tests
        if ($status === 'error' && $event->push->getPreviousCommitStatus() === 'success') {

            $event->push->createCommitComment(sprintf(
                "Please verify your [commit](%s) as it didn't pass [some tests](%s)",
                $event->push->getCompareUrl(),
                $resultUrl
            ));
        }

        // Update commit status locally
        Commit::where('sha', $event->push->getSha())->update([
            'status' => $status
        ]);
    }
}
