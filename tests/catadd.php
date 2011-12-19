<?php
$c = curl_init('http://xn--h1afdfc2d/ajax/categoriesAdd');
curl_setopt($c, CURLOPT_POST, 1);
curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(array(
    'parent'=>'test',
    'name'=>'test'
)));
curl_exec($c);
?>