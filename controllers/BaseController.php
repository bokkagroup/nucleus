<?php

namespace CatalystWP\Nucleus;

Class Controller {
    /**
     * Loads and instantiates model
     * @return [type] [description]
     */
    public function loadModel()
    {
        //parse model class name
        $nameSpace = explode('\\', get_class($this));
        $modelClass = '\CatalystWP\\'.$nameSpace[1] .'\\models\\'. explode("Controller",  end($nameSpace) )[0];

        //instantiate the model
        if (class_exists($modelClass)) {
            return new $modelClass($this->options);
        }

        return false;
    }

    public function loadView($viewType)
    {
        //parse view class name
        $nameSpace = explode('\\', get_class($this));
        $viewClass = '\CatalystWP\\'.$nameSpace[1] .'\\views\\'. explode("Controller",  end($nameSpace) )[0] .'View';

        //instantiate the view
        if (class_exists($viewClass)) {
            return new $viewClass($viewType);
        }

        return false;
    }

    public function __construct($options = array())
    {
        $this->options = $options;

        $this->model = $this->loadModel();

        if (property_exists($this->model, 'resource') && $this->model::$resource) {
            require_once(CATALYST_WP_NUCLEUS_DIRECTORY . 'Service.php');
            $service = new Service(get_class($this->model));
        }

        if (is_archive()) {
            $data['posts'] = $service->getAll();
            $this->view = $this->loadView('overview');
        } else if (is_singular()) {
            $data = $service->get();
            $this->view = $this->loadView('detail');
        } else {
            $data = $this->model->data;
            $this->view = $this->loadView();
        }

        if (isset($options['json']) && $options['json']) {
            $this->json = $options['json'];
        }

        $this->initialize($data);
    }
}
