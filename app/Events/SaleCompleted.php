<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SaleCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Sale $sale) {}

    /**
     * Broadcast on the branch POS channel so every counter / dashboard
     * on the same branch can react in real time.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('pos.'.$this->sale->branch_id);
    }

    public function broadcastAs(): string
    {
        return 'SaleCompleted';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'sale_id' => $this->sale->id,
            'sale_no' => $this->sale->sale_no,
            'branch_id' => $this->sale->branch_id,
            'shift_id' => $this->sale->shift_id,
            'grand_total' => (float) $this->sale->grand_total,
            'payment_method' => $this->sale->payment_method,
            'items_count' => $this->sale->items()->count(),
            'cashier' => $this->sale->creator?->name,
            'at' => $this->sale->sale_date?->toDateTimeString(),
        ];
    }
}
