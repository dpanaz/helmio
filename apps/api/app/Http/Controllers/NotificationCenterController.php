<?php

namespace App\Http\Controllers;

use App\Services\Notifications\WebPushService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;
use Throwable;

class NotificationCenterController extends Controller
{
    public function __construct(
        private readonly WebPushService $webPushService,
    ) {
    }

    public function index(
        Request $request,
    ): View {
        $notifications =
            $request
                ->user()
                ->notifications()
                ->latest()
                ->paginate(25);

        return view(
            'notifications.index',
            [
                'notifications' =>
                    $notifications,

                'unreadCount' =>
                    $request
                        ->user()
                        ->unreadNotifications()
                        ->count(),
            ],
        );
    }

    public function read(
        Request $request,
        DatabaseNotification $notification,
    ): RedirectResponse {
        $this->authorizeNotification(
            $request,
            $notification,
        );

        $notification->markAsRead();

        $this->syncBadge(
            $request,
        );

        $actionUrl =
            $notification->data['action_url']
            ?? route(
                'notifications.index',
            );

        return redirect()->to(
            $actionUrl,
        );
    }

    public function markAllRead(
        Request $request,
    ): RedirectResponse {
        $request
            ->user()
            ->unreadNotifications
            ->markAsRead();

        $this->syncBadge(
            $request,
        );

        return back()->with(
            'success',
            'All notifications marked as read.',
        );
    }

    public function destroy(
        Request $request,
        DatabaseNotification $notification,
    ): RedirectResponse {
        $this->authorizeNotification(
            $request,
            $notification,
        );

        $notification->delete();

        $this->syncBadge(
            $request,
        );

        return back()->with(
            'success',
            'Notification removed.',
        );
    }

    private function syncBadge(
        Request $request,
    ): void {
        $user =
            $request->user();

        $unreadCount =
            $user
                ->unreadNotifications()
                ->count();

        try {
            $this->webPushService
                ->sendToUser(
                    $user,
                    [
                        'type' =>
                            'badge_sync',

                        'silent' =>
                            true,

                        'unread_count' =>
                            $unreadCount,

                        'event_key' =>
                            'badge-sync:'
                            . $user->id
                            . ':'
                            . now()->timestamp,
                    ],
                );
        } catch (Throwable $exception) {
            report(
                $exception,
            );
        }
    }

    private function authorizeNotification(
        Request $request,
        DatabaseNotification $notification,
    ): void {
        abort_unless(
            $notification->notifiable_type
                === $request
                    ->user()
                    ->getMorphClass()
            && (string) $notification->notifiable_id
                === (string) $request
                    ->user()
                    ->getKey(),
            403,
        );
    }
}