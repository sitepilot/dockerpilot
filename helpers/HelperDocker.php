<?php
/**
 * Get container id by name.
 *
 * @return string
 */
function sp_get_container_id( $name )
{
    $container_id = shell_exec( 'docker ps -aqf "name=^/' . $name . '$" --filter "status=running"' );

    if( $container_id ){
        return trim( preg_replace( '/\s\s+/', ' ', $container_id ) );
    }

    return false;
}
