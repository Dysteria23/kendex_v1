<?php

namespace App\Livewire\Notifications;

use App\Models\UserNotification;
use Livewire\Component;

/** Centro de notificaciones in-app con actualización periódica (RF-75). */
class Bell extends Component
{
    public bool $open = false;

    public function markRead(int $id): mixed
    {
        $n = UserNotification::where('user_id', auth()->id())->whereKey($id)->first();
        $n?->update(['read_at' => now()]);

        return $n?->url ? $this->redirect($n->url, navigate: true) : null;
    }

    public function markAll(): void
    {
        UserNotification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function render()
    {
        return view('livewire.notifications.bell', [
            'items' => UserNotification::where('user_id', auth()->id())->orderByDesc('id')->limit(10)->get(),
            'unread' => UserNotification::where('user_id', auth()->id())->whereNull('read_at')->count(),
        ]);
    }
}
