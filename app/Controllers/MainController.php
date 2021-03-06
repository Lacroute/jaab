<?php

class MainController{
  
  function __construct(){

  }


  function home(){
    session_start();
    $s = new Session();
    F3::set('SESSION.csrf', $s->csrf());
    F3::set('content', 'home.htm');
    F3::set('script', 'main.js');

    echo View::instance()->render('layout.htm');
  }


  function subscribe(){
    $status = array(
      'status' => false,
      'msg' => ''
    );

    $csrf_client = F3::get('SESSION.csrf');

    if( $csrf_client == F3::get('POST.token') && isset($csrf_client) == true){
      $email_form = F3::get('POST.subscriber');
      $audit = \Audit::instance();
      
      // check if a form data is a valid e-mail address and DNS MX records 
      if($audit->email($email_form, true)){
        $db=new \DB\SQL(
          'mysql:host='.F3::get('mysql_host').
          ';port='.F3::get('mysql_port').
          ';dbname='.F3::get('mysql_dbname'),
          F3::get('mysql_user'),
          F3::get('mysql_pass'),
          array( \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION )
        );

        $inscriptions = new DB\SQL\Mapper($db,'inscriptions');
        $inscriptions->email=$email_form;
        try{
          $inscriptions->save();
        }catch(\PDOException $e){
          $status['status'] = false;
          if($e->errorInfo[1] == 1062){
            $status['status'] = true;
            $status['msg'] = 'Tu existes déjà ;)';
          }else{
            $status['msg'] = $e->errorInfo[1];
          }
          echo json_encode($status);
          exit();
        }

        $status['status'] = true;
        $status['msg'] = "YEAH ! ♥ sur toi";
      }else{
        $status['status'] = false;
        $status['msg'] = "Hum, email invalide :/";
      }
    }else{
      $status['status'] = false;
      $status['msg'] = "classic shit ;)";
    }

    echo json_encode($status);
  }


  function pageHandler(){
    F3::set('content', F3::get('PARAMS')['page'].'.htm');
    
    echo View::instance()->render('layout.htm');
  }


  function __destruct(){

  }
}
?>