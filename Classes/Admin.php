<?php

namespace DELIVERY\Classes\Admin;
require_once 'Classes/User.php';
use DELIVERY\User\User;

class Admin extends User {
    public function login($email, $password){}
    public function createOrder($clientid, $address, $details) {}
    public function assignOrderToDriver($order_id, $user_id) {}
    
}