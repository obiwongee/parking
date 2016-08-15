<?php

namespace Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query;
use Phalcon\Di;

use Models\Cars;

class ParkingLot extends Model
{
    public $id;
    public $car_id;
    public $spot;
    public $check_in;
    public $check_out;
    public $duration;
    public $amount;

    public function initialize() {
        $this->setSource('parking_lot');
    }

    /**
     * Check how many used spots there are in the parking lot
     *
     * @param string $type
     * @return ParkingLot
     */
    public static function getUsed($type = null) {
        $query = ParkingLot::query()
                 ->where('check_out IS NULL')
                 ->join('Models\Cars', 'c.id = Models\ParkingLot.car_id', 'c')
                 ->order('spot ASC');

        if (!is_null($type)) {
            $query->andWhere('type = :type:')
                  ->bind(['type' => $type]);
        }

        return $query->execute();
    }

    /**
     * Check if a car is currently parked in the lot
     *
     * @param Cars $car
     * @return bool
     */
    public static function hasCar(Cars $car) {
        $car = static::getParkedCar($car);

        return $car !== false;
    }

    /**
     * Returns a parked car
     *
     * @param Cars $car
     * @return Car
     */
    public static function getParkedCar(Cars $car) {
        return ParkingLot::findfirst([
            'conditions' => 'car_id = :carId: AND check_out IS NULL',
            'bind'       => ['carId' => $car->id]
        ]);
    }
}
