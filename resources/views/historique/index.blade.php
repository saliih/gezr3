@extends('layouts.app')
@section('title', 'سجل العمليات')
@section('page-title', 'سجل العمليات')

@php
    $actions = [
        'created' => ['إنشاء', 'bg-success'],
        'updated' => ['تعديل', 'bg-warning text-dark'],
        'deleted' => ['حذف',  'bg-danger'],
    ];
    $types = ['credit' => 'مداخيل', 'debit' => 'الاستهلاك', 'activate' => 'التفعيلات'];
    $fields = [
        'client_id' => 'الفلاح (معرّف)', 'vannes_id' => 'العداد (معرّف)', 'plan_date' => 'التاريخ',
        'transfert_number' => 'رقم التحويل', 'amount' => 'المبلغ', 'old_counter' => 'العداد القديم',
        'new_counter' => 'العداد الجديد', 'consume' => 'الاستهلاك', 'reminder' => 'البقية',
        'type' => 'النوع', 'coupon_number' => 'رقم القسيمة', 'auto_number' => 'رقم تلقائي', 'locked' => 'مقفل', 'comment' => 'ملاحظة',
    ];
    $fmt = fn($v) => ($v === null || $v === '') ? '—' : (is_scalar($v) ? $v : json_encode($v));
@endphp

@section('content')

@unless($tableReady)
    <div class="alert alert-warning">
        جدول السجل غير موجود بعد. نفّذ <code>php artisan migrate --force</code> أو السكربت
        <code>docker/mysql/migrate_solde_history.sql</code>.
    </div>
@else
<div class="card">
    <div class="card-header flex-wrap gap-2">
        <h5><i class="bi bi-clock-history me-2"></i>سجل العمليات على الأرصدة</h5>
    </div>

    <div class="p-3 border-bottom">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">المستخدم</label>
                <select name="username" class="form-select form-select-sm">
                    <option value="">-- الكل --</option>
                    @foreach($usernames as $u)
                        <option value="{{ $u }}" {{ request('username') === $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">العملية</label>
                <select name="action" class="form-select form-select-sm no-select2">
                    <option value="">-- الكل --</option>
                    @foreach($actions as $k => $a)
                        <option value="{{ $k }}" {{ request('action') === $k ? 'selected' : '' }}>{{ $a[0] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">النوع</label>
                <select name="type" class="form-select form-select-sm no-select2">
                    <option value="">-- الكل --</option>
                    @foreach($types as $k => $t)
                        <option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">رقم السطر</label>
                <input type="number" name="solde_id" class="form-control form-control-sm" value="{{ request('solde_id') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">من تاريخ</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                <a href="{{ route('historique.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>التاريخ والساعة</th>
                    <th>المستخدم</th>
                    <th>العملية</th>
                    <th>النوع</th>
                    <th>رقم السطر</th>
                    <th>الفلاح</th>
                    <th>التفاصيل</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $h)
                <tr>
                    <td dir="ltr" class="text-nowrap" style="text-align:end">{{ $h->created_at?->format('d/m/Y H:i:s') }}</td>
                    <td class="fw-semibold">{{ $h->username ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $actions[$h->action][1] ?? 'bg-secondary' }}">{{ $actions[$h->action][0] ?? $h->action }}</span>
                    </td>
                    <td>{{ $types[$h->type] ?? '—' }}</td>
                    <td class="text-muted">{{ $h->solde_id }}</td>
                    <td>{{ $h->client?->name ?? ($h->client_id ? '#' . $h->client_id : '—') }}</td>
                    <td class="small">
                        @if($h->action === 'updated')
                            @foreach($h->changes ?? [] as $field => $pair)
                                <div>
                                    <span class="text-muted">{{ $fields[$field] ?? $field }} :</span>
                                    <span class="text-danger">{{ $fmt($pair[0] ?? null) }}</span>
                                    <i class="bi bi-arrow-left mx-1"></i>
                                    <span class="text-success">{{ $fmt($pair[1] ?? null) }}</span>
                                </div>
                            @endforeach
                        @else
                            @foreach(collect($h->changes ?? [])->filter(fn($v) => $v !== null && $v !== '') as $field => $val)
                                <span class="badge bg-light text-dark border me-1 mb-1">{{ $fields[$field] ?? $field }} : {{ $fmt($val) }}</span>
                            @endforeach
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">لا توجد عمليات مسجّلة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($history->hasPages())
    <div class="p-3">{{ $history->links() }}</div>
    @endif
</div>
@endunless

@endsection
