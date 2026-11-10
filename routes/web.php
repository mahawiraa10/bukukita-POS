<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::get('/dashboard', fn() => view('dashboard.index'))->name('dashboard');
Route::get('/company-setup', fn() => view('setup.company'))->name('setup.company');
Route::get('/users-management', fn() => view('setup.users'))->name('setup.users');
Route::get('/master/hierarki', fn() => view('master.hierarki'))->name('master.hierarki');
Route::get('/master/vendor', fn() => view('master.vendor'))->name('master.vendor');
Route::get('/master/produk', fn() => view('master.produk'))->name('master.produk');
Route::get('/operational/katalog', fn() => view('operational.katalog'))->name('operational.katalog');
Route::get('/operational/po', fn() => view('operational.po'))->name('operational.po');
Route::get('/operational/receiving', fn() => view('operational.receiving'))->name('operational.receiving');
Route::get('/operational/gudang', fn() => view('operational.gudang'))->name('operational.gudang');
Route::get('/operational/events', fn() => view('operational.events'))->name('operational.events');
Route::get('/operational/event-manage', fn() => view('operational.event-manage'))->name('operational.event-manage');
Route::get('/reports', fn() => view('reports.index'))->name('reports.index');
Route::get('/master/poin', fn() => view('master.poin'))->name('master.poin');
Route::get('/master/member', fn() => view('master.member'))->name('master.member');
Route::get('/pos', fn() => view('operational.pos'))->name('pos.index');
Route::get('/receipt/{sale}', [\App\Http\Controllers\ReceiptController::class, 'show'])->name('receipt.show');
