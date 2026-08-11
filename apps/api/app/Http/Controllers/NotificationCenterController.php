<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
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

    /**
     * Current notification state used by the PWA
     * to keep multiple devices/windows synchronized.
     */
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

            /*
             * Helps detect changes even when total/unread counts
             * happen to remain identical.
             */
            'latest_id' =>
                $user
                    ->notifications()
                    ->latest()
                    ->value('id'),

            'checked_at' =>
                now()->timestamp,
        ]);
    }

    /**
     * Current unread count for badge synchronization.
     */
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

        return back()->with(
            'success',
            'Notification removed.',
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