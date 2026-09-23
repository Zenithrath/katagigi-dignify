<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Informed Consent — {{ $consent->visit->visit_number }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Georgia, 'Times New Roman', serif; color: #1a1a1a; padding: 32px 40px; font-size: 13px; line-height: 1.6; }
    header { text-align: center; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; margin-bottom: 20px; }
    header h1 { font-size: 18px; letter-spacing: .5px; text-transform: uppercase; }
    header p { font-size: 12px; color: #444; margin-top: 2px; }
    .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 24px; margin-bottom: 16px; }
    .meta div { display: flex; gap: 6px; }
    .meta .k { min-width: 150px; font-weight: bold; }
    .statement { border: 1px solid #ccc; padding: 14px 16px; text-align: justify; margin-bottom: 16px; }
    .statement p { white-space: pre-line; }
    .notes { font-size: 12px; color: #444; margin-bottom: 16px; }
    .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-top: 24px; }
    .sig { text-align: center; }
    .sig .box { height: 110px; display: flex; align-items: flex-end; justify-content: center; }
    .sig .box img { max-height: 100px; max-width: 240px; }
    .sig .line { border-top: 1px solid #1a1a1a; padding-top: 4px; font-size: 12px; }
    .sig .name { font-weight: bold; }
    footer { margin-top: 32px; border-top: 1px solid #ccc; padding-top: 8px; font-size: 11px; color: #555; display: flex; justify-content: space-between; }
    @media print { body { padding: 0; } .no-print { display: none; } }
    .no-print { margin-bottom: 16px; }
    .no-print a { display: inline-block; border: 1px solid #1a1a1a; padding: 6px 16px; text-decoration: none; color: #1a1a1a; font-family: sans-serif; font-size: 12px; }
</style>
</head>
<body onload="window.print()">
<header>
    <h1>Lembar Persetujuan Umum (Informed Consent)</h1>
    <p>{{ config('app.name') }} — {{ \App\Models\OrganizationProfile::query()->value('name') ?? '' }}</p>
    <p>Nomor: IC/{{ $consent->consent_type }}/{{ $consent->created_at->format('Y/m') }}/{{ substr($consent->id, 0, 8) }}</p>
</header>

<div class="meta">
    <div><span class="k">Nama pasien</span>: <strong>{{ $consent->patient->name }}</strong></div>
    <div><span class="k">No. RM</span>: {{ $consent->patient->code ?? '-' }}</div>
    <div><span class="k">Nomor visit</span>: {{ $consent->visit->visit_number }}</div>
    <div><span class="k">Tanggal kunjungan</span>: {{ $consent->visit->visit_date->format('d/m/Y') }}</div>
    <div><span class="k">Jenis persetujuan</span>: {{ \App\Models\MedicalConsentRecord::TYPES[$consent->consent_type] ?? $consent->consent_type }}</div>
    <div><span class="k">Keputusan</span>: {{ $consent->granted ? 'SETUJU' : 'MENOLAK' }}</div>
    <div><span class="k">Diberikan oleh</span>: {{ $consent->granted_by_name }} ({{ \App\Models\MedicalConsentRecord::RELATIONS[$consent->granted_by_relation] ?? $consent->granted_by_relation ?? '-' }})</div>
    <div><span class="k">Waktu persetujuan</span>: {{ $consent->granted_at?->format('d/m/Y H:i') }}</div>
    <div><span class="k">Dokter penanggung jawab</span>: {{ $consent->doctor->user->name ?? '-' }}</div>
</div>

<div class="statement">
    <p>{{ $consent->consent_text }}</p>
</div>

@if ($consent->notes)
    <p class="notes"><strong>Catatan:</strong> {{ $consent->notes }}</p>
@endif

<div class="signatures">
    <div class="sig">
        <div class="box">
            @if ($consent->signature_path)
                <img src="{{ $signatureSrc }}" alt="Tanda tangan">
            @endif
        </div>
        <div class="line">Tanda tangan pemberi persetujuan</div>
        <div class="name">{{ $consent->granted_by_name }}</div>
    </div>
    <div class="sig">
        <div class="box"></div>
        <div class="line">Dokter penanggung jawab</div>
        <div class="name">{{ $consent->doctor->user->name ?? '-' }}</div>
    </div>
</div>

<footer>
    <span>Dicetak {{ now()->format('d/m/Y H:i') }} oleh {{ auth()->user()->name }}</span>
    <span>ID: {{ $consent->id }}</span>
</footer>
</body>
</html>
