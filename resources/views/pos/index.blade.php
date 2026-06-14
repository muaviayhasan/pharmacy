@extends('layouts.app')

@section('title', 'POS')
@section('page-title', 'Pharmacy POS')

@section('content')
    @livewire('pos.terminal')
@endsection
