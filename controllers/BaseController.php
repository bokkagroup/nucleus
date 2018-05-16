<?php

namespace CatalystWP\Nucleus;

Class Controller {
    public $options;

    public function __construct($options = array())
    {
        $postID = false;

        $this->options = $options;

        if (isset($options['post_id']) && $options['post_id']) {
            $postID = $options['post_id'];
        }

        $this->model = $this->loadModel();

        // get data and load view
        if (property_exists($this->model, 'resource') && $this->model::$resource && is_archive()) {
            $data['posts'] = $this->model->service->getAll();
            $data['pagination'] = $this->model->service->getPagination();
            $this->view = $this->loadView('overview');
        } else if (property_exists($this->model, 'resource') && $this->model::$resource && is_singular()) {
            $data = $this->model->service->get($postID);
            $this->view = $this->loadView('detail');
        } else {
            $data = $this->model;
            $this->view = $this->loadView();
        }

        if (isset($options['json']) && $options['json']) {
            $this->json = $options['json'];
        }

        $this->initialize($data);
    }

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

    public function loadView($viewType = false)
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
}
