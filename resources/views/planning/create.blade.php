@extends('layouts.app')
@section('title', 'تخطيط الاستهلاك')
@section('page-title', 'تخطيط الاستهلاك')

@section('content')

<form method="POST" action="{{ route('planning.store') }}" id="planning-form">
@csrf

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="position:sticky;top:0;z-index:10;background:#fff;">
        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>تخطيط جديد</h5>
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0 fw-semibold small text-muted">تاريخ التخطيط الافتراضي:</label>
            <input type="date" id="default-date" class="form-control form-control-sm" value="{{ $defaultDate }}" style="width:160px">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0" id="planning-table">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width:40px">#</th>
                    <th>الفلاح</th>
                    <th>المرجع</th>
                    <th>الصنبور</th>
                    <th class="text-end" dir="ltr" style="width:110px">العداد القديم</th>
                    <th class="text-end" dir="ltr" style="width:110px">العداد الجديد</th>
                    <th class="text-end" dir="ltr" style="width:110px">الاستهلاك</th>
                    <th style="width:150px">تاريخ التخطيط</th>
                    <th style="width:50px"></th>
                </tr>
            </thead>
            <tbody id="rows-container">
                {{-- rows injected by JS --}}
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-outline-primary btn-sm" id="add-row">
            <i class="bi bi-plus-lg me-1"></i>إضافة سطر
        </button>
        <div class="d-flex gap-2">
            <a href="{{ route('consommation.index') }}" class="btn btn-outline-secondary btn-sm">إلغاء</a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-save me-1"></i>حفظ التخطيط
            </button>
        </div>
    </div>
</div>

</form>

{{-- Pending (planned but not yet recorded) --}}
@if($pending->isNotEmpty())
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">
            <i class="bi bi-calendar-check me-2"></i>الصنابير المخططة وغير المسجّلة
            <span class="badge bg-warning text-dark ms-1" id="pending-count">{{ $pending->count() }}</span>
        </h5>
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0 small fw-semibold text-muted">تصفية بالتاريخ:</label>
            <input type="date" id="filter-date" class="form-control form-control-sm" style="width:160px">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-filter">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>تاريخ التخطيط</th>
                    <th>الفلاح</th>
                    <th>الصنبور</th>
                    <th class="text-end" dir="ltr" style="width:120px">العداد القديم</th>
                    <th class="text-end" dir="ltr" style="width:120px">العداد الجديد</th>
                    <th class="text-end" dir="ltr" style="width:110px">الاستهلاك</th>
                    <th class="text-end" dir="ltr" style="width:110px">الرصيد</th>
                    <th style="width:90px"></th>
                </tr>
            </thead>
            <tbody id="rows-pending">
                @foreach($pending as $s)
                <tr id="pending-row-{{ $s->id }}"
                    data-url="{{ route('planning.releve', $s) }}"
                    data-prev-reminder="{{ $s->prev_reminder }}"
                    data-date="{{ $s->plan_date?->format('Y-m-d') }}">
                    <td>{{ $s->plan_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        @if($s->client)
                            <a href="{{ route('clients.show', $s->client) }}" class="text-decoration-none">{{ $s->client }}</a>
                        @else —
                        @endif
                    </td>
                    <td class="fw-semibold">{{ $s->vannes ?? '—' }}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-end p-old-counter" dir="ltr"
                               value="{{ $s->old_counter ?? 0 }}" min="0">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-end p-new-counter" dir="ltr"
                               min="0" placeholder="0">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm text-end p-consume bg-light" dir="ltr" readonly placeholder="—">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm text-end p-reminder bg-light" dir="ltr" readonly placeholder="—">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-success btn-save-releve" title="حفظ">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <a href="{{ route('consommation.edit', $s) }}" class="btn btn-sm btn-outline-secondary" title="تفاصيل">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif


@endsection

@push('scripts')
<script>
$(function () {
    var clientVannesUrl = @json(route('planning.client-vannes'));
    var clients         = @json($clients->map(fn($c) => ['id' => $c->id, 'label' => (string)$c, 'num_dossier' => $c->num_dossier ?? '']));
    var clientMap       = {};
    $.each(clients, function(_, c) { clientMap[c.id] = c; });
    var $tbody = $('#rows-container');
    var idx    = 0;
    var s2opts = { theme: 'bootstrap-5', dir: 'rtl', width: '100%' };

    function buildClientOptions() {
        var html = '<option value="">-- اختر --</option>';
        $.each(clients, function(_, c) {
            html += '<option value="' + c.id + '" data-num-dossier="' + $('<span>').text(c.num_dossier).html() + '">'
                  + $('<span>').text(c.label).html() + '</option>';
        });
        return html;
    }

    function renumber() {
        $tbody.find('.row-num').each(function(i) { $(this).text(i + 1); });
    }

    function initSelect2($tr) {
        $tr.find('select').each(function() {
            $(this).select2($.extend({}, s2opts, { dropdownParent: $tr }));
        });
    }

    function addRow() {
        var i   = idx++;
        var def = $('#default-date').val();
        var $tr = $('<tr>').html(
            '<td class="text-center text-muted small row-num"></td>' +
            '<td><select name="rows[' + i + '][client_id]" class="form-select form-select-sm client-sel" required>' + buildClientOptions() + '</select></td>' +
            '<td><input type="text"   name="rows[' + i + '][ref_client]"  class="form-control form-control-sm ref-client bg-light" readonly placeholder="—"></td>' +
            '<td><select name="rows[' + i + '][vannes_id]" class="form-select form-select-sm vanne-sel no-select2" disabled required><option value="">-- اختر الفلاح أولاً --</option></select></td>' +
            '<td><input type="number" name="rows[' + i + '][old_counter]" class="form-control form-control-sm text-end old-counter bg-light" readonly dir="ltr"></td>' +
            '<td><input type="number" name="rows[' + i + '][new_counter]" class="form-control form-control-sm text-end new-counter" min="0" dir="ltr" placeholder="0"></td>' +
            '<td><input type="text"   name="rows[' + i + '][consume]"     class="form-control form-control-sm text-end consume-display bg-light" readonly dir="ltr" placeholder="—"></td>' +
            '<td><input type="date"   name="rows[' + i + '][plan_date]"   class="form-control form-control-sm plan-date" value="' + def + '" required></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>'
        );
        $tbody.append($tr);
        // Init Select2 only on client-sel (vanne-sel starts disabled)
        $tr.find('.client-sel').select2($.extend({}, s2opts, { dropdownParent: $tr }));
        renumber();
    }

    // Client change (jQuery event from Select2)
    $tbody.on('change', '.client-sel', function () {
        var $tr       = $(this).closest('tr');
        var clientId  = $(this).val();
        var numDos    = clientId && clientMap[clientId] ? clientMap[clientId].num_dossier : '';
        var $vanneSel = $tr.find('.vanne-sel');
        var $old      = $tr.find('.old-counter');
        var $new      = $tr.find('.new-counter');
        var $cons     = $tr.find('.consume-display');

        $tr.find('.ref-client').val(numDos);
        $old.val(''); $new.val(''); $cons.val('');

        // Destroy existing Select2 on vanne if any
        if ($vanneSel.hasClass('select2-hidden-accessible')) {
            $vanneSel.select2('destroy');
        }
        $vanneSel.prop('disabled', true)
                 .html('<option value="">-- اختر الصنبور --</option>');

        if (!clientId) return;

        $vanneSel.html('<option value="">جاري التحميل...</option>');

        $.getJSON(clientVannesUrl, { client_id: clientId }, function (data) {
            var opts = '<option value="">-- اختر الصنبور --</option>';
            $.each(data.vannes, function(_, v) {
                opts += '<option value="' + v.id + '" data-old-counter="' + v.old_counter + '">'
                      + $('<span>').text(v.label).html() + '</option>';
            });
            $vanneSel.html(opts)
                     .prop('disabled', false)
                     .select2($.extend({}, s2opts, { dropdownParent: $tr }));

            if (data.vannes.length === 1) {
                $vanneSel.val(data.vannes[0].id).trigger('change');
            }
        }).fail(function() {
            $vanneSel.html('<option value="">خطأ في التحميل</option>');
        });
    });

    // Vanne change
    $tbody.on('change', '.vanne-sel', function () {
        var $tr  = $(this).closest('tr');
        var $opt = $(this).find('option:selected');
        $tr.find('.old-counter').val($opt.val() ? ($opt.data('old-counter') || '') : '');
        $tr.find('.new-counter').val('');
        $tr.find('.consume-display').val('');
    });

    // New counter → calculate consumption
    $tbody.on('input', '.new-counter', function () {
        var $tr  = $(this).closest('tr');
        var oldV = parseFloat($tr.find('.old-counter').val()) || 0;
        var newV = $(this).val();
        var $cons = $tr.find('.consume-display');
        if (newV === '' || parseFloat(newV) === 0) {
            $cons.val('');
        } else {
            $cons.val(Math.max(0, parseFloat(newV) - oldV));
        }
    });

    // Remove row
    $tbody.on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
        renumber();
    });

    // Default date → update all plan-date fields
    $('#default-date').on('change', function () {
        var val = $(this).val();
        $tbody.find('.plan-date').val(val);
    });

    $('#add-row').on('click', addRow);

    addRow();

    // ── Pending table: date filter ───────────────────────────────────────
    function applyFilter() {
        var date = $('#filter-date').val();
        var visible = 0;
        $('#rows-pending tr').each(function () {
            var rowDate = $(this).attr('data-date') || '';
            var match   = !date || rowDate === date;
            $(this).toggle(match);
            if (match) visible++;
        });
        $('#pending-count').text(visible);
    }

    $('#filter-date').on('change', applyFilter);
    $('#clear-filter').on('click', function () {
        $('#filter-date').val('');
        applyFilter();
    });
    // ─────────────────────────────────────────────────────────────────────

    // ── Pending table: inline releve ──────────────────────────────────────
    // Recalculate consume + reminder on old/new counter change
    function recalcPendingRow($tr) {
        var oldV     = parseFloat($tr.find('.p-old-counter').val()) || 0;
        var newV     = $tr.find('.p-new-counter').val();
        var prevRem  = parseFloat($tr.data('prev-reminder')) || 0;
        var $consume = $tr.find('.p-consume');
        var $reminder= $tr.find('.p-reminder');

        if (newV === '' || parseFloat(newV) === 0) {
            $consume.val('').removeClass('text-danger');
            $reminder.val('').removeClass('text-danger');
        } else {
            var consume  = Math.max(0, parseFloat(newV) - oldV);
            var reminder = prevRem - consume;
            $consume.val(consume);
            $reminder.val(reminder.toFixed(2))
                     .toggleClass('text-danger', reminder < 0);
        }
    }

    $(document).on('input', '.p-new-counter, .p-old-counter', function () {
        recalcPendingRow($(this).closest('tr'));
    });

    // Save releve via AJAX
    $(document).on('click', '.btn-save-releve', function () {
        var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        var $tr  = $btn.closest('tr');
        var url  = $tr.data('url');
        var old  = $tr.find('.p-old-counter').val();
        var nw   = $tr.find('.p-new-counter').val();

        $.post({
            url: url,
            data: {
                _token:      $('meta[name="csrf-token"]').attr('content'),
                old_counter: old,
                new_counter: nw || 0,
            },
            success: function (res) {
                $tr.addClass('table-success');
                $tr.find('.p-consume').val(res.consume);
                $tr.find('.p-reminder').val(res.reminder);
                $tr.find('input').prop('disabled', true);
                $btn.prop('disabled', true).html('<i class="bi bi-check-lg"></i>');
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'خطأ في الحفظ';
                $btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i>');
                alert(msg);
            }
        });
    });
    // ─────────────────────────────────────────────────────────────────────
});
</script>
@endpush
