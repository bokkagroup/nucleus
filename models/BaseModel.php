<?php

namespace CatalystWP\Nucleus;

require_once(CATALYST_WP_NUCLEUS_DIRECTORY . 'Service.php');

Class Model
{
    public $service;

    public function __construct($options = array())
    {
        $data = array();
        $this->options = $options;

        if (isset($this->options['parent_blog'])) {
            switch_to_blog($this->options['parent_blog']);
        }

        if (isset($options['post_id']) && $options['post_id'] && is_numeric($options['post_id'])) {
            $data = get_post($options['post_id']);
        }

        $this->service = new Service(get_class($this));

        // TODO: Attach ACF data after filtering so we don't have to explicitly
        // include all custom fields in our model
        $data = $this->service->attachACFFields($data);

        if (($data) && (property_exists($this, 'allowed') && $this::$allowed)) {
            $data = $this->filterProperites($this::$allowed, $data);
        }

        if ($data) {
            $this->createProperties($data);
            unset($this->data);
        }

        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }

        if (isset($this->options['parent_blog'])) {
            restore_current_blog();
        }
    }

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
}
