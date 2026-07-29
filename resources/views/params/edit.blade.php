@extends('layouts.app')
@section('title', 'المعاملات')
@section('page-title', 'إعدادات المعاملات')

@section('content')
<div class="card" style="max-width:450px">
    <div class="card-header">
        <h5><i class="bi bi-sliders me-2"></i>الإعدادات العامة</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('params.update', $params->id ?? 1) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">سعر الوحدة</label>
                <div class="input-group">
                    <input type="number" step="0.001" name="unit_price" class="form-control" required
                           value="{{ old('unit_price', $params->unit_price) }}">
                    <span class="input-group-text">د.ت</span>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">عدد العدادات في اليوم</label>
                <input type="number" name="valve_per_day" class="form-control" required
                       value="{{ old('valve_per_day', $params->valve_per_day) }}">
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-save me-1"></i> حفظ الإعدادات
            </button>
        </form>
    </div>
</div>
@endsection

