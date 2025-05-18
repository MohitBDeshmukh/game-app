<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Score;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScoreController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'score' => 'required|integer|between:50,500',
        ]);

        $user = Auth::user();
        $todayCount = Score::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayCount >= 3) {
            return response()->json(['error' => 'Score can be submitted only 3 times a day'], 400);
        }

        Score::create([
            'user_id' => $user->id,
            'score' => $request->score,
        ]);

        return response()->json(['message' => 'Score saved successfully']);
    }

    public function overallScore()
    {
        $user = Auth::user();

        $totalScore = Score::where('user_id', $user->id)->sum('score');

        // Calculate rank
        $ranks = Score::select('user_id', DB::raw('SUM(score) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->get();

        $rank = $ranks->search(fn($r) => $r->user_id === $user->id) + 1;

        return response()->json([
            'success' => true,
            'totalScore' => $totalScore,
            'rank' => $rank
        ]);
    }

    public function weeklyScore()
    {
        $user = Auth::user();
        $weeks = [];

        // Week 1 starts from March 28, 2025
        $startDate = Carbon::create(2025, 3, 28)->startOfDay();
        $today = now();

        $weekNo = 1;

        while ($startDate->lt($today)) {
            $endOfWeek = (clone $startDate)->addDays(6)->endOfDay();

            $weeklyScore = Score::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endOfWeek])
                ->sum('score');

            $ranks = Score::select('user_id', DB::raw('SUM(score) as total'))
                ->whereBetween('created_at', [$startDate, $endOfWeek])
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->get();

            $rank = $ranks->search(fn($r) => $r->user_id === $user->id);
            $rank = $rank !== false ? $rank + 1 : null;

            $weeks[] = [
                'weekNo' => $weekNo++,
                'rank' => $rank,
                'totalScore' => $weeklyScore,
            ];

            $startDate->addDays(7)->startOfDay();
        }

        return response()->json([
            'success' => true,
            'weeks' => $weeks
        ]);
    }
}
