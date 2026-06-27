@extends('layouts.app')
@section('title', 'إضافة ' . $config['label'])
@section('page-title', 'إضافة ' . $config['label'])

@section('content')
<div class="card" style="max-width:700px">
    <div class="card-header">
        <h5><i class="bi bi-plus-circle me-2"></i>إضافة {{ $config['label'] }}</h5>
        <a href="{{ route($config['prefix'] . '.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('soldes._form', ['solde' => null])
    </div>
</div>
@endsection
