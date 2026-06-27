@extends('layouts.app')
@section('title', 'تعديل ' . $config['label'])
@section('page-title', 'تعديل #' . $solde->id)

@section('content')
<div class="card" style="max-width:700px">
    <div class="card-header">
        <h5><i class="bi bi-pencil me-2"></i>تعديل عملية #{{ $solde->id }}</h5>
        <a href="{{ route($config['prefix'] . '.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('soldes._form', ['solde' => $solde])
    </div>
</div>
@endsection
