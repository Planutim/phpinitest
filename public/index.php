<?php

if($_SERVER['REQUEST_URI'] === '/ajax'){
  return;
}

require '../src/SessionInit.php';
SessionInit::init();

require 'form.php';




switch($_SERVER['REQUEST_URI']){
  case '/destroy':
    session_destroy();
    session_id(session_create_id());
    session_start();
    header("Location: /");break;
  case '/unset':
    unset($_SESSION['name']);
    header("Location: /");break;
  case '/form':
    foreach($_POST as $key=>$val){
      $_SESSION[$key] = $_POST[$key];
    }
    header("Location: /");break;
  case '/gc':
    session_gc();
    header("Location: /");break;
  default:
    break;
}
