<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RelatorioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;




class AuthController extends RelatorioController
{
   public function login(Request $request)
   {
        $credentials = $request->validate([
            'cpf'=> ['required', 'string'],
            'password'=>['required'],
        ]);
   

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Login Realizado',
                'user' => Auth::user()
        ]);
   }

        throw ValidationException::withMessages([
    'cpf'=> ['CPF ou Senha inválidos.'],
   ]);
   }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message'=>'Você se desconectou.']);
    }

}
