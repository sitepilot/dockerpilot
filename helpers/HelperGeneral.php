<?php
/**
 * Generate a slug from a string.
 *
 * @param $text
 * @return string
 */
function dp_create_slug($text)
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '-');

    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

/**
 * Returns the server configuration.
 */
function dp_get_config($key) {
    $configFile = SERVER_WORKDIR . '/config.yml';
    if(file_exists($configFile)) {
        $config = Symfony\Component\Yaml\Yaml::parseFile(SERVER_WORKDIR . '/config.yml');
    } else {
        $config = array();
    }

    $default = Symfony\Component\Yaml\Yaml::parseFile(SERVER_WORKDIR . '/config.default.yml');
    $config = array_replace_recursive($default, $config);
    if(! empty($key) && isset($config[$key])) {
        return $config[$key];
    } elseif (empty($key)) {
        return [];
    }
    return $config;
}

/**
 * Transform a path into a Windows compatible path.
 *
 * @param $path
 * @return string
 */
function dp_path($path)
{
    if (dp_is_windows()) {
        $path = str_replace(["/"], "\\", $path);
    }
    return $path;
}

/**
 * Checks if OS is windows.
 *
 * @return bool
 */
function dp_is_windows()
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return true;
    }
    return false;
}

/**
 * Generate a random password.
 *
 * @param int $length
 * @return string
 */
function dp_random_password($length = 10)
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

/**
 * Returns a list of installed apps.
 *
 * @return mixed
 */
function dp_get_apps()
{
    $appsDir = SERVER_WORKDIR . '/apps';
    $dirs = array_filter(glob($appsDir . '/*'), 'is_dir');
    $apps = array();

    foreach ($dirs as $dir) {
        $apps[dp_path($dir)] = str_replace($appsDir . '/', '', $dir);
    }

    if (count($apps) > 0) {
        return $apps;
    }
    return false;
}
