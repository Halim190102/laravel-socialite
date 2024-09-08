<?php

namespace App\Http\Controllers\Services;

class NameService
{
    public function changeUsername($username)
    {
        try {
            $user = auth()->user();

            $user->username = $username;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Username updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to update username',
            ]);
        }
    }

    public function changeName($name)
    {
        try {
            $user = auth()->user();

            $user->name = $name;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Name updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to update name',
            ]);
        }
    }
}
