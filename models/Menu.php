<?php

namespace CatalystWP\Nucleus\models;

/**
 * Retrieve and modify WordPress menu data.
 *
 * --
 *
 * There is a public method updateMenuItems that can be called inside
 * the child theme to loop over every item in the links array and
 * apply a supplied callback function.
 *
 * The callback function has two parameters, $value and $key. $value is
 * the individual link item in the menu, and $key is the items array key
 * value, which is equal to the nav_menu_item post id.
 *
 * Note: The callback essentially serves as an array_map function so we
 * must return $value at the end of the function.
 *
 * Usage example:
 *  $this->data['menu'] = new Menu('primary');
 *  $this->data['menu']->updateMenuItems(function($value, $key) {
 *      $value['foo'] = 'bar';
 *     return $value;
 *  });
 *
 * This example would add the 'foo' property with a value of 'bar' to
 * all items in the $links array.
 *
 */
Class Menu
{
    /**
     * Array of all menu data
     * @var array
     */
    public $links;

    /**
     * Setup new instance of Menu class
     * @param string $name WordPress menu name, e.g. 'primary' or 'footer'
     * @param object $post WP_Post object
     *
     * Note: This is the name when the menu is created in the admin, not the
     * name of the menu registered in WordPress
     */
    public function __construct($options = array()) {
        $post = false;

        if (!isset($options['name'])) {
            return;
        }

        $wp_menu = wp_get_nav_menu_object($options['name']);

        if (!is_a($wp_menu, 'WP_Term')) {
            return;
        }

        $menu_items = wp_get_nav_menu_items($wp_menu->term_id, array('order' => 'DESC'));

        if (isset($options['post']) && is_a($options['post'], 'WP_Post')) {
            $post = $options['post'];
        } elseif (isset($options['post']) && is_numeric($options['post'])) {
            $post = get_post($options['post']);
        }

        $current_item_id = $this->getCurrentItemID($menu_items, $post);

        $this->links = $this->setMenuLinks($menu_items, $current_item_id);
    }

    /**
     * Checks each menu item title against the provided $post title
     * and returns a matching menu item ID if found.
     * Used to retrieve menu links for a specified post.
     *
     * @param  [type] $menu_items [description]
     * @param  [type] $post       [description]
     * @return [type]             [description]
     */
    protected function getCurrentItemID($menu_items, $post)
    {
        foreach ($menu_items as $item) {
            if ($post && strtolower($post->post_title) == strtolower($item->title)) {
                return $item->ID;
            }
        }

        return false;
    }

    /**
     * Setup array of menu links
     */
    protected function setMenuLinks($menu_items, $current_item_id)
    {
        if (!$menu_items || count($menu_items) < 1) {
            return;
        }

        $menu_object = array();

        foreach ($menu_items as $item) {
            $menu_item = array();
            $menu_item['link'] = $item->url;
            $menu_item['title'] = $item->title;
            $menu_item['slug'] = get_post_field('post_name', $item->object_id);

            $menu_item_parent = trim($item->menu_item_parent);

            // If a $post has been provided when the menu is initalized
            // then filter out items that don't match the provided post,
            // or are a child of the post.
            if (($current_item_id && $current_item_id == $menu_item_parent || $current_item_id == $item->ID) ||
                !$current_item_id) {
                // proceed as normal
            } else {
                continue;
            }

            if (!empty($item->classes) && !empty($item->classes[0])) {
                $menu_item['classes'] = $item->classes;
            }

            if (!$item->menu_item_parent) {
                $menu_item['parent'] = false;
                $menu_object[$item->ID] = $menu_item;
            } else {
                $menu_item['child'] = true;
                $menu_object = Menu::assignChildMenu($item, $menu_item, $menu_object);
            }
        }

        return $menu_object;
    }

    /**
     * Recursively search through all menu links and find a matching parent id
     * to assign menu item to
     */
    protected function assignChildMenu($item, $menu_item, $menu_links)
    {
        if (!$item || !$menu_item || !$menu_links) {
            return false;
        }

        foreach ($menu_links as $key => $value) {
            if ($key == $item->menu_item_parent) {
                $menu_links[$key]['parent'] = true;
                $menu_links[$key]['child_menu'][$item->ID] = $menu_item;
            } elseif (isset($value['child_menu']) && !empty($value['child_menu'])) {
                $menu_links[$key]['child_menu'] = Menu::assignChildMenu($item, $menu_item, $value['child_menu']);
            }
        }

        return $menu_links;
    }

    /**
     * Apply an anonymous callback function to all links in the menu.
     * This function can be used within the child theme.
     */
    public function updateMenuItems($callback, $menu_links = null)
    {
        $is_child = false;

        if (!$menu_links) {
            $menu_links = $this->links;
        } else {
            $is_child = true;
        }

        foreach($menu_links as $key => $value) {
            $menu_links[$key] = $callback($value, $key);

            if (isset($value['child_menu'])) {
                $menu_links[$key]['child_menu'] = Menu::updateMenuItems($callback, $value['child_menu']);
            }
        }

        if (!$is_child) {
            $this->links = $menu_links;
        } else {
            return $menu_links;
        }
    }
}
