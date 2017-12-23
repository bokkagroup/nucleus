<?php

namespace CatalystWP\Nucleus;

Class View {

    /**
     * Generates html from mustache template
     * @param bool $template
     * @param array $data
     * @return html
     */
    public function render($data = array(), $template = false)
    {
        global $Handlebars;

        if(!$template && isset($this->template)) {
            $template = $this->template;
        }
        if (isset($template)) {
            apply_filters('catatlystwp_nucleus_filter_before_render', $data);
            $template = $Handlebars->render($template, $data);
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
            echo $this->render(array('BOKKA' => array('env_local' => false)), 'head');
            do_action('catatlystwp_nucleus_before_display', $data);
            echo $this->render($data);
            do_action('catatlystwp_nucleus_after_display', $data);
            echo $this->render(array('BOKKA' => array('env_local' => false)), 'foot');
        }

        return;
    }

    public function __construct(){
        $this->initialize();
    }
}
