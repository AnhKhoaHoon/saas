<?php

namespace App\Notifications;

use App\Models\TeamInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInviteNotification extends Notification
{
    use Queueable;

    public function __construct(public TeamInvite $invite)
    {
        // Load project và inviter để email có đủ ngữ cảnh hiển thị.
        $this->invite->loadMissing(['project', 'inviter']);
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Gửi invite qua email.
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Tạo URL accept invite từ token đã lưu trong database.
        $acceptUrl = route('team-invites.accept', $this->invite->token);

        // Trả về nội dung email invite.
        return (new MailMessage)
            // Tiêu đề email.
            ->subject("You're invited to {$this->invite->project->name} on KeyForge")
            // Dòng chào mở đầu.
            ->greeting('You have been invited to KeyForge')
            // Nêu rõ ai đã mời và project nào.
            ->line("{$this->invite->inviter?->name} invited you to join {$this->invite->project->name}.")
            // Nêu role sẽ được cấp khi accept.
            ->line("Role: {$this->invite->role}")
            // Nêu thời hạn link nếu có.
            ->line('This invite expires '.$this->invite->expires_at?->diffForHumans().'.')
            // Nút accept invite.
            ->action('Accept invite', $acceptUrl)
            // Nhắc user không cần làm gì nếu không nhận ra invite.
            ->line('If you did not expect this invite, you can safely ignore this email.');
    }
}
