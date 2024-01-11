<?php

namespace MPHB\Addons\Contract\MetaBoxes;

use MPHB\Addons\Contract\Utils\BookingUtils;
use MPHB\Addons\Contract\UsersAndRoles\Capabilities;

class ContractMetaBox extends CustomMetaBox
{
    /**
     * @param \MPHB\Admin\Groups\MetaBoxGroup[] $metaBoxes
     * @param string $postType
     * @return \MPHB\Admin\Groups\MetaBoxGroup[]
     */
    public function registerInMphb($metaBoxes, $postType)
    {
        if (current_user_can(Capabilities::GENERATE_CONTRACTS)) {

            if ($postType == $this->postType) {
                $booking = BookingUtils::getEditingBooking();
                if (!is_null($booking) && !$booking->isImported()) {
                    $metaBoxes[] = $this;
                }
            }
        }

        return $metaBoxes;
    
    }

    protected function registerFields()
    {

    }

    public function render()
    {
        $nonce = wp_create_nonce('mphb-contract');
        parent::render();
        $booking = BookingUtils::getEditingBooking();
        if (is_null($booking) || $booking->isImported()) {
            return;
        }

        echo '<p class="mphb-contract">';
            echo '<a target="_blank" href="'.admin_url( 'admin.php?post=' . $booking->getId() . '&action=mphb-contract&_wpnonce='.$nonce ).'"  class="button button-primary button-large" >' . esc_attr__('Generate Contract', 'mphb-contracts') . '</a>';
        echo '</p>';
    }

    public function save()
    {
        parent::save();
    }
}
