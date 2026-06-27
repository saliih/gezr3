@php
    $action = $prixM3 ? route('prix-m3.update', $prixM3) : route('prix-m3.store');
@endphp

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if($prixM3) @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label fw-semibold">السنة <span class="text-danger">*</span></label>
        <input type="number" name="year" class="form-control" required
               value="{{ old('year', $prixM3?->year ?? date('Y')) }}"
               min="2000" max="2100">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">السعر (د.ت/م³) <span class="text-danger">*</span></label>
        <input type="number" step="0.001" name="price" class="form-control" required
               value="{{ old('price', $prixM3?->price) }}">
    </div>
    <div class="mb-4">
        <label class="form-label fw-semibold">رسوم الاشتراك (د.ت)</label>
        <input type="number" step="0.01" name="activation_fee" class="form-control"
               value="{{ old('activation_fee', $prixM3?->activation_fee) }}">
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ $prixM3 ? 'حفظ' : 'إضافة' }}
        </button>
        <a href="{{ route('prix-m3.index') }}" class="btn btn-outline-secondary">إلغاء</a>
    </div>
</form>

