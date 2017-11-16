<?php

namespace App;

class CommitRepository
{
    public static function addFromPush(Push $push)
    {
        if (!$push->isTag() && $push->getRef() !== 'refs/heads/master') {
            // @todo: configurable refs
            return;
        }

        // save repository
        $repository = Repository::firstOrCreate([
            'owner' => $push->getRepositoryOwnerName(),
            'name' => $push->getRepositoryName(),
            'url' => $push->getRepositoryUrl(),
        ]);

        // save commits
        $repositoryId = $repository->getKey();
        $ref = $push->isTag() ? $push->getData('base_ref') : $push->getRef();
        $tag = $push->getTagName();

        foreach ($push->getData('commits', []) as $commit) {
            Commit::updateOrCreate([
                'repository_id' => $repositoryId,
                'sha' => $commit['id'],
            ], [
                'ref' => $ref,
                'tag' => ($commit['id'] === $push->getData('after') ? $tag : null),
                'data' => [
                    'message' => $commit['message'],
                    'author' => $commit['author'],
                    'timestamp' => $commit['timestamp'],
                ],
            ]);
        }
    }
}
