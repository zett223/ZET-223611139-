<?php
namespace Src\Middlewares;

use Src\Helpers\Response;
use Src\Helpers\Jwt;

class AuthMiddleware
{
    public static function user(array $cfg)
    {
        $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/Bearer\s+(.+)/', $hdr, $m)) {
            Response::jsonError(401, 'Missing token');
            return null;
        }
        $token = $m[1];
        $pl = Jwt::verify($token, $cfg['app']['jwt_secret']);
        if (!$pl) {
            Response::jsonError(401, 'Invalid/expired token');
            return null;
        }
        return $pl;
    }
    public static function admin(array $cfg)
    {
        $pl = self::user($cfg);
        if (!$pl || ($pl['role'] ?? '') !== 'admin') {
            Response::jsonError(403, 'Forbidden');
            return null;
        }
        return $pl;
    }
}