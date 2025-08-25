<?php
function rate_limit(string $key, int $limit, int $seconds): bool {
    $file = sys_get_temp_dir() . '/rate_' . md5($key);
    $entries = [];
    if (file_exists($file)) {
        $entries = json_decode(file_get_contents($file), true) ?: [];
    }
    $now = time();
    $entries = array_filter($entries, fn($t) => $t > $now - $seconds);
    if (count($entries) >= $limit) {
        return false;
    }
    $entries[] = $now;
    file_put_contents($file, json_encode($entries));
    return true;
}
