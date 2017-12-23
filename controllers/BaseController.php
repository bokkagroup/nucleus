<?php

namespace CatalystWP\Nucleus;

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
        $modelClass = '\CatalystWP\Atom\models\\'. explode("Controller",  $controllerName )[0];

        //instantiate the model
        if (class_exists($modelClass))
            return new $modelClass();


    }

    public function loadView( ){
        global $BGMVC;

        //parse view class name
        $controllerName = basename(str_replace('\\', '/', get_class($this)));
        $viewClass = '\CatalystWP\Atom\views\\'. explode("Controller",  $controllerName )[0] . 'View';

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