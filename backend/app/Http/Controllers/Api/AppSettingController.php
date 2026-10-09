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
        $keys = ['whatsapp_number', 'instagram_url', 'facebook_url', 'tiktok_url', 'youtube_url'];
        $settings = AppSetting::whereIn('key', $keys)->pluck('value', 'key');

        return response()->json([
            'whatsapp_number' => $settings['whatsapp_number'] ?? '',
            'instagram_url' => $settings['instagram_url'] ?? '',
            'facebook_url' => $settings['facebook_url'] ?? '',
            'tiktok_url' => $settings['tiktok_url'] ?? '',
            'youtube_url' => $settings['youtube_url'] ?? '',
        ]);
    }

    public function updateWhatsapp(Request $request)
    {
        abort_unless($request->user()?->role === 'owner', 403);

        $validated = $request->validate([
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'tiktok_url' => ['nullable', 'string', 'max:255'],
            'youtube_url' => ['nullable', 'string', 'max:255'],
        ]);

        $keys = ['whatsapp_number', 'instagram_url', 'facebook_url', 'tiktok_url', 'youtube_url'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $validated)) {
                AppSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => trim($validated[$key] ?? '')]
                );
            }
        }

        return $this->whatsapp();
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

    public function heroSlider()
    {
        $setting = AppSetting::where('key', 'hero_slider_slides')->value('value');
        $slides = $setting ? json_decode($setting, true) : [];

        if (empty($slides)) {
            $slides = [
                [
                    'id' => 'default_1',
                    'image' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=1200&q=80',
                    'title' => 'L\'ÉTOILE Signature Experience',
                    'subtitle' => 'White Marble • Dark Wood • Fine Coffee'
                ],
                [
                    'id' => 'default_2',
                    'image' => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=1200&q=80',
                    'title' => 'Artisanal Coffee & Delicacies',
                    'subtitle' => 'Freshly Roasted Beans • Handcrafted Sweets'
                ],
                [
                    'id' => 'default_3',
                    'image' => 'https://images.unsplash.com/photo-1559925393-8be0ec4767c8?auto=format&fit=crop&w=1200&q=80',
                    'title' => 'Cozy & Premium Atmosphere',
                    'subtitle' => 'Perfect Spot for Meetings & Relaxation'
                ]
            ];
        }

        return response()->json($slides);
    }

    public function updateHeroSlider(Request $request)
    {
        abort_unless($request->user()?->role === 'owner', 403);

        $validated = $request->validate([
            'slides' => 'required|array',
            'slides.*.id' => 'required|string',
            'slides.*.image' => 'required|string',
            'slides.*.title' => 'nullable|string|max:255',
            'slides.*.subtitle' => 'nullable|string|max:255',
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'hero_slider_slides'],
            ['value' => json_encode(array_values($validated['slides']))]
        );

        return $this->heroSlider();
    }

    public function uploadHeroSlideImage(Request $request)
    {
        abort_unless($request->user()?->role === 'owner', 403);

        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $file = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // 1. Primary destination: storage/app/public/slider (Nginx location /storage root)
        $storageDir = storage_path('app/public/slider');
        File::ensureDirectoryExists($storageDir);
        $file->move($storageDir, $filename);

        // 2. Secondary destination: public/storage/slider (fallback / symlink)
        $publicDir = public_path('storage/slider');
        File::ensureDirectoryExists($publicDir);
        @copy($storageDir . '/' . $filename, $publicDir . '/' . $filename);

        $path = 'slider/' . $filename;

        return response()->json([
            'path' => $path,
            'url' => asset('storage/' . $path)
        ]);
    }
}
