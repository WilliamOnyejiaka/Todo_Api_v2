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
    $stmt->bind_param("i",$id);
    $stmt->execute();
    return $stmt->get_result();
  }

  public function update_name(int $id,string $name):bool {
    $query = "UPDATE $this->tbl_name SET name = ? WHERE id = ?";
    $stmt = $this->connection->prepare($query);
    $name = htmlspecialchars(strip_tags($name));
    $stmt->bind_param("si",$name,$id);
    return $stmt->execute();
  }

  public function update_email(int $id,string $email):bool {
    $query = "UPDATE $this->tbl_name SET email = ? WHERE id = ?";
    $stmt = $this->connection->prepare($query);
    $email = htmlspecialchars(strip_tags($email));
    $stmt->bind_param("si",$email,$id);
    return $stmt->execute();
  }

  public function update_password(int $id,string $password):bool {
    $query = "UPDATE $this->tbl_name SET password = ? WHERE id = ?";
    $stmt = $this->connection->prepare($query);
    $password = htmlspecialchars(strip_tags(password_hash($password,PASSWORD_DEFAULT)));
    $stmt->bind_param("si",$password,$id);
    return $stmt->execute();
  }

  public function delete_user(int $id){
    $query = "DELETE FROM $this->tbl_name WHERE  id = ?";
    $stmt = $this->connection->prepare($query);
    $stmt->bind_param("i",$id);
    return $stmt->execute();
  }
}
