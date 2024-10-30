<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

delete_option( 'aodroost_default_out_of_stock_free' );
delete_option( 'aodroost_default_in_stock_free' );
delete_option( 'aodroost_default_low_stock_free' );