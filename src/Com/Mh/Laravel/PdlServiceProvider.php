<?php


namespace Com\Mh\Laravel;

use Illuminate\Support\ServiceProvider;

/**
 * Class PdlServiceProvider
 * @package Com\Mh\Laravel
 */
class PdlServiceProvider extends ServiceProvider
{

    const ConfigPath = __DIR__ . '/../../../../config/';
    const ConfigFile = self::ConfigPath . 'config.php';

    /**
     * Bootstrap any package services.
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes( [
            self::ConfigPath => self::getConfigDestPath( 'pdl/'),
        ], 'pdl-php-config' );
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    private static function getConfigDestPath( $path )
    {
        if ( function_exists( 'config_path' ) )
        {
            $result = config_path( $path );
        }
        else
        {
            $result = base_path( "config/{$path}" );
        }

        return $result;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            self::ConfigFile, 'pdl'
        );
    }
}
