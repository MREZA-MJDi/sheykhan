<?php

return [
    /*
    | Maximum URLs read from the sitemap for one manual audit. The service
    | applies a hard upper bound independently of environment configuration.
    */
    'audit_max_pages' => (int) env('SEO_AUDIT_MAX_PAGES', 20),

    /*
    | Network timeouts are deliberately short because audits run on the queue.
    | They are not Core Web Vitals measurements; only scanner-observed response time.
    */
    'audit_connect_timeout_seconds' => (int) env('SEO_AUDIT_CONNECT_TIMEOUT', 1),
    'audit_request_timeout_seconds' => (int) env('SEO_AUDIT_REQUEST_TIMEOUT', 3),
];