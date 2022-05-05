<?php
declare(strict_types=1);
namespace Models;

ini_set("display_errors",1);

class User {

  private $connection;
  private $tbl_name;

  public function __construct($connection){
    $this->connection = $connection;
    $this->tbl_name = "users";
  }

  public function create_user($name,$email,$password){
    $query = "INSERT INTO $this->tbl_name(name,email,password) VALUES(?,?,?)";
    $stmt = $this->connection->prepare($query);

    $name = htmlspecialchars(strip_tags($name));
    $email = htmlspecialchars(strip_tags($email));
    $password = htmlspecialchars(strip_tags(password_hash($password,PASSWORD_DEFAULT)));

    $stmt->bind_param("sss",$name,$email,$password);
    return $stmt->execute() ? true : false;
  }

  public function get_user($email){
    $query = "SELECT * FROM $this->tbl_name WHERE email = ?";
    $stmt = $this->connection->prepare($query);
    $stmt->bind_param("s",$email);
    $stmt->execute();
    return $stmt->get_result();
  }

  public function get_user_with_id($id){
    $query = "SELECT * FROM $this->tbl_name WHERE id = ?";
    $stmt = $this->connection->prepare($query);
    $id = htmlspecialchars(strip_tags($id));
    $stmt->bind_param("i",$id);
    $stmt->execute();
    return $stmt->get_result();
  }
}
