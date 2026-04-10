<?php

namespace Modules\ActivityLogs\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ActivityLogs\Models\ActivityLog;

class ActivityLogged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public ActivityLog $activityLog)
    {
    }
}
