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
     * @return mixed Car object on sucess or else false
     */
    public static function getParkedCar(Cars $car, ParkingLots $parkingLot = null) {
        $args = [];

        $query = ParkingSpots::query()
                    ->where('car_id = :carId: AND check_out IS NULL');
        $args['carId'] = $car->id;

        // Check if we're looking in a specific lot
        if (!is_null($parkingLot)) {
            $query->andWhere('parking_lot_id = :parkingLotId:');
            $args['parkingLotId'] = $parkingLot->id;
        }

        $query->bind($args);

        $results = $query->execute();

        // We want "findfirst" behaviour
        return count($results) > 0 ? $results[0] : false;
    }

    /**
     * Check how many used spots there are in the parking lot
     *
     * @param string $type
     * @return ParkingSpots
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
