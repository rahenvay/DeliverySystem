<?php 
namespace DELIVERY\Driver;
use DELIVERY\User\User;

class Driver extends User {
    public function login($email, $password){}

    public function updateOrderStatus($order_id, $status){}

    public function viewAssignedOrders(){}
    
}