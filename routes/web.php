<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', fn() => view('auth.login'))->name('login');
Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');
Route::get('/company-setup', fn() => view('setup.company'))->name('setup.company');
Route::get('/users-management', fn() => view('setup.users'))->name('setup.users');
Route::get('/master/hierarki', fn() => view('master.hierarki'))->name('master.hierarki');
