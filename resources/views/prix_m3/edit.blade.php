@extends('layouts.app')
@section('title', 'تعديل السعر')
@section('page-title', 'تعديل سعر ' . $prixM3->year)

@section('content')
<div class="card" style="max-width:450px">
    <div class="card-header">
        <h5><i class="bi bi-pencil me-2"></i>سعر {{ $prixM3->year }}</h5>
        <a href="{{ route('prix-m3.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('prix_m3._form', ['prixM3' => $prixM3])
    </div>
</div>
@endsection
