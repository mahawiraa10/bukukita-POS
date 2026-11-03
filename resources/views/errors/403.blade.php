@extends('errors.layout')

@section('code', '403')
@section('title', 'Akses ditolak')
@section('accent', 'text-rose-600')
@section('primary_url', '/dashboard')
@section('primary_label', 'Ke Dashboard')

@section('message')
Role akun lu tidak punya izin buat halaman ini. Hubungi Owner kalau ini keliru.
@endsection
