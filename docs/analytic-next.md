What to do next
1. Deploy to the live server
The library changes are pushed — a git push deploy (or composer update on the server) will pull them in.
Run php artisan migrate on the server to create the three analytics tables (activity_logs, user_sessions, page_views). The .cpanel.yml deploy script already runs migrations automatically.
Add POSTHOG_API_KEY=phc_... and POSTHOG_API_HOST to the server's .env.
2. Configure PostHog dashboards
Log into app.posthog.com and set up:

A dashboard with the metrics you care about (active users, pageviews by route, top pages).
A funnel for key workflows (e.g., Login → Dashboard → Create Record).
Session recordings (enable in settings) to watch real user behavior and find UX friction.
3. Add the analytics widget to other dashboards (optional)
You can add the analytics widget to any existing dashboard config by including:

[
    'type' => 'analytics',
    'days' => 30,
    'top_pages_limit' => 10,
    'width' => 12,
]

4. Let data accumulate
The analytics value grows over 1–4 weeks of real usage. Then review:

Top Pages — which modules/features are actually used.
Activity Actions — which operations happen most (create/edit/delete).
Daily Active Users — retention trend.
PostHog funnels — where users drop off.
5. Consider security/privacy
Page views and sessions store user/company IDs — ensure your privacy policy covers this.
PostHog self-hosted is available if you need EU/data-residency compliance.
