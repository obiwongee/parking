<?php

namespace Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query;
use Phalcon\Di;

use Models\ParkingSpots;

class ParkingLots extends Model
{
    public $id;
    public $name;
    public $capacity;

    protected $small = 10, $medium = 45, $large = 35, $super_sized = 10;

    public function initialize() {
        $this->setSource('parking_lots');
    }

    /**
     * Get the space alloted for a type
     *
     * @param string $type
     * @return int
     */
    public function getSpaceForType($type) {
        if (!isset($this->{$type}))
            return false;

        return $this->{$type};
    }
}