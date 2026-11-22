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

    {{-- Modules et actions autorisés (ignorés pour les مدير / مدير عام, qui ont tous les droits) --}}
    @php
        $modules = \App\Support\Permissions::modules();
        if (old('permissions') !== null) {
            $current = old('permissions');
        } elseif ($user && $user->permissions !== null) {
            $current = $user->permissions;
        } elseif ($user) {
            $current = \App\Support\Permissions::all();   // ancien compte sans réglage = accès complet
        } else {
            $current = \App\Support\Permissions::defaultForNewUser();
        }
        $allActions = \App\Support\Permissions::ACTION_LABELS;
    @endphp
    <div class="mb-4" id="permissions-box">
        <label class="form-label fw-semibold">الوحدات والعمليات المسموح بها</label>
        @unless(\Illuminate\Support\Facades\Schema::hasColumn('user', 'permissions'))
        <div class="alert alert-warning py-2 small mb-2">
            عمود الصلاحيات غير موجود بعد في قاعدة البيانات، لن يتم حفظ هذه الإعدادات.
            نفّذ <code>php artisan migrate --force</code>.
        </div>
        @endunless
        <div class="alert alert-info py-2 small mb-2" id="perm-admin-note" style="display:none">
            المدير والمدير العام لديهما كل الصلاحيات، ولا تُطبَّق الإعدادات أدناه عليهما.
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الوحدة</th>
                        @foreach($allActions as $act => $label)
                            <th class="text-center">{{ $label }}</th>
                        @endforeach
                        <th class="text-center">الكل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($modules as $key => $def)
                    <tr>
                        <td class="fw-semibold">{{ $def['label'] }}</td>
                        @foreach($allActions as $act => $label)
                            <td class="text-center">
                                @if(isset($def['actions'][$act]))
                                    <input type="checkbox" class="form-check-input perm-cb"
                                           name="permissions[{{ $key }}][]" value="{{ $act }}"
                                           title="{{ $def['actions'][$act] }}"
                                           {{ in_array($act, $current[$key] ?? []) ? 'checked' : '' }}>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input perm-row" title="تحديد الكل">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="form-text">
            «عرض» يسمح بدخول الصفحة، والباقي يتحكم في الأزرار والعمليات. صفحات المستخدمين وسجل العمليات مخصصة للمديرين فقط.
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i> {{ $user ? 'حفظ' : 'إضافة' }}
        </button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        var box = document.getElementById('permissions-box');
        var note = document.getElementById('perm-admin-note');

        function isAdminChecked() {
            return document.getElementById('role_ROLE_ADMIN').checked || document.getElementById('role_ROLE_SUPER_ADMIN').checked;
        }
        function refresh() {
            var admin = isAdminChecked();
            note.style.display = admin ? '' : 'none';
            // Cases désactivées visuellement mais toujours envoyées (réglage conservé si le compte redevient simple utilisateur)
            box.style.opacity = admin ? '.55' : '1';
            box.style.pointerEvents = admin ? 'none' : '';
        }
        function syncRows() {
            box.querySelectorAll('tbody tr').forEach(function (tr) {
                var cbs = tr.querySelectorAll('.perm-cb');
                tr.querySelector('.perm-row').checked = cbs.length > 0 && Array.prototype.every.call(cbs, function (c) { return c.checked; });
            });
        }

        box.addEventListener('change', function (e) {
            if (e.target.classList.contains('perm-row')) {
                e.target.closest('tr').querySelectorAll('.perm-cb').forEach(function (c) { c.checked = e.target.checked; });
            }
            syncRows();
        });
        document.querySelectorAll('input[name="roles[]"]').forEach(function (r) { r.addEventListener('change', refresh); });

        refresh();
        syncRows();
    })();
</script>
@endpush
