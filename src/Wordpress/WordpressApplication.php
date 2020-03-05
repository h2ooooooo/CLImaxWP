<?php


namespace CLImax\Wordpress;

use CLImax\ApplicationUtf8;
use CLImax\Plugins\HighlightPlugin;
use SGI\helpers\Path;

abstract class WordpressApplication extends ApplicationUtf8 {
    /** @var string $_wpRoot The WP root path for caching purposes */
    private $_wpRoot;

    /**
     * The constructor - parses arguments and saves the starting
     * time of the script so we can check how long the entire
     * application took to run, when it dies (see __destruct())
     *
     * @param int        $debugLevel       Which debug level to start the script on - if null, it will default to
     *                                     $defaults->debugLevel.
     * @param string     $environmentClass One of the environments from the Environment class
     * @param array|null $defaultsOverride An array to specify what defaults should be overwritten compared to the
     *                                     environment you're in
     * @param bool       $disableAnsi      Whether or not to disable ANSI codes from output
     *
     * @throws \Exception Throws an exception if we're not in a CLI app
     */
    public function __construct(
        $debugLevel = null,
        $environmentClass = null,
        $defaultsOverride = null,
        $disableAnsi = false
    ) {
        parent::__construct($debugLevel, $environmentClass, $defaultsOverride, $disableAnsi);

        $this->__load();

        $this->registerPlugin(new HighlightPlugin());

        $this->scriptName = get_class($this);
    }



    /**
     * Loads the WordPress Environment if needed
     *
     * @throws \Exception
     */
    public function __load() {

        // Don't use themes if we don't have to
        if ( ! defined('WP_USE_THEMES')) {
            define('WP_USE_THEMES', false);
        }

        // Some cache plugins (eg. EndurancePageCache) check this setting, so let's make sure to set it
        $_GET['doing_wp_cron'] = true;

        if (!defined('ABSPATH')) {
            $this->__requireWpRootFile('wp-load.php');
        }

        if ( ! function_exists('\wp_set_current_user')) {
            $this->__requireWpRootFile('wp-includes/pluggable.php');
        }

        if (function_exists('remove_all_actions')) {
            \remove_all_actions('shutdown');
        }
    }

    /**
     * Gets the root path of the WP installation
     *
     * @return string The root of the WP installation (the folder with wp-content, wp-admin and wp-include folders
     *
     * @throws \Exception Throws an exception if they couldn't be found
     */
    protected function __getWpRoot() {
        $thisPath = Path::normalize(__FILE__, '/');

        if (preg_match('~^(.+)/wp-content/~i', $thisPath, $match)) {
            return $match[1];
        }

        throw new \Exception(sprintf('Could not find WP root - please override Application->__getWpRoot()'));
    }

    /**
     * Loads a specific path from the root of the WP installation
     *
     * @param string $path The path to require from the root of the WP installation
     *
     * @return mixed Whatever require_once returns for that particular path
     *
     * @throws \Exception Throws an exception if $path couldn't be found
     */
    private function __requireWpRootFile($path) {
        $wpRoot = $this->__getWpRoot();

        $loadPath = Path::normalize($wpRoot . '/' . $path);

        if (!file_exists($loadPath)) {
            throw new \Exception(sprintf('No file exists at "%s"', $loadPath));
        }

        return require_once($loadPath);
    }

    /**
     * Gets the $wpdb variable without having to access globals directly
     *
     * @return \wpdb
     */
    protected function getWpDb() {
        global $wpdb;

        return $wpdb;
    }
}
