<?php

namespace CatalystWP\Nucleus;

class autoloader
{
    public function __construct()
    {
        spl_autoload_register(array($this, 'loader'));
    }

    public function loader($className)
    {

        $classArray = explode('\\', $className);

        if ($classArray[0] !== "CatalystWP") {
            return;
        }

        if ($classArray[1] === "Theme") {

            if ($classArray[2] === "controllers" ||
                $classArray[2] === "models" ||
                $classArray[2] === "views"
            ) {
                $fileType = strtolower($classArray[2]);
            }
        }

        $fileName = end($classArray) . '.php';

        if(!isset($fileType))
            return;

        $childFileURI = implode('/', array(THEME_CHILD_DIR, $fileType, $fileName));
        $parentFileURI = implode('/', array(THEME_PARENT_DIR, $fileType, $fileName));

        //make sure the file exists
        if(!file_exists($childFileURI) && !file_exists($parentFileURI)) {
            error_log( 'Catalyst WP Error: '. __( 'Could not find file {' . $fileType . $fileName . '}, please create file.', 'CATALYST_WP_NUCLEUS' ) );
            return;
        }

        //load it ( check child theme first )
        if (file_exists($childFileURI)) {
            require_once($childFileURI);
            return true;
        }

        if (file_exists($parentFileURI) && !file_exists($childFileURI)) {
            require_once($parentFileURI);
            return true;
        }

        return false;

    }
}