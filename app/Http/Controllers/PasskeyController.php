<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyAuthenticationOptionsAction;
use Spatie\LaravelPasskeys\Actions\FindPasskeyToAuthenticateAction;
use Spatie\LaravelPasskeys\Models\Passkey;

class PasskeyController extends Controller
{
    public function index()
    {
        return Inertia::render('Profile/Edit', [
            'passkeys' => Auth::user()->passkeys()->get()->map(fn($passkey) => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'created_at' => $passkey->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function registerOptions(GeneratePasskeyRegisterOptionsAction $action)
    {
        /** @var User $user */
        $user = Auth::user();

        $options = $action->execute($user);
        
        session()->put('passkey-register-options', $options);

        // Decode the Spatie JSON string to avoid double-encoding in the response.
        return response()->json(json_decode($options, true));
    }

    public function register(Request $request, StorePasskeyAction $action)
    {
        /** @var User $user */
        $user = Auth::user();

        $action->execute(
            authenticatable: $user,
            passkeyJson: json_encode($request->input('publicKeyCredential')),
            passkeyOptionsJson: session()->get('passkey-register-options'),
            hostName: $request->getHost(),
            additionalProperties: ['name' => $request->input('name', 'Мое устройство')]
        );

        return back()->with('status', 'passkey-registered');
    }

    public function loginOptions(Request $request, GeneratePasskeyAuthenticationOptionsAction $action)
    {
        // Decode the Spatie JSON string to avoid double-encoding in the response.
        return response()->json(json_decode($action->execute(), true));
    }

    public function login(Request $request, FindPasskeyToAuthenticateAction $action)
    {
        $passkey = $action->execute(
            publicKeyCredentialJson: json_encode($request->input('publicKeyCredential')),
            passkeyOptionsJson: session()->get('passkey-authentication-options')
        );

        if (!$passkey) {
            return back()->withErrors(['email' => 'Биометрическая аутентификация не удалась.']);
        }

        Auth::login($passkey->authenticatable);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Passkey $passkey)
    {
        if ($passkey->authenticatable_id !== Auth::id()) {
            abort(403);
        }

        $passkey->delete();

        return back();
    }
}
