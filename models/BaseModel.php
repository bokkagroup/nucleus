<?php

namespace CatalystWP\Nucleus;

Class Model
{
    public $data = array();

    public function __construct($options = array())
    {
        $this->options = $options;

        if (isset($this->options['parent_blog'])) {
            switch_to_blog($this->options['parent_blog']);
        }

        if (class_exists('acf') && isset($this->options['post_id'])) {
            $this->attachACFFields($this->options['post_id']);
        }


        if (isset($this->options['post_id'])) {
            $this->import($this->options['post_id']);
        }

        if(method_exists($this, 'initialize')) {
            $this->initialize();
        }

        if (isset($this->options['parent_blog'])) {
            restore_current_blog();
        }

    }

    /**
     * Checks to see if there are associated ACF fields and creates members for them
     * @param $post_id
     * @return $this
     */
    private function attachACFFields($post_id)
    {

        if (!isset($post_id)) {
            global $post;
            if($post)
                $post_id = $post->ID;
        }

        $fields = get_fields($post_id);

        if (!empty($fields)) {
            foreach ($fields as $field_name => $value) {
                $this->$field_name = $value;
            }
        }
        return $this;
    }

    private function import($post_id)
    {
        if (is_int($post_id)) {
            $post_type = get_post_type($post_id);
            $post = get_posts(array('post__in' => [$post_id], 'post_type'=> $post_type, 'suppress_filters' => false));
            if(count($post) > 0)
                $post = $post[0];
        } else {
            $post = $post_id;
        }
        foreach (get_object_vars($post) as $key => $value) {
            $this->$key = $value;
        }
    }
}
