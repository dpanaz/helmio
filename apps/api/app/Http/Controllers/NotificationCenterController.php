<?php

namespace App\Http\Controllers;

use App\Services\Notifications\WebPushService;
use Illuminate\Http\JsonResponse;
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

    public function state(
        Request $request,
    ): JsonResponse {
        $user =
            $request->user();

        return response()->json([
            'total' =>
                $user
                    ->notifications()
                    ->count(),

            'unread' =>
                $user
                    ->unreadNotifications()
                    ->count(),

            'latest_id' =>
                $user
                    ->notifications()
                    ->latest()
                    ->value('id'),

            'checked_at' =>
                now()->timestamp,
        ]);
    }

    public function unreadCount(
        Request $request,
    ): JsonResponse {
        return response()->json([
            'unread_count' =>
                $request
                    ->user()
                    ->unreadNotifications()
                    ->count(),
        ]);
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

        $this->sendNotificationStateUpdate(
            request:
                $request,

            title:
                'Alert reviewed',
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

        $this->sendNotificationStateUpdate(
            request:
                $request,

            title:
                'Alerts marked as read',
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

        $this->sendNotificationStateUpdate(
            request:
                $request,

            title:
                'Alert removed',
        );

        return back()->with(
            'success',
            'Notification removed.',
        );
    }

    private function sendNotificationStateUpdate(
        Request $request,
        string $title,
    ): void {
        $user =
            $request->user();

        $unreadCount =
            $user
                ->unreadNotifications()
                ->count();

        $message =
            $this->remainingAlertMessage(
                $unreadCount,
            );

        try {
            $this->webPushService
                ->sendToUser(
                    $user,
                    [
                        /*
                         * This is intentionally a visible
                         * user notification.
                         */
                        'type' =>
                            'notification_state_update',

                        'title' =>
                            $title,

                        'body' =>
                            $message,

                        'message' =>
                            $message,

                        'action_url' =>
                            route(
                                'notifications.index',
                            ),

                        'unread_count' =>
                            $unreadCount,

                        'event_key' =>
                            sprintf(
                                'notification-state:%s:%s:%s',
                                $user->id,
                                now()->timestamp,
                                bin2hex(
                                    random_bytes(4),
                                ),
                            ),
                    ],
                );
        } catch (Throwable $exception) {
            /*
             * Never allow a push-delivery problem to stop
             * the actual notification action from succeeding.
             */
            report(
                $exception,
            );
        }
    }

    private function remainingAlertMessage(
        int $unreadCount,
    ): string {
        if ($unreadCount === 0) {
            return 'You’re all caught up.';
        }

        if ($unreadCount === 1) {
            return 'You have 1 unread alert remaining.';
        }

        return sprintf(
            'You have %d unread alerts remaining.',
            $unreadCount,
        );
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