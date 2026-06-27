@extends('layouts.app')
@section('title', 'إضافة عميل')
@section('page-title', 'إضافة عميل جديد')

@section('content')
<div class="card" style="max-width:800px">
    <div class="card-header">
        <h5><i class="bi bi-person-plus me-2"></i>عميل جديد</h5>
        <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('clients._form', ['client' => null, 'vannes' => $vannes, 'selectedVannes' => []])
    </div>
</div>
@endsection
