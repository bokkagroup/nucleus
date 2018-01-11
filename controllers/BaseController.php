<?php

namespace CatalystWP\Nucleus;

Class Controller {

    /**
     * Loads and instantiates model
     * @return [type] [description]
     */
    public function loadModel(){

        //parse model class name
        $nameSpace = explode('\\', get_class($this));
        $modelClass = '\CatalystWP\\'.$nameSpace[1] .'\\models\\'. explode("Controller",  end($nameSpace) )[0];

        //instantiate the model
        if (class_exists($modelClass))
            return new $modelClass();

        return false;

    }

    public function loadView( ){

        //parse view class name
        $nameSpace = explode('\\', get_class($this));
        $viewClass = '\CatalystWP\\'.$nameSpace[1] .'\\views\\'. explode("Controller",  end($nameSpace) )[0] .'View';

        //instantiate the view
        if( class_exists( $viewClass ) )
            return new $viewClass();

        return false;
    }


    public function __construct(){
        $this->model = $this->loadModel();
        $this->view = $this->loadView();

        $this->initialize();
    }
}
