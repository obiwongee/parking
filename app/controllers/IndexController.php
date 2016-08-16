<?php

use Phalcon\Mvc\Controller;

use Models\Cars;
use Models\ParkingLots;

class IndexController extends Controller
{

    public function indexAction() {
        $this->view->types       = Cars::getTypes();
        $this->view->parkingLots = ParkingLots::getParkingLots();
    }

}
