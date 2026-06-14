<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Prescription;
use App\Models\Sale;
use App\Services\PrescriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Prescription::query()->with('customer', 'sale', 'items')->latest();

        if ($status = $request->string('status')->value()) {
            $query->where('verification_status', $status);
        }
        if ($search = $request->string('search')->trim()->value()) {
            $query->where(fn ($q) => $q->where('prescription_no', 'like', "%{$search}%")->orWhere('doctor_name', 'like', "%{$search}%"));
        }

        return view('prescriptions.index', [
            'prescriptions' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('status', 'search'),
            'stats' => [
                'pending' => Prescription::where('verification_status', 'pending')->count(),
                'verified' => Prescription::where('verification_status', 'verified')->count(),
                'rejected' => Prescription::where('verification_status', 'rejected')->count(),
                'flagged_sales' => Sale::where('prescription_status', 'pending')->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $branchId = (int) (session('active_branch_id') ?: Auth::user()->branches()->value('branches.id') ?: 1);

        return view('prescriptions.create', [
            'branches' => Auth::user()->branches,
            'customers' => Customer::where('status', 'active')->orderBy('name')->get(),
            'pendingSales' => Sale::where('prescription_status', 'pending')->latest('sale_date')->limit(50)->get(),
            'activeBranchId' => $branchId,
            'saleId' => $request->integer('sale') ?: null,
        ]);
    }

    public function store(Request $request, PrescriptionService $service): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'sale_id' => ['nullable', 'integer', 'exists:sales,id'],
            'doctor_name' => ['required', 'string', 'max:255'],
            'doctor_registration_no' => ['nullable', 'string', 'max:100'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'prescription_date' => ['nullable', 'date'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $prescription = $service->create($data);

        return redirect()->route('prescriptions.index')->with('status', "Prescription {$prescription->prescription_no} logged for verification.");
    }

    public function verify(Prescription $prescription, PrescriptionService $service): RedirectResponse
    {
        $service->verify($prescription, Auth::user());

        return back()->with('status', "Prescription {$prescription->prescription_no} verified.");
    }

    public function approve(Prescription $prescription, PrescriptionService $service): RedirectResponse
    {
        $service->approve($prescription, Auth::user());

        return back()->with('status', "Prescription {$prescription->prescription_no} approved (controlled medicine).");
    }

    public function reject(Request $request, Prescription $prescription, PrescriptionService $service): RedirectResponse
    {
        try {
            $service->reject($prescription, Auth::user(), $request->string('reason')->value() ?: null);
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }

        return back()->with('status', "Prescription {$prescription->prescription_no} rejected.");
    }
}
