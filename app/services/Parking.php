<?php

namespace Services;

use \Exception;

use Models\ParkingSpots;
use Models\ParkingLots;
use Models\Cars;
use Models\Fees;

class Parking
{
    /**
     * Park a car in the parking lot
     *
     * @param string $type
     * @param string $plate
     * @return array
     */
    public function parkCar($parkingLotId, $type, $plate) {
        $response = [];
        $car      = Cars::getCar($type, $plate);

        // If there is a valid car
        if ($car === false)
            throw new Exception("Car does not exist [$type, $plate]");

        // The car is not already parked
        if (($parkedCar = ParkingSpots::getParkedCar($car)) !== false)
            throw new Exception("Car is already parked");

        // There is a valud parking lot        
        if (($parkingLot = ParkingLots::findfirst($parkingLotId)) === false)
            throw new Exception("Invalid parking lot");

        // There is space for that type of car
        if (!$this->hasSpaceByType($parkingLot, $type))
            throw new Exception("There are no available spaces for $type cars");
        
        // Park the car
        $parkingSpot = new ParkingSpots();
        $parkingSpot->assign([
            'parking_lot_id' => $parkingLot->id,
            'car_id'         => $car->id,
            'check_in'       => date('Y-m-d H:i:s', time())
        ]);
        $parkingSpot->save();

        return $parkingSpot->toArray();
    }

    /**
     * Unpark a car from the parking lot
     *
     * @param string $plate
     * @return array
     */
    public function unparkCar($parkingLotId, $plate, $save = true) {
        $car = Cars::findfirst([
            'conditions' => 'license_plate = :plate:',
            'bind'       => ['plate' => $plate]
        ]);

        if ($car === false)
            throw new Exception("Car does not exist with license plate $plate");
        
        if (($parkingLot = ParkingLots::findfirst($parkingLotId)) === false)
            throw new Exception("Invalid parking lot");

        if (($parkedCar = ParkingSpots::getParkedCar($car)) === false)
            throw new Exception("Car is not parked");

        $checkOut = date('Y-m-d H:i:s', time());

        // Calculate duration
        if (($fees = Fees::getRate($parkedCar->check_in)) === false)
            throw new Exception("Checkout fee unavailable for {$parkedCar->check_in}");

        $duration = max(floor((strtotime($checkOut) - strtotime($parkedCar->check_in)) / 60), 1);
        $amount   = $this->getAmount($fees, $duration);

        $parkedCar->assign([
            'check_out' => $checkOut,
            'duration'  => $duration,
            'amount'    => $amount
        ]);

        if ($save)
            $parkedCar->save();

        return $parkedCar->toArray();
    }

    /**
     *
     * @param Fees $fees
     * @param int $duration duration in minutes
     * @return float
     */
    protected function getAmount(Fees $fees, $duration) {       
        $amount    = 0;
        $halfHours = ceil($duration / 30);
        $days      = floor($halfHours / 48);

        if ($days > 0) {
            $amount    = $days * $fees->max_daily;
            $halfHours = $halfHours - ($days * 48);
        }

        $amount += $halfHours * $fees->half_hour > $fees->max_daily ? $fees->max_daily : $halfHours * $fees->half_hour;

        return $amount;
    }

    /**
     * Check if there is space available for a certain type of car
     *
     * @param string $type
     * @return boolean
     */
    protected function hasSpaceByType(ParkingLots $parkingLot, $type = null) {
        if (($space = $parkingLot->getSpaceForType($type)) === false)
            return false;

        $allowed = floor($parkingLot->capacity * ($space / 100));
        $used    = ParkingSpots::getUsed($parkingLot, $type);

        return $allowed > count($used);
    }
}

