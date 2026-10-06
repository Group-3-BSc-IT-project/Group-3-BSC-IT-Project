<?php

class NotificationController
{
    public function __construct(
        private NotificationRepository $notifications
    ) {
    }

    public function all(): array
    {
        return $this->notifications->allForUser(Session::userId());
    }

    public function unread(): int
    {
        return $this->notifications->unreadCount(Session::userId());
    }

    public function read(int $id): bool
    {
        return $this->notifications->markRead($id, Session::userId());
    }

    public function readAll(): void
    {
        $this->notifications->markAllRead(Session::userId());
    }
}
