<?php

namespace Models;

use Phalcon\Mvc\Model;

class Fees extends Model
{
    public $id;
    public $half_hour;
    public $max_daily;
    public $created;

    /**
     * Returns fees that would be applied to a date
     *
     * @param string $date
     * @return Fees
     */
    public static function getRate($date) {
        return Fees::findfirst([
            'conditions' => 'created <= :date:',
            'limit'      => 1,
            'order'      => 'created DESC',
            'bind'       => [
                'date' => date('Y-m-d H:i:s', strtotime($date))
            ]
        ]);
    }
}