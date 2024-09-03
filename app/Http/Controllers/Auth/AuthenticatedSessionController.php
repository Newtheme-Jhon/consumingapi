<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use App\Traits\Token;

class AuthenticatedSessionController extends Controller
{

    use Token;

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        // $request->authenticate();
        // $request->session()->regenerate();
        // return redirect()->intended(route('dashboard', absolute: false));

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $response = Http::withHeaders([
            'Accept' => 'application/json'
        ])->post('http://api.blog.test/v1/login', [
            'email' => $request->email,
            'password' => $request->password
        ]);

        //condicional error 404
        if($response->status() == 404){
            return redirect()->back()->with('error-api', 'Las credenciales de usuario no existen');
        }

        $service = $response->json();
        $user = User::updateOrCreate(['email' => $request->email], $service['data']);
        
        if(!$user->accessToken){

            //trait
            $this->setAccessToken($user, $service);

        }

        Auth::login($user, $request->remember);
        return redirect()->route('home');

        
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
