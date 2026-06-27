@extends('layouts.app')
@section('title', $client->name)
@section('page-title', $client->name)

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5><i class="bi bi-person me-2"></i>معلومات العميل</h5>
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i>
                </a>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">الاسم</dt>
                    <dd class="col-7 fw-semibold">{{ $client->name }}</dd>

                    <dt class="col-5 text-muted">رقم الملف</dt>
                    <dd class="col-7">{{ $client->num_dossier ?? '—' }}</dd>

                    <dt class="col-5 text-muted">CIN</dt>
                    <dd class="col-7">{{ $client->cin ?? '—' }}</dd>

                    <dt class="col-5 text-muted">الهاتف</dt>
                    <dd class="col-7">{{ $client->tel ?? '—' }}</dd>

                    <dt class="col-5 text-muted">الرصيد القديم</dt>
                    <dd class="col-7" dir="ltr" style="text-align:end">{{ number_format($lastReminder, 2) }} م²</dd>

                    <dt class="col-5 text-muted">الحالة {{ $year }}</dt>
                    <dd class="col-7">
                        @if($client->activations->where('year', $year)->count())
                            <span class="badge badge-active">نشط</span>
                        @else
                            <span class="badge badge-inactive">غير نشط</span>
                        @endif
                    </dd>

                    @if($client->comments)
                    <dt class="col-5 text-muted">ملاحظات</dt>
                    <dd class="col-7">{{ $client->comments }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5><i class="bi bi-tools me-2"></i>الصنابير</h5></div>
            <div class="card-body">
                @forelse($client->vannes as $vanne)
                    <span class="badge bg-primary mb-1">{{ $vanne->reference }}{{ $vanne->link }}</span>
                @empty
                    <p class="text-muted mb-0">لا يوجد صنبور مرتبط</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5><i class="bi bi-calendar-check me-2"></i>التفعيلات</h5></div>
            <div class="card-body">
                @forelse($client->activations->sortByDesc('year') as $act)
                    <span class="badge bg-success mb-1">{{ $act->year }}</span>
                @empty
                    <p class="text-muted mb-0">لا يوجد تفعيل</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-receipt me-2"></i>سجل العمليات</h5>
                <a href="{{ route('paiements.create') }}?client_id={{ $client->id }}" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-plus"></i> دفعة
                </a>
            </div>
            <div class="card-body pb-0">
                @if($soldesByYear->isEmpty())
                    <p class="text-muted text-center py-3">لا توجد عمليات</p>
                @else
                {{-- Tabs by year --}}
                <ul class="nav nav-tabs" id="yearTabs" role="tablist">
                    @foreach($soldesByYear as $yr => $soldesOfYear)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                id="tab-{{ $yr }}"
                                data-bs-toggle="tab"
                                data-bs-target="#pane-{{ $yr }}"
                                type="button" role="tab">
                            {{ $yr }}
                        </button>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="yearTabContent">
                    @foreach($soldesByYear as $yr => $soldesOfYear)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                         id="pane-{{ $yr }}" role="tabpanel">

                        @php
                            $byVanne = $soldesOfYear->groupBy(fn($s) => $s->vannes_id ?? 0);
                        @endphp

                        @foreach($byVanne as $vanneId => $soldesOfVanne)
                        @php $vanne = $soldesOfVanne->first()->vannes; @endphp
                        <h6 class="mt-3 mb-2 text-muted">
                            <i class="bi bi-droplet me-1"></i>
                            @if($vanne)
                                الصنبور : <strong>{{ $vanne->reference }}{{ $vanne->link }}</strong>
                            @else
                                بدون صنبور
                            @endif
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-3">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>النوع</th>
                                        <th>التاريخ</th>
                                        <th>المبلغ (د.ت)</th>
                                        <th>البقية (م²)</th>
                                        <th>مقفل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($soldesOfVanne as $solde)
                                    <tr>
                                        <td class="text-muted">{{ $solde->id }}</td>
                                        <td>
                                            @if($solde->type === 'credit')
                                                <span class="badge bg-success">دفعة</span>
                                            @elseif($solde->type === 'debit')
                                                <span class="badge bg-primary">استهلاك</span>
                                            @else
                                                <span class="badge bg-warning text-dark">تفعيل</span>
                                            @endif
                                        </td>
                                        <td>{{ $solde->date_transfert?->format('Y-m-d') ?? '—' }}</td>
                                        <td dir="ltr" class="text-end">
                                            @if($solde->type === 'debit')
                                                {{ number_format($solde->consume ?? 0, 2) }} م²
                                            @else
                                                {{ number_format($solde->amount ?? 0, 2) }} د.ت
                                            @endif
                                        </td>
                                        <td dir="ltr" class="text-end">{{ number_format($solde->reminder ?? 0, 2) }} م²</td>
                                        <td>
                                            @if($solde->locked)
                                                <i class="bi bi-lock-fill text-warning"></i>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endforeach

                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
