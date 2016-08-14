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
        
        try {
            $type  = $request->getPost('type');
            $plate = $request->getPost('plate');

            if (!is_null($type) && !is_null($plate)) {
                $parking  = $this->di->getShared('parking');
                $response = $parking->parkCar($type, $plate);
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
    }

    /**
     * @Post('/unpark', name='unpark')
     */
    public function unparkAction() {
        $request  = new Request();
        $response = [];

        try {
            $plate = $request->getPost('plate');

            if (!is_null($plate)) {
                $parking  = $this->di->getShared('parking');
                $response = $parking->unparkCar($plate);
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        echo json_encode($response);
    }
}
