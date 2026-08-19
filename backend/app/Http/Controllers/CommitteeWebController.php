<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use Illuminate\Http\Request;

class CommitteeWebController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriod = $request->query('period');

        $query = Committee::query();
        if ($selectedPeriod) {
            $query->where('period', $selectedPeriod);
        }

        $committees = $query->latest()->get();

        // Ambil daftar unik periode yang ada untuk filter
        $periods = Committee::select('period')->distinct()->pluck('period');

        return view('committees.index', compact('committees', 'periods', 'selectedPeriod'));
    }
}