@extends('layouts.app')
@section('title', 'المستخدمون')
@section('page-title', 'إدارة المستخدمين')

@section('content')
<div class="card" style="max-width:700px">
    <div class="card-header">
        <h5><i class="bi bi-person-gear me-2"></i>المستخدمون</h5>
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> إضافة مستخدم
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم المستخدم</th>
                    <th>الصلاحيات</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-muted">{{ $user->id }}</td>
                    <td class="fw-semibold">{{ $user->username }}</td>
                    <td>
                        @foreach($user->roles ?? [] as $role)
                            <span class="badge {{ $role === 'ROLE_SUPER_ADMIN' ? 'bg-danger' : ($role === 'ROLE_ADMIN' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ $role }}
                            </span>
                        @endforeach
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->username !== auth()->user()->username)
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                  onsubmit="return confirm('حذف المستخدم؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">لا يوجد مستخدمون</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-body pt-2">{{ $users->links() }}</div>
    @endif
</div>
@endsection
