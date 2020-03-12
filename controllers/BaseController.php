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

        //we can set an archive page to pull extra data from
        if(isset($options['archive_page'])) {
            $isArchivePage = $this->isArchivePage($options['archive_page']);
        } else {
            $isArchivePage = false;
        }

        // get data and load view
        if ($this->model &&
            property_exists($this->model, 'resource') &&
            $this->model::$resource &&
            (is_archive() || is_home() || $isArchivePage)) {

                //if this is the archive page and the page has a different slug than the archive page, redirect to the archive.
                $this->redirectArchivePage($isArchivePage, $options['archive_page']);
                    $page = get_page_by_path('/' . $options['archive_page'], OBJECT, 'page');
                    $args = [];
                    if($page) {
                        $args['post_id'] = $page->ID;
                    }
                    $data = new Model($args);
                    $data->posts = $this->model->service->getAll();
                    $data->pagination = $this->model->service->getPagination();
                    $this->view = $this->loadView('overview');


        } else if ($this->model &&
            property_exists($this->model, 'resource') &&
            $this->model::$resource && is_singular()) {

                $data = $this->model;
                $this->view = $this->loadView('detail');
        } else {
            $data = $this->model;
            $this->view = $this->loadView();
        }

        if (isset($options['json']) && $options['json']) {
            $this->json = $options['json'];
        }

        if (method_exists($this, 'initialize')) {
            $this->initialize($data);
        }
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

    private function redirectArchivePage($isArchive, $archive) {
        $post_type_link = get_post_type_archive_link(getModelSlug(get_class($this->model)));
        $page_permalink = get_the_permalink(end(array_filter((explode( '/', $archive)))));
        if($isArchive && $post_type_link !== $page_permalink ) {
            header("Location: " . get_post_type_archive_link(getModelSlug(get_class($this->model))));
            exit();
        } else {
            return;
        }
    }

    private function isArchivePage($archive) {
        return is_page(end(array_filter((explode( '/', $archive)))));
    }
}
