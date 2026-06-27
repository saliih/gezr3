@extends('layouts.app')
@section('title', 'الصنابير')
@section('page-title', 'الصنابير')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-tools me-2"></i>الصنابير <span class="badge bg-secondary">{{ $vannes->total() }}</span></h5>
        <a href="{{ route('vannes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> إضافة صنبور
        </a>
    </div>
    <div class="card-body pb-0">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-sm-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="بحث بالمرجع..." value="{{ request('search') }}">
            </div>
            <div class="col-sm-3">
                <select name="enable" class="form-select form-select-sm">
                    <option value="">كل الحالات</option>
                    <option value="1" {{ request('enable') === '1' ? 'selected' : '' }}>مفعّل</option>
                    <option value="0" {{ request('enable') === '0' ? 'selected' : '' }}>معطّل</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                @if(request()->anyFilled(['search','enable']))
                    <a href="{{ route('vannes.index') }}" class="btn btn-sm btn-outline-danger ms-1"><i class="bi bi-x"></i></a>
                @endif
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصنبور</th>
                    <th>الحالة</th>
                    <th>مستهلك</th>
                    <th>آخر قيمة</th>
                    <th>العملاء</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($vannes as $vanne)
                <tr>
                    <td class="text-muted">{{ $vanne->id }}</td>
                    <td class="fw-semibold"><a href="{{ route('vannes.show', $vanne) }}" class="text-decoration-none">{{ $vanne }}</a></td>
                    <td>
                        @if($vanne->enable)
                            <span class="badge badge-active">مفعّل</span>
                        @else
                            <span class="badge badge-inactive">معطّل</span>
                        @endif
                    </td>
                    <td>
                        @if($vanne->consumed)
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                            <i class="bi bi-circle text-muted"></i>
                        @endif
                    </td>
                    <td>{{ $vanne->last_value ?? 0 }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $vanne->clients_count }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('vannes.show', $vanne) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('vannes.edit', $vanne) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('vannes.destroy', $vanne) }}"
                                  onsubmit="return confirm('حذف الصنبور؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">لا يوجد صنابير</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($vannes->hasPages())
    <div class="card-body pt-2">{{ $vannes->links() }}</div>
    @endif
</div>
@endsection
