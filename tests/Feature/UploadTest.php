<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_receipt_is_stored(): void
    {
        Storage::fake('public');
        $this->seed(RolePermissionSeeder::class);

        $branch = Branch::create(['name' => 'Main', 'code' => 'M1', 'status' => 'active']);
        $user = User::create(['name' => 'Admin', 'email' => 'a@test.com', 'password' => 'x', 'status' => 'active']);
        $user->assignRole('super_admin');
        $user->branches()->attach($branch->id, ['access_level' => 'full', 'status' => 'active']);

        $response = $this->actingAs($user)
            ->withSession(['active_branch_id' => $branch->id, 'otp.verified' => true])
            ->post(route('expenses.store'), [
                'branch_id' => $branch->id,
                'expense_date' => now()->toDateString(),
                'title' => 'Electricity',
                'amount' => 1500,
                'payment_method' => 'cash',
                'receipt' => UploadedFile::fake()->image('receipt.png'),
            ]);

        $response->assertRedirect(route('expenses.index'));

        $expense = Expense::where('title', 'Electricity')->firstOrFail();
        $this->assertNotNull($expense->attachment_path);
        Storage::disk('public')->assertExists($expense->attachment_path);
    }
}
