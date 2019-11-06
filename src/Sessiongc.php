<?php

require_once 'SessionMapper.php';


class Sessiongc{

  public static function run(){
    $mapper = new SessionMapper();

    if(php_sapi_name() === 'cli'){
      $mapper->gc();
    }
    else{
      if(session_status() === PHP_SESSION_NONE)
        session_start();
      session_gc();
      session_write_close();
    }
  }
}


Sessiongc::run();

?>