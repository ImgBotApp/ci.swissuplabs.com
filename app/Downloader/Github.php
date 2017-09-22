<?php

namespace App\Downloader;

use App\Lib\Github as Git;
use App\Lib\Terminal;
use Illuminate\Support\Facades\Storage;

class Github
{
    public function download($values, $destination)
    {
        $destination .= '.tar';

        Storage::put(
            $destination,
            Git::api('repo')->contents()->archive(
                $values['username'],
                $values['repository'],
                'tarball',
                $values['ref']
            )
        );

        $unpackPath = explode('.', $destination);
        $unpackPath = $unpackPath[0];

        Storage::deleteDirectory($unpackPath);
        Storage::makeDirectory($unpackPath);

        $command = sprintf(
            "tar -xf %s --directory %s --strip-components=1",
            storage_path("app/{$destination}"),
            storage_path("app/{$unpackPath}")
        );
        Terminal::exec($command);

        Storage::delete($destination);
    }
}
