@extends('layouts.app')
@section('title', 'تعديل فلاح')
@section('page-title', 'تعديل الفلاح: ' . $client->name)

@section('content')
<div class="card" style="max-width:800px">
    <div class="card-header">
        <h5><i class="bi bi-pencil me-2"></i>تعديل: {{ $client->name }}</h5>
        <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('clients._form', ['client' => $client, 'vannes' => $vannes, 'selectedVannes' => $selectedVannes])
    </div>
</div>
@endsection
