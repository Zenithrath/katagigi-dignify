<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\VisitAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VisitAttachmentController extends Controller
{
    private function visit($visitId): Visit
    {
        $visit = Visit::findOrFail($visitId);
        abort_if($visit->isSigned(), 422, 'Visit SIGNED tidak bisa diubah.');

        return $visit;
    }

    /**
     * Upload boleh oleh "update visit" (dokter, nurse pendamping, admin).
     * Disimpan di disk private (local), diakses hanya via signed URL.
     */
    public function store(Request $request, $visitId)
    {
        $this->authorize('update visit');
        $visit = $this->visit($visitId);

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(VisitAttachment::TYPES))],
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'description' => 'nullable|string|max:255',
        ]);

        $path = $request->file('file')->store('visit-attachments/'.$visit->id, 'local');

        VisitAttachment::create([
            'id' => (string) Str::uuid(),
            'visit_id' => $visit->id,
            'type' => $validated['type'],
            'path' => $path,
            'description' => $validated['description'] ?? null,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Lampiran diunggah.');
    }

    /**
     * Unduh via signed URL (middleware signed + read visit).
     */
    public function file(Request $request, $id)
    {
        $this->authorize('read visit');

        $attachment = VisitAttachment::findOrFail($id);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download(
            $attachment->path,
            $attachment->type.'-'.$attachment->id.'.'.pathinfo($attachment->path, PATHINFO_EXTENSION)
        );
    }

    public function destroy($visitId, $id)
    {
        $this->authorize('update visit');
        $visit = $this->visit($visitId);

        $attachment = VisitAttachment::where('visit_id', $visit->id)->where('id', $id)->firstOrFail();
        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Lampiran dihapus.');
    }
}
