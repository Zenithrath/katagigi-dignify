<x-app-layout>
    <x-slot:title>WhatsApp</x-slot:title>

    <main class="main-table-container">
        <section class="heading">
            <div>
                <h1>WhatsApp <span class="text-xs font-normal text-slate-400">({{ $driver }})</span></h1>
                <p>Outbox Official API + kirim manual — link wa.me lama tetap ada di detail pasien</p>
            </div>
        </section>

        <x-flash-alerts />

        <form method="post" action="{{ route('whatsapp.send') }}" class="content-card p-6 mb-4">
            @csrf
            <h3 class="font-bold text-slate-900 mb-3">Kirim manual</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <div class="input-group">
                    <label>No. tujuan *</label>
                    <input type="text" name="phone" class="custom-input" placeholder="08…" required />
                    @error('phone')<small class="danger">{{ $message }}</small>@enderror
                </div>
                <div class="input-group md:col-span-2">
                    <label>Pesan *</label>
                    <input type="text" name="body" class="custom-input" maxlength="1000" required />
                    @error('body')<small class="danger">{{ $message }}</small>@enderror
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="submit" class="btn-submit !w-auto !px-8">Kirim</button>
            </div>
        </form>

        <div class="content-card overflow-hidden">
            <form method="get" action="{{ route('whatsapp.index') }}" class="p-6 pb-0 flex flex-wrap gap-3 items-end">
                <div class="input-group !mb-0">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="custom-select">
                        <option value="">Semua</option>
                        @foreach (['QUEUED', 'SENT', 'FAILED'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-submit !w-auto !px-6">Tampil</button>
            </form>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="py-2 pr-4">Waktu</th>
                            <th class="py-2 pr-4">Tujuan</th>
                            <th class="py-2 pr-4">Template</th>
                            <th class="py-2 pr-4">Isi</th>
                            <th class="py-2 pr-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $message)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-2.5 pr-4 text-xs">{{ $message->created_at?->format('d M Y H:i') }}</td>
                                <td class="py-2.5 pr-4 font-medium">{{ $message->phone }}</td>
                                <td class="py-2.5 pr-4 text-xs">{{ $message->template->name ?? '-' }}</td>
                                <td class="py-2.5 pr-4 max-w-md truncate" title="{{ $message->body }}">{{ $message->body }}</td>
                                <td class="py-2.5 pr-4">
                                    <span class="badge {{ $message->status === 'SENT' ? 'badge-success' : ($message->status === 'FAILED' ? 'badge-danger' : 'badge-neutral') }}">{{ $message->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-12">
                                <p class="text-sm text-slate-500">Belum ada pesan. Reminder H-1 jalan via <code>php artisan wa:remind-h1</code>.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($messages->hasPages())
                <div class="p-6 pt-0">{{ $messages->links() }}</div>
            @endif
        </div>

        <div class="content-card p-6 mt-4">
            <h3 class="font-bold text-slate-900 mb-2">Template aktif ({{ $templates->count() }})</h3>
            @foreach ($templates as $template)
                <div class="py-2 border-b border-slate-100 last:border-0 text-sm">
                    <p class="font-semibold">{{ $template->name }} <span class="text-xs font-normal text-slate-400">({{ $template->category }})</span></p>
                    <p class="text-slate-600">{{ $template->body }}</p>
                </div>
            @endforeach
        </div>
    </main>
</x-app-layout>
