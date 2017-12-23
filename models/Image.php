<?php

namespace CatalystWP\Nucleus\models;

/**
 * Returns a new instance of an Image object
 */
class Image
{

    /**
     * WP_Post
     *
     * Holds the attachment WP_Post Object
     *
     * @var int
     */
    private $post;

    /**
     * Sets up the necessary data for using images in templates
     *
     * @param int   $id     WP attachment ID
     */
    public function __construct($id)
    {
        $this->post = get_post($id);
        $this->setCaption();
        $this->setUrls();
        $this->setSrc();
    }

    /**
     * Set array of image source URLs for all available sizes
     */
    protected function setUrls()
    {
        $sizes = get_intermediate_image_sizes();
        $sizes[] = 'full';

        foreach ($sizes as $size) {
            $this->sizes[$size] = wp_get_attachment_image_src($this->post->ID, $size)[0];
        }
    }

    /**
     * Sets the image caption data
     */
    protected function setCaption()
    {
        $this->caption = $this->post->post_excerpt;
    }

    /**
     * Set the image size to be used in the template
     *
     * @param string    $size   WP image size value
     */
    public function setSrc($size = 'full')
    {
        if (isset($this->sizes[$size])) {
            $this->src = $this->sizes[$size];
        }
    }
}