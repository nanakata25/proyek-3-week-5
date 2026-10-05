<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function form() { return view('login'); }
    public function login(Request $r) {
        $credentials = $r->validate(['username'=>'required|string|max:50','password'=>'required|string|max:255']);
        if (!Auth::attempt($credentials)) return back()->withErrors(['username'=>'Username atau password salah.'])->onlyInput('username');
        $r->session()->regenerate();
        return redirect()->intended(route('products'))->with('success','Login berhasil. Selamat berbelanja!');
    }
    public function logout(Request $r) {
        Auth::logout(); $r->session()->invalidate(); $r->session()->regenerateToken();
        return redirect()->route('login')->with('success','Anda berhasil logout.');
    }
}
