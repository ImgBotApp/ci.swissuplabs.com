<?php

namespace App\Downloader;

use App\Lib\Github as Git;
use App\Lib\Terminal;
use Illuminate\Support\Facades\Storage;

class Github
{
    public function download($values, $destination)
    {
        $archive = $destination . '.tar';

        Storage::put(
            $archive,
            Git::api('repo')->contents()->archive(
                $values['username'],
                $values['repository'],
                'tarball',
                $values['ref']
            )
        );

        Storage::deleteDirectory($destination);
        Storage::makeDirectory($destination);

        $command = sprintf(
            "tar -xf %s --directory %s --strip-components=1",
            storage_path("app/{$archive}"),
            storage_path("app/{$destination}")
        );
        Terminal::exec($command);

        Storage::delete($archive);
    }
}
