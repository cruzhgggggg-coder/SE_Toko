<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');

        $defaults = [
            'shop_name' => 'Toko Sumber Makmur',
            'shop_address' => 'Jl. Kebon Jeruk No. 123',
            'shop_phone' => '08123456789',
            'currency' => 'IDR',
            'low_stock_threshold' => 10,
        ];

        // Never return sensitive settings like password data
        $safeSettings = $settings->except([
            'password_current', 'password_new', 'password_hash'
        ]);

        return response()->json(array_merge($defaults, $safeSettings->toArray()));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
        ]);

        // Handle password change separately with proper validation
        if (isset($validated['settings']['password_current']) &&
            isset($validated['settings']['password_new'])) {

            $request->validate([
                'settings.password_current' => 'required|string',
                'settings.password_new' => ['required', 'string', Password::min(8)
                    ->mixedCase()
                    ->numbers()],
            ]);

            $user = $request->user();

            if (!Hash::check($validated['settings']['password_current'], $user->password)) {
                return response()->json([
                    'message' => 'Password saat ini salah.'
                ], 422);
            }

            $user->update([
                'password' => Hash::make($validated['settings']['password_new'])
            ]);

            // Remove password fields from settings storage
            unset($validated['settings']['password_current']);
            unset($validated['settings']['password_new']);
        }

        // Store only allowed non-sensitive settings
        $allowedKeys = [
            'shop_name', 'shop_address', 'shop_phone', 'currency',
            'low_stock_threshold', 'security_pin_required',
            'security_auto_lock', 'security_activity_log',
        ];

        foreach ($validated['settings'] as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        return response()->json(['message' => 'Pengaturan berhasil diperbarui.']);
    }
}
