<?php
declare(strict_types=1);

namespace Module;

require __DIR__ . "/../vendor/autoload.php";
include_once __DIR__ . "/../config/config.php";

use Lib\Response;
use Lib\Database;
use Model\User;

ini_set("display_errors",1);


class UserExists{
  private int $user_id;
  public function __construct($user_id){
    $this->user_id= $user_id;
  }

  private function user_exists():bool{
    $user = new User((new Database(config('host'),config('username'),config('password'),config('database_name')))->connect());
    $result = $user->get_user_with_id($this->user_id);
    return $result->num_rows > 0 ? true : false;
  }

  public function user_exists_response():void{
    if(!$this->user_exists()){
      (new Response())->send_response(400,[['error',true],['message',"user with id '$this->user_id' does not exist"]]);
      exit();
    }
  }
}
?>
