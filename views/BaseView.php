<?php

namespace CatalystWP\Nucleus;

Class View {
    /**
     * Which template to use
     * @var string
     */
    private $viewType;

    /**
     * Generates html from mustache template
     * @param bool $template
     * @param array $data
     * @return html
     */
    public function render($data = array(), $template = false)
    {
        global $Handlebars;

        if (!$template && isset($this->template)) {
            $template = $this->template;
        }

        if (isset($template)) {
            apply_filters('catatlystwp_nucleus_filter_before_render', $data);

            if (is_array($template)) {
                if (isset($template['overview']) && $this->viewType === 'overview') {
                    $template = $Handlebars->render($template['overview'], $data);
                } else if (isset($template['detail']) && $this->viewType === 'detail') {
                    $template = $Handlebars->render($template['detail'], $data);
                } else {
                    error_log('catatlystwp_nucleus Error: '. __('Missing overview or detail template in view.', 'CATALYST_WP_NUCLEUS'));
                    return;
                }
            } else {
                $template = $Handlebars->render($template, $data);
            }
        }

        return $template;
    }

    /**
     * echos mustache template
     * @param array $data
     */
    public function display($data = array())
    {
        if (!isset($this->template)) {
            error_log('catatlystwp_nucleus Error: '. __('Template not defined in view.', 'CATALYST_WP_NUCLEUS'));
            return;
        }

        echo $this->render(array('BOKKA' => array('env_local' => false)), 'head');
        do_action('catatlystwp_nucleus_before_display', $data);
        echo $this->render($data);
        do_action('catatlystwp_nucleus_after_display', $data);
        echo $this->render(array('BOKKA' => array('env_local' => false)), 'foot');
    }

    public function __construct($viewType = false)
    {
        $this->viewType = $viewType;

        $this->initialize();
    }
}
