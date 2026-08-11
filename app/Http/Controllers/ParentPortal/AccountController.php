<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    use ResolvesParentProfile;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $parent = $this->parentFor($request);
        $childrenCount = $this->linksFor($parent)->count();

        return Inertia::render('Parent/Account', [
            'profile' => [
                'name' => $parent->name ?: $user->name,
                'email' => $parent->email ?: $user->email,
                'phone' => $parent->phone ?: $user->phone,
                'cpf' => $parent->cpf,
                'children_count' => $childrenCount,
            ],
        ]);
    }
}
