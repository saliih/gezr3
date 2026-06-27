@php
    $action = $client ? route('clients.update', $client) : route('clients.store');
    $method = $client ? 'PUT' : 'POST';
@endphp

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label fw-semibold">الاسم <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required
                   value="{{ old('name', $client?->name) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">رقم الملف</label>
            <input type="text" name="num_dossier" class="form-control"
                   value="{{ old('num_dossier', $client?->num_dossier) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">CIN</label>
            <input type="text" name="cin" class="form-control" maxlength="8"
                   value="{{ old('cin', $client?->cin) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">الهاتف</label>
            <input type="text" name="tel" class="form-control"
                   value="{{ old('tel', $client?->tel) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">الرصيد القديم</label>
            <input type="number" step="0.01" name="old_solde" class="form-control"
                   value="{{ old('old_solde', $client?->old_solde ?? 0) }}">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">ملاحظات</label>
            <textarea name="comments" class="form-control" rows="2">{{ old('comments', $client?->comments) }}</textarea>
        </div>
    </div>

    <hr>
    <h6 class="text-muted mb-3">شجرة التوزيع</h6>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <label class="form-label">G-Tree</label>
            <input type="number" name="gtree" class="form-control" value="{{ old('gtree', $client?->gtree ?? 0) }}">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">M-Tree</label>
            <input type="number" name="mtree" class="form-control" value="{{ old('mtree', $client?->mtree ?? 0) }}">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">O-Tree</label>
            <input type="number" name="otree" class="form-control" value="{{ old('otree', $client?->otree ?? 0) }}">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label">P-Tree</label>
            <input type="number" name="ptree" class="form-control" value="{{ old('ptree', $client?->ptree ?? 0) }}">
        </div>
    </div>

    @if($vannes->count())
    <hr>
    <h6 class="text-muted mb-2">الصنابير المرتبطة</h6>
    <div class="row g-2">
        @foreach($vannes as $vanne)
        <div class="col-sm-4 col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="vannes[]"
                       value="{{ $vanne->id }}" id="vanne_{{ $vanne->id }}"
                       {{ in_array($vanne->id, old('vannes', $selectedVannes)) ? 'checked' : '' }}>
                <label class="form-check-label" for="vanne_{{ $vanne->id }}">
                    {{ $vanne->reference }}{{ $vanne->link ? ' '.$vanne->link : '' }}
                </label>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ $client ? 'حفظ التعديلات' : 'إضافة العميل' }}
        </button>
        <a href="{{ $client ? route('clients.show', $client) : route('clients.index') }}"
           class="btn btn-outline-secondary">إلغاء</a>
    </div>
</form>
