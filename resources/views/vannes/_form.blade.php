@php
    $action = $vanne ? route('vannes.update', $vanne) : route('vannes.store');
    $method = $vanne ? 'PUT' : 'POST';
@endphp

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label fw-semibold">المرجع <span class="text-danger">*</span></label>
        <input type="number" name="reference" class="form-control" required
               value="{{ old('reference', $vanne?->reference) }}">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">الرابط (link)</label>
        <input type="text" name="link" class="form-control" maxlength="2"
               value="{{ old('link', $vanne?->link) }}" placeholder="مثال: A, B">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">آخر قيمة عداد</label>
        <input type="number" name="last_value" class="form-control"
               value="{{ old('last_value', $vanne?->last_value ?? 0) }}">
    </div>
    <div class="mb-3 d-flex gap-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="enable" value="1" id="enable"
                   {{ old('enable', $vanne?->enable ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="enable">مفعّل</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="consumed" value="1" id="consumed"
                   {{ old('consumed', $vanne?->consumed) ? 'checked' : '' }}>
            <label class="form-check-label" for="consumed">مستهلك</label>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ $vanne ? 'حفظ' : 'إضافة' }}
        </button>
        <a href="{{ route('vannes.index') }}" class="btn btn-outline-secondary">إلغاء</a>
    </div>
</form>
