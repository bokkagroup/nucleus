<?php

namespace CatalystWP\Nucleus;

Class Service
{
    private $modelClass;
    private $postType;

    public function __construct($modelClass)
    {
        if (!$modelClass || !class_exists($modelClass)) {
            error_log('catatlystwp_nucleus Error: '. __('Invalid model provided to Service class.', 'CATALYST_WP_NUCLEUS'));
            return;
        }

        $this->modelClass = $modelClass;
        $this->postType = getModelSlug($this->modelClass);
    }

    /**
     * Get single post by $id
     * @param  boolean $id WP post ID
     * @return array       Single instance of this model
     */
    public function get($id = false)
    {
        global $post;

        $id = $id || $post->ID;

        $post = $this->queryPosts(array(
            'posts_per_page' => 1
        ));

        return $post;
    }

    /**
     * Retrieve all posts of $this->postType
     * @return array    Array of instances of this model
     */
    public function getAll()
    {
        $posts = $this->queryPosts(array(
            'posts_per_page' => 500
        ));

        return $posts;
    }

    /**
     * Query for posts of $this->postType
     * @param  array  $args WP_Query args
     * @return array        Array of instances of this model
     */
    private function queryPosts($args = array()) {
        if (!$this->postType) {
            return;
        }

        $defaultArgs = array(
            'post_type' => $this->postType
        );

        $args = array_merge($defaultArgs, $args);

        $result = new \WP_Query($args);

        $results = array_map(function ($post) {
            $post = $this->attachACFFields($post);
            $instance = new $this->modelClass(false, $post);
            return $instance;
        }, $result->posts);

        return $results;
    }

    private function attachACFFields($post)
    {
        $fields = get_fields($post->ID);

        if (!empty($fields)) {
            foreach ($fields as $field_name => $value) {
                $post->$field_name = $value;
            }
        }

        return $post;
    }
}
