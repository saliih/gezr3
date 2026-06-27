@php
    $action = $user ? route('users.update', $user) : route('users.store');
@endphp

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if($user) @method('PUT') @endif

    <div class="mb-3">
        <label class="form-label fw-semibold">اسم المستخدم <span class="text-danger">*</span></label>
        <input type="text" name="username" class="form-control" required autocomplete="off"
               value="{{ old('username', $user?->username) }}">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">كلمة المرور {{ $user ? '(اتركها فارغة لعدم التغيير)' : '*' }}</label>
        <input type="password" name="password" class="form-control" autocomplete="new-password"
               {{ $user ? '' : 'required' }}>
    </div>
    <div class="mb-4">
        <label class="form-label fw-semibold">تأكيد كلمة المرور</label>
        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
    </div>

    <div class="mb-4">
        <label class="form-label fw-semibold">الصلاحيات</label>
        @php $userRoles = old('roles', $user?->roles ?? ['ROLE_USER']); @endphp
        @foreach(['ROLE_USER' => 'مستخدم', 'ROLE_ADMIN' => 'مدير', 'ROLE_SUPER_ADMIN' => 'مدير عام'] as $role => $label)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]"
                   value="{{ $role }}" id="role_{{ $role }}"
                   {{ in_array($role, $userRoles) ? 'checked' : '' }}>
            <label class="form-check-label" for="role_{{ $role }}">{{ $label }} ({{ $role }})</label>
        </div>
        @endforeach
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ $user ? 'حفظ' : 'إضافة' }}
        </button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
    </div>
</form>
