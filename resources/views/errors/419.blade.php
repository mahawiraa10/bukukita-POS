@extends('errors.layout')

@section('code', '419')
@section('title', 'Sesi kedaluwarsa')
@section('accent', 'text-amber-600')
@section('primary_url', '/dashboard')
@section('primary_label', 'Muat Ulang')

@section('message')
Token halaman sudah lewat masa berlaku. Muat ulang halaman lalu coba lagi.
@endsection
