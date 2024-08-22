<?php

namespace App\Http\Controllers\Services;

class NameService
{
    public function changeUsername($username)
    {
        $user = auth()->user();

        $user->username = $username;
        $user->save();

        return response()->json([
            'type' => 1,
            'status' => 'success',
            'message' => 'Username updated successfully',
            'data' => $user
        ]);
    }

    public function changeName($name)
    {
        $user = auth()->user();

        $user->name = $name;
        $user->save();

        return response()->json([
            'type' => 1,
            'status' => 'success',
            'message' => 'Name updated successfully',
            'data' => $user
        ]);
    }
}
