<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    
    public function store(){

        $this->resolveAuthorization();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '. Auth::user()->accessToken->access_token
        ])
        ->post('http://api.blog.test/v1/posts', [
            'name' => 'prueba 3',
            'slug' => 'prueba-3',
            'extract' => 'Hola Holaaa',
            'content' => 'Esto es el contenido',
            'category_id' => 1
        ]);

        return $response->json();

    }
}
