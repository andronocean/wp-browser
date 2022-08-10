<?php
/**
 *  Post generation data.
 *
 * @package lucatume\WPBrowser\Generators
 */

namespace lucatume\WPBrowser\Generators;

use function lucatume\WPBrowser\slug;

/**
 * Class Post
 *
 * @package lucatume\WPBrowser\Generators
 */
class Post
{
    /**
     * Builds a post semi-random data.
     *
     * @param int                 $id   The post ID.
     * @param string              $url  The URL of the site to generate a post for.
     * @param array<int|string,mixed> $data A map of fixed data for the post.
     *
     * @return array<int|string,mixed> The post data.
     */
    public static function buildPostData($id, $url = 'http://www.example.com', array $data = []): array
    {
        return array_merge(self::getDefaults($id, $url), $data);
    }

    /**
     * Returns a set of default values for the creation of a post.
     *
     * @param int    $id  The post ID to use.
     * @param string $url The site URL.
     *
     * @return mixed[] A map of the post creation default values.
     */
    protected static function getDefaults($id, $url): array
    {
        $title    = self::generateTitle($id);
        $defaults = array(
            'post_author'           => 1,
            'post_date'             => Date::now(),
            'post_date_gmt'         => Date::gmtNow(),
            'post_content'          => self::generateContent($id),
            'post_title'            => $title,
            'post_excerpt'          => self::generateExcerpt($id),
            'post_status'           => 'publish',
            'comment_status'        => 'open',
            'ping_status'           => 'open',
            'post_password'         => '',
            'post_name'             => slug($title),
            'to_ping'               => '',
            'pinged'                => '',
            'post_modified'         => Date::now(),
            'post_modified_gmt'     => Date::gmtNow(),
            'post_content_filtered' => '',
            'post_parent'           => 0,
            'guid'                  => "{$url}/?p={$id}",
            'menu_order'            => 0,
            'post_type'             => 'post'
        );

        return $defaults;
    }

    /**
     * Generates a post title.
     *
     * @param int $id The post ID.
     *
     * @return string The generated post title.
     */
    protected static function generateTitle($id): string
    {
        return sprintf('Post %d title', $id);
    }

    /**
     * Generates a post content.
     *
     * @param int $id The post ID.
     *
     * @return string The generated post content.
     */
    protected static function generateContent($id): string
    {
        return sprintf('Post %d content', $id);
    }

    /**
     * Generates the post excerpt.
     *
     * @param int $id The post ID.
     *
     * @return string The post excerpt.
     */
    private static function generateExcerpt($id): string
    {
        return sprintf('Post %d excerpt', $id);
    }

    /**
     * Generates a page guid.
     *
     * @param int    $id  The page id.
     * @param string $url The site url.
     *
     * @return string      The database guid entry.
     */
    protected static function generatePageGuid($id, $url)
    {
        $guid = rtrim($url, '/') . '/?page_id=' . $id;

        return $guid;
    }

    /**
     * Generates a post guid.
     *
     * @param int    $id  The post id.
     * @param string $url The site url.
     *
     * @return string      The database guid entry.
     */
    protected static function generatePostGuid($id, $url)
    {
        $guid = rtrim($url, '/') . '/?p=' . $id;

        return $guid;
    }
}
