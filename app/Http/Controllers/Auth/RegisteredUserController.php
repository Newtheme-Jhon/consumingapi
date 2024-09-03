<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Traits\Token;

class RegisteredUserController extends Controller
{

    use Token;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        //peticion api register
        $response = Http::withHeaders([
            'Accept' => 'application/json'
        ])->post('http://api.blog.test/v1/register', $request->all());

        if($response->status() == 422){
            return back()->withErrors($response->json()['errors']);
        }

        //insert user
        $service = $response->json();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

       //create access token
       $this->setAccessToken($user, $service);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
