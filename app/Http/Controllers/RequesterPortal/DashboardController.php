<?php

namespace App\Http\Controllers\RequesterPortal;

use App\Http\Controllers\StudentPortal\DashboardController as StudentDashboardController;

class DashboardController extends StudentDashboardController
{
    protected function portalRole(): string
    {
        return 'requester';
    }

    protected function routeName(string $name): string
    {
        return 'requester.'.$name;
    }
}
