<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Services\EmiService;
use Illuminate\Http\Request;

class EmiController extends Controller
{
    protected EmiService $emiService;

    public function __construct(EmiService $emiService)
    {
        $this->emiService = $emiService;
    }

    public function show(Loan $loan)
    {
        $schedules = $loan->emiSchedules()->orderBy('emi_number')->get();
        return view('loans.emi-schedule', compact('loan', 'schedules'));
    }

    public function simulate(Request $request)
    {
        $request->validate([
            'principal'     => 'required|numeric|min:1000',
            'annual_rate'   => 'required|numeric|min:0.1',
            'tenure_months' => 'required|integer|min:1',
            'method'        => 'required|in:flat,reducing',
        ]);

        $calculation = $this->emiService->simulateSchedule(
            (float) $request->principal,
            (float) $request->annual_rate,
            (int) $request->tenure_months,
            $request->method
        );

        return response()->json($calculation);
    }
}
