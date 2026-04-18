<?php
function get_settings_map(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $rows = $pdo->query('SELECT `key`,`value` FROM settings')->fetchAll();
    $map = [];
    foreach ($rows as $row) {
        $map[$row['key']] = $row['value'];
    }
    $cache = $map;
    return $map;
}

function setting(PDO $pdo, string $key, string $default = ''): string
{
    $settings = get_settings_map($pdo);
    return $settings[$key] ?? $default;
}
