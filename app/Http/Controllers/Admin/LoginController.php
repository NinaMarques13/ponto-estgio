<?php
// Admin controller
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    protected $guard = 'admin';


    protected $loginPath = '/views/adm';

    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect('/dashboard');
        }

        return view('pages.login.adm');
    }


    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'cpf';

        $loginValue = $fieldType === 'cpf' ? preg_replace('/\D/', '', $request->login) : $request->login;

        $credentials = [
            $fieldType => $loginValue,
            'password' => $request->password,
        ];
        if (Auth::guard('admin')->attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()
            ->withInput($request->only('login'))
            ->withErrors(['login' => 'As informações não coincidem com nossos registros.']);
    }


    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect('/');

    }


    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:admins',
            'password' => 'required|min:6|confirmed',
        ]);
    }

}