<?php

namespace App\Http\Controllers\RequesterPortal;

use App\Http\Controllers\StudentPortal\OrderController as StudentOrderController;

class OrderController extends StudentOrderController
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
