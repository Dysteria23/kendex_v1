<div class="relative" wire:poll.20s x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
    <button type="button" @click="open = !open" class="relative rounded-md border border-white/40 px-3 py-1.5 text-sm text-white hover:bg-white/10"
            :aria-expanded="open" aria-haspopup="true">
        Avisos
        @if ($unread > 0)
            <span class="ml-1 inline-flex min-w-5 justify-center rounded-full bg-amber-400 px-1.5 text-xs font-bold text-ink" aria-label="{{ $unread }} sin leer">{{ $unread }}</span>
        @endif
    </button>

    <div x-show="open" x-cloak class="absolute right-0 z-40 mt-2 w-80 max-w-[85vw] rounded-lg border border-line bg-white text-ink shadow-lg">
        <div class="flex items-center justify-between border-b border-line px-4 py-2">
            <p class="text-sm font-semibold">Notificaciones</p>
            @if ($unread > 0)<button type="button" wire:click="markAll" class="kx-link text-xs">Marcar todas como leídas</button>@endif
        </div>
        <ul class="max-h-80 overflow-y-auto">
            @forelse ($items as $n)
                <li wire:key="n-{{ $n->id }}" class="border-b border-line last:border-0">
                    <button type="button" wire:click="markRead({{ $n->id }})" class="block w-full px-4 py-3 text-left text-sm hover:bg-slate-50">
                        <span class="{{ $n->read_at ? 'text-muted' : 'font-semibold' }}">{{ $n->message }}</span>
                        <span class="mt-1 block text-xs text-muted">@dt($n->created_at)</span>
                    </button>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-sm text-muted">No tienes notificaciones.</li>
            @endforelse
        </ul>
    </div>
</div>
