<?php

namespace CatalystWP\Nucleus;

Class Model
{

    public $data = array();

    public function __construct($post_id = null){
        $this->initialize($post_id);
    }
}
