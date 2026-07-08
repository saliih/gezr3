@extends('layouts.app')
@section('title', 'إضافة مداخيل')
@section('page-title', 'إضافة مداخيل')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

@if($errors->any())
<div class="alert alert-danger mb-3">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('paiements.store') }}" id="paiementForm">
@csrf

<div class="card mb-3">
    <div class="card-header"><h5><i class="bi bi-cash-coin me-2"></i>بيانات الدفعة - {{ $year }}</h5></div>
    <div class="card-body">
        <div class="row g-3">

            {{-- Client --}}
            <div class="col-md-8">
                <label class="form-label fw-semibold">الفلاح <span class="text-danger">*</span></label>
                <select name="client_id" id="client_id" class="form-select" required>
                    <option value="">-- اختر فلاح --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
                            {{ $c }}
                        </option>
                    @endforeach
                </select>
                <div id="not-active-warn" class="alert alert-danger mt-2 mb-0 py-2 d-none">
                    <i class="bi bi-exclamation-triangle-fill"></i> هذا الفلاح غير مفعّل لسنة {{ $year }}
                </div>
            </div>

            {{-- Date --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">تاريخ العملية</label>
                <input type="date" name="date_transfert" class="form-control"
                       value="{{ old('date_transfert', date('Y-m-d')) }}">
            </div>

            {{-- Transfer / coupon --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">رقم التحويل</label>
                <input type="text" name="transfert_number" class="form-control"
                       value="{{ old('transfert_number') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">رقم القسيمة</label>
                <input type="text" name="coupon_number" class="form-control" maxlength="10"
                       value="{{ old('coupon_number') }}">
            </div>
        </div>
    </div>
</div>

{{-- Financial breakdown --}}
<div class="card mb-3">
    <div class="card-header"><h5><i class="bi bi-calculator me-2"></i>تفاصيل المبالغ</h5></div>
    <div class="card-body">
        <div class="row g-3">

            {{-- Remaining m² from previous year --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted">
                    الرصيد المتبقي من {{ $year - 1 }} (م²)
                </label>
                <div class="input-group">
                    <input type="number" id="remaining_m2" class="form-control bg-light" readonly value="0">
                    <span class="input-group-text">م²</span>
                </div>
            </div>

            {{-- Settlement amount (remaining / prev_price) --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted">
                    مبلغ تسوية {{ $year - 1 }}
                </label>
                <div class="input-group">
                    <input type="number" id="settlement_amount" class="form-control bg-light" readonly value="0.00" step="0.01">
                    <span class="input-group-text">د.ت</span>
                </div>
            </div>

            {{-- Amount (paiement) — repartie par vanne --}}
            <div class="col-12">
                <label class="form-label fw-semibold">
                    المبلغ <span class="text-danger">*</span>
                    <small class="text-muted fw-normal">(موزع على الفوانات)</small>
                </label>
                <div id="vannes-container" class="row g-2"></div>
                <div id="no-vannes-warn" class="text-danger mt-1 small d-none">
                    <i class="bi bi-exclamation-triangle-fill"></i> لا توجد فوانات مرتبطة بهذا الفلاح
                </div>
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- Calculated total --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-muted">المجموع المحسوب</label>
                <div class="input-group">
                    <input type="number" id="calc_total" class="form-control bg-light fw-bold" readonly value="0.00" step="0.01">
                    <span class="input-group-text">د.ت</span>
                </div>
            </div>

            {{-- Manual total to verify --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">
                    المجموع المدفوع (للتحقق) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" id="manual_total" class="form-control" step="0.01" min="0" value="0">
                    <span class="input-group-text">د.ت</span>
                </div>
                <div id="total-match" class="mt-1 small d-none"></div>
            </div>

        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" id="submitBtn" class="btn btn-primary" disabled>
        <i class="bi bi-check-lg me-1"></i> إضافة
    </button>
    <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary">إلغاء</a>
</div>

</form>
</div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const ajaxUrl = "{{ route('paiements.client-data') }}";

    let settlementAmt = 0;

    function escapeHtml(str) {
        return $('<div>').text(str).html();
    }

    function vannesTotal() {
        let total = 0;
        $('#vannes-container input.vanne-amount').each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        return total;
    }

    function renderVannes(vannes) {
        const $container = $('#vannes-container');
        $container.empty();

        $('#no-vannes-warn').toggleClass('d-none', vannes.length > 0);
        $('#submitBtn').prop('disabled', vannes.length === 0);

        vannes.forEach(function (v) {
            $container.append(`
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">${escapeHtml(v.label)}</span>
                        <input type="number" name="vanne_amount[${v.id}]" class="form-control vanne-amount"
                               step="0.01" min="0" required value="0">
                        <span class="input-group-text">د.ت</span>
                    </div>
                </div>
            `);
        });

        $container.find('input.vanne-amount').on('input', recalc);
        recalc();
    }

    function recalc() {
        const amt   = vannesTotal();
        const total = settlementAmt + amt;
        $('#calc_total').val(total.toFixed(2));
        checkTotals();
    }

    function checkTotals() {
        const manual = parseFloat($('#manual_total').val()) || 0;
        const calc   = parseFloat($('#calc_total').val()) || 0;
        const $match = $('#total-match');
        const hasVannes = $('#vannes-container input.vanne-amount').length > 0;

        $match.removeClass('d-none');

        if (hasVannes && Math.abs(manual - calc) < 0.01) {
            $match.html('<i class="bi bi-check-circle-fill text-success"></i> المجموع متطابق');
            $('#submitBtn').prop('disabled', false);
        } else {
            $match.html(`<i class="bi bi-x-circle-fill text-danger"></i> الفرق: ${(manual - calc).toFixed(2)} د.ت`);
            $('#submitBtn').prop('disabled', true);
        }

        if (manual === 0 && calc === 0) {
            $match.addClass('d-none');
            $('#submitBtn').prop('disabled', true);
        }
    }

    $('#client_id').on('change', function () {
        const id = $(this).val();
        if (!id) {
            $('#remaining_m2').val(0);
            $('#settlement_amount').val('0.00');
            settlementAmt = 0;
            $('#not-active-warn').addClass('d-none');
            renderVannes([]);
            return;
        }

        $.getJSON(ajaxUrl, { client_id: id }, function (data) {
            $('#remaining_m2').val(data.remaining);
            $('#settlement_amount').val(parseFloat(data.settlement).toFixed(2));

            settlementAmt = parseFloat(data.settlement) || 0;

            $('#not-active-warn').toggleClass('d-none', !!data.is_active);

            renderVannes(data.vannes || []);
        });
    });

    $('#manual_total').on('input', checkTotals);
    renderVannes([]);
});
</script>
@endpush
