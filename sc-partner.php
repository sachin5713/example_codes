<?php
/**
 * Custom Shortcode for display partners post and search functionality
 * 
**/

class Display_Partners {
    function __construct() {
        $this->init();
    }

    // Register shortcode and scripts
    private function init() {
        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
        add_action('init', [$this, 'register_async']);
        add_action('wp_ajax_nopriv_load_partners', [$this, 'load_partners']);
        add_action('wp_ajax_load_partners', [$this, 'load_partners']);
    }

    // Create shortcode
    public function register_async() {
        add_shortcode('norboat_partners', [$this, 'render']);
    }

    // Define scripts
    public function register_scripts() {
        $ajax_script_ver = date("ymd-Gis", filemtime(get_stylesheet_directory() . '/partners/ajax-scripts.js'));
        wp_register_script('nbsc-ajax-script', get_stylesheet_directory_uri() . '/partners/ajax-scripts.js', array('jquery'), $ajax_script_ver, true);

        // Localizing script
        wp_localize_script('nbsc-ajax-script', 'nbsc', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'empty_partner_message' => __('No more partners is available!', 'nbsc_domain'),
        ));

        wp_enqueue_script('nbsc-ajax-script');
    }

    // Prepare Partners Posts
    private function get_partners($args = []) {
        $default_args = [
            'post_type' => 'partner',
            'posts_per_page' => 9,
            'paged' => isset($args['paged']) ? $args['paged'] : 1,
        ];

        // Check if search keyword is passed and add it to query args
        if (!empty($args['search'])) {
            $default_args['s'] = sanitize_text_field($args['search']);
        }

        // Merge passed arguments with default arguments
        $query_args = wp_parse_args($args, $default_args);

        // Query the posts
        $query = new WP_Query($query_args);

        // Prepare output
        $output = '';
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $output .= '<div class="partner">';
                if (has_post_thumbnail()) {
                    $output .= get_the_post_thumbnail(get_the_ID(), 'full');
                }
                $output .= '<h3>' . get_the_title() . '</h3>';
                $output .= '<div class="content">' . get_the_excerpt() . '</div>';
                $output .= '<a href="' . get_the_permalink() . '" class="read-more">'.__('READ MORE','nbsc_domain').'</a>';
                $output .= '</div>';
            }
        } else {
            $output .= false;
        }

        // Restore original Post Data
        wp_reset_postdata();

        return $output;
    }

    // Render the shortcode
    public function render($atts) {
        $args = shortcode_atts([
            'search' => ''
        ], $atts);

        ob_start();
        ?>
        <div id="partner-search-container">
            <input type="text" id="partner-search" placeholder="<?php echo __('Type to search partners','nbsc_domain')?>">
            <button id="partner-search-button"><?php include(get_stylesheet_directory() . '/partners/search.svg'); ?></button>
            <button id="partner-close-button" style="display:none;"><?php include(get_stylesheet_directory() . '/partners/close.svg'); ?></button>
        </div>
        <div id="partner-grid">
            <?php echo $this->get_partners($args); ?>
        </div>
        <div id="load-more-container">
            <div id="load-more"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Load more partners via AJAX
    public function load_partners() {
        $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        $args = [
            'paged' => $paged,
            'search' => $search,
        ];

        echo $this->get_partners($args);
        wp_die();
    }
}

new Display_Partners();



