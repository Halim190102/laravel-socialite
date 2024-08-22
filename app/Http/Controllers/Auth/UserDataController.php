<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\EmailVerificationService;
use App\Http\Controllers\Services\ImageService;
use App\Http\Controllers\Services\NameService;
use App\Http\Controllers\Services\PasswordService;
use App\Http\Controllers\Services\ResetPasswordService;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ImageUpdateRequest;
use App\Http\Requests\NameRequest;
use App\Http\Requests\ResendEmailVerificationLinkRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SendCodeLinkRequest;
use App\Http\Requests\UsernameRequest;
use App\Http\Requests\VerifyResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserDataController extends Controller
{
    public function __construct(
        protected EmailVerificationService $emailService,
        protected ImageService $imageService,
        protected PasswordService $passwordService,
        protected NameService $nameService,
        protected ResetPasswordService $resetPasswordService,
    ) {}

    public function verifiedSuccess($email, $token)
    {
        $verificationStatus = $this->emailService->verifyEmail($email, $token);

        switch ($verificationStatus) {
            case 'success':
                return view('verify')->with(['status' => 'success']);
            case 'invalid':
                return view('verify')->with(['status' => 'invalid']);

            case 'already_verified':
                return view('verify')->with([
                    'status' => 'already_verified',
                ]);
            case 'expired':
                return view('verify')->with([
                    'status' => 'token_expired',
                    'email' => $email,
                ]);
            default:
                return view('verify')->with('status', 'error');
        }
    }

    public function resendEmailVerificationLink(ResendEmailVerificationLinkRequest $request)
    {
        return $this->emailService->resendLink($request['email']);
    }

    public function checkVerify(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && $user->email_verified_at) {
            return response()->json([
                'type' => 1,
                'status' => 'success',
                'message' => 'Email has already been verified'
            ]);
        } else {
            return response()->json([
                'type' => 2,
                'status' => 'failed',
                'message' => 'Email has not been verified'
            ]);
        }
    }

    public function changeUserImage(ImageUpdateRequest $request)
    {
        return $this->imageService->updateImage($request['profilepict']);
    }

    public function changeUserPassword(ChangePasswordRequest $request)
    {
        return $this->passwordService->changePassword($request);
    }

    public function changeUsername(UsernameRequest $request)
    {
        return $this->nameService->changeUsername($request['username']);
    }

    public function changeName(NameRequest $request)
    {
        return $this->nameService->changeName($request['name']);
    }

    public function sendCodeLink(SendCodeLinkRequest $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $this->resetPasswordService->sendVerificationlink($user);

        return response()->json([
            'type' => 1,
            'status' => 'success',
            'message' => 'Verification link sent'
        ]);
    }

    public function checkCodeVerify(VerifyResetPasswordRequest $request)
    {
        return $this->resetPasswordService->verifyCode($request);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        return $this->resetPasswordService->resetPass($request);
    }
}
