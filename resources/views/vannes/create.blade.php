@extends('layouts.app')
@section('title', 'إضافة عداد')
@section('page-title', 'إضافة عداد جديد')

@section('content')
<div class="card" style="max-width:500px">
    <div class="card-header">
        <h5><i class="bi bi-plus-circle me-2"></i>عداد جديد</h5>
        <a href="{{ route('vannes.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('vannes._form', ['vanne' => null])
    </div>
</div>
@endsection
