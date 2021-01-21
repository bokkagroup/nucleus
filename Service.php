<?php

namespace CatalystWP\Nucleus;

Class Service
{
    private $modelClass;
    public $postType;
    private $queryArgs;
    private $query;
    private $paged;

    public function __construct($modelClass)
    {
        if (!$modelClass || !class_exists($modelClass)) {
            error_log('catatlystwp_nucleus Error: ' . __('Invalid model provided to Service class.', 'CATALYST_WP_NUCLEUS'));
            return;
        }

        $this->modelClass = $modelClass;

        if(property_exists($modelClass, 'resource')) {
            $resourceConfig = $modelClass::$resource;
            $this->postType = isset($resourceConfig['existing_post_type']) ? $resourceConfig['existing_post_type'] : getModelSlug($this->modelClass);
            $this->paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        } else {
            $this->postType = getModelSlug($modelClass);

        }

        // set custom query args
        if (property_exists($modelClass, 'resource') && isset($modelClass::$resource['query'])) {
            $this->queryArgs = $modelClass::$resource['query'];
        }
    }

    /**
     * Get single post by $id
     * @param  boolean $id WP post ID
     * @return array       Single instance of this model
     */
    public function get($id = false)
    {

        if (!$id) {
            return;
        }

        $args = array(
            'p' => $id
        );

        $instance = $this->queryPosts($args);

        if (isset($instance[0])) {
            return $instance[0];
        }

        return;
    }

    /**
     * Retrieve all posts of $this->postType
     * @return array    Array of instances of this model
     */
    public function getAll()
    {
        global $posts;
        global $wp_query;

        //for posts that don't need to be queried
        if (is_date() || is_category() || is_archive()) {
            $collection = $wp_query->posts;
        } else {
            $args = array(
                'posts_per_page' => 500,
                'paged' => $this->paged,

            );

            if (isset($this->queryArgs)) {
                $args = array_merge($args, $this->queryArgs);
            }

            $collection = $this->queryPosts($args);

        }

        $collection = $this->applyFilters($collection);

        if (count($collection) > 0) {
            return $collection;
        }


        return;
    }

    /**
     * Get pagination markup
     * @return string   WP generated pagination markup
     */
    public function getPagination()
    {
    	if(isset($this->query) && isset($this->query->max_mum_pages)) {

			$pagination = paginate_links(array(
				'current' => max(1, $this->paged),
				'total' => $this->query->max_num_pages
			));

			return $pagination;
		}

    }

    /**
     * Query for posts of $this->postType
     * @param  array $args WP_Query args
     * @return array        Array of instances of this model
     */
    private function queryPosts($args = array())
    {
        if (!$this->postType) {
            return;
        }

        $defaultArgs = array(
            'post_type' => $this->postType
        );
        $args = array_merge($defaultArgs, $args);

        $this->query = new \WP_Query($args);
        $posts = $this->query->get_posts();

        return $posts;
    }

    private function applyFilters($array)
    {

        $results = array_map(function($item){

            if (isset($item->ID)) {

                $instance = new $this->modelClass(['post_id' => $item->ID]);

                //make sure these are passed through view filters
                return apply_filters('catatlystwp_nucleus_filter_before_render', $instance);
            } else {
                return $item;
            }
        }, $array);

        return $results;
    }

    /**
     * Checks to see if there are associated ACF fields and creates members for them
     * @param  $post
     * @return $post
     */
    public function attachACFFields($post)
    {
        if (!isset($post->ID)) {
            return;
        }

        $fields = get_fields($post->ID);

        if (!empty($fields)) {
            foreach ($fields as $field_name => $value) {
                $post->$field_name = $value;
            }
        }

        return $post;
    }
}
