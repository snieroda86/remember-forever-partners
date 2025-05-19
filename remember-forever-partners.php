<?php 
/*
 * Plugin Name:       Partnerzy biznesowi
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics with this plugin.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Sebastian Nieroda
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       remember-forever
 * Domain Path:       /languages
 */

if(!defined('ABSPATH')){
	exit;
}

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    exit;
}

// Chceck if WPML is active
add_action( 'admin_init', 'remember_forever_partnets_check_wpml' );
function remember_forever_partnets_check_wpml() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) && 
         ( ! defined( 'ICL_SITEPRESS_VERSION' ) || ! class_exists( 'SitePress' ) ) ) {

        add_action( 'admin_notices', 'remember_forever_partners_wpml_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}



function remember_forever_partners_wpml_notice() {
    echo '<div class="notice notice-error is-dismissible">';
    echo '<p><strong>Nie można aktywować wtyczki: wymagana jest aktywna wtyczka WPML.</strong></p>';
    echo '</div>';
}


if( ! class_exists('Remember_Forever_Partners')){
	class Remember_Forever_Partners{


		public function __construct(){
			$this->define_constants();

			add_action('plugins_loaded', array($this , 'snrm_load_textdomain'));

			// Create admin menu
			add_action('admin_menu' , array($this , 'create_admin_menu') );
			

			// registration form shortcode
			require_once(RMF_SN_PATH.'/shortcodes/business-partners-registration-form.php');
			$RMB_Forever_Registration = new RMB_Forever_Registration();

			// partner account
			require_once(RMF_SN_PATH.'/shortcodes/business-partner-account.php');
			$RMB_Forever_Account = new RMB_Forever_Account();

			// Enqueue scripts
			add_action('wp_enqueue_scripts' , array($this , 'register_scripts'),999 );

			
		}

		// Constans
		public function define_constants(){
			define('RMF_SN_PATH' , plugin_dir_path( __FILE__ ));
			define('RMF_SN_URL' , plugin_dir_url(__FILE__));
			define('RMF_SN_VERSION' , '1.0.0');
		}

		// Load textdomain
		public function snrm_load_textdomain() {
		    load_plugin_textdomain('remember-forever', false, dirname(plugin_basename(__FILE__)) . '/languages');
		}
		

		// Activate
		public static function activate(){ 

			// Create custom user role
			$customer_role = get_role('customer');
    
		    if (!get_role('partner_biznesowy')) {
			    if ($customer_role) {
			        add_role('partner_biznesowy', 'Partner biznesowy', $customer_role->capabilities);
			    } else {
			        add_role('partner_biznesowy', 'Partner biznesowy', ['read' => true]);
			    }
			}

		    // Create login / business partneta account page 
		    $page_title = 'Konto partnera biznesowego';
		    $page_slug = 'konto-partnera-biznesowego';
		    $current_user = wp_get_current_user();

		    $args = array(
			    'post_type' => 'page',
			    'post_status' => 'publish',
			    'posts_per_page' => 1,
			    'name' => $page_slug,
			    
		    );

		    $query = new WP_Query($args);

		    if (!$query->have_posts()) {
		       $page_id = wp_insert_post(array(
		            'post_title'     => $page_title,
		            'post_name' => $page_slug,
		            'post_type'      => 'page',
		            'post_status'    => 'publish',
		            'post_author'   => $current_user->ID,
			    	'post_content'  => '<!-- wp:shortcode -->[rm_partner_account]<!-- /wp:shortcode -->'
		        ));

		       if ($page_id) {
				    update_option('business_partner_account_page_id', $page_id);
			   }

		    }




		}

		// Deactivate
		public static function deactivate(){
			
		}

		// Uninstall
		public static function uninstall(){
			
		}

		// Create admin menu
		public function create_admin_menu(){
			add_menu_page(
		        __( 'Partnerzy biznesowi', 'remember-forever' ),
		        'Partnerzy biznesowi',
		        'manage_options',
		        'remember_forever_admin',
		        array($this , 'remember_forever_partners_list'),
		        'dashicons-images-alt2',
		        6
		    );

		    // Submenu page
		    add_submenu_page(
			    null,
			    'Szczegóły partnera',
			    'Szczegóły partnera',
			    'manage_options',
			    'remember_forever_partner_details',
			    array($this, 'remember_forever_partner_details_page')
			);

		     
		}

		// Admin menu page
		public function remember_forever_partners_list(){
			$args = array(
			    'role'    => 'partner_biznesowy',
			    'orderby' => 'ID',
			    'order'   => 'ASC'
			);
			$users = get_users( $args );
			require_once RMF_SN_PATH.'views/business-partners-list.php';
		}

		// Admin submenu page
		public function remember_forever_partner_details_page() {
		    if (!current_user_can('manage_options')) {
		        wp_die(__('Brak dostępu.'));
		    }

		    $user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

		    if ($user_id <= 0) {
		        echo '<div class="notice notice-error"><p>Nieprawidłowy identyfikator użytkownika.</p></div>';
		        return;
		    }

		    require_once RMF_SN_PATH.'views/business-partner-single.php';
		}


		

		// Regitser scripts
		public function register_scripts(){
			wp_register_style( 'rmf-partners-style', RMF_SN_URL.'/assets/css/rmf-partners-style.css' );
			wp_register_style( 'rmf-partners-account-style', RMF_SN_URL.'/assets/css/rmf-partners-account-style.css' );

		}

	}
}

if( class_exists('Remember_Forever_Partners')){

	register_activation_hook( __FILE__ , array('Remember_Forever_Partners' , 'activate'));
	register_deactivation_hook( __FILE__ , array('Remember_Forever_Partners' , 'deactivate'));
	register_uninstall_hook( __FILE__ , array('Remember_Forever_Partners' , 'uninstall'));
	$Remember_Forever_Partners = new Remember_Forever_Partners();
}