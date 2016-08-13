<?php

namespace Models;

use Phalcon\Mvc\Model;

class Cars extends Model
{

    public $id;
    public $type;
    public $license_plate;

    private static $types = [
        'small',
        'medium',
        'large',
        'super_sized'
    ];

    //TODO: validation

    public static function getTypes() {
        return static::$types;
    }

    /**
     * Get a car object or create one if it does not exist
     *
     * @param string $type
     * @param string $plate
     * @return \Models\Cars
     */
    public static function getCar($type, $plate) {
        $car = Cars::findfirst([
            'conditions' => 'license_plate = :plate:',
            'bind'       => ['plate' => $plate]
        ]);

        if ($car === false) {
            $car = new Cars();
            $car->assign([
                'type'          => $type,
                'license_plate' => $plate
            ]);
            $car->save();
        }

        return $car;
    }
}
