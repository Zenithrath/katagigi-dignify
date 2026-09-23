@php
    $consents = $visit->consents->sortByDesc('created_at');
@endphp

<div x-show="tab === 'consent'" class="p-8 space-y-6">
    <div>
        <h3 class="font-bold text-lg text-slate-900 mb-1">Informed Consent</h3>
        <p class="text-xs text-slate-500">Persetujuan tindakan wajib terdokumentasi (Permenkes 24/2022). Gambar tanda tangan disimpan privat dan hanya bisa dibuka lewat tautan bertanda tangan.</p>
    </div>

    @forelse ($consents as $consent)
        <section class="rounded-xl border border-slate-200 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-slate-900 text-sm">
                        {{ \App\Models\MedicalConsentRecord::TYPES[$consent->consent_type] ?? $consent->consent_type }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        oleh {{ $consent->granted_by_name }}
                        ({{ \App\Models\MedicalConsentRecord::RELATIONS[$consent->granted_by_relation] ?? $consent->granted_by_relation ?? '—' }})
                        · {{ $consent->granted_at?->format('d M Y H:i') }}
                        · dokter {{ $consent->doctor->user->name ?? '-' }}
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="badge {{ $consent->granted ? 'badge-success' : 'badge-danger' }}">{{ $consent->granted ? 'Disetujui' : 'Ditolak' }}</span>
                    @if ($consent->signature_path)
                        <a href="{{ route('consents.print', $consent->id) }}" target="_blank"
                            class="text-sm text-brand-600 hover:text-brand-700">Cetak</a>
                    @endif
                    @can('revoke consent')
                        <form action="{{ route('visits.consents.destroy', [$visit->id, $consent->id]) }}" method="post"
                            @submit.prevent="if(confirm('Hapus catatan consent ini?')) $el.submit()">
                            @csrf
                            @method('delete')
                            <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                        </form>
                    @endcan
                </div>
            </div>
            <p class="text-sm text-slate-600 mt-3">{{ $consent->consent_text }}</p>
            @if ($consent->notes)
                <p class="text-xs text-slate-500 mt-2">{{ $consent->notes }}</p>
            @endif
            @if ($consent->signature_path)
                <a href="{{ URL::temporarySignedRoute('consents.signature', now()->addMinutes(30), ['consent' => $consent->id]) }}"
                    target="_blank" class="inline-block mt-2 text-sm text-brand-600 hover:text-brand-700">Lihat tanda tangan</a>
            @endif
        </section>
    @empty
        <p class="text-sm text-slate-500">Belum ada consent.</p>
    @endforelse

    @can('record consent')
        @unless ($visit->isSigned())
            <form method="post" action="{{ route('visits.consents.store', $visit->id) }}" enctype="multipart/form-data"
                class="rounded-xl border border-slate-200 p-4">
                @csrf
                <h4 class="text-sm font-bold text-slate-700 mb-3">Catat consent</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-1">
                    <div class="input-group">
                        <label for="consent_type">Jenis</label>
                        <select name="consent_type" id="consent_type" class="custom-select">
                            @foreach (\App\Models\MedicalConsentRecord::TYPES as $code => $label)
                                <option value="{{ $code }}" @selected(old('consent_type', 'treatment') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('consent_type')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="granted_by_name">Nama pemberi <span class="text-red-500">*</span></label>
                        <input type="text" name="granted_by_name" id="granted_by_name" class="custom-input" required
                            value="{{ old('granted_by_name', $visit->patient->name ?? '') }}" />
                        @error('granted_by_name')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="granted_by_relation">Hubungan</label>
                        <select name="granted_by_relation" id="granted_by_relation" class="custom-select">
                            @foreach (\App\Models\MedicalConsentRecord::RELATIONS as $code => $label)
                                <option value="{{ $code }}" @selected(old('granted_by_relation', 'self') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('granted_by_relation')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="granted">Keputusan <span class="text-red-500">*</span></label>
                        <select name="granted" id="granted" class="custom-select">
                            <option value="1" @selected(old('granted', '1') === '1')>Setuju</option>
                            <option value="0" @selected(old('granted') === '0')>Menolak</option>
                        </select>
                        @error('granted')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="input-group mt-2">
                    <label for="consent_text">Pernyataan <span class="text-red-500">*</span></label>
                    <textarea name="consent_text" id="consent_text" rows="4" class="custom-input" required>{{ old('consent_text', \App\Models\MedicalConsentRecord::DEFAULT_TEXT) }}</textarea>
                    @error('consent_text')<small class="danger">{{ $message }}</small>@enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 mt-2">
                    <div class="input-group"
                        x-data="{
                            drawing: false,
                            clear() {
                                const c = $refs.canvas;
                                c.getContext('2d').clearRect(0, 0, c.width, c.height);
                                $refs.data.value = '';
                            },
                            pos(e) {
                                const r = $refs.canvas.getBoundingClientRect();
                                return [($event.clientX - r.left) * $refs.canvas.width / r.width, ($event.clientY - r.top) * $refs.canvas.height / r.height];
                            },
                            start(e) {
                                this.drawing = true;
                                const ctx = $refs.canvas.getContext('2d');
                                ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#0f172a';
                                const [x, y] = this.pos(e);
                                ctx.beginPath(); ctx.moveTo(x, y);
                            },
                            move(e) {
                                if (! this.drawing) return;
                                const [x, y] = this.pos(e);
                                const ctx = $refs.canvas.getContext('2d');
                                ctx.lineTo(x, y); ctx.stroke();
                            },
                            stop() {
                                if (! this.drawing) return;
                                this.drawing = false;
                                $refs.data.value = $refs.canvas.toDataURL('image/png');
                            },
                        }">
                        <label for="signature_canvas">Tanda tangan (gambar di kanvas — mendukung layar sentuh)</label>
                        <canvas x-ref="canvas" width="560" height="160"
                            class="w-full touch-none rounded-lg border border-slate-200 bg-white cursor-crosshair"
                            x-on:pointerdown.prevent="start($event)"
                            x-on:pointermove.prevent="move($event)"
                            x-on:pointerup="stop()"
                            x-on:pointerleave="stop()"></canvas>
                        <input type="hidden" name="signature_data" x-ref="data" value="{{ old('signature_data') }}">
                        <button type="button" class="mt-1 text-xs text-slate-400 hover:text-red-600"
                            x-on:click="clear()">Bersihkan kanvas</button>
                    </div>
                    <div class="input-group">
                        <label for="signature">Atau unggah gambar tanda tangan (jpg/png/webp, maks 4MB)</label>
                        <input type="file" name="signature" id="signature" class="custom-input" accept="image/*" />
                        @error('signature')<small class="danger">{{ $message }}</small>@enderror
                        <div class="input-group mt-2">
                            <label for="consent_notes">Catatan</label>
                            <input type="text" name="notes" id="consent_notes" class="custom-input" value="{{ old('notes') }}" />
                            @error('notes')<small class="danger">{{ $message }}</small>@enderror
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="btn-submit !w-auto !px-8">Simpan consent</button>
                </div>
            </form>
        @endunless
    @endcan
</div>
