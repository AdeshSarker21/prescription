@extends('admin.layouts.app')

@section('title', 'Add Medicine - Admin')

@section('content')
    @include('admin.medicines._form', [
        'medicine' => null,
        'categories' => $categories,
        'route' => route('admin.medicines.store'),
        'method' => 'POST',
        'title' => 'Add Medicine',
    ])
@endsection
