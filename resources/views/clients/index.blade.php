@extends('layouts.app')
@section('title', 'الفلاحين')
@section('page-title', 'الفلاحين')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-people me-2"></i>قائمة الفلاحين <span class="badge bg-secondary">{{ $clients->total() }}</span></h5>
        <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> إضافة فلاح
        </a>
    </div>
    <div class="card-body pb-0">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-sm-6 col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="بحث بالاسم، CIN، رقم الملف، الهاتف..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-sm-6 col-md-3">
                <select name="vannes[]" class="form-select form-select-sm" multiple data-placeholder="— تصفية بالصنبور —">
                    @foreach($vannes as $v)
                        <option value="{{ $v->id }}" {{ in_array($v->id, request('vannes', [])) ? 'selected' : '' }}>
                            {{ $v->reference }}{{ $v->link }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                @if(request('search') || request('vannes'))
                    <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-danger ms-1">
                        <i class="bi bi-x"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>رقم الملف</th>
                    <th>CIN</th>
                    <th>الهاتف</th>
                    <th>الصنابير</th>
                    <th>الحالة {{ $year }}</th>
                    <th>الرصيد القديم</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr>
                    <td class="text-muted">{{ $client->id }}</td>
                    <td><a href="{{ route('clients.show', $client) }}" class="fw-semibold text-decoration-none">{{ $client->name }}</a></td>
                    <td>{{ $client->num_dossier ?? '—' }}</td>
                    <td>{{ $client->cin ?? '—' }}</td>
                    <td>{{ $client->tel ?? '—' }}</td>
                    <td>
                        @foreach($client->vannes as $v)
                            <span class="badge bg-light text-dark border">{{ $v->reference }}{{ $v->link }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if($client->activations->where('year', $year)->count())
                            <span class="badge badge-active">نشط</span>
                        @else
                            <span class="badge badge-inactive">غير نشط</span>
                        @endif
                    </td>
                    <td>{{ number_format($client->old_solde, 2) }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                  onsubmit="return confirm('حذف الفلاح؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">لا يوجد فلاحين</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())
    <div class="card-body pt-2">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection
