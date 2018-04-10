<?php

namespace CatalystWP\Nucleus;

Class Service
{
    private $modelClass;
    private $postType;
    private $queryArgs;
    private $query;
    private $paged;

    public function __construct($modelClass)
    {
        if (!$modelClass || !class_exists($modelClass)) {
            error_log('catatlystwp_nucleus Error: '. __('Invalid model provided to Service class.', 'CATALYST_WP_NUCLEUS'));
            return;
        }

        $this->modelClass = $modelClass;
        $this->postType = getModelSlug($this->modelClass);
        $this->paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        // set custom query args
        if (isset($this->modelClass::$resource['query'])) {
            $this->queryArgs = $modelClass::$resource['query'];
        }
    }

    /**
     * Get single post by $id
     * @param  boolean $id WP post ID
     * @return array       Single instance of this model
     */
    public function get($id = false)
    {
        if (!$id) {
            return;
        }

        $args = array(
            'p' => $id
        );

        $instance = $this->queryPosts($args);

        if (isset($instance[0])) {
            return $instance[0];
        }

        return;
    }

    /**
     * Retrieve all posts of $this->postType
     * @return array    Array of instances of this model
     */
    public function getAll()
    {
        $args = array(
            'posts_per_page' => 500,
            'paged' => $this->paged
        );

        if (isset($this->queryArgs)) {
            $args = array_merge($args, $this->queryArgs);
        }

        $collection = $this->queryPosts($args);

        if (count($collection) > 0) {
            return $collection;
        }

        return;
    }

    /**
     * Get pagination markup
     * @return string   WP generated pagination markup
     */
    public function getPagination()
    {
        $pagination = paginate_links(array(
            'current' => max(1, $this->paged),
            'total' => $this->query->max_num_pages
        ));

        return $pagination;
    }

    /**
     * Query for posts of $this->postType
     * @param  array  $args WP_Query args
     * @return array        Array of instances of this model
     */
    private function queryPosts($args = array()) {
        if (!$this->postType) {
            return;
        }

        $defaultArgs = array(
            'post_type' => $this->postType
        );
        $args = array_merge($defaultArgs, $args);

        $this->query = new \WP_Query($args);
        $posts = $this->query->get_posts();

        $results = array_map(function ($post) {
            $post = $this->attachACFFields($post);
            $instance = new $this->modelClass(false, $post);
            return $instance;
        }, $posts);

        return $results;
    }

    /**
     * Checks to see if there are associated ACF fields and creates members for them
     * @param  $post
     * @return $post
     */
    private function attachACFFields($post)
    {
        $fields = get_fields($post->ID);

        if (!empty($fields)) {
            foreach ($fields as $field_name => $value) {
                $post->$field_name = $value;
            }
        }

        return $post;
    }
}
