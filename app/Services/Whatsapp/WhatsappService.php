<?php

namespace App\Services\Whatsapp;

use App\Models\WhatsappMessage;
use App\Services\Service;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class WhatsappService extends Service
{
    /**
     * Normalisasi ke format internasional tanpa + (08… → 628…).
     */
    public static function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }
        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    public function send(string $phone, string $body, ?string $templateId = null, ?string $patientId = null): WhatsappMessage
    {
        if (! config('whatsapp.enabled')) {
            throw new Exception('Fitur WhatsApp sedang dinonaktifkan.', 422);
        }

        $normalized = self::normalizePhone($phone);

        // Catat dulu setiap percobaan ke outbox — termasuk nomor invalid — agar
        // staff bisa lihat penyebab kegagalan dan job reminder tidak spam retry.
        $message = WhatsappMessage::create([
            'id' => (string) Str::uuid(),
            'template_id' => $templateId,
            'patient_id' => $patientId,
            'phone' => $normalized ?? mb_substr($phone, 0, 32),
            'body' => $body,
            'status' => WhatsappMessage::STATUS_QUEUED,
            'error' => $normalized ? null : 'Nomor WhatsApp tidak valid.',
        ]);

        if (! $normalized) {
            $message->update(['status' => WhatsappMessage::STATUS_FAILED]);
            throw new Exception('Nomor WhatsApp tidak valid.', 422);
        }

        try {
            if (config('whatsapp.driver') === 'cloud') {
                $this->sendViaCloud($message);
            } else {
                Log::channel('single')->info('[WA-log] ke '.$normalized.': '.$body);
            }

            $message->update([
                'status' => WhatsappMessage::STATUS_SENT,
                'sent_at' => now(),
            ]);
        } catch (Throwable $th) {
            $this->writeLog('WhatsappService::send', $th);
            $message->update([
                'status' => WhatsappMessage::STATUS_FAILED,
                'error' => $th->getMessage(),
            ]);

            // Lempar ulang agar pemanggil (controller/command) tahu pengiriman gagal
            // — status pesan tetap tercatat FAILED di outbox.
            throw $th;
        }

        return $message->fresh();
    }

    private function sendViaCloud(WhatsappMessage $message): void
    {
        if (! config('whatsapp.phone_number_id') || ! config('whatsapp.access_token')) {
            throw new Exception('WhatsApp Cloud belum dikonfigurasi.', 422);
        }

        $response = Http::withToken(config('whatsapp.access_token'))
            ->timeout(30)
            ->post(config('whatsapp.base_url').'/'.config('whatsapp.phone_number_id').'/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $message->phone,
                'type' => 'text',
                'text' => ['body' => $message->body],
            ]);

        if (! $response->successful()) {
            throw new Exception('WhatsApp Cloud menolak: HTTP '.$response->status(), 500);
        }

        $externalId = $response->json('messages.0.id');
        if ($externalId) {
            $message->update(['external_id' => $externalId]);
        }
    }
}
