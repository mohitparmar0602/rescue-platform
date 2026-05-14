<?php

namespace App\Livewire;

use App\Events\NewMessageReceived;
use App\Models\Agency;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AgencyChat extends Component
{
    // ── State ────────────────────────────────────────────────────────────
    public ?int    $activeConversationAgencyId = null;
    public string  $newMessage = '';
    public array   $messages   = [];
    public array   $sidebar    = [];  // [{agency, unread, last_message, last_at}]

    // ── Mount ────────────────────────────────────────────────────────────
    public function mount(?int $withAgency = null): void
    {
        $this->loadSidebar();

        if ($withAgency) {
            $this->openConversation($withAgency);
        } elseif (count($this->sidebar) > 0) {
            $this->openConversation($this->sidebar[0]['agency_id']);
        }
    }

    // ── Load sidebar conversation list ───────────────────────────────────
    public function loadSidebar(): void
    {
        $myAgencyId = Auth::user()->agency_id;
        if (! $myAgencyId) {
            $this->sidebar = [];
            return;
        }

        // All agencies that have sent us a message OR that we've sent to
        $partnerIds = Message::query()
            ->where(function ($q) use ($myAgencyId) {
                // Messages WE sent (our users as sender, their agency as receiver)
                $q->whereHas('sender', fn ($u) => $u->where('agency_id', $myAgencyId));
            })
            ->orWhere('receiver_agency_id', $myAgencyId)
            ->selectRaw('
                CASE
                    WHEN receiver_agency_id = ? THEN
                        (SELECT agency_id FROM users WHERE id = sender_id LIMIT 1)
                    ELSE receiver_agency_id
                END as partner_id,
                MAX(created_at) as last_at
            ', [$myAgencyId])
            ->groupBy('partner_id')
            ->orderByDesc('last_at')
            ->get();

        $sidebar = [];
        $seen    = [];

        foreach ($partnerIds as $row) {
            $partnerId = (int) $row->partner_id;
            if (! $partnerId || $partnerId === $myAgencyId || isset($seen[$partnerId])) {
                continue;
            }
            $seen[$partnerId] = true;

            $agency = Agency::find($partnerId);
            if (! $agency) continue;

            // Unread count — messages sent TO us (receiver = my agency) by this partner that aren't read
            $unread = Message::where('receiver_agency_id', $myAgencyId)
                ->whereHas('sender', fn ($u) => $u->where('agency_id', $partnerId))
                ->where('is_read', false)
                ->count();

            // Last message preview
            $lastMsg = Message::where(function ($q) use ($myAgencyId, $partnerId) {
                    $q->where('receiver_agency_id', $myAgencyId)
                      ->whereHas('sender', fn ($u) => $u->where('agency_id', $partnerId));
                })
                ->orWhere(function ($q) use ($myAgencyId, $partnerId) {
                    $q->where('receiver_agency_id', $partnerId)
                      ->whereHas('sender', fn ($u) => $u->where('agency_id', $myAgencyId));
                })
                ->latest()
                ->first();

            $sidebar[] = [
                'agency_id'    => $agency->id,
                'agency_name'  => $agency->name,
                'agency_type'  => $agency->type,
                'unread'       => $unread,
                'last_preview' => $lastMsg ? mb_substr($lastMsg->body, 0, 55) : '',
                'last_at'      => $lastMsg?->created_at?->diffForHumans() ?? '',
            ];
        }

        $this->sidebar = $sidebar;
    }

    // ── Open a conversation thread ────────────────────────────────────────
    public function openConversation(int $agencyId): void
    {
        $this->activeConversationAgencyId = $agencyId;
        $this->loadMessages();
        $this->markRead($agencyId);
    }

    // ── Load messages for active thread ──────────────────────────────────
    public function loadMessages(): void
    {
        if (! $this->activeConversationAgencyId) {
            $this->messages = [];
            return;
        }

        $myAgencyId    = Auth::user()->agency_id;
        $partnerAgency = $this->activeConversationAgencyId;

        $rows = Message::with('sender')
            ->where(function ($q) use ($myAgencyId, $partnerAgency) {
                // Messages WE sent to them
                $q->where('receiver_agency_id', $partnerAgency)
                  ->whereHas('sender', fn ($u) => $u->where('agency_id', $myAgencyId));
            })
            ->orWhere(function ($q) use ($myAgencyId, $partnerAgency) {
                // Messages THEY sent to us
                $q->where('receiver_agency_id', $myAgencyId)
                  ->whereHas('sender', fn ($u) => $u->where('agency_id', $partnerAgency));
            })
            ->orderBy('created_at')
            ->get();

        $this->messages = $rows->map(fn (Message $m) => [
            'id'           => $m->id,
            'body'         => $m->body,   // accessor decrypts automatically
            'sender_name'  => $m->sender->name,
            'sender_agency'=> $m->sender->agency_id,
            'mine'         => $m->sender->agency_id === $myAgencyId,
            'sent_at'      => $m->created_at->format('H:i'),
            'sent_date'    => $m->created_at->format('d M'),
        ])->toArray();
    }

    // ── Mark messages from this partner as read ───────────────────────────
    public function markRead(int $partnerAgencyId): void
    {
        $myAgencyId = Auth::user()->agency_id;

        Message::where('receiver_agency_id', $myAgencyId)
            ->whereHas('sender', fn ($u) => $u->where('agency_id', $partnerAgencyId))
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Refresh unread count in sidebar
        $this->loadSidebar();
    }

    // ── Send a message ────────────────────────────────────────────────────
    public function send(): void
    {
        $this->validate([
            'newMessage' => 'required|string|max:2000',
        ]);

        if (! $this->activeConversationAgencyId) return;

        $message = Message::create([
            'sender_id'          => Auth::id(),
            'receiver_agency_id' => $this->activeConversationAgencyId,
            'body'               => $this->newMessage,  // mutator encrypts
        ]);

        // Eager load sender + their agency for the event constructor
        $message->load('sender.agency');

        broadcast(new NewMessageReceived($message))->toOthers();

        $this->newMessage = '';
        $this->loadMessages();
        $this->loadSidebar();
    }

    // ── Real-time: called from the browser when a WS message arrives ──────
    // (Livewire 3+ supports $wire.call via Alpine listeners)
    public function refreshOnIncoming(int $fromAgencyId): void
    {
        if ($this->activeConversationAgencyId === $fromAgencyId) {
            $this->loadMessages();
            $this->markRead($fromAgencyId);
        } else {
            $this->loadSidebar(); // just bump unread badge
        }
    }

    // ── Start a brand-new conversation (called from map popup "Send Message") 
    public function startConversation(int $agencyId): void
    {
        $this->openConversation($agencyId);
    }

    public function render()
    {
        return view('livewire.agency-chat');
    }
}
