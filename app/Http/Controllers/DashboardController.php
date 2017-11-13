<?php

namespace App\Http\Controllers;

use App;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return $this->activity();
    }

    public function activity()
    {
        $repositories = App\Repository::whereHas('commits', function ($query) {
            $query->where('created_at', '>', Carbon::now()->subDays(3));
        })->with(['commits' => function ($query) {
            $query->where('created_at', '>', Carbon::now()->subDays(3));
        }])->get();

        $repositories = App\Repository::with(['commits' => function ($query) {
            $query->latest();
        }])->get();

        return view('dashboard', [
            'repositories' => $repositories
        ]);
    }
}
