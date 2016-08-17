<?php

namespace Library;

use \Exception;

class ParkingException extends Exception {
    public function __construct($message) {
        if (is_array($message)) {
            throw new Exception(json_encode($message));
        } else {
            throw new Exception(json_encode([$message]));
        }
    }
}

