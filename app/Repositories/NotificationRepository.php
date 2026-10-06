<?php
class NotificationRepository
{
    public function __construct(private PDO $db) {}
    public function create(Notification $n,string $type='booking'): bool { $s=$this->db->prepare('INSERT INTO notifications(user_id,message,notification_type) VALUES(?,?,?)');return$s->execute([$n->userId(),$n->message(),$type]); }
    public function markRead(int $id,int $userId): bool { $s=$this->db->prepare('UPDATE notifications SET is_read=1 WHERE notification_id=? AND user_id=?');$s->execute([$id,$userId]);return$s->rowCount()>0;}
    public function markAllRead(int $userId): void { $s=$this->db->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?');$s->execute([$userId]); }
    public function unreadCount(int $userId): int { $s=$this->db->prepare('SELECT COUNT(*) total FROM notifications WHERE user_id=? AND is_read=0');$s->execute([$userId]);return(int)$s->fetch()['total'];}
    public function allForUser(int $userId): array { $s=$this->db->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC');$s->execute([$userId]);return$s->fetchAll();}
}
