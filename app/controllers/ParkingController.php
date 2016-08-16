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
            $plate = $request->get('plate');

            if (!is_null($plate)) {
                $parking  = $this->di->getShared('parking');
                $response = $parking->unparkCar($plate, false);
            }
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
            $plate        = $request->getPost('plate');

            if (!is_null($parkingLotId) && !is_null($type) && !is_null($plate)) {
                $parking  = $this->di->getShared('parking');
                $response = $parking->parkCar($parkingLotId, $type, $plate);
            }
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
            $plate        = $request->getPut('plate');

            if (!is_null($parkingLotId) && !is_null($plate)) {
                $parking  = $this->di->getShared('parking');
                $response = $parking->unparkCar($parkingLotId, $plate);
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
    }
}
