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
        $activitySince = Carbon::now()->subDays(2);

        $repositories = App\Repository::whereHas(
                'commits', function ($query) use ($activitySince) {
                    $query->where('created_at', '>', $activitySince);
                }
            )
            ->with([
                'commits' => function ($query) use ($activitySince) {
                    $query->where('created_at', '>', $activitySince)->latest();
                }
            ])
            ->get()
            ->map(function ($repository) {
                $repository->setRelation('commits', $repository->commits->take(3));
                return $repository;
            })
            ->sortByDesc(function ($repository) {
                return $repository->commits[0]->created_at;
            });

        return view('dashboard', [
            'repositories' => $repositories
        ]);
    }
}
