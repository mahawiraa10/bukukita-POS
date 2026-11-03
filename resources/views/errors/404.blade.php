@extends('errors.layout')

@section('code', '404')
@section('title', 'Halaman tidak ditemukan')
@section('accent', 'text-emerald-600')
@section('primary_url', '/dashboard')
@section('primary_label', 'Ke Dashboard')

@section('message')
Alamat yang lu tuju tidak ada, sudah dipindah, atau butuh akses berbeda. Cek lagi URL-nya.
@endsection
