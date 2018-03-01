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
 * Returns a list of installed apps.
 *
 * @return mixed
 */
function dp_get_apps()
{
    $dirs = array_filter(glob(SERVER_APP_DIR . '/*'), 'is_dir');
    $apps = array();

    foreach ($dirs as $dir) {
        $apps[dp_path($dir)] = str_replace(SERVER_APP_DIR . '/', '', $dir);
    }

    if (count($apps) > 0) {
        return $apps;
    }
    return false;
}

/**
 * Returns a list of installed templates.
 *
 * @return mixed
 */
function dp_get_stacks()
{
    $dirs = array_filter(glob(SERVER_STACK_DIR . '/*'), 'is_dir');
    $templates = array();

    foreach ($dirs as $dir) {
        $templates[dp_path($dir)] = str_replace(SERVER_STACK_DIR . '/', '', $dir);
    }

    if (count($templates) > 0) {
        return $templates;
    }
    return false;
}

/**
 * Load environment variables based on .env file.
 *
 * @param $dir
 * @return mixed
 */
function dp_get_env($dir)
{
    if (file_exists($dir . '/.env')) {
        $environment = (new josegonzalez\Dotenv\Loader($dir . '/.env'))->parse()->toArray();

        return $environment;
    }
    return false;
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
 * Copy a directory.
 *
 * @param $src
 * @param $dst
 * @return void
 */
function dp_copy_directory($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if ($file != 'config') {
                if (is_dir($src . '/' . $file)) {
                    dp_copy_directory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
    }
    closedir($dir);
}

/**
 * Remove a non empty directory.
 *
 * @param $dir
 * @return bool
 */
function dp_rmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    dp_rmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        reset($objects);
        return rmdir($dir);
    }
    return false;
}

/**
 * Change an environment variable in a .env file.
 *
 * @param $dir
 * @param $key
 * @param $value
 * @return void
 */
function dp_change_env_var($dir, $key, $value)
{
    $path = dp_path($dir . '/.env');

    if (file_exists($path)) {
        $env = dp_get_env($dir);
        $old = (isset($env[$key]) ? $env[$key] : false);

        // Change existing value
        if ($old && file_exists($path)) {
            file_put_contents($path, str_replace(
                "$key=" . $old, "$key=" . $value, file_get_contents($path)
            ));
        } else {
            // Add value
            $content = file_get_contents($path) . "\n" . $key . '=' . $value;
            file_put_contents($path, $content);
        }
    }
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
