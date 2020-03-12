<?php

namespace CatalystWP\Nucleus;

/**
 * Get lowercase, underscore separated slug from model class name
 * Used to register and query custom post types
 * @param  {string} $model
 * @return {string}
 */
function getModelSlug($model)
{
    $modelName = explode('\\', $model);
    $slug = strtolower(end($modelName));

    if ($slug) {
        return $slug;
    }

    return false;
}
