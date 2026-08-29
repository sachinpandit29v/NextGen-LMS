<?php
if (!function_exists('getAppBaseUrl')) {
    function getAppBaseUrl(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '';
        $scriptName = str_replace('\\', '/', $scriptName);

        $directory = str_replace('\\', '/', dirname($scriptName));
        if (preg_match('#/(user|admin)$#', $directory)) {
            $directory = str_replace('\\', '/', dirname($directory));
        }

        if ($directory === '/' || $directory === '.' || $directory === '\\') {
            return '';
        }

        return rtrim($directory, '/');
    }
}

if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', getAppBaseUrl());
}

if (!defined('APP_USER_PATH')) {
    define('APP_USER_PATH', APP_BASE_URL . '/user');
}

if (!defined('APP_ADMIN_PATH')) {
    define('APP_ADMIN_PATH', APP_BASE_URL . '/admin');
}

if (!defined('APP_LOGOUT_ACTION')) {
    define('APP_LOGOUT_ACTION', APP_USER_PATH . '/logout.php');
}
