@extends('layouts.app')
@section('title', 'أسعار م³')
@section('page-title', 'أسعار المتر المكعب')

@section('content')
<div class="card" style="max-width:700px">
    <div class="card-header">
        <h5><i class="bi bi-currency-dollar me-2"></i>الأسعار السنوية</h5>
        <a href="{{ route('prix-m3.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> إضافة سنة
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>السنة</th>
                    <th>السعر (د.ت/م³)</th>
                    <th>رسوم الاشتراك (د.ت)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($prixList as $prix)
                <tr>
                    <td class="fw-semibold">{{ $prix->year }}</td>
                    <td>{{ number_format($prix->price, 3) }}</td>
                    <td>{{ $prix->activation_fee ? number_format($prix->activation_fee, 2) : '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('prix-m3.edit', $prix) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('prix-m3.destroy', $prix) }}"
                                  onsubmit="return confirm('حذف السعر؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">لا توجد أسعار</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

