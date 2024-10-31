<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wordpress.org/plugins/scheduled-post-shortcut/
 * @since             1.0.0
 * @package           Pressware\BloggingPlugins\ScheduledPostShortcut
 *
 * @wordpress-plugin
 * Plugin Name:       Scheduled Post Shortcut
 * Plugin URI:        https://wordpress.org/plugins/scheduled-post-shortcut/
 * Description:       Easily access your scheduled posts from the WordPress dashboard.
 * Version:           1.7.0
 * Author:            Pressware, LLC
 * Author URI:        https://pressware.co
 * Text Domain:       scheduled-post-shortcuts
 * Domain Path:       /languages
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Pressware;

use Pressware\BloggingPlugins\ScheduledPostShortcut\Admin\ScheduledPostShortcut;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// The primarily class responsible for introducing functionality into WordPress.
include_once 'admin/classes/ScheduledPostShortcut.php';

add_action('plugins_loaded', __NAMESPACE__ . '\\presswareScheduledPostShortcut');
/**
 * Officially starts the plugin.
 *
 * @since 1.0.0
 */
function presswareScheduledPostShortcut()
{
    $plugin = new ScheduledPostShortcut();
    $plugin->init();
}
