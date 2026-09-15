<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /** @var list<string> */
    private const PROVIDERS = ['google', 'discord'];

    public function redirect(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        // The Electron app opens this route in the system browser with
        // ?client=desktop. Stash that in the session so the callback below
        // knows to hand back a token instead of starting a web session.
        if ($request->query('client') === 'desktop') {
            $request->session()->put('oauth_client', 'desktop');
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        $socialUser = Socialite::driver($provider)->user();

        $user = User::query()
            ->where('provider_name', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (! $user) {
            $user = User::query()->where('email', $socialUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'provider_name' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            } else {
                // First time we've seen this person: create their account on
                // the spot, matching the "auto-register on first login" flow.
                $user = User::create([
                    'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Pengguna',
                    'email' => $socialUser->getEmail(),
                    'password' => bcrypt(Str::random(40)),
                    'provider_name' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'is_active' => true,
                ]);
            }
        }

        if (! $user->is_active) {
            if (request()->session()->pull('oauth_client') === 'desktop') {
                return redirect()->away($this->desktopCallbackUrl(['error' => 'account_disabled']));
            }

            return redirect()->route('login')->with('status', 'Akun ini sudah dinonaktifkan.');
        }

        if (request()->session()->pull('oauth_client') === 'desktop') {
            $token = $user->createToken('desktop-app-oauth')->plainTextToken;

            return redirect()->away($this->desktopCallbackUrl(['token' => $token]));
        }

        Auth::login($user, remember: true);

        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Build the deep link back into the Electron app, carrying either the
     * issued token or an error code as a query string.
     *
     * @param  array<string, string>  $params
     */
    private function desktopCallbackUrl(array $params): string
    {
        $scheme = config('desktop.scheme');

        return $scheme.'://oauth-callback?'.http_build_query($params);
    }
}
