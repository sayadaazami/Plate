Iran Vehicle Plate Number Package
===================

### Requirements:
- php ~5.4.* 

Instalation:
==========
First add package name to your composer requirements
```json
"require": {
    "plate/plate": "dev"
}
```

Next, update Composer from the Terminal:
>composer update

Next, add your new provider to the providers array of config/app.php:

```php
'providers' => [
    // ...
    Plate\PlateServiceProvider::class,
    // ...
  ]
```

Finally, add class alias to the aliases array of config/app.php:

```php
'aliases' => [
	 // ...
      'Plate' => Plate\PlateFacade::class
    // ...
]
```

Ho to use:
====
```php
$plate_number = '12' .
	'س' .
	321 .
	'ایران' .
	. 22;
$plate = Plate::setPlate($plate_number);
$plate->getType(); // تاکسی
$plate->getState(); // تهران
```