<?php
namespace Src\Helpers;
class Jwt{
    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string|false {
        $pad = strlen($data) % 4;
        if ($pad) {
            $data .= str_repeat('=', 4 - $pad);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function sign(array $payload, string $secret, string $alg='HS256'){
        $header=['typ'=>'JWT','alg'=>$alg];
        $segments = [];
        $segments[] = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $segments[] = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = self::base64UrlEncode($signature);
        return implode('.', $segments);
    }

    public static function verify(string $jwt, string $secret){
        $parts = explode('.', $jwt);
        if(count($parts)!=3) return null;
        [$header, $body, $signature] = $parts;

        $decodedSignature = self::base64UrlDecode($signature);
        if($decodedSignature===false) return null;
        $expected = hash_hmac('sha256', $header.'.'.$body, $secret, true);
        if(!is_string($expected)||!is_string($decodedSignature)) return null; 
        if(!hash_equals($expected, $decodedSignature)) return null; 

        $payload = self::base64UrlDecode($body);
        if($payload===false) return null;
        $payloadData = json_decode($payload, true);
        if(!is_array($payloadData)) return null;
        if(isset($payloadData['exp']) && time()>$payloadData['exp']) return null; 
        return $payloadData;
    }
}
