<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request, $id, $hash)
    {

        $user = User::findOrFail($id);
        if (! URL::hasValidSignature($request)) {
            Log::error(' Invalid signature for email verification', ['request' => $request->all()]);
            return response()->json(['message' => 'Invalid verification link.'], 403);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            Log::error(' Invalid hash for email verification', ['request' => $request->all()]);
            return response()->json(['message' => 'Invalid verification hash.'], 403);
        }
        $user->markEmailAsVerified();
        event(new Verified($user));
        $user->status= 'active';
        $user->save();

        return response()->json(['message' => 'Email verified successfully!']);
    }
}
