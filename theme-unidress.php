<?php
/**
 * Plugin Name:       Unidress
 * Description:       Unidress theme.
 * Text Domain: unidress
 * Domain Path: /lang
 *
 * @package Theme_unidress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
define( 'MY_PLUGIN_ROOT', dirname( __FILE__) );
define( 'MY_PLUGIN_NAME', plugin_basename(__DIR__) );
/**
 * Main Theme_unidress Class
 *
 * @class Theme_unidress
 * @version	1.0.0
 * @since 1.0.0
 * @package	Theme_unidress
 */
register_activation_hook( __FILE__, array( 'Theme_unidress', 'install' ) );

final class Theme_unidress {

	/**
	 * Set up the Theme
	 */
	public function __construct() {
		load_plugin_textdomain( 'unidress', false, dirname( plugin_basename(__FILE__) ) . '/lang' );
		require_once('admin/acf-fields.php');
		add_action( 'init', array( $this, 'theme_unidress_setup' ), -1 );
		add_action( 'init', array( $this, 'theme_unidress_meta_box' ), 10 );
		require_once('ajax.php');
		require_once('admin/functions.php');
		require_once('front-end/functions.php');
		require_once('admin/import_excel.php');
	}

	/**
	 * Setup all the things
	 */
	public function theme_unidress_setup() {

		remove_action( 'storefront_homepage',  'storefront_homepage_header',10);
		remove_action( 'storefront_homepage',  'storefront_page_content', 20);

		add_action( 'wp_enqueue_scripts',       array( $this, 'theme_unidress_css' ), 999 );
		add_action( 'wp_enqueue_scripts',       array( $this, 'theme_unidress_js' ) );
		add_action( 'plugins_loaded',           array( $this, 'theme_unidress_language' ), 11 );
		add_filter( 'template_include',         array( $this, 'theme_unidress_template' ), 11 );
		add_filter( 'wc_get_template',          array( $this, 'theme_unidress_wc_get_template' ), 11, 5 );
        add_filter( 'wc_get_template_part',     array( $this, 'override_woocommerce_template_part'), 10, 3 );
		require_once( 'admin/class/users-report-page.php' );
	}
	/**
	 * Setup language
	 */
	public function theme_unidress_language() {
	}
	/**
	 * Install all the things
	 */
	static function install() {

		// make dir if does not exist
		$dir = wp_get_upload_dir();
		if( !file_exists($dir['basedir'] . '/temp/') ) {
			mkdir($dir['basedir'] . '/temp/', 0775, true);
		}

		$product_option['embroidery']['name']   = 'Embroidery type';
		$product_option['embroidery']['slug']   = 'und_embroidery';
		$product_option['embroidery']['type']   = 'select';

		$product_option['colors']['name']       = 'Colors in logo';
		$product_option['colors']['slug']       = 'und_colors';
		$product_option['colors']['type']       = 'select';

		$product_option['location']['name']     = 'Print location';
		$product_option['location']['slug']     = 'und_location';
		$product_option['location']['type']     = 'select';

		global $wpdb;
		$post = $wpdb->get_results( "SELECT post_name FROM $wpdb->posts WHERE post_type='product_options'", 'OBJECT_K');

		foreach ( $product_option as $args ) {

			if (is_array($post) && array_key_exists($args['slug'], $post))
				continue;

			$post_content = array(
				'attribute_label'   => $args['name'],
				'attribute_name'    => $args['slug'],
				'attribute_type'    => $args['type'],
				'attribute_public'  => 0,
			);

			$post_data = array(
				'post_title'    => $args['name'],
				'post_content'  => serialize($post_content),
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type'     => 'product_options',
				'post_name'     => $args['slug'],
			);

			wp_insert_post( wp_slash($post_data), false );

		}

    }
	/**
	 * Setup all meta-boxes
	 */
	public function theme_unidress_meta_box() {
		require_once( 'admin/meta-boxes/meta-box-shipping.php' );
		require_once( 'admin/meta-boxes/meta-box-assign-product.php' );
	}

    public function override_woocommerce_template_part( $template, $slug, $name ) {
        // UNCOMMENT FOR @DEBUGGING
        // echo '<pre>';
        // echo 'template: ' . $template . '<br/>';
        // echo 'slug: ' . $slug . '<br/>';
        // echo 'name: ' . $name . '<br/>';
        // echo '</pre>';
        //E.g. /wp-content/plugins/my-plugin/woocommerce/
        $template_directory = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/front-end/templates/woocommerce/';
        if ( $name ) {
            $path = $template_directory . "{$slug}-{$name}.php";
        } else {
            $path = $template_directory . "{$slug}.php";
        }

        return file_exists( $path ) ? $path : $template;
    }

	/**
	 * Enqueue the CSS
	 *
	 * @return void
	 */
	public function theme_unidress_css() {
        wp_enqueue_style( 'main-css', plugins_url( '/front-end/css/main.css', __FILE__ ) );

    }

	/**
	 * Enqueue the Javascript
	 *
	 * @return void
	 */
	public function theme_unidress_js() {
		wp_enqueue_script( 'common-js', plugins_url( '/unidress/front-end/js/common.js' ), array( 'jquery' ) );
	}

	/**
	 * Look in this plugin for template files first.
	 * This works for the top level templates (IE single.php, page.php etc). However, it doesn't work for
	 * template parts yet (content.php, header.php etc).
	 *
	 * Relevant trac ticket; https://core.trac.wordpress.org/ticket/13239
	 *
	 * @param  string $template template string.
	 * @return string $template new template string.
	 */
	public function theme_unidress_template( $template ) {
		if ( file_exists( untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/front-end/templates/' . basename( $template ) ) ) {
			$template = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/front-end/templates/' . basename( $template );
		}

		return $template;
	}

	/**
	 * Look in this plugin for WooCommerce template overrides.
	 *
	 * For example, if you want to override woocommerce/templates/cart/cart.php, you
	 * can place the modified template in <plugindir>/custom/templates/woocommerce/cart/cart.php
	 *
	 * @param string $located is the currently located template, if any was found so far.
	 * @param string $template_name is the name of the template (ex: cart/cart.php).
	 * @return string $located is the newly located template if one was found, otherwise
	 *                         it is the previously found template.
	 */
	public function theme_unidress_wc_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		$plugin_template_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/front-end/templates/woocommerce/' . $template_name;

		if ( file_exists( $plugin_template_path ) ) {
			$located = $plugin_template_path;
		}

		return $located;
	}
} // End Class

/**
 * The 'main' function
 *
 * @return void
 */
function theme_unidress_main() {
	new Theme_unidress();
}

/**
 * Initialise the plugin
 */
add_action( 'plugins_loaded', 'theme_unidress_main' );
