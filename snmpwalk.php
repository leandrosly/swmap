<html>
<head>
<title>MACs via SNMP</title>
<style>
</style>
<script>
	function marcar(mac) {
		for (let i = 0; i < document.getElementsByName(mac).length; i++) {
			document.getElementsByName(mac)[i].style.backgroundColor = '#FF8888';
		}
	}
	function chgqry() {
		window.history.replaceState( {} , 'foo', '/foo' );
	}
</script>
</head>
<body>
<?php

	$marcas = array(
		"00:0C:29"=>"VMware, Inc.",
		"00:05:03"=>"ICONAG",
		"00:1E:C9"=>"Dell Inc.",
		"20:89:84"=>"COMPAL INFORMATION (KUNSHAN) CO., LTD.",
		"00:21:CC"=>"Flextronics International",
		"08:9E:01"=>"Quanta Computer Inc.",
		"00:7E:56"=>"China Dragon Technology Limited",
		"00:25:64"=>"Dell Inc.",
		"30:D0:42"=>"Dell Inc.",
		"68:D7:9A"=>"Ubiquiti Networks Inc.",
		"24:5A:4C"=>"Ubiquiti Networks Inc.",
		"D0:94:66"=>"Dell Inc.",
		"B8:AC:6F"=>"Dell Inc.",
		"C0:CB:38"=>"Hon Hai Precision Ind. Co.,Ltd.",
		"10:C3:7B"=>"ASUSTek COMPUTER INC.",
		"00:24:E8"=>"Dell Inc."
	);

	function mac_dechex($macdec) {
		foreach(explode(".",$macdec) as $dec) {
			$machex .= $machex ? ":" : "";
			$machex .= str_pad(dechex($dec),2,"0",STR_PAD_LEFT);
		}
		return strtoupper($machex);
	}

	$ips = explode(",", $_GET["ips"]);

	foreach($ips as $ip) {
		$arp=`arp -a $ip`;
		$swmac = strtoupper(trim(preg_replace("/^.*((([0-9a-f]){2}:){5}([0-9a-f]){2}).*$/","$1",$arp)));
		$switches[$ip] = $swmac;
	}
	#echo "<pre>\n";
	#print_r($switches);
	#echo "</pre>\n";

	echo "<spam  onclick=\"chgqry()\">teste</spam>";

	echo "<table><tr>\n";
	foreach($switches as $ip=>$swmac) {
		echo "<td style='vertical-align:top;border:1px solid gray';>\n";
		echo "<pre>\n";
		echo "Switch: $ip\n";
		echo "MAC: <spam onclick=\"marcar('$swmac')\">$swmac</spam>\n\n";
		if ($walk = snmp2_real_walk($ip,"public","1.3.6.1.2.1.17.7.1.2.2.1.2")) {
			echo count($walk) . " endereços\n\n";
			if ($_GET["ord"]) asort($walk, SORT_NATURAL);
			foreach ($walk as $oid => $value) {
				$mac = preg_replace("/iso.3.6.1.2.1.17.7.1.2.2.1.2.([\d]+)./","\\1;",$oid);
				$vlan = explode(";",$mac)[0];
				$mac = explode(";",$mac)[1];
				$machex = mac_dechex($mac);
				if ($_GET["onlyswitches"] != true || in_array($machex,$switches)) {
					$marca = $_GET["marcas"] ? $marcas[substr($machex, 0, 8)] : "";
					echo str_pad($vlan, 3, " ", STR_PAD_LEFT) . "  <spam name=\"$machex\" onclick=\"marcar('$machex')\">$machex</spam> - " . str_pad(substr($value, 9), 2, " ", STR_PAD_LEFT) . " " . $marca . "\n";
				}
			}
		} else {
			echo "n&atilde;o rolou..<br>\n";
		}
		echo "</pre>\n";
		echo "</td>\n";
	}
	echo "</tr></table>\n";

?>
</body>
</html>
