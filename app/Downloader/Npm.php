<?php

namespace App\Downloader;

use App\Lib\Terminal;

class Npm
{
    public function download($values, $destination)
    {
        $command = sprintf(
            "npm install --prefix %s %s",
            storage_path('app/tools'),
            $values['package']
        );
        Terminal::exec($command);
    }
}
