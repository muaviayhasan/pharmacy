<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PrescriptionService
{
    /**
     * Create a prescription record (pending verification).
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $data, array $items = []): Prescription
    {
        $prescription = Prescription::create([
            'prescription_no' => $this->nextNo((int) $data['branch_id']),
            'customer_id' => $data['customer_id'] ?? null,
            'sale_id' => $data['sale_id'] ?? null,
            'branch_id' => $data['branch_id'],
            'doctor_name' => $data['doctor_name'] ?? null,
            'doctor_registration_no' => $data['doctor_registration_no'] ?? null,
            'clinic_name' => $data['clinic_name'] ?? null,
            'prescription_date' => $data['prescription_date'] ?? now()->toDateString(),
            'attachment_path' => $data['attachment_path'] ?? null,
            'verification_status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($items as $item) {
            $prescription->items()->create([
                'medicine_id' => $item['medicine_id'] ?? null,
                'medicine_name' => $item['medicine_name'] ?? null,
                'dosage' => $item['dosage'] ?? null,
                'quantity' => $item['quantity'] ?? 0,
            ]);
        }

        return $prescription;
    }

    public function verify(Prescription $prescription, User $verifier): void
    {
        $prescription->update([
            'verification_status' => 'verified',
            'verified_by' => $verifier->id,
        ]);

        $prescription->items()->update(['verified' => true]);

        $prescription->sale?->update(['prescription_status' => 'verified']);
    }

    public function approve(Prescription $prescription, User $approver): void
    {
        $prescription->update([
            'verification_status' => 'verified',
            'approved_by' => $approver->id,
        ]);

        $prescription->sale?->update(['prescription_status' => 'verified']);
    }

    public function reject(Prescription $prescription, User $approver, ?string $reason): void
    {
        if (! $reason) {
            throw ValidationException::withMessages(['reason' => 'A rejection reason is required.']);
        }

        $prescription->update([
            'verification_status' => 'rejected',
            'approved_by' => $approver->id,
            'rejection_reason' => $reason,
        ]);

        $prescription->sale?->update(['prescription_status' => 'rejected']);
    }

    private function nextNo(int $branchId): string
    {
        $code = Branch::whereKey($branchId)->value('code') ?: 'BR';
        $seq = Prescription::whereDate('created_at', today())->count() + 1;

        return 'RX-'.strtoupper($code).'-'.now()->format('ymd').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
