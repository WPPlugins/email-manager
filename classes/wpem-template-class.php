<?php
if (!class_exists('WPEM_Email_Template')) {

    /**
     * Creates a custom post type and associated taxonomies
     */
    class WPEM_Email_Template extends WPEM_Module implements WPEM_Custom_Post_Type {

        protected static $readable_properties = array();
        protected static $writeable_properties = array();

        const POST_TYPE_NAME = 'Email Template';
        const POST_TYPE_SLUG = 'wpem-temps';
        const TAG_NAME = 'Template Categories';
        const TAG_SLUG = 'temp-cats';


        /*
         * Magic methods
         */

        /**
         * Constructor
         *
         * @mvc Controller
         */
        protected function __construct() {
            $this->register_hook_callbacks();
        }

        /*
         * Static methods
         */

        /**
         * Registers the custom post type
         *
         * @mvc Controller
         */
        public static function create_post_type() {
            if (!post_type_exists(self::POST_TYPE_SLUG)) {
                $post_type_params = self::get_post_type_params();
                $post_type = register_post_type(self::POST_TYPE_SLUG, $post_type_params);

                if (is_wp_error($post_type)) {
                    add_notice(__METHOD__ . ' error: ' . $post_type->get_error_message(), 'error');
                }
            }
        }

        /**
         * Defines the parameters for the custom post type
         *
         * @mvc Model
         *
         * @return array
         */
        protected static function get_post_type_params() {
            $labels = array(
                'name' => self::POST_TYPE_NAME . 's',
                'singular_name' => self::POST_TYPE_NAME,
                'add_new' => 'Add New',
                'add_new_item' => 'Add New ' . self::POST_TYPE_NAME,
                'edit' => 'Edit',
                'edit_item' => 'Edit ' . self::POST_TYPE_NAME,
                'new_item' => 'New ' . self::POST_TYPE_NAME,
                'view' => 'View ' . self::POST_TYPE_NAME . 's',
                'view_item' => 'View ' . self::POST_TYPE_NAME,
                'search_items' => 'Search ' . self::POST_TYPE_NAME . 's',
                'not_found' => 'No ' . self::POST_TYPE_NAME . 's found',
                'not_found_in_trash' => 'No ' . self::POST_TYPE_NAME . 's found in Trash',
                'parent' => 'Parent ' . self::POST_TYPE_NAME
            );

            $post_type_params = array(
                'labels' => $labels,
                'singular_label' => self::POST_TYPE_NAME,
                'public' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'register_meta_box_cb' => __CLASS__ . '::add_meta_boxes',
                'taxonomies' => array(self::TAG_SLUG),
                'menu_position' => 20,
                'hierarchical' => true,
                'capability_type' => 'post',
                'has_archive' => false,
                'rewrite' => false,
                'query_var' => false,
                'supports' => array('title', 'editor', 'revisions')
            );

            return apply_filters('wpem_post-type-params', $post_type_params);
        }

        /**
         * Registers the category taxonomy
         *
         * @mvc Controller
         */
        public static function create_taxonomies() {
            if (!taxonomy_exists(self::TAG_SLUG)) {
                $tag_taxonomy_params = self::get_tag_taxonomy_params();
                register_taxonomy(self::TAG_SLUG, self::POST_TYPE_SLUG, $tag_taxonomy_params);
            }
        }

        /**
         * Defines the parameters for the custom taxonomy
         *
         * @mvc Model
         *
         * @return array
         */
        protected static function get_tag_taxonomy_params() {
            $tag_taxonomy_params = array(
                'label' => self::TAG_NAME,
                //'public'                => false,
                'show_in_nav_menus' => false,
                'labels' => array('name' => self::TAG_NAME, 'singular_name' => self::TAG_NAME),
                'hierarchical' => true,
                'rewrite' => array('slug' => self::TAG_SLUG),
                'update_count_callback' => '_update_post_term_count'
            );

            return apply_filters('wpem_tag-taxonomy-params', $tag_taxonomy_params);
        }

        /**
         * Adds meta boxes for the custom post type
         *
         * @mvc Controller
         */
        public static function add_meta_boxes() {
            add_meta_box(
                    'wpem_css-box', 'CSS Box', __CLASS__ . '::markup_meta_boxes', self::POST_TYPE_SLUG, 'normal', 'core'
            );
        }

        /**
         * Builds the markup for all meta boxes
         *
         * @mvc Controller
         *
         * @param object $post
         * @param array  $box
         */
        public static function markup_meta_boxes($post, $box) {
            $variables = array();

            switch ($box['id']) {
                case 'wpem_css-box':
                    $variables['cssBoxField'] = get_post_meta($post->ID, 'wpem_css-box-field', true);
                    $view = 'wpem-email-template/metabox-css.php';
                    break;

                default:
                    $view = false;
                    break;
            }
            $variables = apply_filters('wpem_variables', $variables, $view);
            echo self::render_template($view, $variables);
        }

        /**
         * Determines whether a meta key should be considered private or not
         *
         * @mvc Model
         *
         * @param bool $protected
         * @param string $meta_key
         * @param mixed $meta_type
         * @return bool
         */
        public static function is_protected_meta($protected, $meta_key, $meta_type) {
            switch ($meta_key) {

                case 'wpem_css-box':
                case 'wpem_css-box2':
                    $protected = false;
                    break;
            }

            return $protected;
        }

        /**
         * Saves values of the the custom post type's extra fields
         *
         * @mvc Controller
         *
         * @param int    $post_id
         * @param object $post
         */
        public static function save_post($post_id, $revision) {
            global $post;
            $ignored_actions = array('trash', 'untrash', 'restore');

            if (isset($_GET['action']) && in_array($_GET['action'], $ignored_actions)) {
                return;
            }

            if (!$post || $post->post_type != self::POST_TYPE_SLUG || !current_user_can('edit_post', $post_id)) {
                return;
            }

            if (( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || $post->post_status == 'auto-draft') {
                return;
            }

            self::save_custom_fields($post_id, $_POST);
        }

        /**
         * Validates and saves values of the the custom post type's extra fields
         *
         * @mvc Model
         *
         * @param int   $post_id
         * @param array $new_values
         */
        protected static function save_custom_fields($post_id, $new_values) {
            if (did_action('save_post') !== 1)
                return;

            if (isset($new_values['wpem_css-box-field']) && !empty($new_values['wpem_css-box-field'])) {
                $new_css = apply_filters('wpem_save_css-box-field', $new_values['wpem_css-box-field']);
                update_post_meta($post_id, 'wpem_css-box-field', $new_css);
                //add_notice( 'Example of failing validation', 'error' );
            }

            do_action('wpem_save_css', $post_id);
        }

        function sanitize_css($new_values) {
            $keywords = array("/<style>/", "/<\/style>/");
            $replacements = array("");
            $new_values = preg_replace($keywords, $replacements, $new_values);
        }

        /**
         * Defines the shortcode
         *
         * @mvc Controller
         *
         * @param array $attributes
         * return string
         */
        public static function wpem_shortcode($attributes) {
            static $display_post;
            $attributes = apply_filters('wpem_shortcode-attributes', $attributes);
            $attributes = self::validate_shortcode_atts($attributes);

            if (isset($attributes['id'])) {
                if (!isset($display_post) || ($display_post->ID != $attributes['id'])) {
                    $display_post = get_post($attributes['id']);
                }
                switch ($attributes[__('content', 'wpem')]) {
                    case __('img', 'wpem'):
                        $size = 'post-thumbnail';
                        if (isset($attributes['img_size'])) {
                            $size = $attributes['img_size'];
                        }
                        return get_the_post_thumbnail($display_post->ID, $size);
                        break;
                    case __('title', 'wpem'):
                        return $display_post->post_title;
                        break;
                    case __('post_link', 'wpem'):
                        return get_permalink($display_post->ID);
                    case __('title_link', 'wpem'):
                        return '<a href="' . get_permalink($display_post->ID) . '">' . $display_post->post_title . '</a>';
                        break;
                    case __('excerpt', 'wpem'):
                        return $display_post->post_excerpt;
                        break;
                    case __('body', 'wpem'):
                        return $display_post->post_content;
                        break;
                }
            } elseif (isset($attributes[__('link', 'wpem')])) {
                switch ($attributes[__('link', 'wpem')]) {
                    case __('unsubscribe', 'wpem'):
                        $settings = WPEM()->modules['settings']->settings['user'];
                        $location = add_query_arg('wpem_action', 'unsubscribe', get_permalink($settings['unsubscribe_page']));
                        return '<a href="' . $location . '">' . $settings['unsubscribe_text'] . '</a>';
                        break;
                }
            } elseif (isset($attributes[__('archive', 'wpem')]) && intval($attributes[__('archive', 'wpem')])) {
                $settings = WPEM()->modules['settings']->settings['mail'];
                return '<a href="' . site_url('/?' . __('wpem_browser_mail', 'wpem') . '=' . $attributes[__('archive', 'wpem')]) . '">' . $settings['show_in_browser_text'] . '</a>';
                break;
            }
        }

        /**
         * Validates the attributes for the shortcodes
         *
         * @mvc Model
         *
         * @param array $attributes
         * return array
         */
        protected static function validate_shortcode_atts($attributes) {

            if (isset($attributes['id'])) {

                if (!absint($attributes['id'])) {
                    $attributes['id'] = 1;
                }

                if (!isset($attributes['content']) || !in_array($attributes['content'], array('img', 'title', 'title_link', 'link', 'excerpt', 'body'))) {
                    $attributes['content'] = 'title_link';
                }
            } elseif (isset($attributes['action'])) {
                $attributes['action'] = 'unsubscribe'; //only one action available 
            }

            return apply_filters('wpem_validate-shortcode-atts', $attributes);
        }

        public function unsubscribe($content) {

            if (isset($_REQUEST['wpem_action']) && $_REQUEST['wpem_action'] == 'unsubscribe') {

                if (is_user_logged_in()) {
                    if ($this->user_unsubscribe()) {
                        $unsubscribe_text = __('You have been un-subscribed from the mailing list!', 'wpem');
                        $content = '<div id="unsubscribe-text"><h3>' . $unsubscribe_text . '</h3></div>' . $content;
                    } else {
                        
                    }
                }
            }
            return $content;
        }

        public function unsubscribe_link_action() {

            if (isset($_REQUEST['wpem_action']) && $_REQUEST['wpem_action'] == 'unsubscribe') {
                if ($this->user_unsubscribe()) {
                    add_notice('You have been removed from the emailing list. You can enlist again from your profile settings', 'wpem');
                } else {
                    
                }
            }
        }

        function user_unsubscribe() {
            global $user_email;
            return WPEM_Settings::unsubscribe($user_email);
        }

        public static function create_default_pages() {
            $fetch_post = get_posts(array('post_type' => 'wpem-temps', 'numberposts' => 1));

            if (empty($fetch_post)) {
                require_once( WPEM_PLUGIN_PATH . '/includes/sample-email/sample-email.php' );
                // Basic Template
                $basic_temp = wp_insert_post(
                        array(
                            'post_title' => $sample1_title,
                            'post_content' => $sample1_body,
                            'post_status' => 'publish',
                            'post_author' => 1,
                            'post_type' => 'wpem-temps',
                        )
                );

                if ($basic_temp && !is_wp_error($basic_temp)) {
                    update_post_meta($basic_temp, 'wpem_css-box-field', $sample1_css);
                }
            }
        }

        /**
         * Extend TinyMCE config with a setup function.
         * See http://www.tinymce.com/wiki.php/API3:event.tinymce.Editor.onInit
         *
         * @since 0.1.0
         *
         * @param array $init
         * @return array
         */
        public static function template_tinymce_css($init) {
            global $typenow;
            if ($typenow == 'wpem-temps' || $_REQUEST['page'] == 'wpem_mail') {

                if (isset($_REQUEST['temp_id'])) {
                    $post_id = absint($_REQUEST['temp_id']);
                } elseif (isset($_POST['schedule']['template']) && !empty($_POST['schedule']['template'])) {
                    $post_id = absint($_POST['schedule']['template']);
                } else {
                    global $post_id;
                }

                $css = get_post_meta($post_id, 'wpem_css-box-field', true);
                ?>
                <script type="text/javascript">
                                                                                                                                                
                    function addTempCSS( ed ) {
                        ed.onInit.add( function() {
                            tinyMCE.activeEditor.dom.addStyle(<?php echo json_encode($css) ?>);
                        } );
                    };
                </script>

                <?php
                if (wp_default_editor() == 'tinymce')
                    $init['setup'] = 'addTempCSS';
            }

            return $init;
        }

        public static function add_tinymce_style_sheet($css) {
            global $typenow;
            if ($typenow == 'wpem-temps' || $_REQUEST['page'] == 'wpem_mail') {
                $wpme_css = plugins_url('css/editor.css', dirname(__FILE__)) . '?ver=' . WPEM_VERSION;
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
                $version = 'ver=' . $GLOBALS['wp_version'];
                $dashicons = includes_url("css/dashicons$suffix.css?$version");

                // WordPress default stylesheet and dashicons
                $baseurl = includes_url('js/tinymce');
                $mce_css = array(
                    $dashicons,
                    $baseurl . '/skins/wordpress/wp-content.css?' . $version,
                    $wpme_css
                );
                return implode(',', $mce_css);
            }
            return $css;
        }

        public function browser_preview_button() {
            global $pagenow, $typenow, $post;
            $output = '';

            /** Only run in email template screens */
            if (in_array($pagenow, array('post.php', 'page.php', 'post-edit.php')) && $typenow == 'wpem-temps') {
                $img = '<span class="wp-media-buttons-icon" id="browser_preview"></span>';
                $output = '<a target="_blank" href="' . site_url('/?' . __('wpem_browser_mail', 'wpem') . '=' . $post->ID) . '" class="button wpem_icons" title="' . __('Browser Preview', 'wpem') . '" style="padding-left: .4em;">' . $img . ' ' . __('Browser Preview', 'wpem') . '</a>';
            }
            echo $output;
        }

        public function show_email_in_browser() {
            if (isset($_REQUEST[__('wpem_browser_mail', 'wpem')]) && absint($_REQUEST[__('wpem_browser_mail', 'wpem')])) {
                $mail = get_post($_REQUEST[__('wpem_browser_mail', 'wpem')]);

                if (isset($mail)) {
                    if ($mail->post_type != 'wpem-temps') {
                        return;
                    }
                    $style = '';

                    $css = get_post_meta(intval($_REQUEST[__('wpem_browser_mail', 'wpem')]), 'wpem_css-box-field', true);

                    if (!empty($css)) {
                        $style = '<style>' . $css . '</style>';
                    }
                    $mailtext = '<html><head><title>' . $mail->post_title . '</title>' . $style . '</head><body>' . do_shortcode($mail->post_content) . '</body></html>';
                    echo $mailtext;

                    exit;
                }
            }
        }

        public function remove_wpautop($settings) {
            global $typenow;
            if ($typenow == 'wpem-temps') {
                $settings['wpautop'] = false;
                remove_filter('the_editor_content', 'wp_richedit_pre');
            }
            return $settings;
        }

        public function remove_wp_richedit_pre($content) {
            global $pagenow, $typenow;
            if ($typenow == 'wpem-temps') {
                remove_filter('the_editor_content', 'wp_richedit_pre');
                add_filter('the_editor_content', array($this, 'wp_richedit_pre'));
            }
            return $content;
        }

        /**
         * Formats text for the rich text editor.
         *
         * The filter 'richedit_pre' is applied here. If $text is empty the filter will
         * be applied to an empty string.
         *
         *
         * @param string $text The text to be formatted.
         * @return string The formatted text after filter is applied.
         */
        function wp_richedit_pre($text) {
            if (empty($text)) {
                /**
                 * Filter text returned for the rich text editor.
                 *
                 * This filter is first evaluated, and the value returned, if an empty string
                 * is passed to wp_richedit_pre(). If an empty string is passed, it results
                 * in a break tag and line feed.
                 *
                 * If a non-empty string is passed, the filter is evaluated on the wp_richedit_pre()
                 * return after being formatted.
                 *
                 * @since 2.0.0
                 *
                 * @param string $output Text for the rich text editor.
                 */
                return apply_filters('richedit_pre', '');
            }

            $output = convert_chars($text);
            $output = htmlspecialchars($output, ENT_NOQUOTES, get_option('blog_charset'));

            /** This filter is documented in wp-includes/formatting.php */
            return apply_filters('richedit_pre', $output);
        }

        /*
         * Instance methods
         */

        /**
         * Register callbacks for actions and filters
         *
         * @mvc Controller
         */
        public function register_hook_callbacks() {
            add_action('init', __CLASS__ . '::create_post_type');
            add_action('init', __CLASS__ . '::create_taxonomies');
            add_action('save_post', __CLASS__ . '::save_post', 10, 2);
            add_filter('is_protected_meta', __CLASS__ . '::is_protected_meta', 10, 3);
            add_filter('the_content', array($this, 'unsubscribe'));
            add_action('init', array($this, 'init'));
            add_shortcode('wpem', __CLASS__ . '::wpem_shortcode');
            add_action('media_buttons', array($this, 'browser_preview_button'), 11);
            add_filter('mce_css', __CLASS__ . '::add_tinymce_style_sheet');
            add_filter('tiny_mce_before_init', array(__CLASS__, 'template_tinymce_css'));
            // Display a plain-text changelog
            add_action('template_redirect', array($this, 'show_email_in_browser'), -999);
            add_filter('the_editor_content', array($this, 'remove_wp_richedit_pre'), -99);
            add_filter('wp_editor_settings', array($this, 'remove_wpautop'), 1);
        }

        /**
         * Prepares site to use the plugin during activation
         *
         * @mvc Controller
         *
         * @param bool $network_wide
         */
        public function activate($network_wide) {
            self::create_post_type();
            self::create_taxonomies();
            self::create_default_pages();
        }

        /**
         * Rolls back activation procedures when de-activating the plugin
         *
         * @mvc Controller
         */
        public function deactivate() {
            
        }

        /**
         * Initializes variables
         *
         * @mvc Controller
         */
        public function init() {
            
        }

        /**
         * Executes the logic of upgrading from specific older versions of the plugin to the current version
         *
         * @mvc Model
         *
         * @param string $db_version
         */
        public function upgrade($db_version = 0) {
            /*
              if( version_compare( $db_version, 'x.y.z', '<' ) )
              {
              // Do stuff
              }
             */
        }

        /**
         * Checks that the object is in a correct state
         *
         * @mvc Model
         *
         * @param string $property An individual property to check, or 'all' to check all of them
         *
         * @return bool
         */
        protected function is_valid($property = 'all') {
            return true;
        }

    }

    // end WPEM_Email_Template
}
