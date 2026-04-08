<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Video;

class DashboardController extends Controller
{
    public function index()
    {
        $videos = Video::where('user_id', auth()->id())->latest()->get();
        $user = auth()->user();
        $unlimitedCredits = (bool) ($user?->hasUnlimitedCredits() ?? false);

        $creditStats = [
            'unlimited' => $unlimitedCredits,
            'limit' => $unlimitedCredits ? null : (int) ($user->credit_limit ?? 100),
            'used' => (int) ($user->credit_used ?? 0),
            'remaining' => $unlimitedCredits ? null : (int) ($user->creditos_restantes ?? 100),
            'percent' => $unlimitedCredits ? 0 : (int) ($user->creditos_porcentaje ?? 0),
            'half_alert' => $unlimitedCredits ? false : (bool) ($user->credito_mitad_activa ?? false),
        ];

        return view('modules.dashboard.index', compact('videos', 'creditStats'));
    }
}