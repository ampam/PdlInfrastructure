<?php


namespace Com\Mh\Laravel;

use Com\Mh\Ds\Infrastructure\Data\Db\DbUtils;
use Com\Mh\Ds\Infrastructure\Data\Db\MySql\MySqlUtils;
use Com\Mh\Ds\Infrastructure\Data\Row;
use Com\Mh\Ds\Infrastructure\Diagnostic\Debug;
use Com\Mh\Ds\Infrastructure\Languages\LanguageUtils;
use Com\Mh\Ds\Infrastructure\Languages\Pdl\PdlDecoder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

/**
 * Class PdlServiceProvider
 * @package Com\Mh\Laravel
 */
class PdlServiceProvider extends ServiceProvider
{

    const ConfigPath = __DIR__ . '/../../../../config/';
    const ResourceJsPath = __DIR__ . '/../../../../resources/js/pdl/';
    const PdlProjectPath = __DIR__ . '/../../../../pdl-project/';
    const ConfigFile = self::ConfigPath . 'pdl.php';

    /**
     * Bootstrap any package services.
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ( $this->app->runningInConsole() )
        {
            $this->publishes( [
                self::ConfigPath => self::getConfigDestPath( '/' ),
            ], 'pdl-php-config' );

            $this->publishes( [
                self::ResourceJsPath => self::getResourcesDestPath( 'js/pdl/' ),
            ], 'pdl-js-resources' );

            $this->publishes( [
                self::PdlProjectPath => self::getPdlProjectDestPath( '' ),
            ], 'pdl-project' );
        }
        else
        {
            $this->initPdl();
        }
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    private static function getPdlProjectDestPath( $path )
    {
        $result = base_path( "pdl-project/{$path}" );
        return $result;
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    private static function getResourcesDestPath( $path )
    {
        if ( function_exists( 'resource_path' ) )
        {
            $result = resource_path( $path );
        }
        else
        {
            $result = base_path( "resources/{$path}" );
        }

        return $result;
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

    /**
     *
     */
    private function setConfig()
    {
        $config = Config::get('pdl');
        LanguageUtils::setConfig( $config );
        PdlDecoder::setConfig( $config );
        MySqlUtils::setConfig( $config );
        DbUtils::setConfig( $config );
        Debug::setConfig( $config );
    }

    /**
     *
     */
    private function initPdl()
    {
        $rowFactory = LaravelRowFactory::getInstance();
        Row::setDefaultFactory( $rowFactory );
        $this->setConfig();
    }
}
