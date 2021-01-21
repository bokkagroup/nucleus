<?php

namespace CatalystWP\Nucleus;

class Controller
{
	public $options;

	public function __construct($options = array())
	{
		$args = [];

		if (isset($options['post_id']) && $options['post_id']) {
			$args['post_id'] = $options['post_id'];
		}

		if(isset($options['parent_blog'])) {
			$args['parent_blog'] = $options['parent_blog'];
		}

		if (isset($options['archive_page'])) {
			$page = get_page_by_path($options['archive_page'], OBJECT, 'page');
			if ($page) {
				$args['post_id'] = $page->ID;
				$args['archive_page'] = $options['archive_page'];
			}
		}

		$this->model = $this->loadModel($args);


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

			//if this is the archive page and the page has a different slug than the archive page, redirect to the archive.
			if ($isArchivePage) {
				$this->redirectArchivePage($isArchivePage, $options['archive_page']);
			}

			$this->view = $this->loadView('overview');


		} else if ($this->model &&
			property_exists($this->model, 'resource') &&
			$this->model::$resource && is_singular()) {
			$this->view = $this->loadView('detail');
		} else {
			$this->view = $this->loadView();
		}

		if (isset($options['json']) && $options['json']) {
			$this->json = $options['json'];
		}

		if (method_exists($this, 'initialize')) {
			$this->initialize($this->model);
		}
	}

	/**
	 * Loads and instantiates model
	 * @return [type] [description]
	 */
	public function loadModel($args)
	{
		//parse model class name
		$nameSpace = explode('\\', get_class($this));
		$modelClass = '\CatalystWP\\' . $nameSpace[1] . '\\models\\' . explode("Controller", end($nameSpace))[0];

		//instantiate the model
		if (class_exists($modelClass)) {
			return new $modelClass($args);
		}

		return false;
	}

	public function loadView($viewType = false)
	{
		//parse view class name
		$nameSpace = explode('\\', get_class($this));
		$viewClass = '\CatalystWP\\' . $nameSpace[1] . '\\views\\' . explode("Controller", end($nameSpace))[0] . 'View';

		//instantiate the view
		if (class_exists($viewClass)) {
			return new $viewClass($viewType);
		}

		return false;
	}

	private function redirectArchivePage($isArchive, $archive)
	{
		$post_type_link = get_post_type_archive_link(getModelSlug(get_class($this->model)));
		$explode = array_filter(explode('/', $archive));
		$page_permalink = get_the_permalink(end($explode));

		if ($isArchive && $post_type_link !== $page_permalink) {
			header("Location: " . get_post_type_archive_link(getModelSlug(get_class($this->model))));
			exit();
		} else {
			return;
		}
	}

	private function isArchivePage($archive)
	{
		$explode = array_filter(explode('/', $archive));
		return is_page(end($explode));

	}
}
