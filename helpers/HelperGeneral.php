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
 * Returns the server configuration.
 */
function dp_get_app_config($appDir, $key = 'all') {
    $configFile = $appDir . '/config.yml';
    if(file_exists($configFile)) {
        $config = Symfony\Component\Yaml\Yaml::parseFile($configFile);
    } else {
        $config = array();
    }

    if(isset($config['stack'])) {
        $defaultConfigFile = SERVER_WORKDIR . '/stacks/' . $config['stack'] . '/config.default.yml';
        if(file_exists($defaultConfigFile)) {
            $default = Symfony\Component\Yaml\Yaml::parseFile($defaultConfigFile);
            $config = array_replace_recursive($default, $config);
            if(! empty($key) && isset($config[$key])) {
                return $config[$key];
            } elseif ($key == 'all') {
                return $config;
            }
        }
    }

    return [];
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
    $appsConfig = dp_get_config('apps');
    $dirs = array_filter(glob( $appsConfig['configPath'] . '/*'), 'is_dir');
    $apps = array();
    foreach ($dirs as $dir) {
        $apps[dp_path($dir)] = str_replace($appsConfig['configPath'] . '/', '', $dir);
    }
    if (count($apps) > 0) {
        return $apps;
    }
    return false;
}

/**
 * Delete a (not empty) directory.
 *
 * @param $dir
 * @return bool
 */
function dp_delete_dir($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!dp_delete_dir($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}


/**
 * Returns a list available application stacks.
 *
 * @return mixed
 */
function dp_get_app_stacks()
{
    $stackDir = SERVER_WORKDIR . '/stacks/apps';
    $dirs = array_filter(glob( $stackDir . '/*'), 'is_dir');
    $stacks = array();
    foreach ($dirs as $dir) {
        $stackName = str_replace($stackDir . '/', '', $dir);
        $stacks[] = $stackName;
    }
    if (count($stacks) > 0) {
        return $stacks;
    }
    return false;
}

/**
 * Returns code screenshot by a given URL.
 *
 * @param $url
 * @return bool|string
 */
function dp_get_code($url)
{
    $url = trim($url);
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
    );
    $query = parse_url($url, PHP_URL_QUERY);
    if ($query) {
        $url .= '&dockerpilot-timestamp=' . uniqid();
    } else {
        $url .= '?dockerpilot-timestamp=' . uniqid();
    }
    return @file_get_contents($url, false, stream_context_create($arrContextOptions));
}