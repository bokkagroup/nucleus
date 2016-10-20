<?php

namespace BokkaWP\MVC;
Class View {

    /**
     * Generates html from mustache template
     * @param bool $template
     * @param array $data
     * @return html
     */
    public function render($data = array())
    {
        global $Handlebars;
        if (isset($this->template)) {
            apply_filters( 'bokkamvc_filter_before_render', $data);
            $template = $Handlebars->render($this->template, $data);
        }
       return $template;
    }

    /**
     * echos mustache template
     * @param array $data
     */
    public function display($data = array())
    {
        if (isset($this->template)) {
            do_action( 'bokkamvc_before_display', $data);
            echo $this->render($data);
            do_action( 'bokkamvc_after_display', $data);
        }
        return;
    }

    public function __construct(){
        $this->initialize();
    }
}