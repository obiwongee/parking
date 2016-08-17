<?php

namespace Models;

use Library\ParkingException;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\InclusionIn as InclusionInValidator;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\Regex as RegexValidator;

class Cars extends Model
{
    public $id;
    public $type;
    public $license_plate;

    protected static $types = [
        'small',
        'medium',
        'large',
        'super_sized'
    ];    

    public function validation() {
        $validator = new Validation();

        $validator->add('type', new InclusionInValidator([
            'domain'  => static::$types,
            'message' => 'Type must be: [' . implode(static::$types, ', ') . ']'
        ]));

        $validator->add('license_plate', new Uniqueness([
            'message' => "A car with the license plate '{$this->license_plate}' already exists"
        ]));

        $this->license_plate = strtoupper($this->license_plate);
        $validator->add('license_plate', new RegexValidator([
            'pattern' => '/^[A-Z0-9]*$/',
            'message' => 'Licence plate must only be alphanumeric characters'
        ]));
        $validator->add('license_plate', new RegexValidator([
            'pattern' => '/^[A-Z0-9]{6,7}$/',
            'message' => 'Licence plate must be 6 or 7 alphanumeric characters'
        ]));

        return $this->validate($validator);
    }

    /**
     * Get a car object or create one if it does not exist
     *
     * @param string $type
     * @param string $plate
     * @return \Models\Cars
     */
    public static function getCar($type, $plate) {
        // Try to find by license plate
        $car = Cars::findfirst([
            'conditions' => 'license_plate = :plate: AND type = :type:',
            'bind'       => ['plate' => $plate, 'type' => $type]
        ]);

        // If none exists try to make a new car
        if ($car === false) {
            $car = new Cars();
            $car->assign([
                'type'          => $type,
                'license_plate' => $plate
            ]);
            
            if (!$car->save()) {
                if (!empty($car->getMessages())) {
                    $errors = [];
                    foreach ($car->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }                    

                    new ParkingException($errors);
                }

                return false;
            }
        }

        return $car;
    }

    public static function getTypes() {
        return static::$types;
    }
}
