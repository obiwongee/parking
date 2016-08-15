<?php

namespace Services;

use \Exception;

use Models\ParkingLot;
use Models\Cars;
use Models\Fees;

class Parking {
    protected $capacity;
    protected $small = 10, $medium = 45, $large = 35, $superSized = 10;

    public function __construct($capacity) {
        if (($this->small + $this->medium + $this->large + $this->superSized) != 100)
            throw new Exception("Invalid parking lot distribution");

        if ($capacity < 1)
            throw new Exception("Invalid parking lot size");
        
        $this->capacity = $capacity;
    }

    /**
     * Park a car in the parking lot
     *
     * @param string $type
     * @param string $plate
     * @return array
     */
    public function parkCar($type, $plate) {
        $response = [];
        $car      = Cars::getCar($type, $plate);

        // If there is a valid car
        if ($car === false)
            throw new Exception("Car does not exist [$type, $plate]");

        // The car is not already parked
        if ($this->isParked($car))
            throw new Exception("Car is already parked");

        // There is space for that type of car
        if (!$this->hasSpaceByType($type))
            throw new Exception("There are no available spaces for $type cars");

        // A free spot can be found
        if (($spot = $this->findFreeSpace()) === false)
            throw new Exception("Failed to find a free space");
        
        // Park the car
        $parkingLot = new ParkingLot();
        $parkingLot->assign([
            'car_id'   => $car->id,
            'spot'     => $spot,
            'check_in' => date('Y-m-d H:i:s', time())
        ]);
        $parkingLot->save();

        return $parkingLot->toArray();
    }

    /**
     * Unpark a car from the parking lot
     *
     * @param string $plate
     * @return array
     */
    public function unparkCar($plate, $save = true) {
        $car = Cars::findfirst([
            'conditions' => 'license_plate = :plate:',
            'bind'       => ['plate' => $plate]
        ]);

        if ($car === false)
            throw new Exception("Car does not exist with license plate $plate");

        if (($parkedCar = ParkingLot::getParkedCar($car)) === false)
            throw new Exception("Car is not parked");

        $checkOut = date('Y-m-d H:i:s', time());

        // Calculate duration
        if (($fees = Fees::getRate($parkedCar->check_in)) === false)
            throw new Exception("Checkout fee unavailable for {$parkedCar->check_in}");

        $duration  = max(floor((strtotime($checkOut) - strtotime($parkedCar->check_in)) / 60), 1);
        $halfHours = ceil($duration / 30);
        $amount    = $halfHours * $fees->half_hour > $fees->max_daily ? $fees->max_daily : $halfHours * $fees->half_hour;

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
     * Find the first free spot in the parking lot
     *
     * @return the parking spot number or false if there is none available
     */
    protected function findFreeSpace() {
        $spots = ParkingLot::getUsed();

        if (count($spots) >= $this->capacity)
            return false;

        for($i = 0; $i < $this->capacity; $i++) {
            if (!isset($spots[$i]) || $spots[$i]->spot != $i)
                return $i;
        }

        return false;
    }

    /**
     * Check if there is space available for a certain type of car
     *
     * @param string $type
     * @return boolean
     */
    protected function hasSpaceByType($type) {
        if (!isset($this->{$type}))
            return false;

        $allowed = floor($this->capacity * ($this->{$type} / 100));
        $used    = ParkingLot::getUsed($type);

        return $allowed > count($used);
    }

    /**
     * Check a car is currently parked in the parking lot
     *
     * @param Cars $car
     * @return bool
     */
    protected function isParked(Cars $car) {
        return ParkingLot::hasCar($car);
    }
}

