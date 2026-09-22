<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Http\Request;
use Throwable;

class WhatsappController extends Controller
{
    public function __construct(private WhatsappService $wa) {}

    public function index(Request $request)
    {
        $this->authorize('manage whatsapp');

        $query = WhatsappMessage::with(['template:id,name', 'patient:id,name'])
            ->orderBy('created_at', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('pages.integration.whatsapp.index', [
            'messages' => $query->paginate(20)->withQueryString(),
            'templates' => WhatsappTemplate::where('is_active', true)->orderBy('name')->get(),
            'driver' => config('whatsapp.driver'),
        ]);
    }

    public function send(Request $request)
    {
        $this->authorize('manage whatsapp');

        $validated = $request->validate([
            'phone' => 'required|string|max:32',
            'body' => 'required|string|max:1000',
        ]);

        try {
            $message = $this->wa->send($validated['phone'], $validated['body']);
        } catch (Throwable $th) {
            report($th);

            return back()->withErrors(['phone' => $th->getMessage()])->withInput();
        }

        return back()->with(
            $message->status === WhatsappMessage::STATUS_SENT ? 'success' : 'error',
            $message->status === WhatsappMessage::STATUS_SENT
                ? 'Pesan tercatat terkirim.'
                : 'Pesan gagal dikirim: '.$message->error
        );
    }
}
