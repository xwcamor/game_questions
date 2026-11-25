<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController
{
    public function loginForm(): View { return view('auth.login'); }
    public function registerForm(): View { return view('auth.register'); }
    public function forgotForm(): View { return view('auth.forgot-password'); }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email'=>'Las credenciales no son correctas.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'=>['required','string','max:120'],
            'email'=>['required','email','max:190','unique:users,email'],
            'password'=>['required','confirmed',PasswordRule::min(10)],
        ]);
        $user = User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'role'=>'host']);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard')->with('success','Tu cuenta está lista.');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email'=>['required','email']]);
        $status = Password::sendResetLink($request->only('email'));
        return back()->with('status', __($status));
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token'=>$token,'email'=>$request->query('email')]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token'=>['required'],'email'=>['required','email'],
            'password'=>['required','confirmed',PasswordRule::min(10)],
        ]);
        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password'=>Hash::make($password),'remember_token'=>Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email'=>[__($status)]]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
