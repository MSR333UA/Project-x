<?php
namespace Indeed\Ihc;

class DynamicPrice
{
    /**
     * @var bool
     */
    $isActive       = false;
    /**
     * @param array
     */
    $settings       = [];

    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        $this->settings = ihc_return_meta_arr('level_dynamic_price'); //
        if ( !empty( $this->settings['ihc_level_dynamic_price_on'] ) ){
            $this->isActive = true;
        }
    }

    /**
     * @param int
     * @param float
     * @return bool
     */
    public function checkPrice( $lid=0, $price=0 )
    {
        $minimumPrice = isset($this->settings['ihc_level_dynamic_price_levels_min'][$lid]) ? $this->settings['ihc_level_dynamic_price_levels_min'][$lid] : 0;
        if ( $minimumPrice <= $price ){
            return true;
        }
        return false;
    }

}
