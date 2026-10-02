<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }} — {{ $visit->visit_number }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Georgia, 'Times New Roman', serif; color: #1a1a1a; padding: 32px 48px; font-size: 13px; line-height: 1.7; }
    .letterhead { display: flex; align-items: center; gap: 16px; border-bottom: 3px double #1a1a1a; padding-bottom: 12px; }
    .letterhead .mark { width: 64px; height: 64px; border: 2px solid #1a1a1a; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; }
    .letterhead h1 { font-size: 17px; text-transform: uppercase; letter-spacing: .5px; }
    .letterhead p { font-size: 12px; color: #444; }
    .doc-title { text-align: center; margin: 24px 0 4px; font-size: 16px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
    .doc-number { text-align: center; font-size: 12px; margin-bottom: 20px; }
    table.meta { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
    table.meta td { padding: 1px 0; vertical-align: top; }
    table.meta td.k { width: 170px; font-weight: bold; }
    .body-text { text-align: justify; margin-bottom: 14px; }
    .body-text p { margin-bottom: 8px; }
    .diagnosis-box { border: 1px solid #1a1a1a; padding: 8px 14px; margin: 10px 0 16px; }
    .sign-block { display: flex; justify-content: flex-end; margin-top: 28px; }
    .sign { text-align: center; width: 260px; }
    .sign .gap { height: 64px; }
    .sign .name { font-weight: bold; border-top: 1px solid #1a1a1a; padding-top: 4px; }
    footer { margin-top: 36px; border-top: 1px solid #ccc; padding-top: 6px; font-size: 11px; color: #555; display: flex; justify-content: space-between; }
    @media print { body { padding: 0; } .no-print { display: none; } }
    .no-print { margin-bottom: 16px; }
    .no-print a { display: inline-block; border: 1px solid #1a1a1a; padding: 6px 16px; text-decoration: none; color: #1a1a1a; font-family: sans-serif; font-size: 12px; margin-right: 8px; }
</style>
</head>
<body onload="window.print()">
<div class="letterhead">
    <div class="mark">KG</div>
    <div>
        <h1>{{ $visit->branch->org ?? config('app.name') }}</h1>
        <p>{{ $visit->branch->name ?? '' }}@if($visit->branch->address) — {{ $visit->branch->address }} @endif</p>
        @if($visit->branch->phone)<p>Telp. {{ $visit->branch->phone }}</p>@endif
    </div>
</div>

<h2 class="doc-title">{{ $title }}</h2>
<p class="doc-number">Nomor: {{ strtoupper(substr($type, 0, 3)) }}/{{ $visit->visit_number }}/{{ $visit->visit_date->format('Y/m') }}</p>

<table class="meta">
    <tr><td class="k">Nama</td><td>: <strong>{{ $visit->patient->name }}</strong></td></tr>
    <tr><td class="k">No. RM</td><td>: {{ $visit->patient->code ?? '-' }}</td></tr>
    @if ($visit->patient->nik)
        <tr><td class="k">NIK</td><td>: {{ $visit->patient->nik }}</td></tr>
    @endif
    <tr><td class="k">Jenis kelamin / Tgl. lahir</td><td>: {{ $visit->patient->gender === 'FEMALE' ? 'Perempuan' : 'Laki-laki' }} / {{ $visit->patient->birthdate?->format('d/m/Y') }}</td></tr>
    @if ($visit->patient->address)
        <tr><td class="k">Alamat</td><td>: {{ trim(($visit->patient->address->street ?? '').', '.($visit->patient->address->district ?? '').', '.($visit->patient->address->regency ?? '')) }}</td></tr>
    @endif
    <tr><td class="k">Tanggal pemeriksaan</td><td>: {{ $visit->visit_date->format('d/m/Y') }}</td></tr>
</table>

<div class="body-text">
    @if ($type === 'sick')
        <p>Yang bertanda tangan di bawah ini dokter pemeriksa, menerangkan bahwa pasien tersebut di atas
        sedang dalam perawatan dan <strong>memerlukan istirahat selama {{ $restDays }} hari</strong>,
        terhitung mulai tanggal {{ $restFrom?->format('d/m/Y') }} sampai {{ $restUntil?->format('d/m/Y') }}.</p>
        @if ($restNote)
            <p>Catatan: {{ $restNote }}</p>
        @endif
        <p>Surat keterangan ini diberikan untuk keperluan berkas administrasi ketenagakerjaan/sekolah pasien.</p>
    @elseif ($type === 'medical')
        <p>Yang bertanda tangan di bawah ini dokter pemeriksa, menerangkan bahwa pasien tersebut di atas
        <strong>benar sedang menjalani pemeriksaan/perawatan</strong> di fasilitas kesehatan kami pada
        tanggal {{ $visit->visit_date->format('d/m/Y') }}.</p>
        @if ($restNote)
            <p>Catatan: {{ $restNote }}</p>
        @endif
        <p>Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
    @else
        <p>Yang bertanda tangan di bawah ini dokter pemeriksa, dengan ini merujuk pasien tersebut di atas ke:
        <strong>{{ $referralTo ?: 'Fasilitas Kesehatan Rujukan' }}</strong> untuk tindakan lanjutan.</p>
        @if ($referralNotes)
            <p><strong>Ringkasan klinis / alasan rujukan:</strong><br>{{ $referralNotes }}</p>
        @endif
        <p>Terima kasih atas perhatian dan penanganan yang diberikan.</p>
    @endif
</div>

@php
    $icd10 = $visit->diagnoses->where('system', 'ICD10')->values();
@endphp
@if ($type !== 'referral' && $icd10->isNotEmpty())
    <div class="diagnosis-box">
        <strong>Diagnosis:</strong>
        {{ $icd10->map(fn ($d) => $d->code.($d->display ? ' — '.$d->display : ''))->implode('; ') }}
    </div>
@endif

<div class="sign-block">
    <div class="sign">
        <p>{{ $visit->branch->name ?? '' }}, {{ now()->format('d F Y') }}</p>
        <p style="font-size: 12px;">Dokter pemeriksa,</p>
        <div class="gap"></div>
        <p class="name">{{ $visit->doctor->user->name ?? '-' }}</p>
        @if (! empty($visit->doctor->ihs_id))
            <p style="font-size: 11px;">IHS SATUSEHAT</p>
        @endif
    </div>
</div>

<footer>
    <span>Dicetak {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</span>
    <span>Visit {{ $visit->visit_number }}</span>
</footer>
</body>
</html>
