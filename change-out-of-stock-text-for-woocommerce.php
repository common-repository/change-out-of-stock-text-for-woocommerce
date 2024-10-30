<?php

/*
Plugin Name: Change Out of Stock Text for Woocommerce
Plugin URI:   
Description: Change the text that describes product availability in your Woocommerce store.
Version:           1.0.0
Requires at least: 5.0
Requires PHP:      7.0
Author:            Joseph Parry
Author URI:        https://www.artofdata.com/wp-plugins/authors/
WC tested up to:   4.2
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:       change-out-of-stock-text-for-woocommerce
Domain Path:       /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( class_exists( 'Aodroost_change_Out_Of_Stock' ) ) {	

	$aodroost_change_out_of_stock = new Aodroost_change_Out_Of_Stock();
	$aodroost_change_out_of_stock->register();
	//initialises the class and runs the register function 

}

register_activation_hook( __FILE__, 'aodroost_stock_text_activation' );
//activation hook

register_deactivation_hook( __FILE__, 'aodroost_stock_text_deactivation' );
//deactivation hook



function aodroost_stock_text_activation(){
	
	if ( ! current_user_can( 'activate_plugins' ) ) {
        return;
    }



	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {	

		echo '<p>' . __( 'You must activate/install woocommerce!', 'change-out-of-stock-text-for-woocommerce' ) . '</p>';
    	exit;	

	}
	//only activates when woocommerce is active


	if ( is_plugin_active( 'change-out-of-stock-text-pro-for-woocommerce/change-out-of-stock-text-pro-for-woocommerce.php' ) ) {	

		echo '<p>' . __( 'You already have the pro version active!', 'change-out-of-stock-text-for-woocommerce' ) . '</p>';
    	exit;	

	}
	//if pro is active, plugin does not activate

	flush_rewrite_rules();

}




function aodroost_stock_text_deactivation(){

	if ( ! current_user_can( 'activate_plugins' ) ) {
	        return;
    }
    
	flush_rewrite_rules();

}









class Aodroost_change_Out_Of_Stock {

	function register() {
		add_filter( 'woocommerce_get_settings_products' , array( $this, 'aodroost_get_settings' ) , 10, 2 );
		add_filter( 'woocommerce_get_availability', array( $this, 'aodroost_custom_get_availability' ), 1, 2);
		add_filter( 'plugin_action_links', array( $this, 'aodroost_plugin_links' ), 10, 2 );

	}



	public function aodroost_plugin_links( $links, $file ) {

		if ( $file != plugin_basename( __FILE__ ) ){
			return $links;
		}

		return array_merge(
			array(
				'settings'      => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) . '">' . __( 'Settings', 'change-out-of-stock-text-for-woocommerce' ) . '</a>',

				//'pro'           => '<a href="" style="color:#60a559;" target="_blank" title="' . __( 'Try Change Out of Stock Text Pro for Woocommerce ', 'change-out-of-stock-text-for-woocommerce' ) . '">' . __( 'Try Pro Version', 'change-out-of-stock-text-for-woocommerce' ) . '</a>'
			),
			$links
		);

	}



	function aodroost_get_settings( $settings, $current_section ) {

        if ( $current_section == 'inventory' ) {

			$settings_slider = array();
			$settings_slider[] = array( 
				'name' => __( 'Text Changes', 'change-out-of-stock-text-for-woocommerce' ),
				'type' => 'title', 
				'desc' => __( 'Change the text showing the availability of a product to a customer. If you type $stock$ it will be replaced by the current stock of the relevent product (if you use this be careful that your products have a stock level, otherwise it will not take a value).', 'change-out-of-stock-text-for-woocommerce' ),
				);



			$settings_slider[] = array(
				'name'     => __( 'Out of stock default text', 'change-out-of-stock-text-for-woocommerce' ),
				'desc_tip' => __( 'When a product is out of stock, the availability information is changed to what you type here. Leave blank to keep the Woocommerce default text.', 'change-out-of-stock-text-for-woocommerce' ),
				'id'       => 'aodroost_default_out_of_stock_free',
				'type'     => 'textarea',
			);


			$settings_slider[] = array(
				'name'     => __( 'In stock default text', 'change-out-of-stock-text-for-woocommerce' ),
				'desc_tip' => __( 'When a product is in stock, the availability information is changed to what you type here. Leave blank to keep the Woocommerce default text.', 'change-out-of-stock-text-for-woocommerce' ),
				'id'       => 'aodroost_default_in_stock_free',
				'type'     => 'textarea',
			);


			$settings_slider[] = array(
				'name'     => __( 'Low stock default text', 'change-out-of-stock-text-for-woocommerce' ),
				'desc_tip' => __( 'When a product is low in stock, the availability information is changed to what you type here. Leave blank to use your "in stock" text or the woocommerce default if this is also blank.', 'change-out-of-stock-text-for-woocommerce' ),
				'id'       => 'aodroost_default_low_stock_free',
				'type'     => 'textarea',
			);






			$settings_slider[] = array( 'type' => 'sectionend', 'id' => 'aodroost_free_text' );
			return array_merge( $settings, $settings_slider );

			/**
			 * If not, return the standard settings
			 **/
		} else {
			return $settings;
		}
	}





	function aodroost_custom_get_availability( $availability, $_product ) {

		//values used a lot in this function
		$aodroost_fixed_stock = $_product->get_stock_quantity();
		$aodroost_stock = esc_html(strval($aodroost_fixed_stock));
		$aodroost_low_stock = $_product->get_low_stock_amount();

		if ( empty( $aodroost_low_stock ) ){
			$aodroost_low_stock = get_option( 'woocommerce_notify_low_stock_amount' );
		}



		///////////////////////////////////
		//out of stock
		if ( ! $_product->is_in_stock() ) { 

			if ( ! empty( get_option( 'aodroost_default_out_of_stock_free' ) ) ){

				$aodroost_out_of_stock = get_option( 'aodroost_default_out_of_stock_free' );
				$availability['availability'] = esc_html( str_replace ( '$stock$', $aodroost_stock, $aodroost_out_of_stock ) );

			}


		///////////////////////////////////
		//low on stock
		} elseif (  ( ! empty( $aodroost_fixed_stock ) ) && ( ! empty( $aodroost_low_stock ) ) && ( ! $_product->backorders_allowed() ) && ( $aodroost_fixed_stock <= $aodroost_low_stock ) && (! empty( get_option( 'aodroost_default_low_stock_free' ) )) ){ 


			if (  ! empty( get_option( 'aodroost_default_low_stock_free' ) )){
	    		
		    	$availability['availability'] =  esc_html( str_replace ( '$stock$', $aodroost_stock, get_option( 'aodroost_default_low_stock_free' ) ) );

		    }



		///////////////////////////////////
		//in stock
	    } elseif ( $_product->is_in_stock() ) {

			if ( ! empty( get_option( 'aodroost_default_in_stock_free' ) ) ){

				$aodroost_in_stock = get_option( 'aodroost_default_in_stock_free' );
				$availability['availability'] = esc_html( str_replace ( '$stock$', $aodroost_stock, $aodroost_in_stock ) );

			}

	    }

		return $availability;
	}



}