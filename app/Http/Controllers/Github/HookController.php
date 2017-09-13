<?php

namespace App\Http\Controllers\Github;

use Illuminate\Http\Request;
use App\Jobs\ValidateGithubCommit;
use App\Http\Controllers\Controller;

class HookController extends Controller
{
    public function handle(Request $request)
    {
        ValidateGithubCommit::dispatch(json_decode($request->getContent(), true));
    }
}