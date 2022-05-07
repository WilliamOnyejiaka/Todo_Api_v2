<?php
declare(strict_types=1);
ini_set("display_errors",1);

require __DIR__ . "/../../../../vendor/autoload.php";
include_once __DIR__ . "/../../../../config/config.php";

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Lib\Router;
use Lib\Validator;
use Lib\Database;
use Models\Todo;
use Lib\Response;
use Lib\Controller;
use Lib\Serializer;
use Lib\SearchPagination;

$todo = new Router("todo",config('allow_cors'));

$todo->post("/",fn() => (new Controller())->access_token_controller(function($payload,$body){
    $response = new Response();
    (new Validator())->validate_body($body,['title']);
    $completed = $body->completed?? 0;
    $todo = new Todo((new Database(config('host'),config('username'),config('password'),config('database_name')))->connect());

    if($todo->create_todo($body->title,$completed,$payload->data->id)){
      $response->send_response(200,[['error',false],['message',"todo created successfully"]]);
    }else {
      $response->send_response(500,[['error',false],['message',"something went wrong"]]);
    }
}));

$todo->get("/",fn() => (new Controller())->access_token_controller(function($payload,$body) {
  $response = new Response();
  $todo_id = intval((new Validator())->validate_query_strings(['id'])['id']);
  $user_id = $payload->data->id;

  $todo = new Todo((new Database(config('host'),config('username'),config('password'),config('database_name')))->connect());
  $needed_values = ['id','title','completed','user_id','created_at','updated_at'];
  $response->send_response(200,[['error',false],['data', (new Serializer($needed_values))->tuple($todo->get_todo($todo_id,$user_id))]]);
}));

$todo->get("/get_all_todos",fn() => (new Controller())->access_token_controller(function($payload,$body){
  $connection = (new Database(config('host'),config('username'),config('password'),config('database_name')))->connect();
  $response = new Response();

  $page = $_GET['page']?? 1;
  $limit = $_GET['limit']?? 10;

  $needed_attributes = ['id','title','completed','user_id','created_at','updated_at'];
  $params = array(
    'page'=> $page,
    'results_per_page' => $limit,
    'user_id' => $payload->data->id
  );

  $data = (new Pagination($connection,"todos",$needed_attributes,$params))->meta_data();
  $response->send_response(200,[['error',false],['data', $data]]);

}));

$todo->get("/search",fn() => (new Controller())->access_token_controller(function($payload,$body){
  $connection = (new Database(config('host'),config('username'),config('password'),config('database_name')))->connect();
  $response = new Response();

  $page = $_GET['page']?? 1;
  $limit = $_GET['limit']?? 10;
  $keyword = (new Validator())->validate_query_strings(['keyword'])['keyword'];

  $needed_attributes = ['id','title','completed','user_id','created_at','updated_at'];
  $params = array(
    'page'=> $page,
    'results_per_page' => $limit,
    'user_id' => $payload->data->id
  );

  $data = (new SearchPagination($connection,"todos",$needed_attributes,$keyword,['title'],$params))->meta_data();
  $response->send_response(200,[['error',false],['data', $data]]);

}));

$todo->patch("/update_todo/title",fn() => (new Controller())->access_token_controller(function($payload,$body){
  (new Validator())->validate_body($body,['id','title']);
  $response = new Response();

  $todo_id = intval($body->id);
  $user_id = $payload->data->id;
  $todo = new Todo((new Database(config('host'),config('username'),config('password'),config('database_name')))->connect());
  $todo_item = $todo->get_todo($todo_id,$user_id);
  $todo_exits = (new Serializer(['id']))->tuple($todo_item);

  if($todo_exits){
    if($todo->update_title($todo_id,$user_id,$body->title)){
      $response->send_response(200,[['error',false],['message','todo title updated successfully']]);
    }else{
      $response->send_response(500,[['error',true],['message','something went wrong']]);
    }
  }else {
    $response->send_response(404,[['error',true],['message','todo not found']]);
  }

}));

$todo->patch("/update_todo/completed",fn() => (new Controller())->access_token_controller(function($payload,$body){
  (new Validator())->validate_body($body,['id']);
  $completed_isset = isset($body->completed);
  $response = new Response();

  if(!$completed_isset){
    $response->send_response(400,[['error',true],['message','all values needed']]);
    exit();
  }else if($body->completed > 1 || $body->completed < 0){
    $response->send_response(400,[['error',true],['message','0 or 1 allowed']]);
  }else {
    $todo_id = intval($body->id);
    $user_id = $payload->data->id;
    $todo = new Todo((new Database(config('host'),config('username'),config('password'),config('database_name')))->connect());
    $todo_item = $todo->get_todo($todo_id,$user_id);
    $todo_exits = (new Serializer(['id']))->tuple($todo_item);

    if($todo_exits){
      if($todo->update_completed($todo_id,$user_id,$body->completed)){
        $response->send_response(200,[['error',false],['message','todo completed updated successfully']]);
      }else{
        $response->send_response(500,[['error',true],['message','something went wrong']]);
      }
    }else {
      $response->send_response(404,[['error',true],['message','todo not found']]);
    }
  }

}));

$todo->put("/update_todo",fn() => (new Controller())->access_token_controller(function($payload,$body){
  (new Validator())->validate_body($body,['id','title']);
  $completed_isset = isset($body->completed);
  $response = new Response();

  if(!$completed_isset){
    $response->send_response(400,[['error',true],['message','all values needed']]);
    exit();
  }else if($body->completed > 1 || $body->completed < 0){
    $response->send_response(400,[['error',true],['message','0 or 1 allowed']]);
  }else {
    $todo_id = intval($body->id);
    $user_id = $payload->data->id;
    $todo = new Todo((new Database(config('host'),config('username'),config('password'),config('database_name')))->connect());
    $todo_item = $todo->get_todo($todo_id,$user_id);
    $todo_exits = (new Serializer(['id']))->tuple($todo_item);

    if($todo_exits){
      if($todo->update_title($todo_id,$user_id,$body->title) && $todo->update_completed($todo_id,$user_id,$body->completed)){
        $response->send_response(200,[['error',false],['message','todo updated successfully']]);
      }else{
        $response->send_response(500,[['error',true],['message','something went wrong']]);
      }
    }else {
      $response->send_response(404,[['error',true],['message','todo not found']]);
    }
  }

}));

$todo->delete("/delete",fn() => (new Controller())->access_token_controller(function($payload,$body){
  $todo_id = intval((new Validator())->validate_query_strings(['todo_id'])['todo_id']);
  $user_id = $payload->data->id;
  $todo = new Todo((new Database(config('host'),config('username'),config('password'),config('database_name')))->connect());
  $todo_exits = (new Serializer(['id']))->tuple($todo->get_todo($todo_id,$user_id));

  $response = new Response();

  if($todo_exits){
    if($todo->delete_todo($todo_id,$user_id)){
      $response->send_response(200,[['error',false],['message','todo deleted successfully']]);
    }else{
      $response->send_response(500,[['error',true],['message','something went wrong']]);
    }
  }else {
    $response->send_response(404,[['error',true],['message','todo not found']]);
  }

}));

$todo->run();
