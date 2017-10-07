<?php
/**
 * Generate a slug from a string.
 *
 * @return string
 */
function sp_create_slug($text)
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
 * @return array
 */
function sp_get_apps()
{
    $dirs = array_filter( glob( SERVER_APP_DIR . '/*'), 'is_dir' );
    $apps = array();

    foreach( $dirs as $dir ){
        $apps[sp_path($dir)] = str_replace( SERVER_APP_DIR . '/', '', $dir );
    }

    if( count( $apps ) > 0 ){
        return $apps;
    }

    return false;
}

/**
 * Returns a list of installed templates.
 *
 * @return array
 */
function sp_get_stacks()
{
    $dirs = array_filter( glob( SERVER_STACK_DIR . '/*'), 'is_dir' );
    $templates = array();

    foreach( $dirs as $dir ){
        $templates[sp_path($dir)] = str_replace( SERVER_STACK_DIR . '/', '', $dir );
    }

    if( count( $templates ) > 0 ){
        return $templates;
    }

    return false;
}

/**
 * Load environment variables based on .env file.
 *
 * @return array
 */
function sp_get_env($dir)
{
    if(file_exists($dir.'/.env')){
        $environment = (new josegonzalez\Dotenv\Loader($dir.'/.env'))->parse()->toArray();

        return $environment;
    }

    return false;
}

/**
 * Transform a path into a Windows compatible path.
 *
 * @return string
 */
function sp_path($path) {
    if(sp_is_windows()) {
        $path = str_replace( ["/"], "\\",$path);
    }
    return $path;
}

/**
 * Checks if OS is windows.
 *
 * @return bool
 */
function sp_is_windows()
{
    if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN') {
      return true;
    }
    return false;
}

/**
 * Copy a directory.
 *
 * @return void
 */
function sp_copy_directory( $src, $dst ) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if($file != 'config') {
              if ( is_dir($src . '/' . $file) ) {
                  sp_copy_directory($src . '/' . $file,$dst . '/' . $file);
              }
              else {
                  copy($src . '/' . $file,$dst . '/' . $file);
              }
            }
        }
    }
    closedir($dir);
}

/**
 * Change an environment variable in a .env file.
 *
 * @return void
 */
function sp_change_env_var($dir, $key, $value)
{
    $path = sp_path($dir.'/.env');

    if(file_exists($path)) {
      $env = sp_get_env($dir);
      $old = (isset($env[$key]) ? $env[$key] : false);

      // Change existing value
      if ($old && file_exists($path)) {
        file_put_contents($path, str_replace(
          "$key=".$old, "$key=".$value, file_get_contents($path)
        ));
      } else {
        // Add value
        $content = file_get_contents($path) . "\n" . $key . '=' . $value;
        file_put_contents($path, $content);
      }
    }
}
