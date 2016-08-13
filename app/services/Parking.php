<?php

namespace Services;

use Models\ParkingLot;
use Models\Cars;

class Parking {
    protected $capacity;

    protected $fee          = 2;
    protected $maximumDaily = 15;

    protected $small, $medium, $large, $superSized;

    public function __construct($capacity, $small = 10, $medium = 45, $large = 35, $superSized = 10) {
        if (($small + $medium + $large + $superSized) != 100)
            throw new \Exception("Invalid parking lot distribution");

        if ($capacity < 1)
            throw new \Exception("Invalid parking lot size");

        $this->small      = $small;
        $this->medium     = $medium;
        $this->large      = $large;
        $this->superSized = $superSized;
        $this->capacity   = $capacity;
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

        // If there is a valid car, it is not parked, there is space for it's type, and we got a spot
        if ($car !== false && !$this->isParked($car) && $this->hasSpaceByType($type) && ($spot = $this->findFreeSpace()) !== false) {
            // Park the car
            $parkingLot = new ParkingLot();
            $parkingLot->assign([
                'car_id'   => $car->id,
                'spot'     => $spot,
                'check_in' => date('Y-m-d H:i:s', time())
            ]);
            $parkingLot->save();

            $response = $parkingLot->toArray();
        }

        return $response;
    }

    public function unparkCar($type, $plate) {
        
    }

    /**
     * Find the first free spot in the parking lot
     *
     * @return the parking spot number or false if there is none available
     */
    public function findFreeSpace() {
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
    public function hasSpaceByType($type) {
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
    public function isParked(Cars $car) {
        return ParkingLot::hasCar($car);
    }
}

