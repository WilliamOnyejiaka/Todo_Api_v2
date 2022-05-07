<?php
declare(strict_types=1);
namespace Models;

ini_set('display_errors',1);

class Todo {

  private $connection;
  private $tbl_name;

  public function __construct($connection){
    $this->connection = $connection;
    $this->tbl_name = "todos";
  }

  public function create_todo(string $title,int $completed,int $user_id) {
    $query = "INSERT INTO $this->tbl_name(title,completed,user_id) VALUES(?,?,?)";
    $stmt = $this->connection->prepare($query);

    $title = htmlspecialchars(strip_tags($title));

    $stmt->bind_param("sii",$title,$completed,$user_id);
    return $stmt->execute() ? true : false;
  }

  public function get_todo(int $id,int $user_id){
    $query = "SELECT * FROM $this->tbl_name WHERE id = ? AND user_id = ?";
    $stmt = $this->connection->prepare($query);
    $stmt->bind_param("ii",$id,$user_id);
    $stmt->execute();
    return $stmt->get_result();
  }

  public function upate_timestamp(int $id){
    $query = "UPDATE $this->tbl_name SET updated_at = now() WHERE id = ?";
    $stmt = $this->connection->prepare($query);
    $stmt->bind_param("i",$id);
    return $stmt->execute() ? true : false;
  }

  public function update_title(int $id,int $user_id,string $title){
    $query = "UPDATE $this->tbl_name SET title = ? WHERE id = ? AND user_id = ? ";
    $stmt = $this->connection->prepare($query);
    $title = htmlspecialchars(strip_tags($title));
    $stmt->bind_param("sii",$title,$id,$user_id);
    $this->upate_timestamp($id);
    return $stmt->execute() ? true : false;
  }

  public function update_completed(int $id,int $user_id,int $completed){
    $query = "UPDATE $this->tbl_name SET completed = ? WHERE id = ? AND user_id = ? ";
    $stmt = $this->connection->prepare($query);
    $stmt->bind_param("iii",$completed,$id,$user_id);
    $this->upate_timestamp($id);
    return $stmt->execute() ? true : false;
  }

  public function delete_todo($id,$user_id){
    $query = "DELETE FROM $this->tbl_name WHERE id = ? AND user_id = ?";
    $stmt = $this->connection->prepare($query);
    $stmt->bind_param("ii",$id,$user_id);
    return $stmt->execute() ? true : false;
  }
}
?>
