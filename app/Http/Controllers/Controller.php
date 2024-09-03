<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

abstract class Controller
{
    
public function resolveAuthorization(){

    if(Auth::user()->accessToken->expires_at <= now()){

        //access token refresh
        $response = Http::withHeaders([
            'Accept' => 'application/json'
        ])
        ->post('http://api.blog.test/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => Auth::user()->accessToken->refresh_token,
            'client_id' => config('services.apiblog.client_id'),
            'client_secret' => config('services.apiblog.client_secret'),
            'scope' => 'read-post create-post update-post delete-post'
        ]);

        $access_token = $response->json();
        
        Auth::user()->accessToken->update([
            'access_token' => $access_token['access_token'],
            'refresh_token' => $access_token['refresh_token'],
            'expires_at' => now()->addSecond($access_token['expires_in'])
        ]);

    }

}
}
