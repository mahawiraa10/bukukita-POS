@extends('errors.layout')

@section('code', '429')
@section('title', 'Terlalu banyak permintaan')
@section('accent', 'text-amber-600')
@section('primary_url', '/dashboard')
@section('primary_label', 'Muat Ulang')

@section('message')
Lu nembak server kekencangan. Tunggu sebentar, lalu coba lagi.
@endsection
