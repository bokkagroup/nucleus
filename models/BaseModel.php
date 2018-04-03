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

        $this->initialize();

        if (isset($this->options['parent_blog'])) {
            restore_current_blog();
        }
    }
}
