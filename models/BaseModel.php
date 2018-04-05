<?php

namespace CatalystWP\Nucleus;

Class Model
{
    public $data = array();

    /**
     * Filter out properties of WP_Post not in $allowed
     * @param  array  $allowed Array of allowed properties for model
     * @return array           Filtered results
     */
    public function filterProperites($allowed = array(), $data = array())
    {
        // convert WP_Post object to array
        $data = (array) $data;

        // remove any non-allowed properties
        $data = array_filter($data, function ($key) use ($allowed) {
            return in_array($key, $allowed);
        }, ARRAY_FILTER_USE_KEY);

        return $data;
    }

    /**
     * Take properties from $data and re-assign to primary model instance
     * @param  array  $data Data to attach to instance
     * @return void
     */
    public function createProperties($data = array())
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return;
    }

    public function __construct($options = array(), $data = array())
    {
        $this->options = $options;

        if (isset($this->options['parent_blog'])) {
            switch_to_blog($this->options['parent_blog']);
        }

        if (($data) && (property_exists($this, 'allowed') && $this::$allowed)) {
            $data = $this->filterProperites($this::$allowed, $data);
        }

        if ($data) {
            $this->createProperties($data);
            unset($this->data);
        }

        $this->initialize($data);

        if (isset($this->options['parent_blog'])) {
            restore_current_blog();
        }
    }
}
