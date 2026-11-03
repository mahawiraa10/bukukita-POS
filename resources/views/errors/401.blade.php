@extends('errors.layout')

@section('code', '401')
@section('title', 'Belum login')
@section('accent', 'text-amber-600')
@section('primary_url', '/login')
@section('primary_label', 'Masuk')

@section('message')
Sesi lu habis atau belum login. Masuk ulang buat lanjut.
@endsection
