@extends('admin.layouts.app')

@section('title', 'Edit Medicine - Admin')

@section('content')
    @include('admin.medicines._form', [
        'medicine' => $medicine,
        'categories' => $categories,
        'route' => route('admin.medicines.update', $medicine),
        'method' => 'PATCH',
        'title' => 'Edit Medicine',
    ])
@endsection
