<?php
namespace Src\Controllers;

use Src\Helpers\Response;

class BaseController
{
    protected array $cfg;

    public function __construct(array $cfg)
    {
        $this->cfg = $cfg;
    }

    protected function ok(array $data = [], int $code = 200): void
    {
        Response::json($data, $code);
    }

    protected function error(int $code, string $message, array $errors = []): void
    {
        Response::jsonError($code, $message, $errors);
    }
}
