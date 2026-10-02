<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('read expense');

        $query = Expense::orderBy('spent_at', 'desc')->orderBy('created_at', 'desc');
        if ($request->filled('month')) {
            $query->whereYear('spent_at', substr($request->month, 0, 4))
                ->whereMonth('spent_at', substr($request->month, 5, 2));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return view('pages.operational.expenses.index', [
            'expenses' => $query->paginate(20)->withQueryString(),
            'monthTotal' => (clone $query)->sum('amount'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage expense');

        $validated = $request->validate([
            'category' => ['required', Rule::in(Expense::CATEGORIES)],
            'amount' => 'required|numeric|min:1',
            'spent_at' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Expense::create([
            'id' => (string) Str::uuid(),
            'branch_id' => app(\App\Services\Clinical\VisitService::class)->defaultBranchId(),
            'created_by' => Auth::id(),
            ...$validated,
        ]);

        return back()->with('success', 'Beban tercatat.');
    }

    public function destroy($id)
    {
        $this->authorize('manage expense');

        Expense::findOrFail($id)->delete();

        return back()->with('success', 'Beban dihapus.');
    }
}
