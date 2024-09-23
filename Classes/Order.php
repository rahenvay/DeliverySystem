<?php

namespace DelIVERY\Order;

class Order {
    private $id;
    private $client_id;
    private $status;

    //constructor

    public function __construct($id, $client_id){
        $this->id = $id;
        $this->client_id = $client_id;
        $this->status = 'pending';
    }
}