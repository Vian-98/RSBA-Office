<?php

namespace App\Http\Controllers;

use App\Models\User;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProfileImageCacheController extends Controller
{

    /**
     * Serve cached profile image with specified dimensions
     */
    public function show(Request $request, $userId, $size = 'small')
    {
        $request->validate([
            'w' => 'nullable|integer|min:10|max:2000',
            'h' => 'nullable|integer|min:10|max:2000',
        ]);

        $width = $request->query('w', $this->getDefaultSize($size)['width']);
        $height = $request->query('h', $this->getDefaultSize($size)['height']);

        $cacheKey = "profile_image:{$userId}_{$width}x{$height}";
        // Cache for 24 hours
        $image = Cache::remember($cacheKey, 86400, function () use ($userId, $width, $height) {
            return $this->processImage($userId, $width, $height);
        });

        if (!$image) {
            return $this->getDefaultImage($width, $height);
        }

        return response($image['content'])
            ->header('Content-Type', $image['mime'])
            ->header('Cache-Control', 'public');
    }

    /**
     * Process and resize image
     */
    protected function processImage($userId, $width, $height)
    {
        $user = User::where('id', $userId)
            ->whereHas('karyawan')
            ->first();

        if (!$user->karyawan->foto) {
            return null;
        }

        $imagePath = $user->karyawan->foto;

        if (!Storage::disk('public')->exists($imagePath)) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($imagePath);

        $img = Image::read($fullPath)
            ->cover($width, $height)
            ->toWebp(80);

        return [
            'content' => $img->toString(),
            'mime' => 'image/webp'
        ];
    }

    /**
     * Get default image dimensions by size name
     */
    protected function getDefaultSize($size)
    {
        return match ($size) {
            'extra_small' => ['width' => 50, 'height' => 50], //Comment avatars, badges, tiny icons
            'small' => ['width' => 80, 'height' => 80], //Navigation bar, dropdown menus, lists
            'medium' => ['width' => 150, 'height' => 150], //User cards, search results, sidebars
            'large' => ['width' => 300, 'height' => 300], //Profile pages, settings, modals
            default => ['width' => 80, 'height' => 80],
        };
    }

    /**
     * Generate default avatar image
     */
    protected function getDefaultImage($width, $height)
    {
        try {

            $img = Image::create($width, $height)
                ->fill('#3b82f6')
                ->toWebp(80);

            return response($img->toString())
                ->header('Content-Type', 'image/webp')
                ->header('Cache-Control', 'public');
            // ->header('Cache-Control', 'public, max-age=86400');
        } catch (Throwable $th) {
            return response('', 404);
        }
    }

    /**
     * Clear cache for specific user
     */
    public function clearCache($userId)
    {
        $sizes = ['extra_small', 'small', 'medium', 'large'];
        foreach ($sizes as $size) {
            $dimensions = $this->getDefaultSize($size);
            $cacheKey = "profile_image:{$userId}_{$dimensions['width']}x{$dimensions['height']}";
            Cache::forget($cacheKey);
        }

        return response()->json(['message' => 'Cache cleared successfully']);
    }
}
