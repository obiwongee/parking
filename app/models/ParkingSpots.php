<?php

namespace Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query;
use Phalcon\Di;

use Models\Cars;
use Models\ParkingLots;

class ParkingSpots extends Model
{
    public $id;
    public $parking_lot_id;
    public $car_id;
    public $check_in;
    public $check_out;
    public $duration;
    public $amount;

    public function initialize() {
        $this->setSource('parking_spots');
    }

    /**
     * Returns a parked car
     *
     * @param Cars $car
     * @return Car
     */
    public static function getParkedCar(Cars $car) {
        return ParkingSpots::findfirst([
            'conditions' => 'car_id = :carId: AND check_out IS NULL',
            'bind'       => [
                'carId' => $car->id
            ]
        ]);
    }

    /**
     * Check how many used spots there are in the parking lot
     *
     * @param string $type
     * @return ParkingLot
     */
    public static function getUsed(ParkingLots $parkingLot, $type = null) {
        $args = [];

        $query = ParkingSpots::query()
                 ->where('check_out IS NULL AND parking_lot_id = :parkingLotId:')
                 ->join('Models\Cars', 'c.id = Models\ParkingSpots.car_id', 'c');
        $args['parkingLotId'] = $parkingLot->id;

        if (!is_null($type)) {
            $query->andWhere('type = :type:');
            $args['type'] = $type;
        }

        $query->bind($args);

        return $query->execute();
    }
}
