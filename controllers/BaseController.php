<?php
namespace BokkaWP\MVC;
global $BGMVC;

Class Controller {

	/**
	 * Loads and instantiates model
	 * @return [type] [description]
	 */
	public function loadModel(){
		global $BGMVC;
		//parse model class name
        $controllerName = basename(str_replace('\\', '/', get_class($this)));
        $modelClass = explode("Controller",  $controllerName )[0];

		//load the model file
		$BGMVC->loadFile( $modelClass . '.php', 'models' );
        $modelClass = '\BokkaWP\theme\models\\'.$modelClass;
		//instantiate the model

		if (class_exists($modelClass))
            return new $modelClass();


	}

	public function loadView( ){
		global $BGMVC;

		//parse view class name
        $controllerName = basename(str_replace('\\', '/', get_class($this)));
		$viewClass = explode("Controller",  $controllerName )[0] . 'View';

		//load the view file
		$BGMVC->loadFile( $viewClass . '.php', 'views' );
        $viewClass = '\BokkaWP\theme\views\\'.$viewClass;
		//instantiate the view
		if( class_exists( $viewClass ) )
            return new $viewClass();

        return 0;
	}


	public function __construct(){
        $this->model = $this->loadModel();
        $this->view = $this->loadView();

		$this->initialize();
	}
	
}