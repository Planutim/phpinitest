<?php


require_once 'Config.php';
require_once 'CustomSessionHandler.php';
require_once 'SessionMapper.php';
require_once 'Db.php';




class SessionInit{
  public static function init(){
    
    session_set_save_handler(new CustomSessionHandler(new SessionMapper()), true);

    session_start([
      'read_and_close' =>'true'
    ]);

  }
}