<?php

namespace App\Http\Controllers;

use App\Models\GameRoom;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $isAdmin = ($user->role ?? '') === 'admin';
        $quizzes = Quiz::query()->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id))->count();
        $rooms = GameRoom::query()->when(! $isAdmin, fn ($q) => $q->where('host_id', $user->id))->count();
        $activeRooms = GameRoom::query()->when(! $isAdmin, fn ($q) => $q->where('host_id', $user->id))->whereIn('status', ['lobby', 'running'])->count();
        return view('dashboard', compact('quizzes', 'rooms', 'activeRooms'));
    }
}
