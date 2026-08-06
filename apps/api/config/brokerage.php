<?php

return [
    /*
     * How often scheduled synchronization should be considered due.
     */
    'sync_interval_hours' => (int) env(
        'BROKERAGE_SYNC_INTERVAL_HOURS',
        6,
    ),

    /*
     * A connection is displayed as stale after this many hours
     * without a successful synchronization.
     */
    'stale_after_hours' => (int) env(
        'BROKERAGE_STALE_AFTER_HOURS',
        24,
    ),

    /*
     * Maximum runtime for one brokerage synchronization job.
     */
    'sync_timeout_seconds' => (int) env(
        'BROKERAGE_SYNC_TIMEOUT_SECONDS',
        300,
    ),
];