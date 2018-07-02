<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'upload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$klein = new \Klein\Klein();

$basePath = getenv('BASE_PATH');
if(!empty($basePath)) {
	$klein->with('/' . $basePath, function() use ($klein) {
		setupRoutes($klein);
	});
} else {
	setupRoutes($klein);
}

$klein->dispatch();

function setupRoutes($klein)
{

	$klein->respond('GET', '/', function ($request, $response, $service, $app) use ($klein) {
		$service->render('xls.php');
	});

	$klein->respond('POST', '/', function ($request, $response, $service, $app) use ($klein) {
		$zip = handleUpload($request->files()['xls-file']['tmp_name']);
		$response->file($zip);
	});
}