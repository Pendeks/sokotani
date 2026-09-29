@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    @include('components.hero')
    @include('components.categories')
    @include('components.products', ['products' => $products])
@endsection