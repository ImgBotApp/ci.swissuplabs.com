<?php

namespace App\Http\Controllers\Github;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HookController extends Controller
{
    public function handle(Request $request)
    {
        // add job to queue with (ssh_url, project_id?) and head_commit id:
            //  cd to appropriate folder (based on ssh_url or project_name or project_id??)
            //  git fetch
            //  git checkout head_commit
            //  run tests
            //  update commit status at github.com if possible
    }
}
