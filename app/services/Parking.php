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
     * @param string $licensePlate
     * @return array
     */
    public function parkCar($parkingLotId, $type, $licensePlate) {
        $response = [];
        $car      = Cars::getCar($type, $licensePlate);

        // If there is a valid car
        if ($car === false)
            throw new Exception("Car does not exist [$type, $licensePlate]");

        // The car is not already parked
        if (($parkedCar = ParkingSpots::getParkedCar($car)) !== false)
            throw new Exception("Car is already parked");

        // There is a valud parking lot
        $params = [
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $parkingLotId]
        ];
        if (empty($parkingLotId) || ($parkingLot = ParkingLots::findfirst($params)) === false)
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

        return $parkingSpot->toArray() + ['parking_lot' => $parkingLot->toArray()] + ['car' => $car->toArray()];
    }

    /**
     * Unpark a car from the parking lot
     *
     * @param string $licensePlate
     * @return array
     */
    public function unparkCar($parkingLotId, $licensePlate) {
        $car = Cars::findfirst([
            'conditions' => 'license_plate = :plate:',
            'bind'       => ['plate' => $licensePlate]
        ]);

        // Make sure there is a car
        if ($car === false)
            throw new Exception("Car does not exist with license plate '$licensePlate'");

        // And there is a parking lot
        $params = [
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $parkingLotId]
        ];
        if (empty($parkingLotId) || ($parkingLot = ParkingLots::findfirst($params)) === false)
            throw new Exception("Invalid parking lot");

        // And the car is parked there
        if (($parkedCar = ParkingSpots::getParkedCar($car, $parkingLot)) === false)
            throw new Exception("Car is not parked in {$parkingLot->name}");        

        // Calculate duration
        if (($fees = Fees::getRate($parkedCar->check_in)) === false)
            throw new Exception("Checkout fee unavailable for {$parkedCar->check_in}");

        $checkOut = date('Y-m-d H:i:s', time());
        $duration = $this->getDuration($parkedCar->check_in, $checkOut);
        $amount   = $this->getAmount($fees, $duration);

        $parkedCar->assign([
            'check_out' => $checkOut,
            'duration'  => $duration,
            'amount'    => $amount
        ]);
        $parkedCar->save();        

        return $parkedCar->toArray() + ['parking_lot' => $parkingLot->toArray()] + ['car' => $car->toArray()];
    }

    public function findCar($licensePlate) {
        $car = Cars::findfirst([
            'conditions' => 'license_plate = :plate:',
            'bind'       => ['plate' => $licensePlate]
        ]);

        // Make sure there is a car
        if ($car === false)
            throw new Exception("Car does not exist with license plate '$licensePlate'");

        // And the car is parked there
        if (($parkedCar = ParkingSpots::getParkedCar($car)) === false)
            throw new Exception("Car is not parked in any lot");       

        // Calculate duration
        if (($fees = Fees::getRate($parkedCar->check_in)) === false)
            throw new Exception("Checkout fee unavailable for {$parkedCar->check_in}");

        $checkOut = date('Y-m-d H:i:s', time());
        $duration = $this->getDuration($parkedCar->check_in, $checkOut);
        $amount   = $this->getAmount($fees, $duration);

        $parkedCar->assign([
            'check_out' => $checkOut,
            'duration'  => $duration,
            'amount'    => $amount
        ]);

        return $parkedCar->toArray() + ['parking_lot' => $parkedCar->ParkingLot->toArray()] + ['car' => $car->toArray()];
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

    /**
     * Get the duration the car has been parked in minutes
     *
     * @param string $checkIn
     * @param string $checkOut
     * @return int
     */
    protected function getDuration($checkIn, $checkOut) {
        return max(floor((strtotime($checkOut) - strtotime($checkIn)) / 60), 1);
    }
}

