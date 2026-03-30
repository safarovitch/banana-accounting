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

        return response()->json($action->execute($user, asJson: false));
    }

    public function register(Request $request, StorePasskeyAction $action)
    {
        /** @var User $user */
        $user = Auth::user();

        $action->execute(
            $user,
            $request->input('name', 'My Device'),
            $request->input('publicKeyCredential')
        );

        return back()->with('status', 'passkey-registered');
    }

    public function loginOptions(Request $request, GeneratePasskeyAuthenticationOptionsAction $action)
    {
        // For auto-login, we don't necessarily need a user upfront if using resident keys (discoverable credentials)
        // But Spatie's action might need it or can be null.
        return response()->json($action->execute());
    }

    public function login(Request $request, FindPasskeyToAuthenticateAction $action)
    {
        $passkey = $action->execute($request->input('publicKeyCredential'));

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
