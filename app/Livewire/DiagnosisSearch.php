<?php

namespace App\Livewire;

use App\Models\DiagnosisCode;
use Livewire\Component;

/**
 * Autocomplete kode diagnosis: ketik bahasa awam ("gigi berlubang")
 * -> pilih kode resmi (ICD-10 / ICD-9 / SNOMED).
 * Kode terpilih wajib min. 1; freetext hanya catatan tambahan di form induk.
 */
class DiagnosisSearch extends Component
{
    public string $query = '';

    public ?string $system = null; // filter: ICD10 | ICD9 | SNOMED | null = semua

    public string $fieldName = 'diagnosis_codes';

    /** @var array<int, array{id:string,system:string,code:string,display_id:string}> */
    public array $selected = [];

    public function updatedQuery(): void
    {
        $this->dispatch('diagnosis-search-updated');
    }

    public function getResultsProperty()
    {
        if (strlen(trim($this->query)) < 2) {
            return collect();
        }

        $picked = collect($this->selected)->pluck('id')->all();

        return DiagnosisCode::query()
            ->active()
            ->system($this->system)
            ->search(trim($this->query))
            ->when($picked !== [], fn ($q) => $q->whereNotIn('id', $picked))
            ->orderByRaw('CASE WHEN LOWER(code) LIKE ? THEN 0 ELSE 1 END', [strtolower(trim($this->query)).'%'])
            ->orderBy('display_id')
            ->limit(8)
            ->get(['id', 'system', 'code', 'display_id']);
    }

    public function select(string $id): void
    {
        $found = DiagnosisCode::active()->find($id);
        if (! $found) {
            return;
        }

        foreach ($this->selected as $row) {
            if ($row['id'] === $found->id) {
                $this->query = '';

                return;
            }
        }

        $this->selected[] = [
            'id' => $found->id,
            'system' => $found->system,
            'code' => $found->code,
            'display_id' => $found->display_id,
        ];
        $this->query = '';
    }

    public function remove(int $index): void
    {
        unset($this->selected[$index]);
        $this->selected = array_values($this->selected);
    }

    public function render()
    {
        return view('livewire.diagnosis-search', [
            'results' => $this->results,
        ]);
    }
}
