<?php
	require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

	$plate = new Plate\Plate('22س328ایران61');
	dd($plate->parsedData());
