@php $avatar = auth()->user()->avatar; @endphp
@if($avatar)
    <div class="flex items-center gap-4 mb-2">
        <img src="{{ asset('storage/' . $avatar) }}"
             alt="Foto atual"
             class="w-20 h-20 rounded-full object-cover border-2 border-gray-600">
        <span class="text-sm text-gray-400">Foto atual — use o campo abaixo para alterar</span>
    </div>
@endif
