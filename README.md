# CLImaxWP

WP interface for CLImax. Simply create a file that extends `WordpressApplication` and launch it. It'll already have run all the WP code and all the WP functions will be available.

**wp-content/plugins/custom/cli.php**:

    <?php
    
    use CLImax\Wordpress\WordpressApplication;
    
    require_once(__DIR__ . '/../vendor/autoload.php');
    
    class CliApplication extends WordpressApplication {
       public function init() {
           $this->info(sprintf('Looking up site URL in wordpress..'));
           $siteUrl = $this->getWpDb()->get_var('SELECT option_value FROM wp_options WHERE option_name = "siteurl"');
    
           $this->success(sprintf('ABSPATH value: {{%s}}', ABSPATH));
           $this->success(sprintf('Site URL: {{%s}}', $siteUrl));
       }
    }
    
    CliApplication::launch();

..and its output:

    C:\git\climax-wp-test\wp-content\plugins\custom>php cli.php
    
    ----------------------------------------------------------------------------------------------------[START]----------------------------------------------------------------------------------------------------08:59:07,8955 INFO: Looking up site URL in wordpress..
    08:59:07,8972 SUCCESS: ABSPATH value: C:\git\climax-wp-test\
    08:59:07,0898 SUCCESS: Site URL: https://climax-wp-test.localhost
    -----------------------------------------------------------------------------------------------------[END]-----------------------------------------------------------------------------------------------------Script ImportApplication ended at 2020-03-05 08:59:07
    
    It took 0 hours, 0 minutes, 0.7750 seconds

## Special methods

**protected function __getWpRoot()**

You might have to override the `__getWpRoot()` method inside of the application, if it's not located inside of a `wp-content` folder. By default the function searches the parent path to find `/wp-content/`.

    protected function __getWpRoot() {
        return __DIR__; // Assuming the cli file is placed in the root of the webserver
    }
    
**protected function getWpDb()**

The getWpDb() method returns an instance of `\wpdb` without the need to use `$GLOBALS` or `global $wpdb`.
