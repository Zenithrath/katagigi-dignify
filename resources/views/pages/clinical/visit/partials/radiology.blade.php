@php
    $statusBadges = [
        \App\Models\RadiologyOrder::STATUS_ORDERED => 'badge-info',
        \App\Models\RadiologyOrder::STATUS_SCHEDULED => 'badge-info',
        \App\Models\RadiologyOrder::STATUS_IN_PROGRESS => 'badge-warning',
        \App\Models\RadiologyOrder::STATUS_COMPLETED => 'badge-success',
        \App\Models\RadiologyOrder::STATUS_CANCELLED => 'badge-neutral',
    ];
@endphp

<div x-show="tab === 'radiologi'" class="p-8 space-y-6">
    <div>
        <h3 class="font-bold text-lg text-slate-900 mb-1">Radiologi</h3>
        <p class="text-xs text-slate-500">Order pemeriksaan penunjang dan hasilnya. Berkas hasil disimpan privat — hanya bisa dibuka lewat tautan bertanda tangan.</p>
    </div>

    @forelse ($visit->radiologyOrders as $order)
        <section class="rounded-xl border border-slate-200 p-4" x-data="{ open: false }">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold text-slate-900 text-sm">
                        <span class="text-xs px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 mr-1">{{ $order->modality }}</span>
                        {{ \App\Models\RadiologyOrder::MODALITIES[$order->modality] ?? $order->modality }}
                        @if ($order->body_site)
                            <span class="text-slate-500">· {{ $order->body_site }}</span>
                        @endif
                    </p>
                    <p class="text-sm text-slate-600 mt-1">{{ $order->clinical_indication }}</p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ \App\Models\RadiologyOrder::PRIORITIES[$order->priority] ?? $order->priority }}
                        · oleh {{ $order->doctor->user->name ?? '-' }}
                        · {{ $order->created_at?->format('d M Y H:i') }}
                        @if ($order->performed_at)
                            · dilakukan {{ $order->performed_at->format('d M Y H:i') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span class="badge {{ $statusBadges[$order->status] ?? 'badge-neutral' }}">{{ \App\Models\RadiologyOrder::STATUSES[$order->status] ?? $order->status }}</span>
                    @can('write radiology')
                        @unless ($visit->isSigned())
                            <button type="button" x-on:click="open = ! open" class="text-sm text-brand-600 hover:text-brand-700">Hasil</button>
                            <form action="{{ route('visits.radiology.destroy', [$visit->id, $order->id]) }}" method="post"
                                @submit.prevent="if(confirm('Hapus order radiologi ini?')) $el.submit()">
                                @csrf
                                @method('delete')
                                <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Hapus</button>
                            </form>
                        @endunless
                    @endcan
                </div>
            </div>

            @if ($order->result_text || $order->result_path || $order->notes)
                <div class="mt-3 pt-3 border-t border-slate-100 text-sm text-slate-600 space-y-2">
                    @if ($order->result_text)
                        <p class="whitespace-pre-line">{{ $order->result_text }}</p>
                    @endif
                    @if ($order->result_path)
                        <a href="{{ URL::temporarySignedRoute('radiology.result.file', now()->addMinutes(30), ['order' => $order->id]) }}"
                            target="_blank" class="text-brand-600 hover:text-brand-700">Buka berkas hasil</a>
                    @endif
                    @if ($order->notes)
                        <p class="text-slate-500">{{ $order->notes }}</p>
                    @endif
                </div>
            @endif

            @can('write radiology')
                @unless ($visit->isSigned())
                    <form method="post" x-show="open" x-cloak action="{{ route('visits.radiology.result', [$visit->id, $order->id]) }}"
                        enctype="multipart/form-data" class="mt-3 pt-3 border-t border-slate-100">
                        @csrf
                        @method('put')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1">
                            <div class="input-group">
                                <label for="status_{{ $order->id }}">Status <span class="text-red-500">*</span></label>
                                <select name="status" id="status_{{ $order->id }}" class="custom-select">
                                    @foreach (\App\Models\RadiologyOrder::RESULT_STATUSES as $code => $label)
                                        <option value="{{ $code }}" @selected($order->status === $code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')<small class="danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="input-group">
                                <label for="performed_at_{{ $order->id }}">Waktu pemeriksaan</label>
                                <input type="datetime-local" name="performed_at" id="performed_at_{{ $order->id }}" class="custom-input"
                                    value="{{ old('performed_at', $order->performed_at?->format('Y-m-d\TH:i')) }}" />
                                @error('performed_at')<small class="danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="input-group">
                                <label for="result_file_{{ $order->id }}">Berkas hasil (maks 20MB)</label>
                                <input type="file" name="result_file" id="result_file_{{ $order->id }}" class="custom-input" />
                                @error('result_file')<small class="danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="input-group mt-2">
                            <label for="result_text_{{ $order->id }}">Bacaan / kesan</label>
                            <textarea name="result_text" id="result_text_{{ $order->id }}" rows="3" class="custom-input">{{ old('result_text', $order->result_text) }}</textarea>
                            @error('result_text')<small class="danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="btn-submit !w-auto !px-8">Simpan hasil</button>
                        </div>
                    </form>
                @endunless
            @endcan
        </section>
    @empty
        <p class="text-sm text-slate-500">Belum ada order radiologi.</p>
    @endforelse

    @can('write radiology')
        @unless ($visit->isSigned())
            <form method="post" action="{{ route('visits.radiology.store', $visit->id) }}" class="rounded-xl border border-slate-200 p-4">
                @csrf
                <h4 class="text-sm font-bold text-slate-700 mb-3">Order baru</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-1">
                    <div class="input-group">
                        <label for="modality">Modalitas <span class="text-red-500">*</span></label>
                        <select name="modality" id="modality" class="custom-select">
                            @foreach (\App\Models\RadiologyOrder::MODALITIES as $code => $label)
                                <option value="{{ $code }}" @selected(old('modality') === $code)>{{ $code }} — {{ $label }}</option>
                            @endforeach
                        </select>
                        @error('modality')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="body_site">Regio</label>
                        <input type="text" name="body_site" id="body_site" class="custom-input" placeholder="Rahang bawah regio 46" value="{{ old('body_site') }}" />
                        @error('body_site')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="input-group">
                        <label for="priority">Prioritas</label>
                        <select name="priority" id="priority" class="custom-select">
                            @foreach (\App\Models\RadiologyOrder::PRIORITIES as $code => $label)
                                <option value="{{ $code }}" @selected(old('priority', 'routine') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('priority')<small class="danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="input-group mt-2">
                    <label for="clinical_indication">Indikasi klinis <span class="text-red-500">*</span></label>
                    <textarea name="clinical_indication" id="clinical_indication" rows="2" class="custom-input" required>{{ old('clinical_indication') }}</textarea>
                    @error('clinical_indication')<small class="danger">{{ $message }}</small>@enderror
                </div>
                <div class="input-group mt-2">
                    <label for="radiology_notes">Catatan</label>
                    <input type="text" name="notes" id="radiology_notes" class="custom-input" value="{{ old('notes') }}" />
                    @error('notes')<small class="danger">{{ $message }}</small>@enderror
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="submit" class="btn-submit !w-auto !px-8">Buat order</button>
                </div>
            </form>
        @endunless
    @endcan
</div>
