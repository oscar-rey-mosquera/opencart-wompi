<?php
namespace Opencart\Catalog\Model\Extension\Wompi\Payment;
/**
 * Class Wompi
 *
 * @package
 */
class Wompi extends \Opencart\System\Engine\Model { 

    public function getMethods(array $address = []) { 

        $option_data['wompi'] = [
            'code' => 'wompi.wompi',
            'name' => 'wompi'
        ];

       return array(
            'code'       => 'wompi',
            'name'      => 'wompi',
            'option'     => $option_data,
            'sort_order' => 1
        );
     }
 }