<?php

namespace App\Livewire\Barcode;

use App\Models\Medicine;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BarcodeManager extends Component
{
    public string $search = '';

    /** @var array<int, array<string, mixed>> keyed by medicine id */
    public array $queue = [];

    private const MAX_LABELS = 200;

    #[Computed]
    public function searchResults()
    {
        $term = trim($this->search);
        if (strlen($term) < 2) {
            return collect();
        }

        return Medicine::query()
            ->where('status', 'active')
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('generic_name', 'like', "%{$term}%")
                ->orWhere('barcode', $term))
            ->limit(8)->get();
    }

    public function addToQueue(int $medicineId): void
    {
        if (isset($this->queue[$medicineId])) {
            $this->queue[$medicineId]['qty']++;

            return;
        }

        $m = Medicine::find($medicineId);
        if (! $m) {
            return;
        }

        $this->queue[$medicineId] = [
            'name' => $m->name,
            'strength' => trim(($m->strength ?? '').($m->strength_unit ?? '')),
            'price' => (float) $m->sale_price,
            'barcode' => $m->barcode ?: ('MED-'.$m->id),
            'qty' => 1,
        ];
        $this->search = '';
        unset($this->searchResults);
    }

    public function removeFromQueue(int $medicineId): void
    {
        unset($this->queue[$medicineId]);
    }

    public function clearQueue(): void
    {
        $this->queue = [];
    }

    /**
     * Flatten the queue into individual labels (capped for print safety).
     *
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function labels(): array
    {
        $labels = [];
        foreach ($this->queue as $item) {
            for ($i = 0; $i < max(1, (int) $item['qty']); $i++) {
                $labels[] = $item;
                if (count($labels) >= self::MAX_LABELS) {
                    return $labels;
                }
            }
        }

        return $labels;
    }

    public function render()
    {
        return view('livewire.barcode.barcode-manager');
    }
}
