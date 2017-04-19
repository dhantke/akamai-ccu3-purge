<?php
// composer autoload
require __DIR__ . '/../vendor/autoload.php';

	// check for uploaded file
	if (!empty($_FILES)) {
		// get temp file path
		$edgerc = $_FILES['edgerc']['tmp_name'];
		if (file_exists($edgerc) && is_uploaded_file($edgerc)) { // handle uploaded file
			// create client
			$edgeGridClient = \Akamai\Open\EdgeGrid\Client::createFromEdgeRcFile('default', $edgerc);
			$edgeGridClient->setVerbose(true);
			$edgeGridClient->setDebug(true);
			// build the POST body
			$purge_body = [
					'hostname' => $_POST['hostname'],
					'objects' => [
							"/com/de",
							"/com/en"
					]
			];
			
			try {
				echo "trying to purge objects: " . implode(", ", $purge_body['objects']) . "<br><br>";
				// WORKAROUND: parse file again, because the properties of the clients are protected
				$tmpCfg = file_get_contents($edgerc);
				$tmpCfg = parse_ini_string($tmpCfg, true, INI_SCANNER_RAW);
				// send purge request
				$response = $edgeGridClient->post($tmpCfg['default']['host'] . '/ccu/v3/invalidate/url', [
						'body' => json_encode($purge_body),
						'headers' => ['Content-Type' => 'application/json']
				]);
			
				$responseBody = json_decode($response->getBody());
				echo 'Success (' . $response->getStatusCode() . ')' . PHP_EOL;
				echo 'Estimated Purge Time: ' . $responseBody->estimatedSeconds . 's' . PHP_EOL;
				echo "<br><br>";
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				echo "An error occurred: " . $e->getMessage() . PHP_EOL;
				echo "Please try again with --debug or --verbose flags.\n";
				echo "<br><br>";
			}
		}
		
	}
?>
<html>
<head>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body style="margin: 2em">
	<form method="post" enctype="multipart/form-data">
		<label for="edgerc">edgerc file</label>
		<input id="edgerc" name="edgerc" type="file">
		<br>
		<label for="hostname" style="display: block">hostname</label>
		<input id="hostname" name="hostname" type="text">
		<br>
		<br>
		<button type="submit">Purge</button>
	</form>

</body>
</html>