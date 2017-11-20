<?php

namespace App\Http\Controllers\App;

use App\Push;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class UpdateController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->header('X-GitHub-Event') !== 'push') {
            return;
        }

        $push = new Push($request->getContent());

        if ($push->isDeleted() || $push->getRef() !== 'refs/heads/master') {
            return;
        }

        Artisan::queue('app:update');
    }
}
