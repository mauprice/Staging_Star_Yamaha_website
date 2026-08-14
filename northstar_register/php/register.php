<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "database.php";
date_default_timezone_set('Australia/Brisbane');



$type = $_POST['type'];

session_start();

function register(){
  $register =   '<div class="register-container">'.
                    '<div class="header-bar">'.
                        '<div class="radio-container">'.
                            '<div class="radio-field-inner">'.
                            '<label class="radio-label">Last 100</label>'.
                            '<input type="radio" name="radios" class="radio-field" id="last100" checked>'.
                            '</div>'.
                            '<div class="radio-field-inner">'.
                                '<label class="radio-label">All</label>'.
                                '<input type="radio" name="radios" class="radio-field" id="all" >'.
                            '</div>'.
                        '</div>'.
                        '<h1 class="header-name">Northstar Yamaha Register</h1>'.
                        '<span class="fa fa-key poskey" title="Change your password"></span>'.                        
                    '</div>'.
                    '<div class="records-container">'.
                        '<div class="records-data"  id="records_data"></div>'.
                        '<img class="loading" src="images/preload.gif" alt="loading">'.
                    '</div>'.
                    '<div class="fields-container">'.
                        '<div class="container">'.
                            '<div class="row">'.
                            '<div class="col">'.
                                '<table cellspacing="10" cellpadding="4">'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">DESCRIPTION</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="form-field" id="desc">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">STOCK NO</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="form-field" id="stock_no">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">PURCHASE DATE</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="calendar form-field" data-type="date" id="purchase_date">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">REGO No</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="form-field" id="rego_no">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">PURCHASE PRICE <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="purchase_price" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">BEFORE WRITE BACK <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="before_writeback" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">LOADING <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="revs" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">REGO <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="rego" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">STAMP DUTY <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="stamp_duty" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">W/SHOP PARTS ACC <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="parts" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">W/SHOP LABOUR <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="labour" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">TRANSPORT</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="transport" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">MISC <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="misc" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                       '<td class="form-label-cell">'.
                                            '<label class="form-label">WRITE BACK <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="writeback" value="0">'.
                                        '</td>'.
                                    '</tr>'.

                                '</table>'.
                            '</div>'.
                            '<div class="col">'.
                                '<table cellspacing="10" cellpadding="4">'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">WARRANTY <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                           ' <input type="number" class="form-field" data-type="number" id="acc_purchased" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                   '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">GST PAID <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="gst_paid" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">GST COLLECTED <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="gst_collected" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">BAL BANKED <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="bal_banked" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">DATE BANKED</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="calendar form-field" data-type="date" id="date_banked">'.
                                        '</td>'.
                                    '</tr>'.
                                    
                                '</table>'.
                            '</div>'.
                            '<div class="col">'.
                                '<table cellspacing="10" cellpadding="4">'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">GST PAYABLE <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                           '<input type="number" class="form-field" data-type="number" id="gst_payable" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">SOLD DATE</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="calendar form-field" data-type="date" id="sold_date">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">SOLD TO</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="form-field" id="sold_to">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">INV NO</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="form-field" id="inv_no">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">VIN NO</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="form-field" id="vin_no">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">YMF UNIT NO</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="form-field" id="ymaf_unit_no">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                        
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">TRADE DETAILS</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="form-field" id="trade_details">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">TRADE IN PRICE <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="trade_price" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">BAL PAID <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="bal_paid" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">SOLD PRICE <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="sold_price" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">SALES PERSON</label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="text" class="form-field" id="salesman">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">PROFIT <span style="float:right">$</span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                            '<input type="number" class="form-field" data-type="number" id="profit" value="0">'.
                                        '</td>'.
                                    '</tr>'.
                                    '<tr>'.
                                        '<td class="form-label-cell">'.
                                            '<label class="form-label">COMM PAID <span style="float:right"></span></label>'.
                                        '</td>'.
                                        '<td class="form-field-cell">'.
                                           '<input type="text" class="calendar form-field" data-type="date" id="comm_paid">'.
                                        '</td>'.
                                    '</tr>'.

                                '</table>'.
                            '</div>'.
                            '</div>'.
                        '</div>'.
                    '</div> '.
                    '<div class="buttons-container">'.
                        '<button class="base-btn delete">Delete</button>'.
                        '<button class="base-btn print">Print</button>'.
                        '<button class="base-btn save">Save</button>'.
                        '<button class="base-btn new">New</button>'.
                        '<button class="base-btn exit">Exit</button>'.
                    '</div>'.                 
                '</div>';   
                
                return $register;
}
if($type === 'session'){
    $sessionid = session_id();
    if($sessionid ===$_POST['session']){
        $out = array('success'=>true,'register_container'=>register(),'session'=>session_id());
    }else{
        $out = array('success'=>false);
    }
}

if($type === 'changepw'){

    $out = changepw($db,$_POST['new_pw']);

}
if ($type === 'login'){

    $user = $_POST['username'];
    $pass = $_POST['password'];
   
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

   // logit($db,$id);

    if($numrows === 1){
        $out = array('success'=>true,'register_container'=>register(),'session'=>session_id());
    }else{
        $out = array('success'=>false);
    }
    $stmt->close();
}
if($type === 'session'){

    $sessionid = session_id();
    if($sessionid ===$_POST['session']){

        $out = array('success'=>true,'register_container'=>register());
        
    }else{
        $out = array('success'=>false);
    }
    
   
} 

if($type === 'list'){
    $list_type = $_POST['list_type'];
    $out = array('success'=>true,'records'=>get_data($db,$list_type));
}
if($type === 'record'){
    $id = $_POST['id'];
    $out = array('success'=>true,'record'=>get_record($db,$id));
}
if($type === 'save'){
    $formdata = $_POST['form_data'];
    $record_id = $_POST['id'];
    $desc = $formdata['desc'];
    $stock_no = $formdata['stock_no'];
    $purchase_date = $formdata['purchase_date'];
    $rego_no = $formdata['rego_no'];
    $purchase_price = $formdata['purchase_price'];
    $before_writeback = $formdata['before_writeback'];
    $revs = $formdata['revs'];
    $rego = $formdata['rego'];
    $stamp_duty = $formdata['stamp_duty'];
    $parts = $formdata['parts'];
    $labour = $formdata['labour'];
    $transport = $formdata['transport'];
    $misc = $formdata['misc'];
    $writeback = $formdata['writeback'];
    $gst_payable = $formdata['gst_payable'];
    $sold_to = $formdata['sold_to'];
    $inv_no = $formdata['inv_no'];
    $vin_no = $formdata['vin_no'];
    $ymaf_unit_no = $formdata['ymaf_unit_no'];
    $trade_price = $formdata['trade_price'];
    $trade_details = $formdata['trade_details'];
    $bal_paid = $formdata['bal_paid'];
    $sold_date = $formdata['sold_date'];
    $sold_price = $formdata['sold_price'];
    $salesman = $formdata['salesman'];
    $profit = $formdata['profit'];
    $comm_paid = $formdata['comm_paid'];
    $acc_purchased = $formdata['acc_purchased'];
    $gst_paid = $formdata['gst_paid'];
    $gst_collected = $formdata['gst_collected'];
    $bal_banked = $formdata['bal_banked'];
    $date_banked = $formdata['date_banked'];
    if($record_id === '0'){       

       if($purchase_date === ''){
           $purchase_date = date('Y-m-d');
       }else{
           list($d,$m,$y) = explode("/", $purchase_date);
           $purchase_date = $y.'-'.$m.'-'.$d;
       }
       if($sold_date === ''){
           $sold_date = date('Y-m-d');
       }else{
           list($d,$m,$y) = explode("/", $sold_date);
           $sold_date = $y.'-'.$m.'-'.$d;
       }
       if($date_banked === ''){
           $date_banked = date('Y-m-d');
       }else{
           list($d,$m,$y) = explode("/", $date_banked);
           $date_banked = $y.'-'.$m.'-'.$d;
       }
       if($comm_paid === ''){
           $comm_paid = date('Y-m-d');
       }else{
           list($d,$m,$y) = explode("/", $comm_paid);
           $comm_paid = $y.'-'.$m.'-'.$d;
       }

       $SQL = "INSERT INTO register (`DESCRIPTION`,`STOCK NO`,`PURCHASE DATE`,`REGO_No`,`PURCHASE PRICE`,`PUR PRICE BEFORE WRITE BACK`,`REVS TRANS FEE`,
                                     `REGO`,`STAMP DUTY`,`W/SHOP PARTS ACC`,`W/SHOP LABOUR`,`TRANSPORT`,`MISC`,`WRITE BACK`,`GST PAYABLE`,`SOLD TO`,
                                     `INV NO`,`VIN NO`,`YMAF UNIT NO`,`TRADE DETAILS`,`TRADE IN PRICE`,`BAL PAID`,`SOLD DATE`,`SOLD PRICE`,`SALESMAN`,
                                     `PROFIT`,`COMM PAID`,`Acc_Purchased`,`GST_Piad`,`GST_Collected`,`Bal_Banked`,`Date_Banked`)
                                     values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                                    /* ('$desc','$stock_no','$purchase_date','$rego_no','$purchase_price','$before_writeback','$revs','$rego','$stamp_duty','$parts','$labour',
                                      '$transport','$misc','$writeback','$gst_payable','$sold_to','$inv_no','$vin_no','$ymaf_unit_no','$trade_details','$bal_paid','$sold_date',
                                      '$sold_price','$salesman','$profit','$comm_paid','$acc_purchased','$gst_paid','$gst_collected','$bal_banked','$date_banked')";*/
      
       $stmt = $db->prepare($SQL);
       if ( false===$stmt ) {
           die('prepare() failed: ' . htmlspecialchars($db->error));
       }
       $stmt->bind_param("ssssssssssssssssssssssssssssssss",$desc,$stock_no,$purchase_date,$rego_no,$purchase_price,$before_writeback,$revs,$rego,$stamp_duty,$parts,$labour,
       $transport,$misc,$writeback,$gst_payable,$sold_to,$inv_no,$vin_no,$ymaf_unit_no,$trade_details,$trade_price,$bal_paid,$sold_date,
       $sold_price,$salesman,$profit,$comm_paid,$acc_purchased,$gst_paid,$gst_collected,$bal_banked,$date_banked);
       if ( false===$stmt ) {
        die('bind_param() failed: ' . htmlspecialchars($db->error));
    }
       $stmt->execute();
       if ( false===$stmt ) {
           die('execute() failed: ' . htmlspecialchars($db->error));
       }
       $stmt->close(); 
       $out = array('success'=>true);

    }else{
      echo 'Update';
      if($purchase_date === ''){
        $purchase_date = date('Y-m-d');
    }else{
        list($d,$m,$y) = explode("/", $purchase_date);
        $purchase_date = $y.'-'.$m.'-'.$d;
    }
    if($sold_date === ''){
        $sold_date = date('Y-m-d');
    }else{
        list($d,$m,$y) = explode("/", $sold_date);
        $sold_date = $y.'-'.$m.'-'.$d;
    }
    if($date_banked === ''){
        $date_banked = date('Y-m-d');
    }else{
        list($d,$m,$y) = explode("/", $date_banked);
        $date_banked = $y.'-'.$m.'-'.$d;
    }
    if($comm_paid === ''){
        $comm_paid = date('Y-m-d');
    }else{
        list($d,$m,$y) = explode("/", $comm_paid);
        $comm_paid = $y.'-'.$m.'-'.$d;
    }

    $SQL = "UPDATE register SET `DESCRIPTION` =  ?,`STOCK NO` = ?,`PURCHASE DATE` = ?,`REGO_No` = ?,`PURCHASE PRICE` = ?,`PUR PRICE BEFORE WRITE BACK` = ?,`REVS TRANS FEE` = ?,
                                `REGO` = ?,`STAMP DUTY` = ?,`W/SHOP PARTS ACC` = ?,`W/SHOP LABOUR` = ?,`TRANSPORT` = ?,`MISC` = ?,`WRITE BACK` = ?,`GST PAYABLE` = ?,`SOLD TO` = ?,
                                `INV NO` = ?,`VIN NO` = ?,`YMAF UNIT NO` = ?,`TRADE DETAILS` = ?,`TRADE IN PRICE` = ?,`BAL PAID` = ?,`SOLD DATE` = ?,`SOLD PRICE` = ?,`SALESMAN` = ?,
                                `PROFIT` = ?,`COMM PAID` = ?,`Acc_Purchased` = ?,`GST_Piad` = ?,`GST_Collected` = ?,`Bal_Banked` = ?,`Date_Banked` = ? WHERE ID = ?";
                                // values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                                 /* ('$desc','$stock_no','$purchase_date','$rego_no','$purchase_price','$before_writeback','$revs','$rego','$stamp_duty','$parts','$labour',
                                   '$transport','$misc','$writeback','$gst_payable','$sold_to','$inv_no','$vin_no','$ymaf_unit_no','$trade_details','$bal_paid','$sold_date',
                                   '$sold_price','$salesman','$profit','$comm_paid','$acc_purchased','$gst_paid','$gst_collected','$bal_banked','$date_banked')";*/
   
    $stmt = $db->prepare($SQL);
    if ( false===$stmt ) {
        die('prepare() failed: ' . htmlspecialchars($db->error));
    }
    $stmt->bind_param("ssssssssssssssssssssssssssssssssi",$desc,$stock_no,$purchase_date,$rego_no,$purchase_price,$before_writeback,$revs,$rego,$stamp_duty,$parts,$labour,
    $transport,$misc,$writeback,$gst_payable,$sold_to,$inv_no,$vin_no,$ymaf_unit_no,$trade_details,$trade_price,$bal_paid,$sold_date,
    $sold_price,$salesman,$profit,$comm_paid,$acc_purchased,$gst_paid,$gst_collected,$bal_banked,$date_banked,$record_id);
    if ( false===$stmt ) {
     die('bind_param() failed: ' . htmlspecialchars($db->error));
 }
    $stmt->execute();
    if ( false===$stmt ) {
        die('execute() failed: ' . htmlspecialchars($db->error));
    }
    $stmt->close(); 
    $out = array('success'=>true);
    }
   
}
if($type === 'delete'){
    $id = $_POST['id'];
    $out = array('success'=>true,'record'=>delete_record($db,$id));
}
if($type === 'print'){
    $sdate = $_POST['sdate'];
    $edate = $_POST['edate'];
    $results = report($db,$sdate,$edate);
    $out = array('success'=>true,'results'=>$results);
}

echo json_encode($out);

function get_data($db,$list_type){

if($list_type ==='all'){
   $limit = '';
}else{
   $limit = 'LIMIT 100';
}    

$SQL="SELECT
        register.ID, 
        register.DESCRIPTION, 
        register.`STOCK NO`, 
        date_format(register.`PURCHASE DATE`,'%d/%m/%Y'), 
        register.REGO_No, 
        register.`PURCHASE PRICE`, 
        register.`PUR PRICE BEFORE WRITE BACK`, 
        register.`REVS TRANS FEE`, 
        register.REGO, 
        register.`STAMP DUTY`, 
        register.`W/SHOP PARTS ACC`, 
        register.`W/SHOP LABOUR`, 
        register.TRANSPORT, 
        register.MISC, 
        register.`WRITE BACK`, 
        register.`GST PAYABLE`, 
        register.`SOLD TO`, 
        register.`INV NO`, 
        register.`VIN NO`, 
        register.`YMAF UNIT NO`, 
        register.`YMAF REBATE`, 
        register.`TRADE DETAILS`, 
        register.`TRADE IN PRICE`, 
        register.`BAL PAID`, 
        date_format(register.`SOLD DATE`,'%d/%m/%Y'), 
        register.`SOLD PRICE`, 
        register.SALESMAN, 
        register.PROFIT, 
        date_format(register.`COMM PAID`,'%d/%m/%Y'), 
        register.INSURANCE, 
        register.Acc_Purchased, 
        register.GST_Piad, 
        register.GST_Collected, 
        register.Bal_Banked, 
        register.Date_Banked
        FROM
        register
        ORDER BY
        register.ID DESC 
        $limit";


            $stmt = $db->prepare($SQL);
            if ( false===$stmt ) {
                die('prepare() failed: ' . htmlspecialchars($db->error));
            }
           // $stmt0->bind_param("ii",$id,$sh);
            $stmt->execute();
            if ( false===$stmt ) {
                die('execute() failed: ' . htmlspecialchars($db->error));
            }
            $stmt->store_result();
            $stmt->bind_result($ID,
                                $DESCRIPTION,
                                $STOCK_NO,
                                $PURCHASE_DATE,
                                $REGO_No,
                                $PURCHASE_PRICE,
                                $PUR_PRICE_BEFORE_WRITE_BACK,
                                $REVS_TRANS_FEE,
                                $REGO,
                                $STAMP_DUTY,
                                $WSHOP_PARTS_ACC,
                                $WSHOP_LABOUR,
                                $TRANSPORT,
                                $MISC,
                                $WRITE_BACK,
                                $GST_PAYABLE,
                                $SOLD_TO,
                                $INV_NO,
                                $VIN_NO,
                                $YMAF_UNIT_NO,
                                $YMAF_REBATE,
                                $TRADE_DETAILS,
                                $TRADE_IN_PRICE,
                                $BAL_PAID,
                                $SOLD_DATE,
                                $SOLD_PRICE,
                                $SALESMAN,
                                $PROFIT,
                                $COMM_PAID,
                                $INSURANCE,
                                $Acc_Purchased,
                                $GST_Paid,
                                $GST_Collected,
                                $Bal_Banked,
                                $Date_Banked);

                                
          $numrows = $stmt->num_rows;
            while($stmt->fetch())
                  { 
                     $records[]= array('ID'=>$ID,
                     'description'=>$DESCRIPTION,
                     'stock_no'=>$STOCK_NO,
                     'purchase_date'=>$PURCHASE_DATE,
                     'rego_no'=>$REGO_No,
                     'purchase_price'=>number_format($PURCHASE_PRICE),
                     'before_writeback'=>$PUR_PRICE_BEFORE_WRITE_BACK,
                     'revs'=>$REVS_TRANS_FEE,
                     'rego'=>$REGO,
                     'stamp_duty'=>$STAMP_DUTY,
                     'parts'=>$WSHOP_PARTS_ACC,
                     'labour'=>$WSHOP_LABOUR,
                     'transport'=>$TRANSPORT,
                     'misc'=>$MISC,
                     'writeback'=>$WRITE_BACK,
                     'gst_payable'=>$GST_PAYABLE,
                     'sold_to'=>$SOLD_TO,
                     'inv_no'=>$INV_NO,
                     'vin_no'=>$VIN_NO,
                     'ymaf_unit_no'=>$YMAF_UNIT_NO,
                     'ymaf_rebate'=>$YMAF_REBATE,
                     'trade_details'=>$TRADE_DETAILS,
                     'trade_price'=>$TRADE_IN_PRICE,
                     'bal_paid'=>$BAL_PAID,
                     'sold_date'=>$SOLD_DATE,
                     'sold_price'=>number_format($BAL_PAID + $TRADE_IN_PRICE),
                     'salesman'=>$SALESMAN,
                     'profit'=>number_format($PROFIT),
                     'comm_paid'=>$COMM_PAID,
                     'insurance'=>$INSURANCE,
                     'acc_purchased'=>$Acc_Purchased,
                     'gst_paid'=>$GST_Paid,
                     'gst_collected'=>$GST_Collected,
                     'bal_banked'=>$Bal_Banked,
                     'date_banked'=>$Date_Banked);
                  }
            $stmt->close();
            return $records;

}

function get_record($db,$id){
    
    $SQL="SELECT
    register.ID, 
    register.DESCRIPTION, 
    register.`STOCK NO`, 
    date_format(register.`PURCHASE DATE`,'%d/%m/%Y'), 
    register.REGO_No, 
    register.`PURCHASE PRICE`, 
    register.`PUR PRICE BEFORE WRITE BACK`, 
    register.`REVS TRANS FEE`, 
    register.REGO, 
    register.`STAMP DUTY`, 
    register.`W/SHOP PARTS ACC`, 
    register.`W/SHOP LABOUR`, 
    register.TRANSPORT, 
    register.MISC, 
    register.`WRITE BACK`, 
    register.`GST PAYABLE`, 
    register.`SOLD TO`, 
    register.`INV NO`, 
    register.`VIN NO`, 
    register.`YMAF UNIT NO`, 
    register.`YMAF REBATE`, 
    register.`TRADE DETAILS`, 
    register.`TRADE IN PRICE`, 
    register.`BAL PAID`, 
    date_format(register.`SOLD DATE`,'%d/%m/%Y'), 
    register.`SOLD PRICE`, 
    register.SALESMAN, 
    register.PROFIT, 
    date_format(register.`COMM PAID`,'%d/%m/%Y'), 
    register.INSURANCE, 
    register.Acc_Purchased, 
    register.GST_Piad, 
    register.GST_Collected, 
    register.Bal_Banked, 
    date_format(register.Date_Banked,'%d/%m/%Y')
    FROM
    register
    WHERE
	register.ID = ?
    ORDER BY
    register.`SOLD DATE` DESC 
    LIMIT 100";


        $stmt = $db->prepare($SQL);
        if ( false===$stmt ) {
            die('prepare() failed: ' . htmlspecialchars($db->error));
        }
        $stmt->bind_param("i",$id);
        $stmt->execute();
        if ( false===$stmt ) {
            die('execute() failed: ' . htmlspecialchars($db->error));
        }
        $stmt->store_result();
        $stmt->bind_result($ID,
                            $DESCRIPTION,
                            $STOCK_NO,
                            $PURCHASE_DATE,
                            $REGO_No,
                            $PURCHASE_PRICE,
                            $PUR_PRICE_BEFORE_WRITE_BACK,
                            $REVS_TRANS_FEE,
                            $REGO,
                            $STAMP_DUTY,
                            $WSHOP_PARTS_ACC,
                            $WSHOP_LABOUR,
                            $TRANSPORT,
                            $MISC,
                            $WRITE_BACK,
                            $GST_PAYABLE,
                            $SOLD_TO,
                            $INV_NO,
                            $VIN_NO,
                            $YMAF_UNIT_NO,
                            $YMAF_REBATE,
                            $TRADE_DETAILS,
                            $TRADE_IN_PRICE,
                            $BAL_PAID,
                            $SOLD_DATE,
                            $SOLD_PRICE,
                            $SALESMAN,
                            $PROFIT,
                            $COMM_PAID,
                            $INSURANCE,
                            $Acc_Purchased,
                            $GST_Paid,
                            $GST_Collected,
                            $Bal_Banked,
                            $Date_Banked);

                            
      $numrows = $stmt->num_rows;
        while($stmt->fetch())
              { 
                 $records[]= array('ID'=>$ID,
                 'description'=>$DESCRIPTION,
                 'stock_no'=>$STOCK_NO,
                 'purchase_date'=>$PURCHASE_DATE,
                 'rego_no'=>$REGO_No,
                 'purchase_price'=>$PURCHASE_PRICE,
                 'before_writeback'=>$PUR_PRICE_BEFORE_WRITE_BACK,
                 'revs'=>$REVS_TRANS_FEE,
                 'rego'=>$REGO,
                 'stamp_duty'=>$STAMP_DUTY,
                 'parts'=>$WSHOP_PARTS_ACC,
                 'labour'=>$WSHOP_LABOUR,
                 'transport'=>$TRANSPORT,
                 'misc'=>$MISC,
                 'writeback'=>$WRITE_BACK,
                 'gst_payable'=>$GST_PAYABLE,
                 'sold_to'=>$SOLD_TO,
                 'inv_no'=>$INV_NO,
                 'vin_no'=>$VIN_NO,
                 'ymaf_unit_no'=>$YMAF_UNIT_NO,
                 'ymaf_rebate'=>$YMAF_REBATE,
                 'trade_details'=>$TRADE_DETAILS,
                 'trade_price'=>$TRADE_IN_PRICE,
                 'bal_paid'=>$BAL_PAID,
                 'sold_date'=>$SOLD_DATE,
                 'sold_price'=>$BAL_PAID + $TRADE_IN_PRICE,
                 'salesman'=>$SALESMAN,
                 'profit'=>$PROFIT,
                 'comm_paid'=>$COMM_PAID,
                 'insurance'=>$INSURANCE,
                 'acc_purchased'=>$Acc_Purchased,
                 'gst_paid'=>$GST_Paid,
                 'gst_collected'=>$GST_Collected,
                 'bal_banked'=>$Bal_Banked,
                 'date_banked'=>$Date_Banked);
              }
        $stmt->close();
        return $records;

}
function delete_record($db,$id){
    $SQL = "DELETE FROM register WHERE ID = ?";

    $stmt = $db->prepare($SQL);
            if ( false===$stmt ) {
                die('prepare() failed: ' . htmlspecialchars($db->error));
            }
            $stmt->bind_param("i",$id);
            $stmt->execute();
            if ( false===$stmt ) {
                die('execute() failed: ' . htmlspecialchars($db->error));
            }
            $stmt->close();

            return 'deleted';

}

function report($db,$sdate,$edate){
    $records=[];
    list($d1,$m1,$y1) = explode("/", $sdate);
    list($d2,$m2,$y2) = explode("/", $edate);  
    
    $sd = $y1.'-'.$m1.'-'.$d1;
    $ed = $y2.'-'.$m2.'-'.$d2;

$SQL="SELECT
register.ID, 
register.DESCRIPTION, 
register.`STOCK NO`, 
date_format(register.`PURCHASE DATE`,'%d/%m/%Y'), 
register.REGO_No, 
register.`PURCHASE PRICE`, 
register.`PUR PRICE BEFORE WRITE BACK`, 
register.`REVS TRANS FEE`, 
register.REGO, 
register.`STAMP DUTY`, 
register.`W/SHOP PARTS ACC`, 
register.`W/SHOP LABOUR`, 
register.TRANSPORT, 
register.MISC, 
register.`WRITE BACK`, 
register.`GST PAYABLE`, 
register.`SOLD TO`, 
register.`INV NO`, 
register.`VIN NO`, 
register.`YMAF UNIT NO`, 
register.`YMAF REBATE`, 
register.`TRADE DETAILS`, 
register.`TRADE IN PRICE`, 
register.`BAL PAID`, 
date_format(register.`SOLD DATE`,'%d/%m/%Y'), 
register.`SOLD PRICE`, 
register.SALESMAN, 
register.PROFIT, 
date_format(register.`COMM PAID`,'%d/%m/%Y'), 
register.INSURANCE, 
register.Acc_Purchased, 
register.GST_Piad, 
register.GST_Collected, 
register.Bal_Banked, 
date_format(register.Date_Banked,'%d/%m/%Y')
FROM
register
WHERE
register.`SOLD DATE` BETWEEN ? AND ?
ORDER BY
register.`SOLD DATE` DESC 
LIMIT 100";

    $stmt = $db->prepare($SQL);
    if ( false===$stmt ) {
        die('prepare() failed: ' . htmlspecialchars($db->error));
    }
    $stmt->bind_param("ss",$sd,$ed);
    $stmt->execute();
    if ( false===$stmt ) {
        die('execute() failed: ' . htmlspecialchars($db->error));
    }
    $stmt->store_result();
    $stmt->bind_result($ID,
                        $DESCRIPTION,
                        $STOCK_NO,
                        $PURCHASE_DATE,
                        $REGO_No,
                        $PURCHASE_PRICE,
                        $PUR_PRICE_BEFORE_WRITE_BACK,
                        $REVS_TRANS_FEE,
                        $REGO,
                        $STAMP_DUTY,
                        $WSHOP_PARTS_ACC,
                        $WSHOP_LABOUR,
                        $TRANSPORT,
                        $MISC,
                        $WRITE_BACK,
                        $GST_PAYABLE,
                        $SOLD_TO,
                        $INV_NO,
                        $VIN_NO,
                        $YMAF_UNIT_NO,
                        $YMAF_REBATE,
                        $TRADE_DETAILS,
                        $TRADE_IN_PRICE,
                        $BAL_PAID,
                        $SOLD_DATE,
                        $SOLD_PRICE,
                        $SALESMAN,
                        $PROFIT,
                        $COMM_PAID,
                        $INSURANCE,
                        $Acc_Purchased,
                        $GST_Paid,
                        $GST_Collected,
                        $Bal_Banked,
                        $Date_Banked);

                        
    $numrows = $stmt->num_rows;
    while($stmt->fetch())
          { 
              $records[]= array('ID'=>$ID,
                                'description'=>$DESCRIPTION,
                                'stock_no'=>$STOCK_NO,
                                'purchase_date'=>$PURCHASE_DATE,
                                'rego_no'=>$REGO_No,
                                'purchase_price'=>$PURCHASE_PRICE,
                                'before_writeback'=>$PUR_PRICE_BEFORE_WRITE_BACK,
                                'revs'=>$REVS_TRANS_FEE,
                                'rego'=>$REGO,
                                'stamp_duty'=>$STAMP_DUTY,
                                'parts'=>$WSHOP_PARTS_ACC,
                                'labour'=>$WSHOP_LABOUR,
                                'transport'=>$TRANSPORT,
                                'misc'=>$MISC,
                                'writeback'=>$WRITE_BACK,
                                'gst_payable'=>$GST_PAYABLE,
                                'sold_to'=>$SOLD_TO,
                                'inv_no'=>$INV_NO,
                                'vin_no'=>$VIN_NO,
                                'ymaf_unit_no'=>$YMAF_UNIT_NO,
                                'ymaf_rebate'=>$YMAF_REBATE,
                                'trade_details'=>$TRADE_DETAILS,
                                'trade_price'=>$TRADE_IN_PRICE,
                                'bal_paid'=>$BAL_PAID,
                                'sold_date'=>$SOLD_DATE,
                                'sold_price'=>$BAL_PAID + $TRADE_IN_PRICE,
                                'salesman'=>$SALESMAN,
                                'profit'=>$PROFIT,
                                'comm_paid'=>$COMM_PAID,
                                'insurance'=>$INSURANCE,
                                'acc_purchased'=>$Acc_Purchased,
                                'gst_paid'=>$GST_Paid,
                                'gst_collected'=>$GST_Collected,
                                'bal_banked'=>$Bal_Banked,
                                'date_banked'=>$Date_Banked);
          }
    $stmt->close();
    return $records;

}

function changepw($db,$changepw){

$SQLa = "SELECT salt FROM users WHERE id=1";
    $stmta = $db->prepare($SQLa);
    //$stmta->bind_param("s",$user); 
    $stmta->execute();
    if ( false===$stmta ) {
        die('execute() failed: ' . htmlspecialchars($db->error));
    }
    $stmta->store_result();
    $stmta->bind_result($salt);
    $stmta->fetch();
   
    $password = md5($changepw.$salt);
    $stmta->close();

$SQL = "UPDATE `users` SET `pass` = ? WHERE id = '1'";
    $stmt = $db->prepare($SQL);
    if ( false===$stmt ) {
        die('prepare() failed: ' . htmlspecialchars($db->error));
    }
    $stmt->bind_param("s",$password);
    $stmt->execute();
    if ( false===$stmt ) {
        die('execute() failed: ' . htmlspecialchars($db->error));
    }
    $stmt->close();

$out = array('success'=>true);
return $out;

}

