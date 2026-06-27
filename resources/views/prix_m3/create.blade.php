@extends('layouts.app')
@section('title', 'إضافة سعر')
@section('page-title', 'إضافة سعر جديد')

@section('content')
<div class="card" style="max-width:450px">
    <div class="card-header">
        <h5><i class="bi bi-plus-circle me-2"></i>سعر جديد</h5>
        <a href="{{ route('prix-m3.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('prix_m3._form', ['prixM3' => null])
    </div>
</div>
@endsection
