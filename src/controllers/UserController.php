<?php
namespace Src\Controllers;

use Src\Repositories\UserRepository;
use Src\Validation\Validator;

class UserController extends BaseController
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = min(100, max(1, (int)($_GET['per_page'] ?? 10)));

        $repository = new UserRepository($this->cfg);
        $this->ok($repository->paginate($page, $perPage));
    }

    public function show(int $id): void
    {
        $repository = new UserRepository($this->cfg);
        $user = $repository->find($id);
        $user ? $this->ok($user) : $this->error(404, 'User not found');
    }

    public function store(): void
    {
        $input = Validator::sanitize(json_decode(file_get_contents('php://input'), true) ?? []);
        $validator = Validator::make($input, [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|min:6|max:72',
            'role' => 'enum:user,admin',
        ]);

        if ($validator->fails()) {
            $this->error(422, 'Validation error', $validator->errors());
            return;
        }

        $hash = password_hash($input['password'], PASSWORD_DEFAULT);
        $repository = new UserRepository($this->cfg);

        try {
            $this->ok(
                $repository->create(
                    $input['name'],
                    $input['email'],
                    $hash,
                    $input['role'] ?? 'user'
                ),
                201
            );
        } catch (\Throwable $e) {
            $this->error(400, 'Create failed', ['details' => $e->getMessage()]);
        }
    }

    public function update(int $id): void
    {
        $input = Validator::sanitize(json_decode(file_get_contents('php://input'), true) ?? []);
        $validator = Validator::make($input, [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|max:150',
            'role' => 'enum:user,admin',
        ]);

        if ($validator->fails()) {
            $this->error(422, 'Validation error', $validator->errors());
            return;
        }

        $repository = new UserRepository($this->cfg);
        $this->ok($repository->update($id, $input['name'], $input['email'], $input['role']));
    }

    public function destroy(int $id): void
    {
        $repository = new UserRepository($this->cfg);
        $deleted = $repository->delete($id);
        $deleted ? $this->ok(['deleted' => true]) : $this->error(400, 'Delete failed');
    }
}
