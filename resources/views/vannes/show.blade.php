@extends('layouts.app')
@section('title', 'صنبور #' . $vanne->reference)
@section('page-title', 'صنبور ' . $vanne->reference . ($vanne->link ? ' ' . $vanne->link : ''))

@section('content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="bi bi-tools me-2"></i>معلومات الصنبور</h5>
                <a href="{{ route('vannes.edit', $vanne) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i>
                </a>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">المرجع</dt>
                    <dd class="col-7 fw-semibold">{{ $vanne->reference }}</dd>
                    <dt class="col-5 text-muted">الرابط</dt>
                    <dd class="col-7">{{ $vanne->link ?? '—' }}</dd>
                    <dt class="col-5 text-muted">الحالة</dt>
                    <dd class="col-7">
                        @if($vanne->enable)
                            <span class="badge badge-active">مفعّل</span>
                        @else
                            <span class="badge badge-inactive">معطّل</span>
                        @endif
                    </dd>
                    <dt class="col-5 text-muted">آخر قيمة</dt>
                    <dd class="col-7">{{ $vanne->last_value ?? 0 }}</dd>
                    <dt class="col-5 text-muted">مستهلك</dt>
                    <dd class="col-7">{{ $vanne->consumed ? 'نعم' : 'لا' }}</dd>
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-people me-2"></i>الفلاحين</h5></div>
            <div class="card-body">
                @forelse($vanne->clients as $c)
                    <a href="{{ route('clients.show', $c) }}" class="badge bg-light text-dark border text-decoration-none mb-1 d-inline-block">
                        {{ $c->name }}
                    </a>
                @empty
                    <p class="text-muted mb-0">لا يوجد فلاحين</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-speedometer2 me-2"></i>التفاصيل الكاملة</h5></div>

            {{-- Summary --}}
            <div class="table-responsive border-bottom">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">المستهلك</th>
                            <th class="text-center">عدد الريات</th>
                            <th class="text-center">منوسط الاستهلاك</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center fw-semibold">{{ number_format($stats['total']) }} م²</td>
                            <td class="text-center">{{ $stats['count'] }}</td>
                            <td class="text-center">{{ number_format($stats['average'], 2) }} م²</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Detail --}}
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>تاريخ السقي</th>
                            <th>الاسم</th>
                            <th>البداية</th>
                            <th>النهاية</th>
                            <th>المستهلك</th>
                            <th>تاريخ التسجيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vanne->soldes as $s)
                        <tr>
                            <td>{{ $s->date_transfert?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($s->client)
                                    <a href="{{ route('clients.show', $s->client) }}" class="text-decoration-none">
                                        {{ $s->client }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $s->old_counter ?? '—' }}</td>
                            <td>{{ $s->new_counter ?? '—' }}</td>
                            <td class="fw-semibold text-primary">{{ number_format($s->consume) }} م²</td>
                            <td class="text-muted">{{ $s->created_at?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">لا توجد قراءات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
