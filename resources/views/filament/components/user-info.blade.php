@php
    $user = auth()->user();
    $levelLabel = match($user->access_level) {
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin',
        'editor'      => 'Editor',
        default       => $user->access_level,
    };
    $levelColor = match($user->access_level) {
        'super_admin' => 'background:#7f1d1d;color:#fca5a5;',
        'admin'       => 'background:#1e3a5f;color:#93c5fd;',
        default       => 'background:#3b2f00;color:#fcd34d;',
    };
@endphp

<div style="display:flex;align-items:center;gap:8px;margin-right:4px;">
    <div style="text-align:right;line-height:1.3;">
        <div style="font-size:13px;font-weight:600;color:var(--fi-clr-gray-200, #e5e7eb);">
            {{ $user->name }}
        </div>
        <span style="{{ $levelColor }}padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;">
            {{ $levelLabel }}
        </span>
    </div>
</div>
