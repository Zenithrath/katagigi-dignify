<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Nurse;
use App\Models\NurseAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NurseAttendanceController extends Controller
{
    /**
     * Asisten menginput jam masuk/pulang harian.
     * - Pemegang 'manage attendance' (manajemen/admin): kelola semua perawat.
     * - Pemegang 'record own attendance' (perawat): hanya boleh catat milik sendiri.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->canAny(['manage attendance', 'record own attendance']), 403);
        $canManage = $user->can('manage attendance');

        $month = $request->input('month', date('Y-m'));
        [$y, $m] = array_map('intval', explode('-', $month) + [date('Y'), date('m')]);

        $nurses = Nurse::join('users', 'users.id', '=', 'nurses.user_id')
            ->select('nurses.user_id', 'users.name')
            ->orderBy('users.name')
            ->get();

        $query = NurseAttendance::with('user:id,name')
            ->whereYear('date', $y)->whereMonth('date', $m)
            ->orderBy('date', 'desc');

        if (! $canManage) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('nurse_id')) {
            $query->where('user_id', $request->nurse_id);
        }

        return view('pages.operational.attendances.index', [
            'rows' => $query->paginate(31)->withQueryString(),
            'nurses' => $nurses,
            'month' => sprintf('%04d-%02d', $y, $m),
            'nurse_id' => $request->input('nurse_id', ''),
            'canManage' => $canManage,
            'defaultEnd' => config('clinic.default_scheduled_end'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->canAny(['manage attendance', 'record own attendance']), 403);
        $canManage = $user->can('manage attendance');

        $validated = $request->validate([
            'user_id' => [$canManage ? 'required' : 'nullable', 'string', Rule::exists('nurses', 'user_id')],
            'date' => 'required|date|before_or_equal:today',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'scheduled_end' => 'required|date_format:H:i',
            'note' => 'nullable|string|max:255',
        ]);

        $targetUser = $canManage ? $validated['user_id'] : $user->id;

        NurseAttendance::updateOrCreate(
            ['user_id' => $targetUser, 'date' => $validated['date']],
            [
                'id' => (string) Str::uuid(),
                'clock_in' => $validated['clock_in'],
                'clock_out' => $validated['clock_out'],
                'scheduled_end' => $validated['scheduled_end'],
                'note' => $validated['note'] ?? null,
                'input_by' => $user->id,
            ]
        );

        return back()->with('success', 'Jam kerja tercatat.');
    }

    public function update(Request $request, $id)
    {
        $row = NurseAttendance::findOrFail($id);
        $this->ensureAccess($row);

        $validated = $request->validate([
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'scheduled_end' => 'required|date_format:H:i',
            'note' => 'nullable|string|max:255',
        ]);

        $row->update($validated + ['input_by' => Auth::id()]);

        return back()->with('success', 'Jam kerja diperbarui.');
    }

    public function destroy($id)
    {
        $row = NurseAttendance::findOrFail($id);
        $this->ensureAccess($row);
        $row->delete();

        return back()->with('success', 'Catatan jam kerja dihapus.');
    }

    private function ensureAccess(NurseAttendance $row): void
    {
        $user = Auth::user();
        if (! $user->can('manage attendance')
            && ($row->user_id !== $user->id || ! $user->can('record own attendance'))) {
            abort(403);
        }
    }
}
