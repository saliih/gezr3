@php
    $isEdit  = $solde !== null;
    $action  = $isEdit ? route($config['prefix'] . '.update', $solde) : route($config['prefix'] . '.store');
@endphp

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if($isEdit) @method('PUT') @endif
    <input type="hidden" name="type" value="{{ $type }}">

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label fw-semibold">العميل <span class="text-danger">*</span></label>
            <select name="client_id" class="form-select" required>
                <option value="">-- اختر عميل --</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}"
                        {{ old('client_id', $solde?->client_id ?? request('client_id')) == $c->id ? 'selected' : '' }}>
                        {{ $c->num_dossier ? "({$c->num_dossier}) " : '' }}{{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">تاريخ العملية</label>
            <input type="date" name="date_transfert" class="form-control"
                   value="{{ old('date_transfert', $solde?->date_transfert?->format('Y-m-d') ?? date('Y-m-d')) }}">
        </div>

        @if($type === 'credit')
        <div class="col-md-4">
            <label class="form-label fw-semibold">المبلغ <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="number" step="0.01" name="amount" class="form-control" required
                       value="{{ old('amount', $solde?->amount) }}">
                <span class="input-group-text">د.ت</span>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">رقم التحويل</label>
            <input type="text" name="transfert_number" class="form-control"
                   value="{{ old('transfert_number', $solde?->transfert_number) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">رقم القسيمة</label>
            <input type="text" name="coupon_number" class="form-control" maxlength="10"
                   value="{{ old('coupon_number', $solde?->coupon_number) }}">
        </div>
        @endif

        @if($type === 'debit')
        <div class="col-md-6">
            <label class="form-label fw-semibold">الصنبور</label>
            <select name="vannes_id" class="form-select">
                <option value="">-- اختر صنبور --</option>
                @foreach($vannes as $v)
                    <option value="{{ $v->id }}"
                        {{ old('vannes_id', $solde?->vannes_id) == $v->id ? 'selected' : '' }}>
                        {{ $v->reference }}{{ $v->link }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">العداد القديم</label>
            <input type="number" name="old_counter" id="old_counter" class="form-control"
                   value="{{ old('old_counter', $solde?->old_counter) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">العداد الجديد</label>
            <input type="number" name="new_counter" id="new_counter" class="form-control"
                   value="{{ old('new_counter', $solde?->new_counter) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">الاستهلاك (م³)</label>
            <input type="number" id="consume_display" class="form-control" readonly
                   value="{{ old('new_counter', $solde?->new_counter ?? 0) - old('old_counter', $solde?->old_counter ?? 0) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">تاريخ القراءة</label>
            <input type="date" name="plan_date" class="form-control"
                   value="{{ old('plan_date', $solde?->plan_date?->format('Y-m-d')) }}">
        </div>
        @endif

        @if($type === 'activate')
        <div class="col-md-4">
            <label class="form-label fw-semibold">رقم التحويل</label>
            <input type="text" name="transfert_number" class="form-control"
                   value="{{ old('transfert_number', $solde?->transfert_number) }}">
        </div>
        @endif

        @if($isEdit)
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="locked" value="1" id="locked"
                       {{ old('locked', $solde?->locked) ? 'checked' : '' }}>
                <label class="form-check-label" for="locked">مقفل</label>
            </div>
        </div>
        @endif
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ $isEdit ? 'حفظ' : 'إضافة' }}
        </button>
        <a href="{{ route($config['prefix'] . '.index') }}" class="btn btn-outline-secondary">إلغاء</a>
    </div>
</form>

@if($type === 'debit')
@push('scripts')
<script>
    const oldC = document.getElementById('old_counter');
    const newC = document.getElementById('new_counter');
    const disp = document.getElementById('consume_display');

    function updateConsume() {
        const v = Math.max(0, (parseInt(newC.value) || 0) - (parseInt(oldC.value) || 0));
        disp.value = v;
    }
    oldC.addEventListener('input', updateConsume);
    newC.addEventListener('input', updateConsume);
</script>
@endpush
@endif
