<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MonthlyReports
{
    private $since;

    private $until;

    private $doctorID;

    public function __construct(?object $filter = null)
    {
        $this->since = $filter->since;
        $this->until = $filter->until;
        $this->doctorID = $filter->doctor_id;
    }

    public function export()
    {
        $query = DB::table('transactions')
            ->select([
                'transactions.id',
                'transactions.sequence',
                'transactions.voucher_code',
                'transactions.patient_id',
                'transactions.appointment_id',
                'transactions.next_schedule',
                'transactions.price',
                'transactions.discount',
                'transactions.billing',
                'transactions.services',
                'transactions.doctor_name',
                'transactions.nurse_name',
                'transactions.payment_method',
                'transactions.created_at',
                'transactions.canceled_at',
            ]);

        if ($this->since) {
            $query->where('transactions.created_at', '>=', $this->since);
        }

        if (! $this->since) {
            $query->whereMonth('transactions.created_at', date('m'))
                ->whereYear('transactions.created_at', date('Y'));
        }

        if ($this->until) {
            $until = Carbon::parse($this->until)->addDays(1);
            $query->where('transactions.created_at', '<', $until);
        }

        if ($this->doctorID) {
            $query->where('transactions.doctor_id', $this->doctorID);
        }

        // Uraikan services JSON di PHP (pengganti json_array_elements Postgres).
        // Paritas perilaku lama: baris tanpa alamat pasien / service tak dikenal dilewati.
        $serviceIndex = DB::table('services')->get(['id', 'code', 'name', 'category_id'])->keyBy('id');
        $categoryIndex = DB::table('categories')->get(['id', 'name', 'code'])->keyBy('id');

        $flat = [];
        foreach ($query->orderBy('transactions.created_at', 'asc')->get() as $trx) {
            $patient = DB::table('patients')->where('id', $trx->patient_id)->first();
            $address = DB::table('patient_addresses')->where('patient_id', $trx->patient_id)->first();
            if (! $patient || ! $address) {
                continue;
            }
            foreach ((array) json_decode($trx->services) as $svc) {
                $svc = (array) $svc;
                if (($svc['class'] ?? 'service') !== 'service' || ! isset($serviceIndex[$svc['id'] ?? ''])) {
                    continue;
                }
                $service = $serviceIndex[$svc['id']];
                $category = $categoryIndex[$service->category_id] ?? null;
                $flat[] = (object) [
                    'id' => $trx->id,
                    'sequence' => $trx->sequence,
                    'voucher_code' => $trx->voucher_code,
                    'patient_name' => $patient->name,
                    'phone' => $patient->phone,
                    'patient_code' => $patient->code,
                    'birthdate' => $patient->birthdate,
                    'gender' => $patient->gender,
                    'street' => $address->street,
                    'tonarigumi' => $address->tonarigumi,
                    'village' => $address->village,
                    'district' => $address->district,
                    'zip_code' => $address->zip_code,
                    'regency' => $address->regency,
                    'province' => $address->province,
                    'service_code' => $service->code,
                    'service_name' => $service->name,
                    'service_price' => $svc['price'] ?? 0,
                    'service_quantity' => $svc['quantity'] ?? 0,
                    'service_subtotal' => $svc['subtotal'] ?? 0,
                    'service_discount' => $svc['discount'] ?? 0,
                    'doctor_name' => $trx->doctor_name,
                    'nurse_name' => $trx->nurse_name,
                    'payment_method' => $trx->payment_method,
                    'created_at' => $trx->created_at,
                    'canceled_at' => $trx->canceled_at,
                    'category_name' => $category->name ?? null,
                    'category_code' => $category->code ?? null,
                    'appointment_id' => $trx->appointment_id,
                    'next_schedule' => $trx->next_schedule,
                    'price' => $trx->price,
                    'discount' => $trx->discount,
                    'billing' => $trx->billing,
                ];
            }
        }

        $data = [];

        foreach ($flat as $item) {
            array_push($data, (object) [
                'id' => $item->sequence,
                'voucher' => strtoupper($item->voucher_code ?? '-'),
                'patient_name' => $item->patient_name,
                'patient_code' => $item->patient_code,
                'patient_birthdate' => $item->birthdate,
                'patient_gender' => $item->gender,
                'address' => sprintf(
                    "%s %s\n%s, %s %s\n%s - %s",
                    $item->street,
                    $item->tonarigumi,
                    $item->village,
                    $item->district,
                    $item->zip_code,
                    $item->regency,
                    $item->province,
                ),
                'village' => $item->village,
                'district' => $item->district,
                'regency' => $item->regency,
                'phone_number' => $item->phone,
                'patient_code' => $item->patient_code,
                'doctor' => $item->doctor_name,
                'assistant' => $item->nurse_name,
                'medical_record_code' => $item->patient_code,
                'category_code' => $item->category_code,
                'category_name' => $item->category_name,
                'treatment_code' => $item->service_code,
                'treatment_name' => $item->service_name,
                'treatment_price' => $item->service_price,
                'treatment_quantity' => $item->service_quantity,
                'treatment_subtotal' => $item->service_subtotal,
                'discount' => $item->service_discount,
                'total_price' => $item->service_subtotal - $item->service_discount,
                'day' => Carbon::parse($item->created_at)->locale('id')
                    ->settings(['formatFunction' => 'translatedFormat'])->format('l'),
                'date' => Carbon::parse($item->created_at)->locale('id')
                    ->settings(['formatFunction' => 'translatedFormat'])->format('d F Y'),
                'time' => Carbon::parse($item->created_at)->format('H:i'),
                'payment_method' => $item->payment_method,
                'canceled_at' => $item->canceled_at,
            ]);
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Nomor Nota');
        $sheet->setCellValue('B1', 'Nama Pasien');
        $sheet->setCellValue('C1', 'Usia');
        $sheet->setCellValue('D1', 'Jenis Kelamin');
        $sheet->setCellValue('E1', 'Kelurahan');
        $sheet->setCellValue('F1', 'Kecamatan');
        $sheet->setCellValue('G1', 'Kota/Kabupaten');
        $sheet->setCellValue('H1', 'Alamat');
        $sheet->setCellValue('I1', 'Nomor Telepon');
        $sheet->setCellValue('J1', 'Kode Pasien');
        $sheet->setCellValue('K1', 'Dokter In Charge');
        $sheet->setCellValue('L1', 'Asisten In Charge');
        $sheet->setCellValue('M1', 'Kode Rekam Medis');
        $sheet->setCellValue('N1', 'Kode Klasifikasi Perawatan Awal');
        $sheet->setCellValue('O1', 'Nama Perawatan Awal');
        $sheet->setCellValue('P1', 'Kode Perawatan');
        $sheet->setCellValue('Q1', 'Nama Perawatan');
        $sheet->setCellValue('R1', 'Biaya Layanan');
        $sheet->setCellValue('S1', 'Diskon');
        $sheet->setCellValue('T1', 'Biaya Setelah Diskon');
        $sheet->setCellValue('U1', 'Hari');
        $sheet->setCellValue('V1', 'Tanggal');
        $sheet->setCellValue('W1', 'Jam (terbit nota)');
        $sheet->setCellValue('X1', 'Tipe Pembayaran');
        $sheet->setCellValue('Y1', 'Status');
        $sheet->setCellValue('Z1', 'Kode Voucher');

        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValueExplicit('A'.$row, $item->id, DataType::TYPE_STRING);
            $sheet->setCellValue('B'.$row, $item->patient_name);
            $sheet->setCellValue('C'.$row, $item->patient_birthdate ? Carbon::parse($item->patient_birthdate)->diffInYears(Carbon::now()) : '-');
            $sheet->setCellValue('D'.$row, $item->patient_gender);
            $sheet->setCellValue('E'.$row, $item->village);
            $sheet->setCellValue('F'.$row, $item->district);
            $sheet->setCellValue('G'.$row, $item->regency);
            $sheet->setCellValue('H'.$row, $item->address);
            $sheet->setCellValueExplicit('I'.$row, '+'.$item->phone_number, DataType::TYPE_STRING);
            $sheet->setCellValue('J'.$row, $item->patient_code);
            $sheet->setCellValue('K'.$row, $item->doctor);
            $sheet->setCellValue('L'.$row, $item->assistant);
            $sheet->setCellValue('M'.$row, $item->medical_record_code);
            $sheet->setCellValue('N'.$row, $item->category_code);
            $sheet->setCellValue('O'.$row, $item->category_name);
            $sheet->setCellValue('P'.$row, $item->treatment_code);
            $sheet->setCellValue('Q'.$row, $item->treatment_name);
            $sheet->setCellValue('R'.$row, $item->treatment_subtotal);
            $sheet->setCellValue('S'.$row, $item->discount);
            $sheet->setCellValue('T'.$row, $item->total_price);
            $sheet->setCellValue('U'.$row, $item->day);
            $sheet->setCellValue('V'.$row, $item->date);
            $sheet->setCellValue('W'.$row, $item->time);
            $sheet->setCellValue('X'.$row, $item->payment_method);
            $sheet->setCellValue('Y'.$row, $item->canceled_at ? 'DIBATALKAN' : 'BERHASIL');
            $sheet->setCellValue('Z'.$row, $item->voucher);
            $row++;
        }

        $this->applyStyling($sheet);

        $monthYear = '';

        if ($this->since) {
            $monthYear .= Carbon::parse($this->since)->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('d F Y');
        }

        if (! $this->since) {
            $monthYear .= Carbon::now()->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('F Y');
        }

        if ($this->until) {
            $monthYear .= ' sd '.Carbon::parse($this->until)->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('d F Y');
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Transaction Record - '.$monthYear.'.xlsx';
        $writer->save($filename);

        return $filename;
    }

    private function applyStyling(Worksheet $sheet)
    {
        // Apply any desired styling to the sheet

        $sheet->getStyle('A1:Z1')->getFont()->setBold(true);
        $sheet->getStyle('A1:Z1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:Z1')->getAlignment()->setWrapText(true);
    }
}
