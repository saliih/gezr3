@extends('layouts.app')
@section('title', 'لوحة التحكم')
@section('page-title', 'لوحة التحكم')

@section('content')

{{-- Year selector --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h5 class="mb-0 text-muted">
        <i class="bi bi-bar-chart-line me-1"></i>إحصائيات سنة
        <span class="text-primary fw-bold">{{ $year }}</span>
        @if($year === $currentYear)
            <span class="badge bg-success ms-1 small">الحالية</span>
        @endif
    </h5>
    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
        <label class="mb-0 small fw-semibold text-muted">تغيير السنة:</label>
        <select name="year" class="form-select form-select-sm no-select2" style="width:110px" onchange="this.form.submit()">
            @foreach($availableYears as $y)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endforeach
            @if($availableYears->isEmpty())
                <option value="{{ $currentYear }}" selected>{{ $currentYear }}</option>
            @endif
        </select>
        @if($year !== $currentYear)
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-counterclockwise me-1"></i>{{ $currentYear }}
        </a>
        @endif
    </form>
</div>

{{-- Stats cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e3f2fd; color:#1565c0">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($totalClients) }}</div>
                <div class="stat-label">إجمالي الفلاحين</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5e9; color:#2e7d32">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($activeClients) }}</div>
                <div class="stat-label">فلاحين نشطون {{ $year }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff3e0; color:#e65100">
                <i class="bi bi-tools"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($totalVannes) }}</div>
                <div class="stat-label">العدادات ({{ $vannesEnabled }} مفعّل)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fce4ec; color:#c62828">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($totalPaiements, 2) }}</div>
                <div class="stat-label">إجمالي مداخيل {{ $year }} (د.ت)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8eaf6; color:#283593">
                <i class="bi bi-droplet-half"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($totalConsoM3) }} م³</div>
                <div class="stat-label">إجمالي الاستهلاك {{ $year }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f3e5f5; color:#6a1b9a">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($totalConsoVal, 2) }}</div>
                <div class="stat-label">قيمة الاستهلاك {{ $year }} (د.ت)</div>
            </div>
        </div>
    </div>
</div>

{{-- Recent operations --}}
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-clock-history me-2"></i>آخر العمليات — {{ $year }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الفلاح</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentSoldes as $solde)
                <tr>
                    <td class="text-muted">{{ $solde->id }}</td>
                    <td>{{ $solde->client?->name ?? '—' }}</td>
                    <td>
                        @if($solde->type === 'credit')
                            <span class="badge bg-success">دفعة</span>
                        @elseif($solde->type === 'debit')
                            <span class="badge bg-primary">استهلاك</span>
                        @else
                            <span class="badge bg-warning text-dark">تفعيل</span>
                        @endif
                    </td>
                    <td>
                        @if($solde->type === 'credit')
                            {{ number_format($solde->amount, 2) }} د.ت
                        @elseif($solde->type === 'debit')
                            {{ number_format($solde->consume) }} م³
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-muted">{{ $solde->date_transfert?->format('Y-m-d') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">لا توجد عمليات لسنة {{ $year }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
