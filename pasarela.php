<?php
/**
 * @package Pasarela
 */
/*
Plugin Name: Formulario de recibos
Description: formulario para recibir la informacin de pagos
Author: jsstoni
*/

define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

function pasarela_plugin_activation()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "posts";
	$resultados = $wpdb->query( "SELECT ID FROM {$table_name} WHERE post_name = 'send-order'" );
	if ($resultados < 1) {
		$post_id = wp_insert_post(
			array(
				'comment_status'	=>	'closed',
				'ping_status'		=>	'closed',
				'post_author'		=>	1,
				'post_name'			=>	'send-order',
				'post_title'		=>	'Send order',
				'post_content'	 	=>	'',
				'post_status'		=>	'publish',
				'post_type'			=>	'page'
			)
		);
	}
	$table_name = $wpdb->prefix . "ordenes";
	$sql = "CREATE TABLE {$table_name} ( `ID` INT NOT NULL AUTO_INCREMENT , `order` VARCHAR(24) NOT NULL , `names` TEXT NOT NULL , `surnames` TEXT NOT NULL , `dni` VARCHAR(24) NOT NULL , `amount` TEXT NOT NULL , `pay` INT NOT NULL , `reference` TEXT NOT NULL , `fileupload` TEXT NOT NULL , PRIMARY KEY (`ID`)) ENGINE = InnoDB;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook(__FILE__, 'pasarela_plugin_activation');

function custom_menu_pspago()
{
	 add_menu_page( 'Payment orders', 'Payment orders', 'manage_options', 'pasarela', 'create_admin_menu_function', 'dashicons-megaphone' );
}
add_action( 'admin_menu', 'custom_menu_pspago' );

function create_admin_menu_function()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "ordenes";
	$result = $wpdb->get_results("SELECT * FROM {$table_name}");
	include ('view/checkout.php');
}

function enqueue_my_scripts($hook)
{
	if ($hook == 'toplevel_page_pasarela') {
		wp_enqueue_script( 'datatable_js', plugins_url( 'js/jquery.dataTables.min.js', __FILE__ ), array('jquery'), '0.1' );
		wp_enqueue_script( 'custom_js', plugins_url( 'js/custom.js', __FILE__ ), array('jquery'), '0.1' );
		wp_enqueue_style( 'bootstrap_css', plugins_url( 'css/bootstrap.min.css', __FILE__ ), array(), '0.1' );
		wp_enqueue_style( 'datatable_css', plugins_url( 'css/jquery.dataTables.min.css', __FILE__ ), array(), '0.1' );
		wp_enqueue_style( 'custom_css', plugins_url( 'css/default.css', __FILE__ ), array(), '0.1' );
	}
}
add_action('admin_enqueue_scripts', 'enqueue_my_scripts');

function enqueue_page_order()
{
	if (is_page( 'send-order' ) || is_cart()) {
		wp_enqueue_script( 'ajax_js', plugins_url( 'js/ajax.js', __FILE__ ), array('jquery'), '0.1' );
		wp_enqueue_style( 'form_css', plugins_url( 'css/form.css', __FILE__ ), array(), '0.1' );
	}
}
add_action('wp_enqueue_scripts', 'enqueue_page_order');

function send_orden_page_template( $page_template )
{
	if ( is_page( 'send-order' ) ) {
		$page_template = MY_PLUGIN_PATH . 'view/send.php';
	}
	return $page_template;
}
add_filter( 'page_template', 'send_orden_page_template' );

function ajax_send_order()
{
	global $wpdb;
	if (!function_exists('wp_handle_upload')) {
		require_once(ABSPATH . 'wp-admin/includes/file.php');
	}

	$uploadedfile = $_FILES['upload'];
	$upload_overrides = array('test_form' => false);

	$imageFileType = pathinfo($uploadedfile['name'], PATHINFO_EXTENSION);
	$allow = array('jpg', 'png', 'pdf');
	
	if (in_array($imageFileType, $allow) && is_user_logged_in()) {
		$movefile = wp_handle_upload($uploadedfile, $upload_overrides);
		if ($movefile && !isset($movefile['error'])) {
			$success_upload = $movefile['url'];
			$table_name = $wpdb->prefix . "ordenes";
			$wpdb->insert($table_name, array(
				'order' => sanitize_text_field($_POST['order']),
				'names' => sanitize_text_field($_POST['names']),
				'surnames' => sanitize_text_field($_POST['surnames']),
				'dni' => sanitize_text_field($_POST['dni']),
				'amount' => sanitize_text_field($_POST['amount']),
				'pay' => sanitize_text_field($_POST['pay']),
				'reference' => sanitize_text_field($_POST['reference']),
				'fileupload' => $success_upload
			));
			echo "success";
		}else {
			echo 'error';
		}
	}else {
		echo 'error';
	}
	die();
}
add_action('wp_ajax_send_order', 'ajax_send_order');
add_action('wp_ajax_nopriv_send_order', 'ajax_send_order');

function change_checkout_url($url)
{
	$url = admin_url('admin-ajax.php');
	return $url;
}
add_filter( 'woocommerce_get_checkout_url', 'change_checkout_url', 30 );

function url_order_pay( $actions, $order ) {
	$total = $order->get_total();
	$id = $order->get_id();
	if( isset( $actions['pay']['url'] ) ) {
		$actions['pay']['url'] = site_url().'/send-order?id='.$id.'&total='.$total;
	}
	return $actions;
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'url_order_pay', 10, 2 );

function woocommerce_button_proceed_to_checkout()
{
	$checkout_url = WC()->cart->get_checkout_url();
?>
	<a href="<?php echo esc_url( wc_get_checkout_url() );?>" class="checkout-button button alt wc-forward" id="proceed_order"><?php esc_html_e( 'Proceed order', 'woocommerce' ); ?></a>
	<div id="proceed-res"></div>
<?php
}

function ajax_proceed_order()
{
	global $woocommerce;
	if (count(WC()->cart->get_cart()) > 0 && is_user_logged_in()) {
		$order = wc_create_order(array('customer_id' => get_current_user_id()));
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$id = $cart_item['data']->get_id();
			$quantity = $cart_item['quantity'];
			$order->add_product( get_product( $id ), $quantity );
		}
		$order->calculate_totals();
		$order->update_status("Completed", 'Imported order', TRUE);
		$woocommerce->cart->empty_cart();
		echo $order->id;
	}else {
		echo "error";
	}
	die();
}
add_action('wp_ajax_proceed_order', 'ajax_proceed_order');
add_action('wp_ajax_nopriv_proceed_order', 'ajax_proceed_order');

function ajax_change_status_order()
{
	$order_id = sanitize_text_field($_POST['id']);
	$order = new WC_Order($order_id);
	$order->update_status('Completed', 'order_note');
	echo "success";
}
add_action('wp_ajax_status_order', 'ajax_change_status_order');
add_action('wp_ajax_nopriv_status_order', 'ajax_change_status_order');
?>