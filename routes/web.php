<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\LicenseController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\VariationController;
use App\Http\Controllers\Web\VersionController;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Use cache to avoid hitting the DB on every request to the homepage.
    $userExists = Cache::rememberForever('user_exists', function () {
        return User::exists();
    });

    if (!$userExists) {
        return redirect()->route('register');
    }

    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::resource('projects', ProjectController::class)->names('web.projects');
    Route::resource('projects.versions', VersionController::class)->names('web.projects.versions');
    Route::resource('projects.variations', VariationController::class)->names('web.projects.variations');
    Route::resource('customers', CustomerController::class)->names('web.customers');
    Route::resource('licenses', LicenseController::class)->names('web.licenses');
    Route::get('licenses/{license}/download', [LicenseController::class, 'download'])->name('web.licenses.download');
    Route::post('licenses/{license}/reset', [LicenseController::class, 'reset'])->name('web.licenses.reset');
    Route::get('generated-licenses/{generatedLicense}/download', [LicenseController::class, 'downloadGenerated'])->name('web.generated-licenses.download');
    Route::resource('users', UserController::class)->names('web.users');
});

Route::get('/dashboard', function () {
    return redirect()->route('web.projects.index');
})->middleware(['auth'])->name('dashboard');

// Auth Routes
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    $credentials = request()->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (auth()->attempt($credentials)) {
        request()->session()->regenerate();
        return redirect()->intended('dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');
