<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\DoctorFee;
use App\Models\DoctorFeeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DoctorFeeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('read turnover');

        $query = DoctorFee::with(['doctor.user:id,name', 'invoice:id,number'])
            ->orderBy('created_at', 'desc');

        if (Auth::user()->hasRole('doctor')) {
            $query->where('doctor_id', Auth::id());
        } elseif ($request->filled('doctor')) {
            $query->where('doctor_id', $request->doctor);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $query->whereYear('created_at', substr($request->month, 0, 4))
                ->whereMonth('created_at', substr($request->month, 5, 2));
        }

        return view('pages.billing.fees.index', [
            'fees' => $query->paginate(20)->withQueryString(),
            'totalUnpaid' => (clone $query)->where('status', DoctorFee::STATUS_UNPAID)->sum('fee_amount'),
            'totalPaid' => (clone $query)->where('status', DoctorFee::STATUS_PAID)->sum('fee_amount'),
        ]);
    }

    public function pay($id)
    {
        $this->authorize('manage doctor fee');

        $fee = DoctorFee::findOrFail($id);
        $fee->update(['status' => DoctorFee::STATUS_PAID, 'paid_at' => now()]);

        return back()->with('success', 'Jasa medis dicairkan.');
    }

    public function rule(Request $request, $doctorId)
    {
        $this->authorize('manage doctor fee');

        $validated = $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
            'xray_percentage' => 'required|numeric|min:0|max:100',
            'shift_allowance' => 'nullable|numeric|min:0',
        ]);

        $rule = DoctorFeeRule::firstOrNew(['doctor_id' => $doctorId]);
        if (! $rule->exists) {
            $rule->id = (string) Str::uuid();
        }
        $rule->fill($validated + ['shift_allowance' => $validated['shift_allowance'] ?? 50000]);
        $rule->save();

        return back()->with('success', 'Aturan fee disimpan.');
    }
}
