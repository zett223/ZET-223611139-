<?php
$header = ['typ' => 'JWT', 'alg' => 'HS256'];
$payload = [
  'sub' => 2,
  'name' => 'zet',
  'role' => 'admin',
  'iat' => time(),
  'exp' => time() + 1
];
$secret = 'zetpakiding123456789101112131415';

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$header64 = base64UrlEncode(json_encode($header));
$payload64 = base64UrlEncode(json_encode($payload));
$signature = base64UrlEncode(hash_hmac('sha256', "$header64.$payload64", $secret, true));

$jwt = "$header64.$payload64.$signature";
echo $jwt;