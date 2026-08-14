<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "database.php";
date_default_timezone_set('Australia/Brisbane');

$type = $_POST['type'];

session_start();

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function logit($db,$id){
 
    $datetime = date("Y-m-d H:i:s");
    $ipaddress = get_client_ip();

    $SQL= "INSERT INTO `login_log` (`user_id`,`datetime`,`ip`) values (?,?,?)";
    $stmt = $db->prepare($SQL);
    $stmt->bind_param("iss",$id,$datetime,$ipaddress); 
    $stmt->execute();
    if ( false===$stmt ) {
        die('execute() failed: ' . htmlspecialchars($db->error));
    }
    $stmt->close();
}

$menubar = '<div class="menubar-container">'.
              '<div class="logo-container">'.
                 '<img src="images/logo.png" class="logo" alt="logo">'.
                 '<div class="logo-text">Jade Carving</div>'.
              '</div>'.
              '<div class="menu-container">'.
                '<div class="menu-options">'.
                    '<ul>'.
                      '<li id="profile" class="menu-item">Profile</li>'.
                      '<li id="products" class="menu-item">Products</li>'.
                      '<li id="reports" class="menu-item">Reports</li>'.
                    '</ul>'.
                '</div>'.    
                '<div class="logout-container">'.
                    '<button class="logout" id="logoutBtn">Logout</button>'.
                '</div>'.          
           '</div>'.
           '<div class="inner-container" id="inner_container"></div>';

if($type === 'login'){
    
    $user = $_POST['user'];
    $pass = $_POST['pass'];
   

    $SQLa = "SELECT salt FROM users WHERE email = ?";
    $stmta = $db->prepare($SQLa);
    $stmta->bind_param("s",$user); 
    $stmta->execute();
    if ( false===$stmta ) {
        die('execute() failed: ' . htmlspecialchars($db->error));
    }
    $stmta->store_result();
    $stmta->bind_result($salt);
    $stmta->fetch();
    $password = md5($pass.$salt);
    $stmta->close();

    $SQL = "SELECT id,email,pass,salt,firstname,lastname FROM users WHERE email = ? AND pass = ?";
    $stmt = $db->prepare($SQL);
    $stmt->bind_param("ss",$user,$password); 
    $stmt->execute();
    if ( false===$stmt ) {
        die('execute() failed: ' . htmlspecialchars($db->error));
    }
    $stmt->store_result();
    $stmt->bind_result($id,$email,$passwd,$salt,$firstname,$lastname);
    $numrows = $stmt->num_rows;
    $stmt->fetch();

    logit($db,$id);

    if(($user === $email) && ($passwd === $password)){
        $out = array('success'=>true,'session'=>session_id(),'id'=>$id,'menubar'=>$menubar);
    }else{
        $out = array('success'=>false);
    }
    $stmt->close();
}

if($type === 'session'){
    $sessionid = session_id();
    if($sessionid ===$_POST['session']){
        $out = array('success'=>true,'menubar'=>$menubar);
    }else{
        $out = array('success'=>false);
    }
}

if($type === 'session_remove'){
    
}


echo json_encode($out);
?>