<?php
/*
Plugin Name: Catalyst WP MVC
Plugin URI: http://github.com
Description: Creates a global interface for handling MVC components in a Wordpress Theme. It's main purpose is to create a global object that will allow us to override models/views/templates in our child-themes.
Author: Mike McGuire
Version: 0.0.1
Author URI: http://bokkagroup.com
*/

namespace CatalystWP;

/**
 * CatalystWP
 * @version 0.0.1 Singleton
 */
class CatalystMVC {

	private static $instance;

	public function __construct()
    {
		//Set directory variables
        define("CATALYST_WP_MVC_DIRECTORY",   plugin_dir_path(__FILE__));
        define("THEME_PARENT_DIR", get_template_directory());
        define("THEME_CHILD_DIR", get_stylesheet_directory());

        $this->loadHandlebars();

		//load base classes
		require_once(CATALYST_WP_MVC_DIRECTORY . 'controllers/BaseController.php');
		require_once(CATALYST_WP_MVC_DIRECTORY . 'views/BaseView.php');
		require_once(CATALYST_WP_MVC_DIRECTORY . 'models/BaseModel.php');
		require_once(CATALYST_WP_MVC_DIRECTORY . 'models/Menu.php');

        require_once(CATALYST_WP_MVC_DIRECTORY . 'autoloader.php');

		//auto load controllers
        add_action('init', array($this, 'autoLoad'));
	}

    private function loadHandlebars()
    {
    //load mustache if nobody else has
        if (!class_exists('Mustache_Autoloader') && !class_exists('Mustache_Engine')) {
            global $Handlebars;
            require_once(CATALYST_WP_MVC_DIRECTORY . 'lib/Handlebars/Autoloader.php');
        }

        \Handlebars\Autoloader::register();
        if (file_exists('THEME_CHILD_DIR')
            && file_exists(THEME_CHILD_DIR . '/templates')) {
            $templateDir = THEME_CHILD_DIR . '/templates';
        } else if (file_exists(THEME_PARENT_DIR)
            && file_exists(THEME_PARENT_DIR . '/templates')) {
            $templateDir = THEME_PARENT_DIR . '/templates';
        } else {
            error_log( 'catatlystwp_mvc Error: '. __( 'Could not find any directories for templates, you may need to add a folder in your child or parent theme.', 'CATALYST_WP_MVC' ) );
            return;
        }

        $Handlebars = new \Handlebars\Handlebars(
            array(
                'loader' => new \Handlebars\Loader\FilesystemLoader($templateDir),
                'partials_loader' => new \Handlebars\Loader\FilesystemLoader($templateDir),
            )
        );

    }

	/**
	 * Singleton instantiation
	 * @return [static] instance
	 */
	public static function get_instance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }


	public function autoLoad()
    {
        self::loadFile('config.php');
        self::loadFiles( 'helpers' );
        new \CatalystWP\MVC\autoloader();

		return;
	}

	/**
	 * Loads multiple files from theme directories, child theme overrides parent
	 * @param  [string] $type [ a MVC file type i.e. "controllers", "models", "templates" ]
	 * @return [void]
	 */
	public static function loadFiles($type)
    {
		$typeURI = strtolower('/' . $type . '/');
        $parentFiles = array();
        $childFiles = array();

		//make sure there are directories to introspect
		if (!file_exists( THEME_CHILD_DIR . $typeURI)
			&& !file_exists(THEME_PARENT_DIR . $typeURI)) {
			error_log( 'Catalyst WP Error: '. __( 'Could not find any directories for {'. $type . '}, you may need to add a folder in your child or parent theme.', 'CATALYST_WP_MVC' ) );
			return;
		}

		//get some arrays of our filenames
		if (file_exists(THEME_PARENT_DIR . $typeURI))
			$parentFiles = array_diff(scandir(THEME_PARENT_DIR . $typeURI), array('.', '..') );


		if (file_exists(THEME_CHILD_DIR . $typeURI))
			$childFiles =  array_diff( scandir( THEME_CHILD_DIR . $typeURI ), array('.', '..') );


		//get the diff and remove dupes from parent (child overrides parent)
		if (isset($parentFiles) && isset($childFiles))
			$parentFiles = array_diff($parentFiles, $childFiles);

		//load remaining parent files
		if (isset($parentFiles)) {
			foreach ($parentFiles as $file) {
				require_once( THEME_PARENT_DIR . $typeURI . $file);
			}
		}


		//load remaining child files
		if (isset($childFiles) && $childFiles !== $parentFiles) {
			foreach ($childFiles as $file) {
				require_once(THEME_CHILD_DIR . $typeURI . $file);
			}
		}

		return;

	}

	/**
	 * Loads a single file, child theme overrides parent
	 * @param  [string] $fileName [full name of the file you are trying to include w/ extension]
	 * @param  [sting] $type     [a MVC file type i.e. "controllers", "models", "templates"]
	 * @return [void]           [description]
	 */
	public static function loadFile($fileName, $type = "")
    {
		$typeURI = $type ? strtolower('/' . $type . '/') : '/';
		$childFileURI = THEME_CHILD_DIR . $typeURI . $fileName;
		$parentFileURI = THEME_PARENT_DIR . $typeURI . $fileName;

		//make sure the file exists
		if(!file_exists($childFileURI) && !file_exists($parentFileURI)) {
			error_log( 'Catalyst WP Warning: '. __( 'Could not find file {' . $typeURI . $fileName . '}, please create file.', 'CATALYST_WP_MVC' ) );
			return;
		}

		//load it ( check child theme first )
		if(file_exists($childFileURI))
			require_once($childFileURI);

		if(file_exists($parentFileURI) && !file_exists($childFileURI))
			require_once($parentFileURI);

		return;

	}

}

CatalystMVC::get_instance();
