<?php

namespace App\Http\Controllers\Services;

use Illuminate\Support\Facades\File;


class ImageService
{
    public function postImage($profilepict)
    {
        $nama_gambar = 'profile-' . time() . rand(1, 9) . "." . $profilepict->getClientOriginalExtension();
        $profilepict->move('uploads/profile', $nama_gambar);
        return $nama_gambar;
    }

    public function updateImage($profilepict)
    {
        $user = auth()->user();
        try {
            File::delete('uploads/profile/' . $user->profilepict);
            $nama_gambar = 'profile-' . time() . rand(1, 9) . "." . $profilepict->getClientOriginalExtension();
            $profilepict->move('uploads/profile', $nama_gambar);
            $user->profilepict = $nama_gambar;
            $user->save();

            return response()->json([
                'type' => 1,
                'status' => 'success',
                'message' => 'Profile image updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 0,
                'status' => 'error',
                'message' => 'Failed to update profile image',
                'error' => $e->getMessage()
            ]);
        }
    }
}
