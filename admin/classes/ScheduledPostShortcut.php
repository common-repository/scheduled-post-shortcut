<?php
/**
 * The core class for loading adding the 'Scheduled' submenu item to the 'Posts'
 * menu.
 *
 * @package           Pressware\BloggingPlugins\ScheduledPostShortcut
 * @since   1.0.0
 */

namespace Pressware\BloggingPlugins\ScheduledPostShortcut\Admin;

/**
 * Adds the 'Scheduled' submenu item to the 'Posts' menu. This will only works
 * for the roles who have the ability to create, schedule, and edit posts.
 *
 * Specifically, this will only work ford
 *
 * @package Pressware\BloggingPlugins\ScheduledPostShortcut
 * @since   1.0.0
 */
class ScheduledPostShortcut
{

    /**
     * The current version of the plugin.
     *
     * @access private
     * @var string $version The current version of this plugin.
     */
    private $version;

    /**
     * Maintains a reference to the query that retrieves the scheduled posts.
     *
     * @access private
     * @var    WP_Query $scheduledPostsQuery A reference to the post query.
     */
    private $scheduledPostsQuery;

    /**
     * Initializes the properties of the class.
     */
    public function __construct()
    {
        $this->version = '1.7.0';
        $this->scheduledPostsQuery = null;
    }

    /**
     * Hooks the submenu page functionality into the admin_menu hook of WordPress
     * so we can customize it for Scheduled posts.
     */
    public function init()
    {
        $pluginPath = plugin_basename(dirname(dirname(dirname(__FILE__))));
        $pluginPath .= '/languages';

        load_plugin_textdomain(
            'scheduled-posts-shortcuts',
            false,
            $pluginPath
        );

        add_action(
            'admin_menu',
            array($this, 'addSubmenuPage')
        );

        add_action(
            'admin_enqueue_scripts',
            array($this, 'adminEnqueueStyles')
        );

        add_filter(
            'parent_file',
            array($this, 'highlightScheduledSubmenu')
        );
    }

    /**
     * Highlights the 'Scheduled' submenu item using WordPress native styles.
     *
     * @param string $parentFile The filename of the parent menu.
     *
     * @return string $parentFile The filename of the parent menu.
     */
    public function highlightScheduledSubmenu($parentFile)
    {
        global $submenuFile;

        // Only update the submenu file value if we're on the Scheduled page.
        if ('future' === get_query_var('post_status')) {
            // Support future posts for every post type.
            $args = array(
                'post_status' => 'future',
                'post_type'   => get_post_type(),
            );

            /* We have to use the full URL so get the admin URL and concatenate
             * it with the parent file.
             */
            $adminUrl = trailingslashit(get_admin_url());
            $url = sprintf('%s%s', $adminUrl, $parentFile);

            $submenuFile = add_query_arg($args, $url);
        }

        return $parentFile;
    }

    /**
     * Adds a shortcuted to the 'Scheduled' posts page in the 'Posts' menu.
     *
     * @return string The submenu item. Primarily returned for unit testing.
     */
    public function addSubmenuPage()
    {
        $url = $this->createMenuUrl();
        $scheduledSubmenuItem = $this->createSubmenuItem($url);
        $this->rebuildPostsMenu($scheduledSubmenuItem);

        // Return this string that composes the menu to improve testability.
        return $scheduledSubmenuItem;
    }

    /**
     * Adds the CSS responsible for properly adjusting the location of the
     * scheduled post icon on the submenu item.
     */
    public function adminEnqueueStyles()
    {
        wp_enqueue_style(
            'scheduled-post-shortcut',
            plugin_dir_url(dirname(__FILE__)) . 'css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Creates the key/value pair query string to be appended to the URL of the
     * 'Scheduled' submenu item.
     *
     * @access private
     *
     * @return array The array of query string parameters for the 'Scheduled' URL.
     */
    private function createMenuUrl()
    {
        return add_query_arg(
            array(
                'post_status' => 'future',
                'post_type'   => 'post',
            ),
            admin_url('edit.php')
        );
    }

    /**
     * Set the array that will be used drive the menu.
     *
     * This is based on code from WordPress core:
     * https://github.com/WordPress/WordPress/blob/d23cd0aa5002e0749555ae355a04fa17e87db5e4/wp-admin/menu.php#L90
     *
     * @access private
     *
     * @param  string $url The URL to which the key/value pair will be appended.
     * @return array       The array that will be added to the 'Posts' menu.
     */
    private function createSubmenuItem($url)
    {
        $menuName = __('Scheduled', 'scheduled-posts-shortcuts');

        $postCount = $this->getScheduledPostsCount();
        $menuName .= '<span id="scheduled-post-count" class="update-plugins count-' . $postCount . '">';
            $menuName .= '<span class="update-count">';
                $menuName .= $postCount;
            $menuName .= '</span>';
        $menuName .= '</span>';

        return array(
            array(
                $menuName,
                'publish_posts',
                $url,
            ),
        );
    }

    /**
     * Rebuilds the 'Posts' menu by placing the 'Scheduled' menu item as the
     * third item in the list.
     *
     * @access private
     *
     * @param array $scheduledSubmenuItem The array that will be added to the 'Posts' menu.
     */
    private function rebuildPostsMenu($scheduledSubmenuItem)
    {
        // Get access to the submenu used to drive the WordPress menu.
        global $submenu;

        // Bail if the current user doesn't have access to the Edit submenu.
        if (null === $submenu || null === $submenu['edit.php']) {
            return;
        }

        // Separate the menu were we want to place the 'Scheduled' menu item.
        $firstSubmenu  = array_slice($submenu['edit.php'], 0, 2, true);
        $secondSubmenu = array_slice($submenu['edit.php'], 2, count($submenu) - 1);

        // Now combine the menu items placing the 'Scheduled' menu item second.
        $submenu['edit.php'] = array_merge(
            $firstSubmenu,
            $scheduledSubmenuItem,
            $secondSubmenu
        );
    }

    /**
     * Retrieves the number of scheduled posts.
     *
     * @access private
     *
     * @return string A string representation of the number of scheduled posts.
     */
    private function getScheduledPostsCount()
    {
        // Create the scheduled post query if it's not been set up.
        if (null === $this->scheduledPostsQuery) {
            $this->scheduledPostsQuery = $this->getScheduledPostsQuery();
        }

        // Define the number of updated posts.
        $postCount = 0;
        if (0 !== $this->scheduledPostsQuery->post_count) {
            $postCount = $this->scheduledPostsQuery->post_count;
        }

        return (string) $postCount;
    }

    /**
     * Returns a reference to the query that retrieves the number of scheduled
     * posts.
     *
     * @access private
     *
     * @return \WP_Query A reference to the query for scheduled posts.
     */
    private function getScheduledPostsQuery()
    {
        $args = array(
            'post_status' => 'future',
            'posts_per_page' => -1,
        );
        $scheduledPostsQuery = new \WP_Query($args);

        return $scheduledPostsQuery;
    }
}
