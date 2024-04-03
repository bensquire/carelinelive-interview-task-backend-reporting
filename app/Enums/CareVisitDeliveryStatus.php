<?php

namespace App\Enums;

/**
 * An enum representing the possible delivery statuses of a CareVisit.
 */
enum CareVisitDeliveryStatus: string
{
    /**
     * The visit is scheduled to happen.
     */
    case Pending = 'pending';

    /**
     * The visit was delivered as planned.
     */
    case Delivered = 'delivered';

    /**
     * The CareVisit was cancelled ahead of time.
     */
    case Cancelled = 'cancelled';

    /**
     * The CareWorker was turned away at the door.
     */
    case Frustrated = 'frustrated';

    /**
     * Note: Feel like this should be a thing, and set using a cron job? (or similar) after a period of
     * time has passed and the status is still set to 'pending', maybe 12 hours? Additionally, if we had a front-end
     * a service operator could also do it manually.
     */
    // case Missed = 'missed';
}
