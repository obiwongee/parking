<?php

use Phalcon\Mvc\Controller;

class CarsController extends Controller
{
    public function indexAction() {
        foreach (Cars::find() as $car) {
            var_dump($car->toArray());
        }
        die;
    }
}
