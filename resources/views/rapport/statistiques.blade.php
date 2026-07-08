@extends('layouts.app')
@section('title', 'الإحصائيات')
@section('page-title', 'إحصائيات ' . $year)

@section('content')
<div class="card mb-4" style="max-width:300px">
    <div class="card-body">
        <form method="GET">
            <label class="form-label fw-semibold">السنة</label>
            <div class="input-group input-group-sm">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                    <option value="{{ date('Y') }}" {{ date('Y') == $year ? 'selected' : '' }}>{{ date('Y') }}</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e3f2fd;color:#1565c0"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-value">{{ $totalClients }}</div>
                <div class="stat-label">إجمالي الفلاحين</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32"><i class="bi bi-person-check-fill"></i></div>
            <div>
                <div class="stat-value">{{ $activeClients }}</div>
                <div class="stat-label">نشطون في {{ $year }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fce4ec;color:#c62828"><i class="bi bi-person-x-fill"></i></div>
            <div>
                <div class="stat-value">{{ $inactiveClients }}</div>
                <div class="stat-label">غير نشطين</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff3e0;color:#e65100"><i class="bi bi-droplet-half"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalConsoM3) }} م³</div>
                <div class="stat-label">الاستهلاك الإجمالي</div>
            </div>
        </div>
    </div>
</div>

<div class="card" style="max-width:600px">
    <div class="card-header"><h5><i class="bi bi-calculator me-2"></i>الملخص المالي {{ $year }}</h5></div>
    <div class="card-body">
        <table class="table">
            <tbody>
                <tr>
                    <td>سعر م³</td>
                    <td class="fw-semibold text-end">{{ number_format($price, 3) }} د.ت</td>
                </tr>
                <tr>
                    <td>رسوم الاشتراك</td>
                    <td class="fw-semibold text-end">{{ number_format($activationFee, 2) }} د.ت</td>
                </tr>
                <tr>
                    <td>فرائض الاشتراك ({{ $activeClients }} × {{ number_format($activationFee, 2) }})</td>
                    <td class="fw-semibold text-end text-primary">{{ number_format($fraisAbonnement, 2) }} د.ت</td>
                </tr>
                <tr>
                    <td>إجمالي مداخيل</td>
                    <td class="fw-semibold text-end text-success">{{ number_format($montantCredite, 2) }} د.ت</td>
                </tr>
                <tr>
                    <td>قيمة الاستهلاك ({{ number_format($totalConsoM3) }} م³)</td>
                    <td class="fw-semibold text-end text-info">{{ number_format($montantDebite, 2) }} د.ت</td>
                </tr>
                <tr class="table-warning">
                    <td class="fw-bold">الإجمالي</td>
                    <td class="fw-bold text-end fs-5">{{ number_format($totalMontant, 2) }} د.ت</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

