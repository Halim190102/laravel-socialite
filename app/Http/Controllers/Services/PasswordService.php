<?php

namespace App\Http\Controllers\Services;

use Illuminate\Support\Facades\Hash;

class PasswordService
{

    private function validateCurrentPassword($current_password)
    {
        if (!password_verify($current_password, auth()->user()->password)) {
            return response()->json([

                'status' => 'error',
                'message' => 'Password did not match the current password'
            ]);
        }
    }

    public function changePassword($data)
    {
        try {
            $this->validateCurrentPassword($data['current_password']);
            $updatePassword = auth()->user()->update([
                'password' => Hash::make($data['password'])
            ]);

            if ($updatePassword) {
                return response()->json([

                    'status' => 'success',
                    'message' => 'Password updated successfully'
                ]);
            } else {
                return response()->json([

                    'status' => 'error',
                    'message' => 'An error occurred while updating the password'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([

                'status' => 'error',
                'message' => 'Failed to change password',
            ]);
        }
    }
}
