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
use Models\User;
use Lib\Response;
use Lib\Serializer;
use Lib\Controller;
use Lib\TokenAttributes;

$auth = new Router("auth",config('allow_cors'));

$auth->post("/sign_up",fn() =>(new Controller())->public_controller(function($body){

    $response = new Response();
    $validator = new Validator();

    $validator->validate_body($body,['name','email','password']);
    $validator->validate_email_with_response($body->email);
    $validator->validate_password_with_response($body->password,5);

    $user = new User((new Database(config('host'),config('username'),config('password'),config('database_name')))->connect());
    $user_exits =  (new Serializer(['email']))->tuple($user->get_user($body->email));

    if(!$user_exits){
      if($user->create_user($body->name,$body->email,$body->password)){
        $response->send_response(200,[['error',false],['message',"user created successfully"]]);
      }else{
        $response->send_response(500,[['error',false],['message',"something went wrong"]]);
      }
    }else {
      $response->send_response(400,[['error',false],['message',"email exits"]]);
    }
  })
);

$auth->get("/login",fn() => (new Controller())->public_controller(function($body) {
  $response = new Response();
  $validator = new Validator();

  $email = $_SERVER['PHP_AUTH_USER']?? null;
  $password = $_SERVER['PHP_AUTH_PW']?? null;

  $user = new User((new Database(config('host'),config('username'),config('password'),config('database_name')))->connect());
  $current_user = (new Serializer(['password','email']))->tuple($user->get_user($email));

  if($current_user){
    $active_user =  (new Serializer(['id','name','email','created_at','updated_at']))->tuple($user->get_user($email));
    if(password_verify($password,$current_user['password'])){
      $token_attr = new TokenAttributes($active_user);
      $access_token = JWT::encode($token_attr->access_token_payload(),config('secret_key'),config('hash'));
      $refresh_token = JWT::encode($token_attr->refresh_token_payload(),config('secret_key'),config('hash'));

      $response->send_response(200,[["error",false],["data",array(
        'user' => $active_user,
        'tokens' => array(
          'access_token' => $access_token,
          'refresh_token' => $refresh_token,
        ),
      ),]]);
    }else{
      $response->send_response(400,[['error',true],['message',"invalid credentials"]]);
    }
  }else {
    $response->send_response(400,[['error',true],['message',"invalid credentials"]]);
  }
}));

$auth->get("/token/new_access_token",fn() => (new Controller())->protected_controller(function($payload,$body){
  $validator = new Validator();
  $response = new Response();

  if($payload->aud == "users"){
    $response->send_response(400,[["error",true],["message","refresh token needed"]]);

  }else {
    $active_user = array('id' => $payload->data->id);
    $access_token = JWT::encode((new TokenAttributes($active_user))->access_token_payload(),config('secret_key'),config('hash'));
    $response->send_response(200,[["error",false],["access_token",$access_token]]);
  }
}));

$auth->get("/test",function(){
  echo "Get Request";
});

$auth->add_404_callback(function(){
  echo "Not Found";
});

$auth->run();
