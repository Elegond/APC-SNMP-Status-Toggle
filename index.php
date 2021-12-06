<?php
error_reporting(E_ALL);
// Return back the numeric OIDs, instead of text strings.
snmp_set_oid_numeric_print(1);

// Get just the values.
snmp_set_quick_print(TRUE);

// For sequence types, return just the numbers, not the string and numbers.
snmp_set_enum_print(TRUE);
// Don't let the SNMP library get cute with value interpretation.  This makes
// MAC addresses return the 6 binary bytes, timeticks to return just the integer
// value, and some other things.
//snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
if(isset( $_GET["js"] )&&$_GET["js"] === "yes"){
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body);
        //var_dump($data);

        switch ($data->mode) {
                case "toggle":
                        if($data->val == 1){
                                snmpset($data->pdu, "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.3.1.1.4.".$data->outlet, "i", 2);
                        }

                        if($data->val == 2){
                                snmpset($data->pdu, "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.3.1.1.4.".$data->outlet, "i", 1);
                        }
                        printOutlett($data->pdu, $data->outlet);
                        break;
                case "changename":
                        snmpset($data->pdu, "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.4.1.1.2.".$data->outlet, "s", $data->val);
                        printOutlett($data->pdu, $data->outlet);
                        break;
        }

        //echo "OK";
        exit();

}
?>
<html>
<head>
 <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function jsButton(outl,data) {
        var xhr2 = new XMLHttpRequest();
        xhr2.open('POST','/test.php?js=yes', true);
        xhr2.setRequestHeader('Content-Type', 'application/json');
        xhr2.send(data);
        xhr2.onload = function() {
          console.log('HELLO');
          document.getElementById(outl).innerHTML=this.responseText;
          console.log(this.responseText);
        };
}

</script>

</head>
<body>
<?php

//set name
//snmpset("192.168.14.10", "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.4.1.1.2."."5", "s", "G9 P1");
//snmpset("192.168.14.10", "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.4.1.1.2."."6", "s", "G9 P2");

//set status
//snmpset("192.168.14.10", "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.3.1.1.4."."16", "i", 1);

//print status
//$o_stat = @snmpget("192.168.14.10", "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.3.1.1.4.16");
//print $o_stat;

function printbuttonjs($data){
        $data2 = json_decode("{".$data."}");
        print "jsButton('";
        print $data2->pdu.'-'.$data2->outlet."', JSON.stringify({'pdu':'".$data2->pdu."','outlet':'".$data2->outlet."','mode':'".$data2->mode."','val':'".$data2->val."'}));";

}
function printOutlett($ip, $i) {
        $o_name = str_replace('"','',@snmpget($ip, "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.5.1.1.2." . strval($i) ));
        print "<td style='width:25px;'>".strval($i).".</td><td>";

        print $o_name;
        print "</td>";
        $o_stat = @snmpget($ip, "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.3.1.1.4.". strval($i) );
        //print $o_stat;
        if ($o_stat == 1){
        print "<td class='alert-success' style='width:25px;'><center>ON</center></td>";
        print '<td style="padding: 0;width: 100;"><button type="button" onclick="';

        printbuttonjs('"pdu":"'.$ip.'", "outlet":"'.$i.'", "mode":"toggle", "val":"'. $o_stat . '"') ;
        print '" class="btn  rounded-0" style="width:100%;height: 100%;">Toggle</button></td>';
        }

        if ($o_stat == 2){
        print "<td class='alert-danger' style='width:25px;'><center>OFF</center></td>";

        print '<td style="padding: 0;width: 100;"><button type="button" onclick="';

        printbuttonjs('"pdu":"'.$ip.'", "outlet":"'.$i.'", "mode":"toggle", "val":"'. $o_stat . '"') ;
        print '" class="btn  rounded-0" style="width:100%;height: 100%;">Toggle</button></td>';

        }
}
function printPDU($ip) {
        $name = str_replace('"','',@snmpget($ip, "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.1.1.0"));
        $o_nr = @snmpget($ip, "A3sMphf2", "1.3.6.1.4.1.318.1.1.12.3.1.4.0");
        print $name2;
        print "<table class='table table-striped table-bordered table-hover'><thead>
                  <tr>
                        <th colspan='2'>".strval($name)."</th>
                        <th>Status</th>
                        <th>Toggle</th>
                  </tr>
                </thead><tbody>";

        for($i=1; $i < $o_nr+1; $i++) {
                print "<tr id='".$ip."-".strval($i)."'>";
                printOutlett($ip, $i);
                print "</tr>";
        }
        print "</tbody></table>";
}
printPDU("192.168.14.11");
printPDU("192.168.14.10");




?>
</body>
</html>
