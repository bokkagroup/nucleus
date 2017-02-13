<?php
/**
 * @package Bokka_builder
 * @version a-0.0.1
 */
/*
Plugin Name: Bokka MVC
Plugin URI: http://github.com
Description: Creates a global interface for handling MVC components in a Wordpress Theme. It's main purpose is to create a global object that will allow us to override models/views/templates in our child-themes.
Author: Codewizard
Version: 0.0.1
Author URI: http://bokkagroup.com
*/

namespace BokkaWP;

/**
 * BokkaBuilder
 * @version 0.0.1 Singleton
 */
class MVC {

	private static $instance;

	public function __construct()
    {
		//Set directory variables
        define("BOKKA_MVC_DIRECTORY",   plugin_dir_path(__FILE__));

        $this->loadHandlebars();

		//load base classes
		require_once(BOKKA_MVC_DIRECTORY . 'controllers/BaseController.php');
		require_once(BOKKA_MVC_DIRECTORY . 'views/BaseView.php');
		require_once(BOKKA_MVC_DIRECTORY . 'models/BaseModel.php');

        require_once(BOKKA_MVC_DIRECTORY . 'autoloader.php');

		//auto load controllers
        add_action('init', array($this, 'autoLoad'));
	}

    private function loadHandlebars()
    {
    //load mustache if nobody else has
        if (!class_exists('Mustache_Autoloader') && !class_exists('Mustache_Engine')) {
            global $Handlebars;
            require_once(BOKKA_MVC_DIRECTORY . 'lib/Handlebars/Autoloader.php');
        }

        \Handlebars\Autoloader::register();

        if (file_exists(BOKKA_CHILD_DIR) 
            && file_exists(BOKKA_CHILD_DIR . '/templates')) {
            $templateDir = BOKKA_CHILD_DIR;
        } else if (file_exists(BOKKA_PARENT_DIR) 
            && file_exists(BOKKA_PARENT_DIR . '/templates')) {
            $templateDir = BOKKA_PARENT_DIR;
        } else {
            error_log( 'BokkaMVC Error: '. __( 'Could not find any directories for templates, you may need to add a folder in your child or parent theme.', 'BOKKA_MVC' ) );
            return;
        }

        $Handlebars = new \Handlebars\Handlebars(
            array(
                'loader' => new \Handlebars\Loader\FilesystemLoader($templateDir . '/templates'),
                'partials_loader' => new \Handlebars\Loader\FilesystemLoader($templateDir . '/templates'),
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
        $this->loadFile('config.php');
        $this->loadFiles( 'helpers' );
        new \BokkaWP\MVC\autoloader();

		return;
	}

	/**
	 * Loads multiple files from theme directories, child theme overrides parent
	 * @param  [string] $type [ a MVC file type i.e. "controllers", "models", "templates" ]
	 * @return [void]
	 */
	public function loadFiles($type)
    {
		$typeURI = strtolower('/' . $type . '/');
        $parentFiles = array();
        $childFiles = array();

		//make sure there are directories to introspect
		if (!file_exists( BOKKA_CHILD_DIR . $typeURI)
			&& !file_exists(BOKKA_PARENT_DIR . $typeURI)) {
			error_log( 'BokkaMVC Error: '. __( 'Could not find any directories for {'. $type . '}, you may need to add a folder in your child or parent theme.', 'BOKKA_MVC' ) );
			return;
		}

		//get some arrays of our filenames
		if (file_exists(BOKKA_PARENT_DIR . $typeURI))
			$parentFiles = array_diff(scandir(BOKKA_PARENT_DIR . $typeURI), array('.', '..') );


		if (file_exists(BOKKA_CHILD_DIR . $typeURI))
			$childFiles =  array_diff( scandir( BOKKA_CHILD_DIR . $typeURI ), array('.', '..') );


		//get the diff and remove dupes from parent (child overrides parent)
		if (isset($parentFiles) && isset($childFiles))
			$parentFiles = array_diff($parentFiles, $childFiles);

		//load remaining parent files
		if (isset($parentFiles)) {
			foreach ($parentFiles as $file) {
				require_once( BOKKA_PARENT_DIR . $typeURI . $file);
			}
		}


		//load remaining child files
		if (isset($childFiles) && $childFiles !== $parentFiles) {
			foreach ($childFiles as $file) {
				require_once(BOKKA_CHILD_DIR . $typeURI . $file);
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
	public function loadFile($fileName, $type = "")
    {
		$typeURI = $type ? strtolower('/' . $type . '/') : '/';
		$childFileURI = BOKKA_CHILD_DIR . $typeURI . $fileName;
		$parentFileURI = BOKKA_PARENT_DIR . $typeURI . $fileName;

		//make sure the file exists
		if(!file_exists($childFileURI) && !file_exists($parentFileURI)) {
			error_log( 'BokkaMVC Error: '. __( 'Could not find file {' . $typeURI . $fileName . '}, please create file.', 'BOKKA_MVC' ) );
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

$BGMVC = MVC::get_instance();
