<?php

namespace App\Http\Controllers\Services;

use Illuminate\Support\Facades\File;


class ImageService
{
    public function postImage($profilepict)
    {
        $path = config('app.url') . '/uploads/profile/';

        $nama_gambar = 'profile-' . time() . rand(1, 9) . "." . $profilepict->getClientOriginalExtension();
        $profilepict->move('uploads/profile', $nama_gambar);
        return $path . $nama_gambar;
    }

    public function updateImage($profilepict)
    {
        $path = config('app.url') . '/uploads/profile/';

        $user = auth()->user();
        try {
            File::delete('uploads/profile/' . $user->profilepict);
            $nama_gambar = 'profile-' . time() . rand(1, 9) . "." . $profilepict->getClientOriginalExtension();
            $profilepict->move('uploads/profile', $nama_gambar);
            $user->profilepict = $path . $nama_gambar;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile image updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to update profile image',
            ]);
        }
    }
}
