@extends('layouts.app')
@section('title', $config['label'])
@section('page-title', $config['label'])

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-receipt me-2"></i>{{ $config['label'] }} <span class="badge bg-secondary">{{ $soldes->total() }}</span></h5>
        <div class="d-flex gap-2">
            @if($type === 'debit')
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#printModal">
                <i class="bi bi-printer me-1"></i> طباعة التخطيط
            </button>
            @endif
            <a href="{{ route($config['prefix'] . '.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> إضافة
            </a>
        </div>
    </div>
    <div class="card-body pb-0">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-sm-4">
                <select name="client_id" class="form-select form-select-sm">
                    <option value="">كل العملاء</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                            {{ $c }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <input type="date" name="date_from" class="form-control form-control-sm"
                       value="{{ request('date_from') }}" placeholder="من">
            </div>
            <div class="col-sm-2">
                <input type="date" name="date_to" class="form-control form-control-sm"
                       value="{{ request('date_to') }}" placeholder="إلى">
            </div>
            @if($type === 'activate')
            <div class="col-sm-2">
                <select name="year" class="form-select form-select-sm">
                    <option value="">كل السنوات</option>
                    @foreach(range(date('Y'), 2020) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                @if(request()->anyFilled(['client_id','date_from','date_to','locked','year']))
                    <a href="{{ route($config['prefix'] . '.index') }}" class="btn btn-sm btn-outline-danger ms-1"><i class="bi bi-x"></i></a>
                @endif
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    @if($type === 'debit') <th>الصنبور</th> @endif
                    <th>التاريخ</th>
                    @if($type === 'credit') <th>المبلغ</th> @endif
                    @if($type === 'debit') <th>تاريخ التخطيط</th><th>القديم</th><th>الجديد</th><th>الاستهلاك</th> @endif
                    <th>البقية</th>
                    <th>رقم التحويل</th>
                    <th>مقفل</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($soldes as $solde)
                <tr>
                    <td class="text-muted">{{ $solde->id }}</td>
                    <td>
                        @if($solde->client)
                            <a href="{{ route('clients.show', $solde->client) }}" class="text-decoration-none">
                                {{ $solde->client->name }}
                            </a>
                        @else —
                        @endif
                    </td>
                    @if($type === 'debit')
                    <td>{{ $solde->vannes ? $solde->vannes->reference . ($solde->vannes->link ?? '') : '—' }}</td>
                    @endif
                    <td>{{ $solde->date_transfert?->format('Y-m-d') ?? '—' }}</td>
                    @if($type === 'credit')
                    <td class="fw-semibold text-success" dir="ltr" style="text-align:end">{{ number_format($solde->amount, 2) }} د.ت</td>
                    @endif
                    @if($type === 'debit')
                    <td>{{ $solde->plan_date?->format('Y-m-d') ?? '—' }}</td>
                    <td dir="ltr" style="text-align:end">{{ $solde->old_counter }}</td>
                    <td dir="ltr" style="text-align:end">{{ $solde->new_counter }}</td>
                    <td class="fw-semibold text-primary" dir="ltr" style="text-align:end">{{ number_format($solde->consume) }} م²</td>
                    @endif
                    <td dir="ltr" style="text-align:end">{{ number_format($solde->reminder ?? 0, 2) }} م²</td>
                    <td>{{ $solde->transfert_number ?? '—' }}</td>
                    <td>
                        @if($solde->locked)
                            <i class="bi bi-lock-fill text-warning"></i>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route($config['prefix'] . '.edit', $solde) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route($config['prefix'] . '.destroy', $solde) }}"
                                  onsubmit="return confirm('حذف هذه العملية؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-muted py-4">لا توجد عمليات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($soldes->hasPages())
    <div class="card-body pt-2">{{ $soldes->links() }}</div>
    @endif
</div>
@endsection

@if($type === 'debit')
{{-- Print Planning Modal --}}
<div class="modal fade" id="printModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-printer me-2"></i>طباعة التخطيط</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">اختر تاريخ التخطيط</label>
                <input type="date" id="printDate" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="openPrint()">
                    <i class="bi bi-printer me-1"></i> طباعة
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openPrint() {
    const date = document.getElementById('printDate').value;
    if (!date) return;
    window.open('{{ route("consommation.print") }}?date=' + date, '_blank');
}
</script>
@endpush
@endif
