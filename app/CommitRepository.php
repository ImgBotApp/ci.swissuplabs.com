<?php

namespace App;

class CommitRepository
{
    public static function add(Push $push)
    {
        if ($push->isDeleted()) {
            return;
        }

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
            Commit::create([
                'repository_id' => $repositoryId,
                'ref' => $ref,
                'sha' => $commit['id'],
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
