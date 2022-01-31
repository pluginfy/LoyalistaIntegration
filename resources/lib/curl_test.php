<?php

$url = "";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
//curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
    "Content-Type: application/json",
    "Accept: application/json",
    "Authorization: Bearer {token}"
);

// json_encode($fields)

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$data = '{"vendor_id":"2","access_token":"2|4ZiUunRpYSL6efouPIh9qOXbhKJnwpqjgGm3ftcG"}';

curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$resp = curl_exec($curl);
curl_close($curl);


return ($resp);





