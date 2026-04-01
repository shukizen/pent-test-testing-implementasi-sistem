<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $posts = $user->posts;
        $notes = $user->notes;

        return view('dashboard', compact('user', 'posts', 'notes'));
    }
}
