<?php
$api_contract = [
    [
        "endpoint" => "/api/v1/auth/login",
        "method" => "POST",
        "description" => "Autentikasi user menggunakan email dan password",
        "request_body" => [
            "email" => "string",
            "password" => "string"
        ],
        "response" => [
            "status" => "success",
            "token" => "string"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/auth/users",
        "method" => "GET",
        "description" => "Menampilkan daftar semua user",
        "response" => [
            "status" => "success",
            "data" => "array of users"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users/{id}",
        "method" => "GET",
        "description" => "Menampilkan detail user berdasarkan ID",
        "response" => [
            "status" => "success",
            "data" => [
                "id" => "integer",
                "name" => "string",
                "email" => "string",
                "created_at" => "string (datetime)"
            ]
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users",
        "method" => "POST",
        "description" => "Menambahkan user baru",
        "request_body" => [
            "name" => "string",
            "email" => "string",
            "password" => "string"
        ],
        "response" => [
            "status" => "success",
            "message" => "User berhasil ditambahkan",
            "data" => [
                "id" => "integer",
                "name" => "string",
                "email" => "string"
            ]
        ],
        "status_code" => 201,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users/{id}",
        "method" => "PUT",
        "description" => "Memperbarui data user berdasarkan ID",
        "request_body" => [
            "name" => "string (opsional)",
            "email" => "string (opsional)",
            "password" => "string (opsional)"
        ],
        "response" => [
            "status" => "success",
            "message" => "Data user berhasil diperbarui",
            "data" => [
                "id" => "integer",
                "name" => "string",
                "email" => "string"
            ]
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/users/{id}",
        "method" => "DELETE",
        "description" => "Menghapus user berdasarkan ID",
        "response" => [
            "status" => "success",
            "message" => "User berhasil dihapus"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/upload",
        "method" => "POST",
        "description" => "Mengunggah file ke server",
        "request_body" => [
            "file" => "binary (multipart/form-data)"
        ],
        "response" => [
            "status" => "success",
            "message" => "File berhasil diunggah",
            "file_url" => "string (URL file)"
        ],
        "status_code" => 201,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/health",
        "method" => "GET",
        "description" => "Memeriksa status API (health check)",
        "response" => [
            "status" => "ok",
            "uptime" => "string (e.g. '24h 15m')"
        ],
        "status_code" => 200,
        "version" => "v1"
    ],
    [
        "endpoint" => "/api/v1/version",
        "method" => "GET",
        "description" => "Menampilkan versi API yang sedang berjalan",
        "response" => [
            "status" => "success",
            "api_version" => "v1.0.0",
            "release_date" => "string (e.g. '2025-10-22')"
        ],
        "status_code" => 200,
        "version" => "v1"
    ]
];

header('Content-Type: application/json');
echo json_encode($api_contract, JSON_PRETTY_PRINT);
?>
