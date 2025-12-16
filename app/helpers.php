<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

if (!function_exists('log_activity')) {
    /**
     * Log activity manually.
     * 
     * @param string $action
     * @param string $description
     * @return ActivityLog
     */
    function log_activity(string $action, string $description): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}

if (!function_exists('format_wib')) {
    /**
     * Format date/time to WIB (Waktu Indonesia Barat) timezone.
     *
     * @param \Carbon\Carbon|string|null $date
     * @param string $format
     * @return string
     */
    function format_wib($date, string $format = 'd/m/Y H:i'): string
    {
        if (!$date) {
            return '-';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->setTimezone('Asia/Jakarta')->format($format);
    }
}

