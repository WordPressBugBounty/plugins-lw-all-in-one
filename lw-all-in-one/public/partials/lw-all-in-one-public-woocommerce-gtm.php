<?php
/**
 * WooCommerce event tracking.
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/public/partials
 */

$lwaio_product_counter   = 0;
$lwaio_last_widget_title = 'Sidebar Products';

$GLOBALS['lwaio_woocommerce_purchase_data_pushed'] = false;


/**
 * Given a WooCommerce product ID, this function will return the first assigned category of the product.
 * Currently, it does not take into account the "primary category" option of various SEO plugins.
 *
 * @param int     $product_id A WooCommerce product ID whose first assigned category has to be returned.
 * @return string The first category name of the product. Incluldes the name of parent categories if the $fullpath parameter is set to true.
 */
function lwaio_get_product_category( $product_id ) {
	$product_cat = '';

	$_product_cats = wp_get_post_terms(
		$product_id,
		'product_cat',
		array(
			'orderby' => 'parent',
			'order'   => 'ASC',
		)
	);

	if ( ( is_array( $_product_cats ) ) && ( count( $_product_cats ) > 0 ) ) {
		$first_product_cat = array_pop( $_product_cats );
		$product_cat = $first_product_cat->name;
	}

	return $product_cat;
}

/**
 * Given a WooCommerce product ID, this function returns the assigned value of a custom taxonomy like the brand name.
 *
 * @see https://developer.wordpress.org/reference/functions/wp_get_post_terms/
 *
 * @param int    $product_id A WooCommerce product ID whose taxonomy assosiation needs to be queried.
 * @param string $taxonomy The taxonomy slug for which to retrieve terms.
 * @return string Returns the first assigned taxonomy value to the given WooCommerce product ID.
 */
function lwaio_woocommerce_getproductterm( $product_id, $taxonomy ) {
	$lwaio_product_terms = wp_get_post_terms(
		$product_id,
		$taxonomy,
		array(
			'orderby' => 'parent',
			'order'   => 'ASC',
		)
	);

	if ( is_array( $lwaio_product_terms ) && ( count( $lwaio_product_terms ) > 0 ) ) {
		return $lwaio_product_terms[0]->name;
	}

	return '';
}

/**
 * Given a WP_Product instane, this function returns an array of product attributes in the format of
 * Google Analytics enhanced ecommerce product data.
 *
 * @see https://developers.google.com/analytics/devguides/collection/ua/gtm/enhanced-ecommerce
 *
 * @param WP_Product $product An instance of WP_Product that needs to be transformed into an enhanced ecommerce product object.
 * @param array      $additional_product_attributes Any key-value pair that needs to be added into the enhanced ecommerce product object.
 * @return array The enhanced ecommerce product object of the WooCommerce product.
 */
function lwaio_process_product( $product, $additional_product_attributes ) {
	if ( ! $product ) {
		return false;
	}

	if ( ! ( $product instanceof WC_Product ) ) {
		return false;
	}

	$product_id     = $product->get_id();
	$product_type   = $product->get_type();
	$remarketing_id = $product_id;
	$product_sku    = $product->get_sku();

	if ( 'variation' === $product_type ) {
		$parent_product_id = $product->get_parent_id();
		$product_cat       = lwaio_get_product_category( $parent_product_id);
	} else {
		$product_cat = lwaio_get_product_category( $product_id);
	}

	$_temp_productdata = array(
		'id'          => $remarketing_id,
		'internal_id' => $product_id,
		'name'        => $product->get_title(),
		'sku'         => $product_sku ? $product_sku : $product_id,
		'category'    => $product_cat,
		'price'       => round( (float) wc_get_price_to_display( $product ), 2 ),
		'stocklevel'  => $product->get_stock_quantity(),
	);


	if ( 'variation' === $product_type ) {
		$_temp_productdata['variant'] = implode( ',', $product->get_variation_attributes() );
	}

	return array_merge( $_temp_productdata, $additional_product_attributes );
}

/**
 * Takes a GA3 style enhanced ecommerce product object and transforms it into a GA4 product object.
 *
 * @param array $productdata WooCommerce product data in GA3 enhanced ecommerce product object format.
 * @return array WooCommerce product data in GA4 enhanced ecommerce product object format.
 */
function lwaio_map_ec_to_ga4( $productdata ) {

	if ( ! is_array( $productdata ) ) {
		return;
	}

	$category_path  = array_key_exists( 'category', $productdata ) ? $productdata['category'] : '';
	$category_parts = explode( '/', $category_path );

	// Default, required parameters.
	$ga4_product = array(
		'item_id'    => array_key_exists( 'id', $productdata ) ? $productdata['id'] : '',
		'item_name'  => array_key_exists( 'name', $productdata ) ? $productdata['name'] : '',
		'item_brand' => array_key_exists( 'brand', $productdata ) ? $productdata['brand'] : '',
		'price'      => array_key_exists( 'price', $productdata ) ? $productdata['price'] : '',
	);

	// Category, also handle category path.
	if ( 1 === count( $category_parts ) ) {
		$ga4_product['item_category'] = $category_parts[0];
	} elseif ( count( $category_parts ) > 1 ) {
		$ga4_product['item_category'] = $category_parts[0];

		$num_category_parts = min( 5, count( $category_parts ) );
		for ( $i = 1; $i < $num_category_parts; $i++ ) {
			$ga4_product[ 'item_category' . (string) ( $i + 1 ) ] = $category_parts[ $i ];
		}
	}

	// Optional parameters which should not be included in the array if not set.
	if ( array_key_exists( 'variant', $productdata ) ) {
		$ga4_product['item_variant'] = $productdata['variant'];
	}
	if ( array_key_exists( 'listname', $productdata ) ) {
		$ga4_product['item_list_name'] = $productdata['listname'];
	}
	if ( array_key_exists( 'listposition', $productdata ) ) {
		$ga4_product['index'] = $productdata['listposition'];
	}
	if ( array_key_exists( 'quantity', $productdata ) ) {
		$ga4_product['quantity'] = $productdata['quantity'];
	}
	if ( array_key_exists( 'coupon', $productdata ) ) {
		$ga4_product['coupon'] = $productdata['coupon'];
	}

	return $ga4_product;
}

/**
 * Takes a WooCommerce order and returns an associative array that can be used
 * for enhanced ecommerce tracking and Google Ads dynamic remarketing (legacy version).
 *
 * @param WC_Order $order The order that needs to be processed.
 * @return array An associative array with the keys:
 *               products - enhanced ecommerce (GA3) products
 *               sumprice - total order value based on item data
 *               product_ids - array of product IDs to be used in ecomm_prodid.
 */
function lwaio_process_order_items( $order ) {

	$return_data = array(
		'products'    => array(),
		'sumprice'    => 0,
		'product_ids' => array(),
	);

	if ( ! $order ) {
		return $return_data;
	}

	if ( ! ( $order instanceof WC_Order ) ) {
		return $return_data;
	}

	$order_items = $order->get_items();

	if ( $order_items ) {
		foreach ( $order_items as $item ) {

			$product       = $item->get_product();
			$inc_tax       = ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) );
			$product_price = round( (float) $order->get_item_total( $item, $inc_tax ), 2 );

			$ec_product_array = lwaio_process_product(
				$product,
				array(
					'quantity' => $item->get_quantity(),
					'price'    => $product_price,
				)
			);

			if ( $ec_product_array ) {
				$return_data['products'][]    = $ec_product_array;
				$return_data['sumprice']     += $product_price * $ec_product_array['quantity'];
				$return_data['product_ids'][] = $ec_product_array['id'];
			}
		}
	}

	return $return_data;
}

/**
 * Takes a WooCommerce order and order items and generates the standard/classic and
 * enhanced ecommerce version of the purchase data layer codes for Universal Analytics.
 *
 * @param WC_Order $order The WooCommerce order that needs to be transformed into an enhanced ecommerce data layer.
 * @param array    $order_items The array returned by lwaio_process_order_items(). It not set, then function will call lwaio_process_order_items().
 * @return array The data layer content as an associative array that can be passed to json_encode() to product a JavaScript object used by GTM.
 */
function lwaio_get_purchase_datalayer( $order, $order_items ) {

	$data_layer = array();

	if ( $order instanceof WC_Order ) {

		/**
		 * Variable for Google Smart Shopping campaign new customer reporting.
		 *
		 * @see https://support.google.com/google-ads/answer/9917012?hl=en-AU#zippy=%2Cinstall-with-google-tag-manager
		 */
		$data_layer['new_customer'] = \Automattic\WooCommerce\Admin\API\Reports\Orders\Stats\DataStore::is_returning_customer($order) === false;

		$order_revenue = (float) $order->get_total();
		$order_currency = $order->get_currency();


		$data_layer['event']     = 'lwaio.orderCompleted';
		$data_layer['ecommerce'] = array(
			'currencyCode' => $order_currency,
			'purchase'     => array(
				'actionField' => array(
					'id'          => $order->get_order_number(),
					'affiliation' => '',
					'revenue'     => $order_revenue,
					'tax'         => (float) $order->get_total_tax(),
					'shipping'    => (float) ( $order->get_shipping_total() ),
					'coupon'      => implode( ', ', ( version_compare( WC()->version, '3.7', '>=' ) ? $order->get_coupon_codes() : $order->get_used_coupons() ) ),
				),
			),
		);

		if ( isset( $order_items ) ) {
			$_order_items = $order_items;
		} else {
			$_order_items = lwaio_process_order_items( $order );
		}

		$data_layer['ecommerce']['purchase']['products'] = $_order_items['products'];
	}

	return $data_layer;
}

/**
 * Function executed when the main LWAIO data layer generation happens.
 * Hooks into lwaio_compile_datalayer.
 *
 * @param array $data_layer An array of key-value pairs that will be converted into a JavaScript object on the frontend for GTM.
 * @return array Extended data layer content with WooCommerce data added.
 */
function lwaio_woocommerce_datalayer( $data_layer ) {

	if ( array_key_exists( 'HTTP_X_REQUESTED_WITH', $_SERVER ) ) {
		return $data_layer;
	}

	$woo = WC();

	if ( is_product() ) {
		$postid  = get_the_ID();
		$product = wc_get_product( $postid );

		$ec_product_array = lwaio_process_product(
			$product,
			array()
		);

		$data_layer['productRatingCounts']  = $product->get_rating_counts();
		$data_layer['productAverageRating'] = (float) $product->get_average_rating();
		$data_layer['productReviewCount']   = (int) $product->get_review_count();
		$data_layer['productType']          = $product->get_type();

		switch ( $data_layer['productType'] ) {
			case 'variable':
				$data_layer['productIsVariable'] = 1;

				$data_layer['ecomm_prodid']     = $ec_product_array['id'];
				$data_layer['ecomm_pagetype']   = 'product';
				$data_layer['ecomm_totalvalue'] = $ec_product_array['price'];

				break;

			case 'grouped':
				$data_layer['productIsVariable'] = 0;

				break;

			default:
				$data_layer['productIsVariable'] = 0;

				$currency_code = get_woocommerce_currency();

				$data_layer['event']     = 'lwaio.changeDetailView';
				$data_layer['ecommerce'] = array(
					'currencyCode' => $currency_code,
					'detail'       => array(
						'products' => array(
							$ec_product_array,
						),
					),
				);
		}
	} elseif ( is_cart() ) {
		$lwaio_cart_products             = array();
		$lwaio_cart_products_remarketing = array();

		foreach ( $woo->cart->get_cart() as $cart_item_id => $cart_item_data ) {
			$product = apply_filters( 'woocommerce_cart_item_product', $cart_item_data['data'], $cart_item_data, $cart_item_id );

			$ec_product_array = lwaio_process_product(
				$product,
				array(
					'quantity' => $cart_item_data['quantity'],
				)
			);

			$lwaio_cart_products[]             = $ec_product_array;
			$lwaio_cart_products_remarketing[] = $ec_product_array['id'];
		}

		// add only ga4 products to populate view_cart event.
		$data_layer['ecommerce'] = array(
			'cart' => $lwaio_cart_products,
		);

	} elseif ( is_order_received_page() ) {
		$do_not_flag_tracked_order = false;

		// Supressing 'Processing form data without nonce verification.' message as there is no nonce accesible in this case.
		$order_id = filter_var( wp_unslash( isset( $_GET['order'] ) ? $_GET['order'] : '' ), FILTER_VALIDATE_INT ); // phpcs:ignore
		if ( ! $order_id & isset( $GLOBALS['wp']->query_vars['order-received'] ) ) {
			$order_id = $GLOBALS['wp']->query_vars['order-received'];
		}
		$order_id = absint( $order_id );

		$order_id_filtered = apply_filters( 'woocommerce_thankyou_order_id', $order_id );
		if ( '' !== $order_id_filtered ) {
			$order_id = $order_id_filtered;
		}

		// Supressing 'Processing form data without nonce verification.' message as there is no nonce accesible in this case.
		$order_key = isset( $_GET['key'] ) ? wc_clean( sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) : ''; // phpcs:ignore
		$order_key = apply_filters( 'woocommerce_thankyou_order_key', $order_key );

		if ( $order_id > 0 ) {
			$order = wc_get_order( $order_id );

			if ( $order instanceof WC_Order ) {
				$this_order_key = $order->get_order_key();

				if ( $this_order_key !== $order_key ) {
					unset( $order );
				}
			} else {
				unset( $order );
			}
		}

		/*
		From this point if for any reason purchase data is not pushed
		that is because for a specific reason.
		In any other case woocommerce_thankyou hook will be the fallback if
		is_order_received_page does not work.
		*/
		$GLOBALS['lwaio_woocommerce_purchase_data_pushed'] = true;

		$order_items = null;

		if ( isset( $order ) && ( 1 === (int) $order->get_meta( '_ga_tracked', true ) ) && ! $do_not_flag_tracked_order ) {
			unset( $order );
		}

		if ( isset( $_COOKIE['lwaio_orderid_tracked'] ) ) {
			$tracked_order_id = filter_var( wp_unslash( $_COOKIE['lwaio_orderid_tracked'] ), FILTER_VALIDATE_INT );

			if ( $tracked_order_id && ( $tracked_order_id === $order_id ) && ! $do_not_flag_tracked_order ) {
				unset( $order );
			}
		}

		if ( isset( $order ) && ( 'failed' === $order->get_status() ) ) {
			// do not track order where payment failed.
			unset( $order );
		}

		if ( isset( $order ) ) {
			$data_layer = array_merge( $data_layer, lwaio_get_purchase_datalayer( $order, $order_items ) );

			if ( ! $do_not_flag_tracked_order ) {
				$order->update_meta_data( '_ga_tracked', 1 );
				$order->save();
			}
		}
	} elseif ( is_checkout() ) {

		$lwaio_checkout_products             = array();
		$lwaio_checkout_products_remarketing = array();
		$lwaio_totalvalue                    = 0;

		foreach ( $woo->cart->get_cart() as $cart_item_id => $cart_item_data ) {
			$product = apply_filters( 'woocommerce_cart_item_product', $cart_item_data['data'], $cart_item_data, $cart_item_id );

			$ec_product_array = lwaio_process_product(
				$product,
				array(
					'quantity' => $cart_item_data['quantity'],
				)
			);

			$lwaio_checkout_products[] = $ec_product_array;

			$lwaio_checkout_products_remarketing[] = $ec_product_array['id'];
			$lwaio_totalvalue                     += $ec_product_array['quantity'] * $ec_product_array['price'];
		} // end foreach cart item

		$currency_code = get_woocommerce_currency();

		$ga4_products = array();
		$sum_value    = 0;

		foreach ( $lwaio_checkout_products as $oneproduct ) {
			$ga4_products[] = lwaio_map_ec_to_ga4( $oneproduct );
			$sum_value     += $oneproduct['price'] * $oneproduct['quantity'];
		}

		$data_layer['event']     = 'lwaio.checkoutStep';
		$data_layer['ecommerce'] = array(
			'currencyCode' => $currency_code,
			'checkout'     => array(
				'actionField' => array(
					'step' => 1,
				),
				'products'    => $lwaio_checkout_products,
			),
		);

		wc_enqueue_js(
			'
			window.lwaio_checkout_products     = ' . wp_json_encode( $lwaio_checkout_products ) . ';
			window.lwaio_checkout_products_ga4 = ' . wp_json_encode( $ga4_products ) . ';
			window.lwaio_checkout_value        = ' . (float) $sum_value . ';
			window.lwaio_checkout_step_offset  = 1;'
		);
	}

	if ( function_exists( 'WC' ) && WC()->session ) {
		$cart_readded_hash = WC()->session->get( 'lwaio_product_readded_to_cart' );
		if ( isset( $cart_readded_hash ) ) {
			$cart_item = $woo->cart->get_cart_item( $cart_readded_hash );
			if ( ! empty( $cart_item ) ) {
				$product = $cart_item['data'];

				$ec_product_array = lwaio_process_product(
					$product,
					array(
						'quantity' => $cart_item['quantity'],
					)
				);

				$currency_code = get_woocommerce_currency();

				$data_layer['event']     = 'lwaio.addProductToCart';
				$data_layer['ecommerce'] = array(
					'currencyCode' => $currency_code,
					'add'          => array(
						'products' => array(
							$ec_product_array,
						),
					),
				);
			}

			WC()->session->set( 'lwaio_product_readded_to_cart', null );
		}
	}

	return $data_layer;
}

/**
 * Executed during woocommerce_thankyou.
 * This is a fallback function to output purchase data layer on customized order received pages where
 * the is_order_received_page() template tag returns false for some reason.
 *
 * @param int $order_id The ID of the order placed by the user just recently.
 * @return void
 */
function lwaio_woocommerce_thankyou( $order_id ) {
	/*
	If this flag is set to true, it means that the puchase event was fired
	when capturing the is_order_received_page template tag therefore
	no need to handle this here twice
	*/
	if ( $GLOBALS['lwaio_woocommerce_purchase_data_pushed'] ) {
		return;
	}

	if ( $order_id > 0 ) {
		$order = wc_get_order( $order_id );
	}

	$do_not_flag_tracked_order = false;
	if ( isset( $order ) && ( 1 === (int) $order->get_meta( '_ga_tracked', true ) ) && ! $do_not_flag_tracked_order ) {
		unset( $order );
	}

	if ( isset( $_COOKIE['lwaio_orderid_tracked'] ) ) {
		$tracked_order_id = filter_var( wp_unslash( $_COOKIE['lwaio_orderid_tracked'] ), FILTER_VALIDATE_INT );

		if ( $tracked_order_id && ( $tracked_order_id === $order_id ) && ! $do_not_flag_tracked_order ) {
			unset( $order );
		}
	}

	if ( isset( $order ) && ( 'failed' === $order->get_status() ) ) {
		// do not track order where payment failed.
		unset( $order );
	}

	if ( isset( $order ) ) {
		$data_layer = lwaio_get_purchase_datalayer( $order, null );

		echo '
		<script data-cfasync="false" data-pagespeed-no-defer type="text/javascript">
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push(' . wp_json_encode( $data_layer ) . ');
		</script>';

		if ( ! $do_not_flag_tracked_order ) {
			$order->update_meta_data( '_ga_tracked', 1 );
			$order->save();
		}
	}
}

/**
 * Function executed with the woocommerce_after_add_to_cart_button hook.
 *
 * @return void
 */
function lwaio_woocommerce_single_add_to_cart_tracking() {
	global $product;

	$ec_product_array = lwaio_process_product(
		$product,
		array()
	);

	foreach ( $ec_product_array as $ec_product_array_key => $ec_product_array_value ) {
		echo '<input type="hidden" name="lwaio_' . esc_attr( $ec_product_array_key ) . '" value="' . esc_attr( $ec_product_array_value ) . '" />' . "\n";
	}
}

/**
 * Universal Analytics enhanced ecommerce product array with the product that is currently shown in the cart.
 *
 * @var array
 */
$GLOBALS['lwaio_cart_item_proddata'] = '';

/**
 * Executed during woocommerce_cart_item_product for each product in the cart.
 * Stores the Universal Analytics enhanced ecommerce product data into a global variable
 * to be processed when the cart item is rendered.
 *
 * @see https://woocommerce.github.io/code-reference/files/woocommerce-templates-cart-cart.html#source-view.41
 *
 * @param WC_Product $product A WooCommerce product that is shown in the cart.
 * @param string     $cart_item Not used by this hook.
 * @param string     $cart_id Not used by this hook.
 * @return array Enhanced ecommerce product data in an associative array.
 */
function lwaio_woocommerce_cart_item_product_filter( $product, $cart_item = '', $cart_id = '' ) {

	$ec_product_array = lwaio_process_product(
		$product,
		array(
			'productlink' => apply_filters( 'the_permalink', get_permalink(), 0 ),
		)
	);

	$GLOBALS['lwaio_cart_item_proddata'] = $ec_product_array;

	return $product;
}

/**
 * Executed during woocommerce_cart_item_remove_link.
 * Adds additional product data into the remove product link of the cart table to be able to track
 * enhanced ecommerce remove_from_cart action with product data.
 *
 * @global lwaio_cart_item_proddata The previously stored product array in lwaio_woocommerce_cart_item_product_filter.
 *
 * @param string $remove_from_cart_link The HTML code of the remove from cart link element.
 * @return string The updated remove product from cart link with product data added in data attributes.
 */
function lwaio_woocommerce_cart_item_remove_link_filter( $remove_from_cart_link ) {
	if ( ! isset( $GLOBALS['lwaio_cart_item_proddata'] ) ) {
		return $remove_from_cart_link;
	}

	if ( ! is_array( $GLOBALS['lwaio_cart_item_proddata'] ) ) {
		return $remove_from_cart_link;
	}

	if ( ! isset( $GLOBALS['lwaio_cart_item_proddata']['variant'] ) ) {
		$GLOBALS['lwaio_cart_item_proddata']['variant'] = '';
	}

	if ( ! isset( $GLOBALS['lwaio_cart_item_proddata']['brand'] ) ) {
		$GLOBALS['lwaio_cart_item_proddata']['brand'] = '';
	}

	$cartlink_with_data = sprintf(
		'data-lwaio_product_id="%s" data-lwaio_product_name="%s" data-lwaio_product_price="%s" data-lwaio_product_cat="%s" data-lwaio_product_url="%s" data-lwaio_product_variant="%s" data-lwaio_product_stocklevel="%s" data-lwaio_product_brand="%s" href="',
		esc_attr( $GLOBALS['lwaio_cart_item_proddata']['id'] ),
		esc_attr( $GLOBALS['lwaio_cart_item_proddata']['name'] ),
		esc_attr( $GLOBALS['lwaio_cart_item_proddata']['price'] ),
		esc_attr( $GLOBALS['lwaio_cart_item_proddata']['category'] ),
		esc_url( $GLOBALS['lwaio_cart_item_proddata']['productlink'] ),
		esc_attr( $GLOBALS['lwaio_cart_item_proddata']['variant'] ),
		esc_attr( $GLOBALS['lwaio_cart_item_proddata']['stocklevel'] ),
		esc_attr( $GLOBALS['lwaio_cart_item_proddata']['brand'] )
	);

	$GLOBALS['lwaio_cart_item_proddata'] = '';

	return preg_replace( '/' . preg_quote( 'href="', '/' ) . '/', $cartlink_with_data, $remove_from_cart_link, 1 );
}

/**
 * Executed during loop_end.
 * Resets the product impression list name after a specific product list ended rendering.
 *
 * @return void
 */
function lwaio_woocommerce_reset_loop() {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = '';
}

/**
 * Executed during woocommerce_related_products_args.
 * Sets the currently rendered product list impression name to Related Products.
 *
 * @param array $arg Not used by this hook.
 * @return array
 */
function lwaio_woocommerce_add_related_to_loop( $arg ) {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = __( 'Related Products', 'lw_all_in_one' );

	return $arg;
}

/**
 * Executed during woocommerce_cross_sells_columns.
 * Sets the currently rendered product list impression name to Cross-Sell Products.
 *
 * @param array $arg Not used by this hook.
 * @return array
 */
function lwaio_woocommerce_add_cross_sell_to_loop( $arg ) {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = __( 'Cross-Sell Products', 'lw_all_in_one' );

	return $arg;
}

/**
 * Executed during woocommerce_upsells_columns.
 * Sets the currently rendered product list impression name to Upsell Products.
 *
 * @param array $arg Not used by this hook.
 * @return array
 */
function lwaio_woocommerce_add_upsells_to_loop( $arg ) {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = __( 'Upsell Products', 'lw_all_in_one' );

	return $arg;
}

/**
 * Executed during woocommerce_before_template_part.
 * Starts output buffering in order to be able to add product data attributes to the link element
 * of a product list (classic) widget.
 *
 * @param string $template_name The template part that is being rendered.
 * @return void
 */
function lwaio_woocommerce_before_template_part( $template_name ) {
	ob_start();
}

/**
 * Executed during woocommerce_after_template_part.
 * Stops output buffering and gets the generated content since woocommerce_before_template_part.
 * Adds data attributes into the product link to be able to track product list impression and
 * click actions with Google Tag Manager.
 *
 * @param string $template_name The template part that is being rendered. This functions looks for content-widget-product.php.
 * @return void
 */
function lwaio_woocommerce_after_template_part( $template_name ) {
	global $product, $lwaio_product_counter, $lwaio_last_widget_title;

	$productitem = ob_get_contents();
	ob_end_clean();

	if ( 'content-widget-product.php' === $template_name ) {
		$ec_product_array = lwaio_process_product(
			$product,
			array(
				'productlink'  => apply_filters( 'the_permalink', get_permalink(), 0 ),
				'listname'     => $lwaio_last_widget_title,
				'listposition' => $lwaio_product_counter,
			)
		);

		if ( ! isset( $ec_product_array['brand'] ) ) {
			$ec_product_array['brand'] = '';
		}

		$productlink_with_data = sprintf(
			'data-lwaio_product_id="%s" data-lwaio_product_internal_id="%s" data-lwaio_product_name="%s" data-lwaio_product_price="%s" data-lwaio_product_cat="%s" data-lwaio_product_url="%s" data-lwaio_productlist_name="%s" data-lwaio_product_listposition="%s" data-lwaio_product_stocklevel="%s" data-lwaio_product_brand="%s" href="',
			esc_attr( $ec_product_array['id'] ),
			esc_attr( $ec_product_array['internal_id'] ),
			esc_attr( $ec_product_array['name'] ),
			esc_attr( $ec_product_array['price'] ),
			esc_attr( $ec_product_array['category'] ),
			esc_url( $ec_product_array['productlink'] ),
			esc_attr( $ec_product_array['listname'] ),
			esc_attr( $ec_product_array['listposition'] ),
			esc_attr( $ec_product_array['stocklevel'] ),
			esc_attr( $ec_product_array['brand'] )
		);

		$lwaio_product_counter++;

		$productitem = str_replace( 'href="', $productlink_with_data, $productitem );
	}

	/*
	$productitem is initialized as the template itself outputs a product item.
	Therefore I can not pass this to wp_kses() as it can include eventually any HTML.
	This filter function only adds additional attributes to the link element that points
	to a product detail page. Attribute values are escaped above.
	*/
	echo $productitem; // phpcs:ignore
}

/**
 * Executed during widget_title.
 * This hook is used for any custom (classic) product list widget with custom title.
 * The widget title will be used to report a custom product list name into Google Analytics.
 * This function also resets the $lwaio_product_counter global variable to report the first
 * product in the widget in the proper position.
 *
 * @param string $widget_title The title of the widget being rendered.
 * @return string The updated widget title which is not changed by this function.
 */
function lwaio_widget_title_filter( $widget_title ) {
	global $lwaio_product_counter, $lwaio_last_widget_title;

	$lwaio_product_counter   = 1;
	$lwaio_last_widget_title = $widget_title . __( ' (widget)', 'lw_all_in_one' );

	return $widget_title;
}

/**
 * Executed during woocommerce_shortcode_before_recent_products_loop.
 * Sets the product list title for product list impression reporting.
 *
 * @return void
 */
function lwaio_before_recent_products_loop() {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = __( 'Recent Products', 'lw_all_in_one' );
}

/**
 * Executed during woocommerce_shortcode_before_sale_products_loop.
 * Sets the product list title for product list impression reporting.
 *
 * @return void
 */
function lwaio_before_sale_products_loop() {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = __( 'Sale Products', 'lw_all_in_one' );
}

/**
 * Executed during woocommerce_shortcode_before_best_selling_products_loop.
 * Sets the product list title for product list impression reporting.
 *
 * @return void
 */
function lwaio_before_best_selling_products_loop() {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = __( 'Best Selling Products', 'lw_all_in_one' );
}

/**
 * Executed during woocommerce_shortcode_before_top_rated_products_loop.
 * Sets the product list title for product list impression reporting.
 *
 * @return void
 */
function lwaio_before_top_rated_products_loop() {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = __( 'Top Rated Products', 'lw_all_in_one' );
}

/**
 * Executed during woocommerce_shortcode_before_featured_products_loop.
 * Sets the product list title for product list impression reporting.
 *
 * @return void
 */
function lwaio_before_featured_products_loop() {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = __( 'Featured Products', 'lw_all_in_one' );
}

/**
 * Executed during woocommerce_shortcode_before_related_products_loop.
 * Sets the product list title for product list impression reporting.
 *
 * @return void
 */
function lwaio_before_related_products_loop() {
	global $woocommerce_loop;

	$woocommerce_loop['listtype'] = __( 'Related Products', 'lw_all_in_one' );
}

/**
 * Generates a <span> element that can be used as a hidden addition to the DOM to be able to report
 * product list impressions and clicks on list pages like product category or tag pages.
 *
 * @param WC_Product $product A WooCommerce product object.
 * @param string     $listtype The name of the product list where the product is currently shown.
 * @param string     $itemix The index of the product in the product list. The first product should have the index no. 1.
 * @param string     $permalink The link where the click should land when a users clicks on this product element.
 * @return string A hidden <span> element that includes all product data needed for enhanced ecommerce reporting in product lists.
 */
function lwaio_woocommerce_get_product_list_item_extra_tag( $product, $listtype, $itemix, $permalink ) {

	if ( ! isset( $product ) ) {
		return;
	}

	if ( ! ( $product instanceof WC_Product ) ) {
		return false;
	}

	if ( is_search() ) {
		$list_name = __( 'Search Results', 'lw_all_in_one' );
	} elseif ( '' !== $listtype ) {
		$list_name = $listtype;
	} else {
		$list_name = __( 'General Product List', 'lw_all_in_one' );
	}

	$paged          = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	$posts_per_page = get_query_var( 'posts_per_page' );
	if ( $posts_per_page < 1 ) {
		$posts_per_page = 1;
	}

	$ec_product_array = lwaio_process_product(
		$product,
		array(
			'productlink'  => $permalink,
			'listname'     => $list_name,
			'listposition' => (int) $itemix + ( $posts_per_page * ( $paged - 1 ) ),
		)
	);

	if ( ! isset( $ec_product_array['brand'] ) ) {
		$ec_product_array['brand'] = '';
	}

	return sprintf(
		'<span class="lwaio_productdata" style="display:none; visibility:hidden;" data-lwaio_product_id="%s" data-lwaio_product_internal_id="%s" data-lwaio_product_name="%s" data-lwaio_product_price="%s" data-lwaio_product_cat="%s" data-lwaio_product_url="%s" data-lwaio_product_listposition="%s" data-lwaio_productlist_name="%s" data-lwaio_product_stocklevel="%s" data-lwaio_product_brand="%s"></span>',
		esc_attr( $ec_product_array['id'] ),
		esc_attr( $ec_product_array['internal_id'] ),
		esc_attr( $ec_product_array['name'] ),
		esc_attr( $ec_product_array['price'] ),
		esc_attr( $ec_product_array['category'] ),
		esc_url( $ec_product_array['productlink'] ),
		esc_attr( $ec_product_array['listposition'] ),
		esc_attr( $ec_product_array['listname'] ),
		esc_attr( $ec_product_array['stocklevel'] ),
		esc_attr( $ec_product_array['brand'] )
	);
}

/**
 * Executed during woocommerce_after_shop_loop_item.
 * Shows a hidden <span> element with product data to report enhanced ecommerce
 * product impression and click actions in product lists.
 *
 * @return void
 */
function lwaio_woocommerce_after_shop_loop_item() {
	global $product, $woocommerce_loop;

	$listtype = '';
	if ( isset( $woocommerce_loop['listtype'] ) && ( '' !== $woocommerce_loop['listtype'] ) ) {
		$listtype = $woocommerce_loop['listtype'];
	}

	$itemix = '';
	if ( isset( $woocommerce_loop['loop'] ) && ( '' !== $woocommerce_loop['loop'] ) ) {
		$itemix = $woocommerce_loop['loop'];
	}

	// no need to escape here as everthing is handled within the function call with esc_attr() and esc_url().
	echo lwaio_woocommerce_get_product_list_item_extra_tag( //phpcs:ignore
		$product,
		$listtype,
		$itemix,
		apply_filters(
			'the_permalink',
			get_permalink(),
			0
		)
	);
}

/**
 * Executed during woocommerce_cart_item_restored.
 * When the user restores the just removed cart item, this function stores the cart item key to
 * be able to generate an add_to_cart event after restoration completes.
 *
 * @param string $cart_item_key A unique cart item key.
 * @return void
 */
function lwaio_woocommerce_cart_item_restored( $cart_item_key ) {
	if ( function_exists( 'WC' ) && WC()->session ) {
		WC()->session->set( 'lwaio_product_readded_to_cart', $cart_item_key );
	}
}

/**
 * Executed during wc_quick_view_before_single_product.
 * This function makes LWAIO compatible with the WooCommerce Quick View plugin.
 * It allows LWAIO to fire product detail action when quick view is opened.
 *
 * @return void
 */
function lwaio_wc_quick_view_before_single_product() {

	$data_layer = array(
		'event' => 'lwaio.changeDetailView',
	);

	$postid  = get_the_ID();
	$product = wc_get_product( $postid );

	$ec_product_array = lwaio_process_product(
		$product,
		array()
	);

	$data_layer['productRatingCounts']  = $product->get_rating_counts();
	$data_layer['productAverageRating'] = (float) $product->get_average_rating();
	$data_layer['productReviewCount']   = (int) $product->get_review_count();
	$data_layer['productType']          = $product->get_type();

	switch ( $data_layer['productType'] ) {
		case 'variable':
			$data_layer['productIsVariable'] = 1;

			$data_layer['ecomm_prodid']     = $ec_product_array['id'];
			$data_layer['ecomm_pagetype']   = 'product';
			$data_layer['ecomm_totalvalue'] = $ec_product_array['price'];

			break;

		case 'grouped':
			$data_layer['productIsVariable'] = 0;

			break;

		default:
			$data_layer['productIsVariable'] = 0;
			$currency_code = get_woocommerce_currency();

			$data_layer['ecommerce'] = array(
				'currencyCode' => $currency_code,
				'detail'       => array(
					'products' => array(
						$ec_product_array,
					),
				),
			);
	}

	echo '
	<span style="display: none;" id="lwaio_quickview_data" data-lwaio_datalayer="' . esc_attr( wp_json_encode( $data_layer ) ) . '"></span>';
}

/**
 * Executed during woocommerce_grouped_product_list_column_label.
 * Adds product list impression info into every product listed on a grouped product detail page to
 * track product list impression and click interactions for individual products in the grouped product.
 *
 * @param string     $labelvalue Not used by this function, returns the value without modifying it.
 * @param WC_Product $product The WooCommerce product object being shown.
 * @return string The string that has been passed to the $labelvalue parameter without any modification.
 */
function lwaio_woocommerce_grouped_product_list_column_label( $labelvalue, $product ) {
	$lwaio_grouped_product_ix = 1;

	if ( ! isset( $product ) ) {
		return $labelvalue;
	}

	$list_name = __( 'Grouped Product Detail Page', 'lw_all_in_one' );

	$ec_product_array = lwaio_process_product(
		$product,
		array(
			'productlink'  => $product->get_permalink(),
			'listname'     => $list_name,
			'listposition' => $lwaio_grouped_product_ix,
		)
	);

	$lwaio_grouped_product_ix++;

	if ( ! isset( $ec_product_array['brand'] ) ) {
		$ec_product_array['brand'] = '';
	}

	$labelvalue .=
		sprintf(
			'<span class="lwaio_productdata" style="display:none; visibility:hidden;" data-lwaio_product_id="%s" data-lwaio_product_internal_id="%s" data-lwaio_product_sku="%s" data-lwaio_product_name="%s" data-lwaio_product_price="%s" data-lwaio_product_cat="%s" data-lwaio_product_url="%s" data-lwaio_product_listposition="%s" data-lwaio_productlist_name="%s" data-lwaio_product_stocklevel="%s" data-lwaio_product_brand="%s"></span>',
			esc_attr( $ec_product_array['id'] ),
			esc_attr( $ec_product_array['internal_id'] ),
			esc_attr( $ec_product_array['sku'] ),
			esc_attr( $ec_product_array['name'] ),
			esc_attr( $ec_product_array['price'] ),
			esc_attr( $ec_product_array['category'] ),
			esc_url( $ec_product_array['productlink'] ),
			esc_attr( $ec_product_array['listposition'] ),
			esc_attr( $ec_product_array['listname'] ),
			esc_attr( $ec_product_array['stocklevel'] ),
			esc_attr( $ec_product_array['brand'] )
		);

	return $labelvalue;
}

/**
 * Executed during woocommerce_blocks_product_grid_item_html.
 * Adds product list impression data into a product list that has been generated using the block templates
 * provided by WooCommerce. This allows proper tracking ot WooCommerce Blocks with product list
 * impression and click actions.
 *
 * @param string     $content Product grid item HTML.
 * @param object     $data Product data passed to the template.
 * @param WC_Product $product Product object.
 * @return string The product grid item HTML with added hidden <span> element for ecommerce tracking.
 */
function lwaio_add_productdata_to_wc_block( $content, $data, $product ) {
	$product_data_tag = lwaio_woocommerce_get_product_list_item_extra_tag( $product, '', 0, $data->permalink );

	return preg_replace( '/<li.+class=("|"[^"]+)wc-block-grid__product("|[^"]+")[^<]*>/i', '$0' . $product_data_tag, $content );
}

/**
 * Function executed during wp_head.
 * Outputs the main Google Tag Manager container code and if WooCommerce is active, it removes the
 * purchase data from the data layer if the order ID has been already tracked before and
 * double tracking prevention option is active.
 *
 * @see https://developer.wordpress.org/reference/functions/wp_head/
 *
 * @param boolean $echo If set to true and AMP is currently generating the page content, the HTML is outputed immediately.
 * @return string|void Returns the HTML if the $echo parameter is set to false or when not AMP page generation is running.
 */
function lwaio_wp_header_begin( $echo = true ) {
	global $woocommerce;

	echo '
	<script data-cfasync="false" data-pagespeed-no-defer type="text/javascript">';

		$lwaio_datalayer_data = lwaio_woocommerce_datalayer([]);

		echo '
			var dataLayer_content = ' . wp_json_encode( $lwaio_datalayer_data, JSON_UNESCAPED_UNICODE ) . ';';

			// fire WooCommerce order double tracking protection only if WooCommerce is active and user is on the order received page.
		if ( isset( $woocommerce ) && is_order_received_page() ) {
			echo '
			// if dataLayer contains ecommerce purchase data, check whether it has been already tracked
			if ( dataLayer_content.transactionId || ( dataLayer_content.ecommerce && dataLayer_content.ecommerce.purchase ) ) {
				// read order id already tracked from cookies
				var lwaio_orderid_tracked = "";

				if ( !window.localStorage ) {
					var lwaio_cookie = "; " + document.cookie;
					var lwaio_cookie_parts = lwaio_cookie.split( "; lwaio_orderid_tracked=" );
					if ( lwaio_cookie_parts.length == 2 ) {
						lwaio_orderid_tracked = lwaio_cookie_parts.pop().split(";").shift();
					}
				} else {
					lwaio_orderid_tracked = window.localStorage.getItem( "lwaio_orderid_tracked" );
				}

				// check enhanced ecommerce
				if ( dataLayer_content.ecommerce && dataLayer_content.ecommerce.purchase ) {
					if ( lwaio_orderid_tracked && ( dataLayer_content.ecommerce.purchase.actionField.id == lwaio_orderid_tracked ) ) {
						delete dataLayer_content.ecommerce.purchase;
					} else {
						lwaio_orderid_tracked = dataLayer_content.ecommerce.purchase.actionField.id;
					}
				}

				// check app+web ecommerce
				if ( dataLayer_content.ecommerce && dataLayer_content.ecommerce.items ) {
					if ( lwaio_orderid_tracked && ( dataLayer_content.ecommerce.transaction_id == lwaio_orderid_tracked ) ) {
						delete dataLayer_content.ecommerce.affiliation;
						delete dataLayer_content.ecommerce.value;
						delete dataLayer_content.ecommerce.currency;
						delete dataLayer_content.ecommerce.tax;
						delete dataLayer_content.ecommerce.shipping;
						delete dataLayer_content.ecommerce.transaction_id;

						delete dataLayer_content.ecommerce.items;
					} else {
						lwaio_orderid_tracked = dataLayer_content.ecommerce.purchase.actionField.id;
					}
				}

				// check standard ecommerce
				if ( dataLayer_content.transactionId ) {
					if ( lwaio_orderid_tracked && ( dataLayer_content.transactionId == lwaio_orderid_tracked ) ) {
						delete dataLayer_content.transactionId;
						delete dataLayer_content.transactionDate;
						delete dataLayer_content.transactionType;
						delete dataLayer_content.transactionAffiliation;
						delete dataLayer_content.transactionTotal;
						delete dataLayer_content.transactionShipping;
						delete dataLayer_content.transactionTax;
						delete dataLayer_content.transactionPaymentType;
						delete dataLayer_content.transactionCurrency;
						delete dataLayer_content.transactionShippingMethod;
						delete dataLayer_content.transactionPromoCode;
						delete dataLayer_content.transactionProducts;
					} else {
						lwaio_orderid_tracked = dataLayer_content.transactionId;
					}
				}

				if ( lwaio_orderid_tracked ) {
					if ( !window.localStorage ) {
						var lwaio_orderid_cookie_expire = new Date();
						lwaio_orderid_cookie_expire.setTime( lwaio_orderid_cookie_expire.getTime() + (365*24*60*60*1000) );
						var lwaio_orderid_cookie_expires_part = "expires=" + lwaio_orderid_cookie_expire.toUTCString();
						document.cookie = "lwaio_orderid_tracked=" + lwaio_orderid_tracked + ";" + lwaio_orderid_cookie_expires_part + ";path=/";
					} else {
						window.localStorage.setItem( "lwaio_orderid_tracked", lwaio_orderid_tracked );
					}
				}

			}';
		}

		echo '
		dataLayer.push( dataLayer_content );
	</script>';
}


/**
 * Function executed during wp_head with high priority.
 * Outputs some global JavaScript variables that needs to be accessable by other parts of the plugin.
 *
 * @see https://developer.wordpress.org/reference/functions/wp_head/
 *
 * @param boolean $echo If set to true and AMP is currently generating the page content, the HTML is outputed immediately.
 * @return string|void Returns the HTML if the $echo parameter is set to false or when not AMP page generation is running.
 */
function lwaio_wp_header_top( $echo = true ) {

	// the data layer initialization has to use 'var' instead of 'let' since 'let' can break related browser extension and 3rd party script.
	$_gtm_top_content = '
	<script data-cfasync="false" data-pagespeed-no-defer type="text/javascript">
		var dataLayer = dataLayer || [];';

		$added_global_js_vars['lwaio_use_sku_instead']        = (int) 0;
		$added_global_js_vars['lwaio_id_prefix']              = '';
		$added_global_js_vars['lwaio_remarketing']            = (bool) false;
		$added_global_js_vars['lwaio_eec']                    = (bool) true;
		$added_global_js_vars['lwaio_classicec']              = (bool) false;
		$added_global_js_vars['lwaio_currency']               = get_woocommerce_currency();
		$added_global_js_vars['lwaio_product_per_impression'] = (int) 0;
		$added_global_js_vars['lwaio_needs_shipping_address'] = (bool) false;

		foreach ( $added_global_js_vars as $js_var_name => $js_var_value ) {
			if ( is_string( $js_var_value ) ) {
				$js_var_value = "'" . esc_js( $js_var_value ) . "'";
			}

			if ( is_bool( $js_var_value ) || empty( $js_var_value ) ) {
				$js_var_value = $js_var_value ? 'true' : 'false';
			}

			if ( is_array( $js_var_value ) ) {
				$js_var_value = wp_json_encode( $js_var_value );
			}

			if ( is_null( $js_var_value ) ) {
				$js_var_value = 'null';
			}

			$_gtm_top_content .= '
			const ' . esc_js( $js_var_name ) . ' = ' . $js_var_value . ';';
		}

		$_gtm_top_content .= '
	</script>';

	echo wp_kses(
		$_gtm_top_content,
		array(
			'script' => array(
				'data-cfasync'            => array(),
				'data-pagespeed-no-defer' => array(),
				'data-cookieconsent'      => array(),
			),
		)
	);
}


// do not add filter if someone enabled WooCommerce integration without an activated WooCommerce plugin.
if ( function_exists( 'WC' ) ) {

	add_filter( 'loop_end', 'lwaio_woocommerce_reset_loop' );
	add_action( 'woocommerce_after_shop_loop_item', 'lwaio_woocommerce_after_shop_loop_item' );
	add_action( 'woocommerce_after_add_to_cart_button', 'lwaio_woocommerce_single_add_to_cart_tracking' );

	add_filter( 'woocommerce_blocks_product_grid_item_html', 'lwaio_add_productdata_to_wc_block', 10, 3 );

	add_action( 'woocommerce_thankyou', 'lwaio_woocommerce_thankyou' );

	add_action( 'woocommerce_before_template_part', 'lwaio_woocommerce_before_template_part' );
	add_action( 'woocommerce_after_template_part', 'lwaio_woocommerce_after_template_part' );
	add_filter( 'widget_title', 'lwaio_widget_title_filter' );
	add_action( 'wc_quick_view_before_single_product', 'lwaio_wc_quick_view_before_single_product' );
	add_filter( 'woocommerce_grouped_product_list_column_label', 'lwaio_woocommerce_grouped_product_list_column_label', 10, 2 );

	add_filter( 'woocommerce_cart_item_product', 'lwaio_woocommerce_cart_item_product_filter' );
	add_filter( 'woocommerce_cart_item_remove_link', 'lwaio_woocommerce_cart_item_remove_link_filter' );
	add_action( 'woocommerce_cart_item_restored', 'lwaio_woocommerce_cart_item_restored' );

	add_filter( 'woocommerce_related_products_args', 'lwaio_woocommerce_add_related_to_loop' );
	add_filter( 'woocommerce_related_products_columns', 'lwaio_woocommerce_add_related_to_loop' );
	add_filter( 'woocommerce_cross_sells_columns', 'lwaio_woocommerce_add_cross_sell_to_loop' );
	add_filter( 'woocommerce_upsells_columns', 'lwaio_woocommerce_add_upsells_to_loop' );

	add_action( 'woocommerce_shortcode_before_recent_products_loop', 'lwaio_before_recent_products_loop' );
	add_action( 'woocommerce_shortcode_before_sale_products_loop', 'lwaio_before_sale_products_loop' );
	add_action( 'woocommerce_shortcode_before_best_selling_products_loop', 'lwaio_before_best_selling_products_loop' );
	add_action( 'woocommerce_shortcode_before_top_rated_products_loop', 'lwaio_before_top_rated_products_loop' );
	add_action( 'woocommerce_shortcode_before_featured_products_loop', 'lwaio_before_featured_products_loop' );
	add_action( 'woocommerce_shortcode_before_related_products_loop', 'lwaio_before_related_products_loop' );

	add_action( 'wp_head', 'lwaio_wp_header_begin', 10, 0 );
	add_action( 'wp_head', 'lwaio_wp_header_top', 1, 0 );

}
