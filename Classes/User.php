<?php

namespace DELIVERY\User;
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

abstract class User {
    private $id;
    private $email;
    private $password;
    private $fullname;
    private $permission;

    public function __construct($email, $password, $fullname, $permission){
        $this->email = $email;
        $this->password = $password;
        $this->fullname = $fullname;
        $this->permission = $permission;
    }
public function createUser($email, $password, $fullname, $permission){
    //connect
    $conn = new Database();
    
    var_dump($conn->getStarted());

    //prepare the request
    $query = "INSERT INTO user (email, password, fullname, permission) VALUES (:email, :password, :fullname, :permission)";
    $statement = $conn->getStarted()->prepare($query);

    //enryption
    $encryption_password = password_hash($password, PASSWORD_BCRYPT );

    $statement->bindParam(":email", $email);
    $statement->bindParam(":password", $encryption_password);
    $statement->bindParam(":fullname", $fullname);
    $statement->bindParam(":permission", $permission);

    $statement->execute();
}

    abstract public function login($email, $password);
}
