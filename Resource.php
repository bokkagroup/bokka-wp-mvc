<?php

namespace CatalystWP\Nucleus;

use ICanBoogie\Inflector as Inflector;

Class Resource
{
    public static function registerPostType($model, $resource = array())
    {
        $slug = getModelSlug($model);

        if (post_type_exists($slug)) {
            return;
        }

        $namespace = explode('\\', $model);
        $modelName = end($namespace);

        $inflector = Inflector::get(Inflector::DEFAULT_LOCALE);
        $plural = $inflector->pluralize($modelName);

        $labels = array(
            // User-defined or auto-generated
            'name'                  => _x( "${plural}", 'Post Type General Name', 'text_domain' ),
            'singular_name'         => _x( "${modelName}", 'Post Type Singular Name', 'text_domain' ),
            'menu_name'             => __( "${plural}", 'text_domain' ),
            'name_admin_bar'        => __( "${modelName}", 'text_domain' ),

            // Generic labels
            'archives'              => __( 'Item Archives', 'text_domain' ),
            'attributes'            => __( 'Item Attributes', 'text_domain' ),
            'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
            'all_items'             => __( 'All Items', 'text_domain' ),
            'add_new_item'          => __( 'Add New Item', 'text_domain' ),
            'add_new'               => __( 'Add New', 'text_domain' ),
            'new_item'              => __( 'New Item', 'text_domain' ),
            'edit_item'             => __( 'Edit Item', 'text_domain' ),
            'update_item'           => __( 'Update Item', 'text_domain' ),
            'view_item'             => __( 'View Item', 'text_domain' ),
            'view_items'            => __( 'View Items', 'text_domain' ),
            'search_items'          => __( 'Search Item', 'text_domain' ),
            'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
            'items_list'            => __( 'Items list', 'text_domain' ),
            'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
            'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
        );

        if (isset($resource['labels'])) {
            $labels = array_merge($labels, $resource['labels']);
        }

        $args = array(
            'label'                 => __( 'Post Type', 'text_domain' ),
            'description'           => __( 'Post Type Description', 'text_domain' ),
            'labels'                => array(),
            'supports'              => array('title', 'editor'),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
        );

        if (isset($resource['options'])) {
            $args = array_merge($args, $resource['options']);
        }

        $args['labels'] = $labels;

        register_post_type( $slug, $args );
    }
}
