<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    /**
     * Setting definition: group => [ key => [label, type] ].
     */
    private function schema(): array
    {
        return [
            'Company Profile' => [
                'app_name' => ['Pharmacy Name', 'string'],
                'company_registration_no' => ['Registration #', 'string'],
                'company_license_no' => ['License #', 'string'],
                'company_phone' => ['Phone Number', 'string'],
                'company_email' => ['Email Address', 'string'],
                'company_address' => ['Address', 'string'],
            ],
            'Localization' => [
                'currency' => ['Currency Code', 'string'],
                'currency_symbol' => ['Currency Symbol', 'string'],
                'timezone' => ['Timezone', 'string'],
            ],
            'Tax & Invoice' => [
                'default_tax_percent' => ['Default Tax (%)', 'integer'],
                'invoice_prefix' => ['Invoice Prefix', 'string'],
                'invoice_footer' => ['Invoice Footer Note', 'string'],
            ],
            'POS & Inventory' => [
                'enable_fefo' => ['Enforce FEFO batch selection', 'boolean'],
                'block_expired_sale' => ['Block expired stock from sale', 'boolean'],
                'near_expiry_days' => ['Near-expiry window (days)', 'integer'],
            ],
            'Security' => [
                'session_timeout_minutes' => ['Session timeout (minutes)', 'integer'],
                'enable_two_factor' => ['Encourage two-factor auth', 'boolean'],
            ],
        ];
    }

    public function index()
    {
        $stored = Setting::whereNull('branch_id')->pluck('value', 'key');

        return view('settings.index', [
            'schema' => $this->schema(),
            'values' => $stored,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        foreach ($this->schema() as $group => $fields) {
            foreach ($fields as $key => [$label, $type]) {
                $value = $type === 'boolean'
                    ? ($request->boolean($key) ? '1' : '0')
                    : $request->input($key);

                Setting::updateOrCreate(
                    ['branch_id' => null, 'key' => $key],
                    ['group' => Str::of($group)->lower()->snake()->value(), 'value' => $value, 'type' => $type]
                );
            }
        }

        return back()->with('status', 'Settings saved.');
    }
}
