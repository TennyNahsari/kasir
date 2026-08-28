<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AppSettingController extends Controller
{
    public function whatsapp()
    {
        return response()->json([
            'whatsapp_number' => AppSetting::where('key', 'whatsapp_number')->value('value'),
        ]);
    }

    public function updateWhatsapp(Request $request)
    {
        abort_unless($request->user()?->role === 'owner', 403);

        $validated = $request->validate([
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
        ]);

        $setting = AppSetting::updateOrCreate(
            ['key' => 'whatsapp_number'],
            ['value' => trim($validated['whatsapp_number'] ?? '')]
        );

        return response()->json(['whatsapp_number' => $setting->value]);
    }

    public function payment()
    {
        $keys = ['bank_name', 'bank_account_number', 'bank_account_name', 'qris_image'];
        $settings = AppSetting::whereIn('key', $keys)->pluck('value', 'key');

        return response()->json([
            'bank_name' => $settings['bank_name'] ?? '',
            'bank_account_number' => $settings['bank_account_number'] ?? '',
            'bank_account_name' => $settings['bank_account_name'] ?? '',
            'qris_image' => $settings['qris_image'] ?? '',
        ]);
    }

    public function updatePayment(Request $request)
    {
        abort_unless($request->user()?->role === 'owner', 403);

        $validated = $request->validate([
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:100'],
            'qris_image' => ['nullable', 'image', 'max:2048'],
        ]);

        foreach (['bank_name', 'bank_account_number', 'bank_account_name'] as $key) {
            if (array_key_exists($key, $validated)) {
                AppSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => trim($validated[$key] ?? '')]
                );
            }
        }

        // Handle QRIS image upload
        if ($request->hasFile('qris_image')) {
            // Product images in this installation are served from public/storage.
            // Store QRIS there as well because public/storage is not a symlink
            // to storage/app/public on the current Windows setup.
            $directory = public_path('storage/settings');
            File::ensureDirectoryExists($directory);
            $file = $request->file('qris_image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);
            $path = 'settings/' . $filename;

            $oldPath = AppSetting::where('key', 'qris_image')->value('value');
            if ($oldPath) {
                File::delete(public_path('storage/' . ltrim($oldPath, '/')));
                \Storage::disk('public')->delete($oldPath);
            }

            AppSetting::updateOrCreate(
                ['key' => 'qris_image'],
                ['value' => $path]
            );
        }

        return $this->payment();
    }

    public function deleteQrisImage(Request $request)
    {
        abort_unless($request->user()?->role === 'owner', 403);

        $setting = AppSetting::where('key', 'qris_image')->first();
        if ($setting && $setting->value) {
            File::delete(public_path('storage/' . ltrim($setting->value, '/')));
            \Storage::disk('public')->delete($setting->value);
            $setting->update(['value' => '']);
        }

        return response()->json(['message' => 'QRIS image deleted']);
    }
}
