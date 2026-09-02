<?php

namespace App\Http\Middleware;

use App\Models\MarketingVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CaptureMarketingAttribution
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasAttribution = $request->filled('utm_source')
            || $request->filled('utm_campaign')
            || $request->filled('rdt_cid');

        if (! $hasAttribution) {
            return $next($request);
        }

        $visitorUuid = $request->cookie('helmio_visitor_uuid')
            ?: (string) Str::uuid();

        Cookie::queue(
            name: 'helmio_visitor_uuid',
            value: $visitorUuid,
            minutes: 60 * 24 * 90,
            path: '/',
            secure: app()->environment('production'),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        );

        $visit = MarketingVisit::query()->firstOrCreate(
            [
                'visitor_uuid' => $visitorUuid,
                'source' => $request->query('utm_source'),
                'campaign' => $request->query('utm_campaign'),
                'content' => $request->query('utm_content'),
            ],
            [
                'session_id' => $request->session()->getId(),
                'medium' => $request->query('utm_medium'),
                'term' => $request->query('utm_term'),
                'reddit_click_id' => $request->query('rdt_cid'),
                'landing_page' => $request->fullUrl(),
                'referrer' => $request->headers->get('referer'),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'first_seen_at' => now(),
                'last_seen_at' => now(),
            ],
        );

        $visit->update([
            'session_id' => $request->session()->getId(),
            'reddit_click_id' => $request->query('rdt_cid')
                ?: $visit->reddit_click_id,
            'last_seen_at' => now(),
        ]);

        $request->session()->put(
            'marketing_visit_id',
            $visit->id,
        );

        return $next($request);
    }
}