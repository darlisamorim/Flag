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

    $avatarUrl = $user->avatar
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF&size=64';

    $profileUrl = \App\Filament\Resources\UserResource::getUrl('edit', ['record' => $user->id]);
    $logoutUrl  = \Filament\Facades\Filament::getLogoutUrl();
@endphp

<div style="border-top:1px solid rgba(255,255,255,0.08);padding:12px 16px 16px;display:flex;flex-direction:column;gap:10px;">

    {{-- Avatar + Nome + Nível --}}
    <div style="display:flex;align-items:center;gap:10px;">
        <img
            src="{{ $avatarUrl }}"
            alt="{{ $user->name }}"
            style="width:38px;height:38px;border-radius:9999px;object-fit:cover;flex-shrink:0;border:2px solid rgba(255,255,255,0.1);"
        />
        <div style="min-width:0;flex:1;">
            <div style="font-size:13px;font-weight:600;color:var(--fi-clr-gray-200,#e5e7eb);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $user->name }}
            </div>
            <span style="{{ $levelColor }}padding:1px 7px;border-radius:9999px;font-size:10px;font-weight:500;display:inline-block;margin-top:2px;">
                {{ $levelLabel }}
            </span>
        </div>
    </div>

    {{-- Botões: Meu Perfil | Sair --}}
    <div style="display:flex;gap:6px;">
        <a
            href="{{ $profileUrl }}"
            style="flex:1;display:flex;align-items:center;justify-content:center;gap:5px;padding:6px 8px;border-radius:8px;font-size:12px;font-weight:500;color:var(--fi-clr-gray-300,#d1d5db);background:rgba(255,255,255,0.05);text-decoration:none;transition:background .15s;"
            onmouseover="this.style.background='rgba(255,255,255,0.1)'"
            onmouseout="this.style.background='rgba(255,255,255,0.05)'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Meu Perfil
        </a>

        <form method="POST" action="{{ $logoutUrl }}" style="flex:1;">
            @csrf
            <button
                type="submit"
                style="width:100%;display:flex;align-items:center;justify-content:center;gap:5px;padding:6px 8px;border-radius:8px;font-size:12px;font-weight:500;color:var(--fi-clr-gray-300,#d1d5db);background:rgba(255,255,255,0.05);border:none;cursor:pointer;transition:background .15s;"
                onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                onmouseout="this.style.background='rgba(255,255,255,0.05)'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                </svg>
                Sair
            </button>
        </form>
    </div>

</div>