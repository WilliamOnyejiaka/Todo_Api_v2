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
}
?>
