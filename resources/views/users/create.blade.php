@extends('layouts.app')
@section('title', 'إضافة مستخدم')
@section('page-title', 'إضافة مستخدم جديد')

@section('content')
<div class="card" style="max-width:500px">
    <div class="card-header">
        <h5><i class="bi bi-person-plus me-2"></i>مستخدم جديد</h5>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('users._form', ['user' => null])
    </div>
</div>
@endsection
