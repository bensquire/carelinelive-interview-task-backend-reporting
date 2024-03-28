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
}
