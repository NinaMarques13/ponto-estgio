<?php

namespace App\Domains\Admins\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Admins\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegistroAdminController extends Controller
{
    /**
     * Exibe o formulário de cadastro de admin
     */
    public function showRegistrationForm()
    {
        return view('pages.login.RegistroAdmin');
    }

    /**
     * Processa o cadastro de admin
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255',
                'cpf' => 'required|string|unique:admins,cpf',
                'email' => 'required|string|email|max:255|unique:admins,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $cpfLimpo = preg_replace('/\D/', '', $request->cpf);

            $admin = Admin::create([
                'name' => $request->nome,
                'cpf' => $cpfLimpo,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'level' => 2,
            ]);

            Auth::guard('admin')->login($admin);

            return redirect()->route('cadastro')->with('success', 'Cadastro realizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao registrar admin: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['error' => 'Ocorreu um erro interno no servidor ao tentar registrar. Verifique os logs.'])->withInput();
        }
    }
}
