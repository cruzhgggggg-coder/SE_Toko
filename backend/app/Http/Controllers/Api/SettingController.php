<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');
        
        // Provide defaults if not set
        $defaults = [
            'shop_name' => 'Toko Sumber Makmur',
            'shop_address' => 'Jl. Kebon Jeruk No. 123',
            'shop_phone' => '08123456789',
            'currency' => 'IDR',
            'low_stock_threshold' => 10,
        ];

        return response()->json(array_merge($defaults, $settings->toArray()));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return response()->json(['message' => 'Settings updated successfully.']);
    }
}
