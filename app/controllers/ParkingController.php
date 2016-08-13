<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;

/**
 * @RoutePrefix('/api/parking')
 */
class ParkingController extends Controller
{
    /**
     * @Get('/', name='default')
     */
    public function indexAction() {
        echo "Hello World";
        die;
    }

    /**
     * @Post('/park', name='park')
     */
    public function parkAction() {
        $request  = new Request();
        $response = [];
                
        $type  = $request->getPost('type');
        $plate = $request->getPost('plate');

        if (!is_null($type) && !is_null($plate)) {
            $parking  = $this->di->getShared('parking');
            $response = $parking->parkCar($type, $plate);
        }

        echo json_encode($response);
        die;
    }
}
