<?php

require_once 'Db.php';


//only depth = 1 session variables
class SessionMapper{
  private $db;
  private $oldSessionData;

  public function __construct(){
    $this->db = new Db();
    $this->oldSessionData = array();

    $this->db->query("CREATE TABLE IF NOT EXISTS sessions (
      SESSION_ID VARCHAR(32) UNIQUE NOT NULL,
      SERVER_ID VARCHAR(16) NOT NULL DEFAULT '123',
      SESSION_LASTACCESSTIME TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY(SESSION_ID, SERVER_ID)
    );
    
    CREATE TABLE IF NOT EXISTS session_data_1 (
      SESSION_ID VARCHAR(32) NOT NULL,
      SESSION_KEY VARCHAR(255) NOT NULL,
      SESSION_VALUE VARCHAR(255) NOT NULL,
      PRIMARY KEY(SESSION_ID, SESSION_KEY),
      FOREIGN KEY(SESSION_ID) REFERENCES sessions (SESSION_ID) ON DELETE CASCADE
    );
    
    CREATE TABLE IF NOT EXISTS session_data_2 (
      SESSION_ID VARCHAR(32) NOT NULL,
      SESSION_KEY VARCHAR(255) NOT NULL,
      SESSION_VALUE VARCHAR(255) NOT NULL,
      PRIMARY KEY(SESSION_ID, SESSION_KEY),
      FOREIGN KEY(SESSION_ID) REFERENCES sessions (SESSION_ID) ON DELETE CASCADE
    );");
  }

  public function read($session_id){

    $query = "SELECT SESSION_KEY, SESSION_VALUE FROM session_data WHERE SESSION_ID = :session_id";

    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':session_id', $session_id);
    $stmt->execute();
    // [session_key => name,
    // session_value => Martha]

    $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if($fetched === false){
      return '';
    }

    $data = array();

    foreach($fetched as $row){
      $data[$row['SESSION_KEY']] = $row['SESSION_VALUE'];
    }

    $this->oldSessionData = $data;

    if($data===array()){
      $data = '';

    }
    else{
      $data = serialize($data);
    }

    return $data;
  }


  public function write($session_id, $session_data){

    if($this->oldSessionData == $session_data)
    { //if nothing changed
      return true;
    } // to remove or add session variables or change existing ones

    $sessionData = unserialize($session_data);
    $oldSessionData = $this->oldSessionData;

    var_dump($sessionData);
    var_dump($oldSessionData);

    $query =    
      "START TRANSACTION;

      INSERT INTO sessions set 
      SESSION_ID = " . $this->db->quote($session_id) . "
      ON DUPLICATE KEY UPDATE 
      SESSION_LASTACCESSTIME = CURRENT_TIMESTAMP();
    ";

    $query .= $this->findChangedData($sessionData,$oldSessionData,$session_id);
 
    $query .= "COMMIT;";

    echo $query;
    
    $stmt = $this->db->query($query);

    return true;
  }

  public function destroy($session_id){

    $query = "DELETE FROM sessions WHERE SESSION_ID = :session_id";


    $stmt = $this->db->prepare($query);

    $stmt->bindValue(':session_id', $session_id);
    $stmt->execute();

    return true;
  }

  public function gc($maxlifetime=1440){
    $query = "DELETE FROM sessions WHERE SESSION_LASTACCESSTIME + :maxlifetime < CURRENT_TIMESTAMP()";

    $stmt = $this->db->prepare($query);
    $stmt->bindValue(':maxlifetime', $maxlifetime);
    $stmt->execute();

    return true;
  }

  //findchangeddata
  public function findChangedData($sessionData, $oldSessionData, $session_id, $level=1){
    if($level > 3){
      return;
    }
    

    $dataQuery = '';

    $toAddKeys = array();
    $toAddData = array();
    
    $toDeleteKeys = array();

    $keysOld = array();
    $keysNew = array();
    $keysBoth = array();

    $keysOld = array_keys($oldSessionData);

    $keysNew = array_keys($sessionData);

    if($keysOld!=$keysNew){
      $toAddKeys = array_diff($keysNew, $keysOld);
      $toDeleteKeys = array_diff($keysOld, $keysNew);

      if(!empty($toAddKeys)){
        foreach($toAddKeys as $key){
          $toAddData[$key] = $sessionData[$key];
        }
      }
    }

    $keysBoth = array_intersect($keysOld,$keysNew);

    foreach($keysBoth as $key){
      if($sessionData[$key] !== $oldSessionData[$key]){
        $toAddData[$key] = $sessionData[$key];
       }
    }
    

    if(!empty($toAddData)){

      $dataQuery = "INSERT INTO session_data (SESSION_ID, SESSION_KEY, SESSION_VALUE) values ";

      foreach($toAddData as $key=>$val){
        $dataQuery .= "(" . 
        $this->db->quote($session_id) . "," . 
        $this->db->quote($key) . ", " . 
        $this->db->quote($val) . "),";
      }

      $dataQuery = rtrim($dataQuery, ','); //trim comma

      $dataQuery .= 
        ' ON DUPLICATE KEY UPDATE SESSION_VALUE = VALUES(SESSION_VALUE);';
    }

    if(!empty($toDeleteKeys)){

      $deleteQuery = "DELETE FROM session_data WHERE SESSION_ID = " . $this->db->quote($session_id) . " AND SESSION_KEY IN (";

      foreach($toDeleteKeys as $keyToDelete){
        $deleteQuery .= $this->db->quote($keyToDelete) . ",";
      }

      $deleteQuery = rtrim($deleteQuery, ',');

      $deleteQuery .= ");";

      $dataQuery .= $deleteQuery;
    }

    return $dataQuery;
  }
}


