<?php

namespace CatalystWP\Nucleus;

class Controller
{
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
		if (isset($options['archive_page'])) {
			$isArchivePage = $this->isArchivePage($options['archive_page']);
		} else {
			$isArchivePage = false;
		}
		// get data and load view
		if ($this->model &&
			property_exists($this->model, 'resource') &&
			$this->model::$resource &&
			(is_archive() || is_home() || $isArchivePage || is_post_type_archive($this->model->service->postType))) {
			$args = [];
			//if this is the archive page and the page has a different slug than the archive page, redirect to the archive.
			if ($isArchivePage) {
				$this->redirectArchivePage($isArchivePage, $options['archive_page']);
				$page = get_page_by_path('/' . $options['archive_page'], OBJECT, 'page');
				if ($page) {
					$args['post_id'] = $page->ID;
				}

			}


			$data = $this->model;
			$data->posts = $this->model->service->getAll();
			$data->pagination = $this->model->service->getPagination();
			$this->view = $this->loadView('overview');
			$this->model = $data;



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
		$modelClass = '\CatalystWP\\' . $nameSpace[1] . '\\models\\' . explode("Controller", end($nameSpace))[0];

		//instantiate the model
		if (class_exists($modelClass)) {
			return new $modelClass($this->options);
		}

		return false;
	}


    /**
     * Loads and instantiates model
     * @return [type] [description]
     */
    public function loadModel()
    {
        //parse model class name
        $nameSpace = explode('\\', get_class($this));
        $controllerIndex = array_search('controllers',$nameSpace);
        $themeDirectories = array_slice($nameSpace,1,$controllerIndex - 1);
        $modelClass = '\CatalystWP\\'.implode('\\',$themeDirectories).'\\models\\'. explode("Controller",  end($nameSpace) )[0];

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
        $controllerIndex = array_search('controllers',$nameSpace);
        $themeDirectories = array_slice($nameSpace,1,$controllerIndex - 1);
        $viewClass = '\CatalystWP\\'.implode('\\',$themeDirectories).'\\views\\'. explode("Controller",  end($nameSpace) )[0] .'View';

        //instantiate the view
        if (class_exists($viewClass)) {
            return new $viewClass($viewType);
        }

        return false;
    }

	private function isArchivePage($archive)
	{
		return is_page(end(array_filter((explode('/', $archive)))));
	}
}
