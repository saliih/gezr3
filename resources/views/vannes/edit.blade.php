@extends('layouts.app')
@section('title', 'تعديل صنبور')
@section('page-title', 'تعديل الصنبور #' . $vanne->reference)

@section('content')
<div class="card" style="max-width:500px">
    <div class="card-header">
        <h5><i class="bi bi-pencil me-2"></i>تعديل: {{ $vanne->reference }}{{ $vanne->link }}</h5>
        <a href="{{ route('vannes.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('vannes._form', ['vanne' => $vanne])
    </div>
</div>
@endsection
