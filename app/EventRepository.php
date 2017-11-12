<?php

namespace App;

class EventRepository
{
    public static function add(PushEvent $event)
    {
        if ($event->isDeleted()) {
            return;
        }

        if (!$event->isTag() && $event->getRef() !== 'refs/heads/master') {
            // @todo: configurable refs
            return;
        }

        // save repository
        $repository = Repository::firstOrCreate([
            'owner' => $event->getRepositoryOwnerName(),
            'name' => $event->getRepositoryName(),
            'url' => $event->getRepositoryUrl(),
        ]);

        // save commits
        $repositoryId = $repository->getKey();
        $ref = $event->isTag() ? $event->getData('base_ref') : $event->getRef();
        $tag = $event->getTagName();

        foreach ($event->getData('commits', []) as $commit) {
            Commit::create([
                'repository_id' => $repositoryId,
                'ref' => $ref,
                'sha' => $commit['id'],
                'tag' => ($commit['id'] === $event->getData('after') ? $tag : null),
                'data' => [
                    'message' => $commit['message'],
                    'author' => $commit['author'],
                    'timestamp' => $commit['timestamp'],
                ],
            ]);
        }
    }
}
