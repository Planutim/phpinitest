<?php

class CustomSessionHandler implements SessionHandlerInterface, SessionIDInterface {
  
  private $mapper;

  public function __construct(SessionMapper $mapper){
    $this->mapper = $mapper;
  }

  public function create_sid(){
    return session_create_id();
  }

  public function open($save_path, $session_name){
    return true;
  }

  public function read($session_id){

    if(session_status() === PHP_SESSION_NONE){
      session_start();
    }

    $data = $this->mapper->read($session_id);
    
    return $data;

    session_write_close();
  }

  public function write($session_id, $session_data){
   if(session_status() === PHP_SESSION_NONE){
      session_start();
    }

    return $this->mapper->write($session_id, $session_data);

    session_write_close();
  }

  public function close(){

    return true;
  }

  public function destroy($session_id){
    if(session_status() === PHP_SESSION_NONE){
      session_start();
    }

    return $this->mapper->destroy($session_id);

    session_write_close();
  }

  public function gc($maxlifetime){
    echo 'gc';
    if(session_status() === PHP_SESSION_NONE){
      session_start();
    }

    return $this->mapper->gc($maxlifetime);

    session_write_close();
  }
}