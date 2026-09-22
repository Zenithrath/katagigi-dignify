<?php

namespace App\Console\Commands;

use App\Models\WhatsappTemplate;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class WhatsappRemindH1 extends Command
{
    protected $signature = 'wa:remind-h1 {--date= : Tanggal target Y-m-d (default besok)}';

    protected $description = 'Kirim pengingat WhatsApp H-1 untuk appointment terkonfirmasi.';

    public function handle(WhatsappService $wa): int
    {
        $date = $this->option('date') ?: date('Y-m-d', strtotime('+1 day'));
        $template = WhatsappTemplate::where('name', 'reminder_h1')->where('is_active', true)->first();
        if (! $template) {
            $this->error('Template reminder_h1 tidak aktif.');

            return self::FAILURE;
        }

        $appointments = DB::table('appointments')
            ->whereDate('date', $date)
            ->whereNotNull('confirmed_at')
            ->whereNull('canceled_at')
            ->get();

        $sent = 0;
        $failed = 0;
        foreach ($appointments as $appointment) {
            // Satu reminder per pasien per hari (idempoten).
            $already = DB::table('whatsapp_messages')
                ->where('template_id', $template->id)
                ->where('patient_id', $appointment->patient_id)
                ->whereDate('created_at', date('Y-m-d'))
                ->exists();
            if ($already) {
                continue;
            }

            try {
                $wa->send(
                    $appointment->patient_phone ?? '',
                    $template->render([
                        'nama' => $appointment->patient_name,
                        'klinik' => config('whatsapp.clinic_name'),
                        'tanggal' => $appointment->date,
                        'jam' => substr($appointment->time_start, 0, 5),
                        'dokter' => $appointment->doctor_name,
                    ]),
                    $template->id,
                    $appointment->patient_id
                );
                $sent++;
            } catch (Throwable) {
                // Service melempar ulang saat gagal; status FAILED sudah tercatat di outbox.
                $failed++;
            }
        }

        $this->info("Reminder {$date}: {$sent} terkirim, {$failed} gagal.");

        return self::SUCCESS;
    }
}
