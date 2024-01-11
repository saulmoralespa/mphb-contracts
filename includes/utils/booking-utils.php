<?php

namespace MPHB\Addons\Contract\Utils;

use MPHB\Addons\RequestPayment\Settings;

class BookingUtils
{

    /**
     * @return \MPHB\Entities\Booking|null
     */
    public static function getEditingBooking()
    {
        $postId = 0;

        if (isset($_REQUEST['post_ID']) && is_numeric($_REQUEST['post_ID'])) {
            $postId = intval($_REQUEST['post_ID']); // On post update ($_POST)

        } else if (isset($_REQUEST['post']) && is_numeric($_REQUEST['post'])) {
            $postId = intval($_REQUEST['post']); // On post edit page ($_GET)
        }

        return ($postId > 0) ? MPHB()->getBookingRepository()->findById($postId) : null;
    }
}
