# KernKalender
Flexible Calendar for WordPress developers. Features API and with automatic view rendering and shortcodes for dummies


```php

$calendar = new KernKalender();


// show a day
$args = array(
   'view'=>'day',
   'day'=>'26',
   'month'=>'8',
   'year'=>'2016',
);

// show a month
$args = array(
   'view'=>'month',
   'day'=>'26',
   'month'=>'8',
   'year'=>'2016',
);


$calendar -> load_date( $args );

// show today
$calendar -> load_date();


```
