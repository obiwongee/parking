<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Request;

/**
 * @RoutePrefix('/api/parking')
 */
class ParkingController extends Controller
{
    /**
     * @Get('/park', name='default')
     */
    public function indexAction() {
        $this->view->disable();
        $request  = new Request();
        $response = [];

        try {
            $plate = $request->get('license_plate');

            $parking  = $this->di->getShared('parking');
            $response = $parking->findCar($plate);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
    }

    /**
     * @Post('/park', name='park')
     */
    public function parkAction() {        
        $request  = new Request();
        $response = [];
        
        try {
            $parkingLotId = $request->getPost('parking_lot_id');
            $type         = $request->getPost('type');
            $licensePlate = $request->getPost('license_plate');

            $parking  = $this->di->getShared('parking');
            $response = $parking->parkCar($parkingLotId, $type, $licensePlate);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
    }

    /**
     * @Put('/park', name='unpark')
     */
    public function unparkAction() {
        $request  = new Request();
        $response = [];

        try {
            $parkingLotId = $request->getPut('parking_lot_id');
            $licensePlate = $request->getPut('license_plate');

            $parking  = $this->di->getShared('parking');
            $response = $parking->unparkCar($parkingLotId, $licensePlate);
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
    }
}
