@extends('errors.layout')

@section('code', '500')
@section('title', 'Kesalahan server')
@section('accent', 'text-rose-600')
@section('primary_url', '/dashboard')
@section('primary_label', 'Muat Ulang')

@section('message')
Ada yang gagal di sisi server. Data lu tidak diproses. Coba lagi atau hubungi admin.
@endsection
