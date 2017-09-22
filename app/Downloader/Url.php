<?php

namespace App\Downloader;

use Illuminate\Support\Facades\Storage;

class Url
{
    public function download($values, $destination)
    {
        $client = new \GuzzleHttp\Client();

        Storage::put(
            $destination,
            $client->request('GET', $values['url'])->getBody()
        );
    }
}
