@extends('layouts.app')
@section('title', 'تعديل مستخدم')
@section('page-title', 'تعديل: ' . $user->username)

@section('content')
<div class="card" style="max-width:500px">
    <div class="card-header">
        <h5><i class="bi bi-pencil me-2"></i>{{ $user->username }}</h5>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-right"></i> رجوع
        </a>
    </div>
    <div class="card-body">
        @include('users._form', ['user' => $user])
    </div>
</div>
@endsection
