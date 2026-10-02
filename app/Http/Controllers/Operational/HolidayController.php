<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manage holiday');

        $year = (int) $request->input('year', date('Y'));

        return view('pages.operational.holidays.index', [
            'holidays' => Holiday::whereYear('date', $year)->orderBy('date')->get(),
            'year' => $year,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage holiday');

        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:120',
        ]);

        Holiday::create(['id' => (string) Str::uuid(), ...$validated]);

        return back()->with('success', 'Tanggal merah ditambahkan.');
    }

    public function destroy($id)
    {
        $this->authorize('manage holiday');

        Holiday::findOrFail($id)->delete();

        return back()->with('success', 'Tanggal merah dihapus.');
    }
}
