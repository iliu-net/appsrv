<?php
Header('Content-type: text/plain');
define('CF_API','https://api.cloudflare.com/client/v4/');

function get_api_key() {
	static $res;

	if (!isset($res)) {
		$secret = getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
		if (!$secret) throw new Exception('Missing credentials');
		$iv = substr(openssl_digest($secret,'sha256',TRUE),0,16);
		$apikey = file_get_contents(dirname(realpath(__FILE__)).'/apikey.enc');
		if ($apikey === FALSE) throw new Exception('Unable to read API key');
		$apikey = trim(openssl_decrypt($apikey,'AES-256-CTR',$secret,0,$iv));
		$res = preg_split('/\s+/',$apikey,2);
	}
  return $res;
}

function cfcall($call,array $opts = []) {
	list($email,$apikey) = get_api_key();
	$ch = curl_init();
	if (isset($opts['params'])) {
		$q = '?';
		foreach ($opts['params'] as $i=>$j) {
			$call .= $q.$i.'='.$j;
			$q = '&';
		}
	}
	curl_setopt($ch, CURLOPT_URL, CF_API.$call);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	if (getenv('https_proxy')) curl_setopt($ch, CURLOPT_PROXY, getenv('https_proxy'));
	$headers = [
		'X-Auth-Email: '.$email,
		'X-Auth-Key: '.$apikey,
		'Content-type: application/json',
	];
	if (isset($opts['xhdrs'])) {
		foreach ($opts['xhdrs'] as $i) {
			$headers[] = $i;
		}
	}
	
	if (isset($opts['PUT'])) {
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		$payload = json_encode($opts['PUT']);
		$headers[] = 'Content-Length: '.strlen($payload);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$payload);
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$txt = curl_exec($ch);
	curl_close($ch);
	if ($txt === FALSE) throw new Exception('CURL failure ('.$call.')');
	$php = json_decode($txt,TRUE);
	if ($php === NULL) throw new Exception('JSON parsing error ('.$call.')');
	if (empty($php['result']) || !count($php['result'])) throw new Exception('Empty Result Set ('.$call.')');
	return $php['result'];
}

#######################################################################

function update_dns($domain,$record,$type, $newaddr) {
	$zone_id = FALSE;
	foreach (cfcall('zones') as $i) {
		if ($i['name'] == $domain) $zone_id = $i['id'];
	}
	if ($zone_id === FALSE) throw new Exception('Domain not found');

	#echo "Zone_Id: $zone_id\n";
	#echo "record=$record\n";
	#echo "domain=$domain\n";

	$out = cfcall('zones/'.$zone_id.'/dns_records', [
							'params'=> [
									'name' => $record.'.'.$domain,
									'type' => $type,
							]]);
	$rdat = $out[0];
	$rid = $rdat['id'];
	if ($rdat['content'] == $newaddr) return FALSE;
	

	$out = cfcall('zones/'.$zone_id.'/dns_records/'.$rid,[
				'PUT' => [
						'type' => $type,
						'name' => $record.'.'.$domain,
						'content' => $newaddr,
						'ttl' => 1,
						'proxied' => FALSE,
				]]);
	return $out;
}

foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_CLIENT_IP',
          'REMOTE_ADDR'] as $key) {
  if (isset($_SERVER[$key])) {
    $client_ip = $_SERVER[$key];
    break;
  }
}

function get_mushu($mushu) {
	$mushu = explode('.', $mushu);
	if (count($mushu) < 3) throw new Exception('Invalid MUSHU');
	$i = array_pop($mushu);
	$j = array_pop($mushu);

	return [ implode($mushu) , $j.'.'.$i ];
}

try {
	if (!isset($client_ip)) throw new Exception('Unable to determine IP');
	if (!isset($_REQUEST['mushu'])) throw new Exception('No MUSHU!');
	list($record,$domain) = get_mushu($_REQUEST['mushu']);
	echo "IP: $client_ip\n";
	echo "Record: $record\n";
	echo "Domain: $domain\n";
	if (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $client_ip)) {
		$type = 'A';
	} elseif (preg_match('/^[:0-9A-Fa-f]+$/', $client_ip)) {
		$type = 'AAAA';
	} else {
		throw new Exception('Address type not recognized');
	}
	$res = update_dns($domain, $record, $type, $client_ip);
	if ($res) {
		echo "Results:\n";
		print_r($res);
	} else {
		echo "Record was not changed\n";
	}
} catch (Exception $e) {
	die("ERROR: ".$e->getMessage()."\n");	
}

exit;

