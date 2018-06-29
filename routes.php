<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'upload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$klein = new \Klein\Klein();
echo 'Using route base ' . getenv('BASE_PATH') . '<br>';
$klein->with('/' . getenv('BASE_PATH'), function() use ($klein) {
	$klein->respond('GET', '/', function ($request, $response, $service, $app) use ($klein) {
		$service->render('xls.php');
	});

	$klein->respond('POST', '/', function ($request, $response, $service, $app) use ($klein) {
		$zip = handleUpload($request->files()['xls-file']['tmp_name']);
		$response->file($zip);
	});
});

$klein->dispatch();
