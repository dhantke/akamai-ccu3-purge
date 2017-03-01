<?php
require __DIR__ . '/../vendor/autoload.php';

	// check for uploaded file
	if (!empty($_FILES)) {
		// get temp file path
		$edgerc = $_FILES['edgerc']['tmp_name'];
		if (file_exists($edgerc) && is_uploaded_file($edgerc)) { // handle uploaded file

			// create client
			$edgeGridClient = \Akamai\Open\EdgeGrid\Client::createFromEdgeRcFile('default', $edgerc);
			
			$purge_body = [
					'hostname' => $_POST['hostname'],
					'objects' => [
							"/image.jpg",
							"/styles.css"
					]
			];
			
			try {
				$responseBody = $edgeGridClient->post('/ccu/v3/invalidate/url', [
						'body' => json_encode($purge_body),
						'headers' => ['Content-Type' => 'application/json']
				]);
			
				$response = json_decode($responseBody->getBody());
				echo 'Success (' .$purge->getStatusCode(). ')' . PHP_EOL;
				echo 'Estimated Purge Time: ' .$response->estimatedSeconds. 's' . PHP_EOL;
			} catch (\GuzzleHttp\Exception\ClientException $e) {
				echo "An error occurred: " .$e->getMessage(). "\n";
				echo "Please try again with --debug or --verbose flags.\n";
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