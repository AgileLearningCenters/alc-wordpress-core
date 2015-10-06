<?php

/*
Paypal Express gateway modifieed from:
MarketPress PayPal Express Gateway Plugin
Author: Aaron Edwards (Incsub)
*/

class PPW_Gateway_Paypal_Express {

	//private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
	var $plugin_name = 'paypal-express';

	//name of your gateway, for the admin side.
	var $admin_name = '';

	//public name of your gateway, for lists and such.
	var $public_name = '';

	//url for an image for your checkout method. Displayed on checkout form if set
	var $method_img_url = '';

	//url for an submit button image for your checkout method. Displayed on checkout form if set
	var $method_button_img_url = '';

	//whether or not ssl is needed for checkout page
	var $force_ssl = false;

	//always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
	var $ipn_url;

	//whether if this is the only enabled gateway it can skip the payment_form step
	var $skip_form = true;

	//only required for global capable gateways. The maximum stores that can checkout at once
	var $max_stores = 10;

	// Payment action
	var $payment_action = 'Sale';

	//paypal vars
	var $API_Username, $API_Password, $API_Signature, $SandboxFlag, $returnURL, $cancelURL, $API_Endpoint, $paypalURL, $version, $currencyCode, $locale;

	/****** Below are the public methods you may overwrite via a plugin ******/

	/**
	 * Runs when your class is instantiated.
	 */
	function __construct( $post_id = 0 ) {
		global $ppw;
		$settings = get_option( 'ppw_options' );


		//set names here to be able to translate
		$this->admin_name  = __( 'PayPal Express Checkout', 'ppw' );
		$this->public_name = __( 'PayPal', 'ppw' );

		//dynamic button img, see: https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_ECButtonIntegration
		$this->method_img_url        = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecmark&locale=' . get_locale();
		$this->method_button_img_url = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=' . get_locale();

		//set paypal vars
		/** @todo Set all array keys to resolve Undefined indexes notice */;

		$this->API_Username  = @$settings['gateways']['paypal-express']['api_user'];
		$this->API_Password  = @$settings['gateways']['paypal-express']['api_pass'];
		$this->API_Signature = @$settings['gateways']['paypal-express']['api_sig'];
		$this->currencyCode  = @$settings['gateways']['paypal-express']['currency'];
		$this->locale        = @$settings['gateways']['paypal-express']['locale'];
		$this->returnURL     = get_permalink( $post_id ) . "?ppw_confirm=1";
		$this->cancelURL     = get_permalink( $post_id ) . "?cancel=1";
		$this->version       = "69.0"; //api version

		$this->cookie = $settings["cookie"]; // Cookie validity time

		$this->post_id = $post_id;

		//set api urls
		if ( @$settings['gateways']['paypal-express']['mode'] == 'sandbox' ) {
			$this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
			$this->paypalURL    = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
		} else {
			$this->API_Endpoint = "https://api-3t.paypal.com/nvp";
			$this->paypalURL    = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
		}
	}

	/**
	 * Echo fields you need to add to the payment screen, like your credit card info fields.
	 *  If you don't need to add form fields set $skip_form to true so this page can be skipped
	 *  at checkout.
	 *
	 * @param array $cart . Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $shipping_info . Contains shipping info and email in case you need it
	 */
	function payment_form( $global_cart ) {
		if ( isset( $_GET['cancel'] ) ) {
			echo '<div class="ppw_checkout_error">' . __( 'Your PayPal transaction has been canceled.', 'ppw' ) . '</div>';
		}
	}

	/**
	 * Use this to authorize ordered transactions.
	 *
	 * @param array $order Contains the list of order ids
	 */
	function process_payment_authorize( $orders ) {
		if ( is_array( $orders ) ) {
			foreach ( $orders as $order ) {
				$transaction_id = $order['transaction_id'];
				$amount         = $order['amount'];

				$authorization = $this->DoAuthorization( $transaction_id, $amount );

				switch ( $result["PAYMENTSTATUS"] ) {
					case 'Canceled-Reversal':
						$status     = __( 'A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.', 'ppw' );
						$authorized = true;
						break;
					case 'Expired':
						$status     = __( 'The authorization period for this payment has been reached.', 'ppw' );
						$authorized = false;
						break;
					case 'Voided':
						$status     = __( 'An authorization for this transaction has been voided.', 'ppw' );
						$authorized = false;
						break;
					case 'Failed':
						$status     = __( 'The payment has failed. This happens only if the payment was made from your customer\'s bank account.', 'ppw' );
						$authorized = false;
						break;
					case 'Partially-Refunded':
						$status     = __( 'The payment has been partially refunded.', 'ppw' );
						$authorized = true;
						break;
					case 'In-Progress':
						$status     = __( 'The transaction has not terminated, e.g. an authorization may be awaiting completion.', 'ppw' );
						$authorized = false;
						break;
					case 'Completed':
						$status     = __( 'The payment has been completed, and the funds have been added successfully to your account balance.', 'ppw' );
						$authorized = true;
						break;
					case 'Processed':
						$status     = __( 'A payment has been accepted.', 'ppw' );
						$authorized = true;
						break;
					case 'Reversed':
						$status          = __( 'A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer:', 'ppw' );
						$reverse_reasons = array(
							'none'            => '',
							'chargeback'      => __( 'A reversal has occurred on this transaction due to a chargeback by your customer.', 'ppw' ),
							'guarantee'       => __( 'A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.', 'ppw' ),
							'buyer-complaint' => __( 'A reversal has occurred on this transaction due to a complaint about the transaction from your customer.', 'ppw' ),
							'refund'          => __( 'A reversal has occurred on this transaction because you have given the customer a refund.', 'ppw' ),
							'other'           => __( 'A reversal has occurred on this transaction due to an unknown reason.', 'ppw' )
						);
						$status .= '<br />' . $reverse_reasons[ $result["REASONCODE"] ];
						$authorized = false;
						break;
					case 'Refunded':
						$status     = __( 'You refunded the payment.', 'ppw' );
						$authorized = false;
						break;
					case 'Denied':
						$status     = __( 'You denied the payment when it was marked as pending.', 'ppw' );
						$authorized = false;
						break;
					case 'Pending':
						$pending_str = array(
							'address'        => __( 'The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences  section of your Profile.', 'ppw' ),
							'authorization'  => __( 'The payment is pending because it has been authorized but not settled. You must capture the funds first.', 'ppw' ),
							'echeck'         => __( 'The payment is pending because it was made by an eCheck that has not yet cleared.', 'ppw' ),
							'intl'           => __( 'The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'ppw' ),
							'multi-currency' => __( 'You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'ppw' ),
							'order'          => __( 'The payment is pending because it is part of an order that has been authorized but not settled.', 'ppw' ),
							'paymentreview'  => __( 'The payment is pending while it is being reviewed by PayPal for risk.', 'ppw' ),
							'unilateral'     => __( 'The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'ppw' ),
							'upgrade'        => __( 'The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. It can also mean that you have reached the monthly limit for transactions on your account.', 'ppw' ),
							'verify'         => __( 'The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'ppw' ),
							'other'          => __( 'The payment is pending for an unknown reason. For more information, contact PayPal customer service.', 'ppw' ),
							'*'              => ''
						);
						$status      = __( 'The payment is pending.', 'ppw' );
						$status .= '<br />' . $pending_str[ $result["PENDINGREASON"] ];
						$authorized = false;
						break;
					default:
						// case: various error cases
						$authorized = false;
				}

				if ( $authorized ) {
					update_post_meta( $order['order_id'], 'ppw_deal', 'authorized' );
					update_post_meta( $order['order_id'], 'ppw_deal_authorization_id', $authorization['TRANSACTIONID'] );
				}
			}
		}
	}

	/**
	 * Use this to capture authorized transactions.
	 *
	 * @param array $cart . Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $authorizations Contains the list of authorization ids
	 */
	function process_payment_capture( $authorizations ) {
		if ( is_array( $authorizations ) ) {
			foreach ( $authorizations as $authorization ) {
				$transaction_id = $authorization['transaction_id'];
				$amount         = $authorization['amount'];

				$capture = $this->DoCapture( $transaction_id, $amount );

				update_post_meta( $authorization['deal_id'], 'ppw_deal', 'captured' );
			}
		}
	}

	/**
	 * Use this to process any fields you added. Use the $_POST global,
	 *  and be sure to save it to both the $_SESSION and usermeta if logged in.
	 *  DO NOT save credit card details to usermeta as it's not PCI compliant.
	 *  Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
	 *  it will redirect to the next step.
	 *
	 * @param array $cart . Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $shipping_info . Contains shipping info and email in case you need it
	 */
	function process_payment_form( $global_cart ) {
		global $ppw;

		//set it up with PayPal
		$result = $this->SetExpressCheckout( $global_cart );

		//check response
		if ( $result["ACK"] == "Success" || $result["ACK"] == "SuccessWithWarning" ) {
			$token = urldecode( $result["TOKEN"] );
			$this->RedirectToPayPal( $token );
		} else { //whoops, error
			$error = "";
			for ( $i = 0; $i <= 5; $i ++ ) { //print the first 5 errors
				if ( isset( $result["L_ERRORCODE$i"] ) ) {
					$error .= "<li>{$result["L_ERRORCODE$i"]} - {$result["L_SHORTMESSAGE$i"]} - {$result["L_LONGMESSAGE$i"]}</li>";
				}
			}
			$error = '<br /><ul>' . $error . '</ul>';
			$this->cart_checkout_error( __( 'There was a problem connecting to PayPal to setup your purchase. Please try again.', 'ppw' ) . $error );
		}
	}

	function cart_checkout_error( $msg, $context = 'checkout' ) {
		$msg     = str_replace( '"', '\"', $msg ); //prevent double quotes from causing errors.
		$content = 'return "<div class=\"ppw_checkout_error\">' . $msg . '</div>";';
		add_action( 'ppw_checkout_error_' . $context, create_function( '', $content ) );
		$this->checkout_error = true;
	}

	/**
	 * Return the chosen payment details here for final confirmation. You probably don't need
	 *  to post anything in the form as it should be in your $_SESSION var already.
	 *
	 * @param array $cart . Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $shipping_info . Contains shipping info and email in case you need it
	 */
	function confirm_payment_form( $global_cart ) {
		global $ppw;

		$content = '';

		if ( isset( $_GET['token'] ) && isset( $_GET['PayerID'] ) ) {
			$_SESSION['TOKEN']   = $_GET['token'];
			$_SESSION['PayerID'] = $_GET['PayerID'];

			//get details from PayPal
			$result = $this->GetExpressCheckoutDetails( $_SESSION['TOKEN'] );

			//check response
			if ( $result["ACK"] == "Success" || $result["ACK"] == "SuccessWithWarning" ) {

				$account_name = isset( $result["BUSINESS"] ) ? $result["BUSINESS"] : $result["EMAIL"];

				//set final amount
				$_SESSION['final_amt']  = 0;
				$_SESSION['final_amts'] = array();
				$_SESSION['prs']        = array();
				$_SESSION['ipns']       = array();
				// $_SESSION['seller_ids'] = array();
				$_SESSION['seller_paypal_accounts'] = array();

				for ( $i = 0; $i < 1; $i ++ ) {
					if ( ! isset( $result[ 'PAYMENTREQUEST_' . $i . '_AMT' ] ) ) {
						continue;
					}
					$_SESSION['final_amt'] += $result[ 'PAYMENTREQUEST_' . $i . '_AMT' ];
					$_SESSION['final_amts'][] = $result[ 'PAYMENTREQUEST_' . $i . '_AMT' ];
				}

			} else { //whoops, error
				for ( $i = 0; $i <= 5; $i ++ ) { //print the first 5 errors
					if ( isset( $result["L_ERRORCODE$i"] ) ) {
						$error .= "<li>{$result["L_ERRORCODE$i"]} - {$result["L_SHORTMESSAGE$i"]} - {$result["L_LONGMESSAGE$i"]}</li>";
					}
				}
				$error = '<br /><ul>' . $error . '</ul>';
				$content .= '<div class="ppw_checkout_error">' . sprintf( __( 'There was a problem with your PayPal transaction. Please try again</a>.', 'ppw' ) ) . $error . '</div>';
			}

		} else {
			$content .= '<div class="ppw_checkout_error">' . sprintf( __( 'Whoops, looks like you skipped a step! ', 'ppw' ) ) . '</div>';
		}

		return $content;
	}

	/**
	 * Use this to do the final payment. Create the order then process the payment. If
	 *  you know the payment is successful right away go ahead and change the order status
	 *  as well.
	 *  Call $mp->cart_checkout_error($msg, $context); to handle errors. If no errors
	 *  it will redirect to the next step.
	 *
	 * @param array $cart . Contains the cart contents for the current blog, global cart if $mp->global_cart is true
	 * @param array $shipping_info . Contains shipping info and email in case you need it
	 */
	function process_payment( $global_cart ) {
		global $ppw;

		if ( isset( $_SESSION['TOKEN'] ) && isset( $_SESSION['PayerID'] ) && isset( $_SESSION['final_amt'] ) ) {
			//attempt the final payment
			$result = $this->DoExpressCheckoutPayment( $_SESSION['TOKEN'], $_SESSION['payer_id'], $_SESSION['final_amts'], $_SESSION['seller_paypal_accounts'], $_SESSION['ipns'], $_SESSION['prs'] );

			//check response
			if ( $result["ACK"] == "Success" || $result["ACK"] == "SuccessWithWarning" ) {

				//setup our payment details
				$payment_info['gateway_public_name']  = $this->public_name;
				$payment_info['gateway_private_name'] = $this->admin_name;
				for ( $i = 0; $i < 1; $i ++ ) {
					if ( ! isset( $result[ 'PAYMENTINFO_' . $i . '_PAYMENTTYPE' ] ) ) {
						continue;
					}
					$payment_info['method']         = ( $result["PAYMENTINFO_{$i}_PAYMENTTYPE"] == 'echeck' ) ? __( 'eCheck', 'ppw' ) : __( 'PayPal balance, Credit Card, or Instant Transfer', 'ppw' );
					$payment_info['transaction_id'] = $result["PAYMENTINFO_{$i}_TRANSACTIONID"];

					// We use servers timestamp
					$timestamp = time();

					//setup status
					switch ( $result["PAYMENTINFO_{$i}_PAYMENTSTATUS"] ) {
						case 'Canceled-Reversal':
							$status = __( 'A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.', 'ppw' );
							$paid   = true;
							break;
						case 'Expired':
							$status = __( 'The authorization period for this payment has been reached.', 'ppw' );
							$paid   = false;
							break;
						case 'Voided':
							$status = __( 'An authorization for this transaction has been voided.', 'ppw' );
							$paid   = false;
							break;
						case 'Failed':
							$status = __( 'The payment has failed. This happens only if the payment was made from your customer\'s bank account.', 'ppw' );
							$paid   = false;
							break;
						case 'Partially-Refunded':
							$status = __( 'The payment has been partially refunded.', 'ppw' );
							$paid   = true;
							break;
						case 'In-Progress':
							$status = __( 'The transaction has not terminated, e.g. an authorization may be awaiting completion.', 'ppw' );
							$paid   = false;
							break;
						case 'Completed':
							$status = __( 'The payment has been completed, and the funds have been added successfully to your account balance.', 'ppw' );
							$paid   = true;
							break;
						case 'Processed':
							$status = __( 'A payment has been accepted.', 'ppw' );
							$paid   = true;
							break;
						case 'Reversed':
							$status          = __( 'A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer:', 'ppw' );
							$reverse_reasons = array(
								'none'            => '',
								'chargeback'      => __( 'A reversal has occurred on this transaction due to a chargeback by your customer.', 'ppw' ),
								'guarantee'       => __( 'A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.', 'ppw' ),
								'buyer-complaint' => __( 'A reversal has occurred on this transaction due to a complaint about the transaction from your customer.', 'ppw' ),
								'refund'          => __( 'A reversal has occurred on this transaction because you have given the customer a refund.', 'ppw' ),
								'other'           => __( 'A reversal has occurred on this transaction due to an unknown reason.', 'ppw' )
							);
							$status .= '<br />' . $reverse_reasons[ $result["PAYMENTINFO_{$i}_REASONCODE"] ];
							$paid = false;
							break;
						case 'Refunded':
							$status = __( 'You refunded the payment.', 'ppw' );
							$paid   = false;
							break;
						case 'Denied':
							$status = __( 'You denied the payment when it was marked as pending.', 'ppw' );
							$paid   = false;
							break;
						case 'Pending':
							$pending_str = array(
								'address'        => __( 'The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences  section of your Profile.', 'ppw' ),
								'authorization'  => __( 'The payment is pending because it has been authorized but not settled. You must capture the funds first.', 'ppw' ),
								'echeck'         => __( 'The payment is pending because it was made by an eCheck that has not yet cleared.', 'ppw' ),
								'intl'           => __( 'The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'ppw' ),
								'multi-currency' => __( 'You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'ppw' ),
								'order'          => __( 'The payment is pending because it is part of an order that has been authorized but not settled.', 'ppw' ),
								'paymentreview'  => __( 'The payment is pending while it is being reviewed by PayPal for risk.', 'ppw' ),
								'unilateral'     => __( 'The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'ppw' ),
								'upgrade'        => __( 'The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. It can also mean that you have reached the monthly limit for transactions on your account.', 'ppw' ),
								'verify'         => __( 'The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'ppw' ),
								'other'          => __( 'The payment is pending for an unknown reason. For more information, contact PayPal customer service.', 'ppw' ),
								'*'              => ''
							);
							$status      = __( 'The payment is pending.', 'ppw' );
							$status .= '<br />' . $pending_str[ $result["PAYMENTINFO_{$i}_PENDINGREASON"] ];
							$paid = false;
							break;
						default:
							// case: various error cases
							$paid = false;
					}
					$status = $result["PAYMENTINFO_{$i}_PAYMENTSTATUS"] . ': ' . $status;

					$currency = $result["PAYMENTINFO_{$i}_CURRENCYCODE"];

					if ( $result["PAYMENTINFO_{$i}_PAYMENTSTATUS"] == "Processed" OR $result["PAYMENTINFO_{$i}_PAYMENTSTATUS"] == "Completed" ) {
						$db_status = "Paid";
					} else if ( $result["PAYMENTINFO_{$i}_PAYMENTSTATUS"] == "Pending" ) {
						$db_status = "Pending";
						// Warn admin
						global $current_site;
						$admin_email = get_option( 'admin_email' );
						if ( ! $admin_email ) {
							$admin_email = 'admin@' . $current_site->domain;
						}
						wp_mail( $admin_email, __( 'Pending Pay Per View order', 'ppw' ), __( 'A Pay Per View order has arrived, and content revealed to the client, but payment is kept in pending state by PayPal. Please check your PayPal account.', 'ppw' ) );
					}

					$amount = $result["PAYMENTINFO_{$i}_AMT"];

					$note = @$result["NOTE"]; //optional, only shown if gateway supports it

					//figure out blog_id of this payment to put the order into it
					$order_id = $result["PAYMENTINFO_{$i}_TRANSACTIONID"];


					//succesful payment, create our order now
					$user_id = get_current_user_id();
					if ( ! $ppw->duplicate_transaction( $user_id, $_SESSION["ppw_post_id"], $amount, $currency, $timestamp, $order_id, $db_status, '', $_SESSION["ppw_content_id"] ) ) {
						$ppw->record_transaction( $user_id, $_SESSION["ppw_post_id"], $amount, $currency, $timestamp, $order_id, $db_status, $note, $_SESSION["ppw_content_id"] );
					}

					// Set a cookie
					$new_order = array(
						'content_id' => $_SESSION["ppw_content_id"],
						'post_id'    => $_SESSION["ppw_post_id"],
						'order_id'   => $order_id
					);
					// Check if user had order from before
					if ( isset( $_COOKIE["pay_per_view"] ) ) {
						$orders = unserialize( stripslashes( $_COOKIE["pay_per_view"] ) );
					}
					// Double check
					if ( ! is_array( $orders ) ) {
						$orders = array();
					}

					$orders[] = $new_order;

					// Let admin set cookie expire at the end of session
					if ( ! isset( $this->cookie ) ) {
						$expire = time() + 3600;
					} else if ( '' == trim( $this->cookie ) || ! is_numeric( $this->cookie ) || ! $this->cookie ) {
						$expire = 0;
					} else {
						$expire = time() + 3600 * $this->cookie;
					}

					if ( defined( 'COOKIEPATH' ) ) {
						$cookiepath = COOKIEPATH;
					} else {
						$cookiepath = "/";
					}
					if ( defined( 'COOKIEDOMAIN' ) ) {
						$cookiedomain = COOKIEDOMAIN;
					} else {
						$cookiedomain = '';
					}
					setcookie( "pay_per_view", serialize( $orders ), $expire, $cookiepath, $cookiedomain );
					wp_redirect( get_permalink( $_SESSION["ppw_post_id"] ) );
					exit;
				}
			} else { //whoops, error

				for ( $i = 0; $i <= 5; $i ++ ) { //print the first 5 errors
					if ( isset( $result["L_ERRORCODE$i"] ) ) {
						$error .= "<li>{$result["L_ERRORCODE$i"]} - {$result["L_SHORTMESSAGE$i"]} - {$result["L_LONGMESSAGE$i"]}</li>";
					}
				}
				$error = '<br /><ul>' . $error . '</ul>';
				$ppw->error( __( 'There was a problem finalizing your purchase with PayPal. Please try again</a>.', 'ppw' ) . $error );
			}
		} else {
			$ppw->error( __( 'There was a problem finalizing your purchase with PayPal. Please try again</a>.', 'ppw' ) );
		}
	}

	/**
	 * Runs before page load incase you need to run any scripts before loading the success message page
	 */
	function order_confirmation( $order ) {

	}

	/**
	 * Filters the order confirmation email message body. You may want to append something to
	 *  the message. Optional
	 *
	 * Don't forget to return!
	 */
	function order_confirmation_email( $msg, $order ) {
		return $msg;
	}

	/**
	 * Return any html you want to show on the confirmation screen after checkout. This
	 *  should be a payment details box and message.
	 *
	 * Don't forget to return!
	 */
	function order_confirmation_msg( $content, $order ) {
		global $ppw;

		if ( $order->post_status == 'order_received' ) {
			$content .= '<p>' . sprintf( __( 'Your PayPal payment for this order totaling %s is not yet complete. Here is the latest status:', 'ppw' ), $mp->format_currency( $order->ppw_payment_info['currency'], $order->ppw_payment_info['total'] ) ) . '</p>';
			$statuses = $order->ppw_payment_info['status'];
			krsort( $statuses ); //sort with latest status at the top
			$status    = reset( $statuses );
			$timestamp = key( $statuses );
			$content .= '<p><strong>' . date( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), $timestamp ) . ':</strong> ' . esc_html( $status ) . '</p>';
		} else {
			$content .= '<p>' . sprintf( __( 'Your PayPal payment for this order totaling %s is complete. The PayPal transaction number is <strong>%s</strong>.', 'ppw' ), $mp->format_currency( $order->ppw_payment_info['currency'], $order->ppw_payment_info['total'] ), $order->ppw_payment_info['transaction_id'] ) . '</p>';
		}

		return $content;
	}

	/**
	 * Echo a settings meta box with whatever settings you need for you gateway.
	 *  Form field names should be prefixed with mp[gateways][plugin_name], like "mp[gateways][plugin_name][mysetting]".
	 *  You can access saved settings via $settings array.
	 */
	function gateway_settings_box( $settings ) {
		global $ppw;
		?>
		<div id="ppw_paypal_express" class="postbox">
			<script type="text/javascript">
				jQuery(document).ready(function ($) {
					$('#mp-hdr-bdr').ColorPicker({
						onSubmit: function (hsb, hex, rgb, el) {
							$(el).val(hex);
							$(el).ColorPickerHide();
						},
						onBeforeShow: function () {
							$(this).ColorPickerSetColor(this.value);
						},
						onChange: function (hsb, hex, rgb) {
							$('#mp-hdr-bdr').val(hex);
						}
					})
						.bind('keyup', function () {
							$(this).ColorPickerSetColor(this.value);
						});
					$('#mp-hdr-bck').ColorPicker({
						onSubmit: function (hsb, hex, rgb, el) {
							$(el).val(hex);
							$(el).ColorPickerHide();
						},
						onBeforeShow: function () {
							$(this).ColorPickerSetColor(this.value);
						},
						onChange: function (hsb, hex, rgb) {
							$('#mp-hdr-bck').val(hex);
						}
					})
						.bind('keyup', function () {
							$(this).ColorPickerSetColor(this.value);
						});
					$('#mp-pg-bck').ColorPicker({
						onSubmit: function (hsb, hex, rgb, el) {
							$(el).val(hex);
							$(el).ColorPickerHide();
						},
						onBeforeShow: function () {
							$(this).ColorPickerSetColor(this.value);
						},
						onChange: function (hsb, hex, rgb) {
							$('#mp-pg-bck').val(hex);
						}
					})
						.bind('keyup', function () {
							$(this).ColorPickerSetColor(this.value);
						});
				});
			</script>
			<h3 class='hndle'><span><?php _e( 'PayPal Express Checkout Settings', 'ppw' ); ?></span></h3>

			<div class="inside">
				<span class="description"><?php _e( 'Express Checkout is PayPal\'s premier checkout solution, which streamlines the checkout process for buyers and keeps them on your site after making a purchase. Unlike PayPal Pro, there are no additional fees to use Express Checkout, though you may need to do a free upgrade to a business account. <a target="_blank" href="https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_ECGettingStarted">More Info &raquo;</a>', 'ppw' ) ?></span>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'PayPal Site', 'ppw' ) ?></th>
						<td>
							<select name="ppw[gateways][paypal-express][locale]">
								<?php
								$base_country = ! empty( $settings['base_country'] ) ? $settings['base_country'] : 'US';
								$sel_locale   = ( ! empty( $settings['gateways'] ) && $settings['gateways']['paypal-express']['locale'] ) ? $settings['gateways']['paypal-express']['locale'] : $base_country;
								$locales      = array(
									'AU' => 'Australia',
									'AT' => 'Austria',
									'BE' => 'Belgium',
									'CA' => 'Canada',
									'CN' => 'China',
									'FR' => 'France',
									'DE' => 'Germany',
									'HK' => 'Hong Kong',
									'IT' => 'Italy',
									'MX' => 'Mexico',
									'NL' => 'Netherlands',
									'PL' => 'Poland',
									'SG' => 'Singapore',
									'ES' => 'Spain',
									'SE' => 'Sweden',
									'CH' => 'Switzerland',
									'TR' => 'Turkey',
									'GB' => 'United Kingdom',
									'US' => 'United States'
								);

								foreach ( $locales as $k => $v ) {
									echo '		<option value="' . $k . '"' . ( $k == $sel_locale ? ' selected' : '' ) . '>' . esc_html( $v, true ) . '</option>' . "\n";
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Paypal Currency', 'ppw' ) ?></th>
						<td>
							<select name="ppw[gateways][paypal-express][currency]">
								<?php
								$currency     = ! empty( $settings['currency'] ) ? $settings['currency'] : 'USD';
								$sel_currency = ( ! empty( $settings['gateways'] ) && $settings['gateways']['paypal-express']['currency'] ) ? $settings['gateways']['paypal-express']['currency'] : $currency;
								$currencies   = array(
									'AUD' => 'AUD - Australian Dollar',
									'BRL' => 'BRL - Brazilian Real',
									'CAD' => 'CAD - Canadian Dollar',
									'CHF' => 'CHF - Swiss Franc',
									'CZK' => 'CZK - Czech Koruna',
									'DKK' => 'DKK - Danish Krone',
									'EUR' => 'EUR - Euro',
									'GBP' => 'GBP - Pound Sterling',
									'ILS' => 'ILS - Israeli Shekel',
									'HKD' => 'HKD - Hong Kong Dollar',
									'HUF' => 'HUF - Hungarian Forint',
									'JPY' => 'JPY - Japanese Yen',
									'MYR' => 'MYR - Malaysian Ringgits',
									'MXN' => 'MXN - Mexican Peso',
									'NOK' => 'NOK - Norwegian Krone',
									'NZD' => 'NZD - New Zealand Dollar',
									'PHP' => 'PHP - Philippine Pesos',
									'PLN' => 'PLN - Polish Zloty',
									'SEK' => 'SEK - Swedish Krona',
									'SGD' => 'SGD - Singapore Dollar',
									'TWD' => 'TWD - Taiwan New Dollars',
									'THB' => 'THB - Thai Baht',
									'TRY' => 'TRY - Turkish lira',
									'USD' => 'USD - U.S. Dollar'
								);

								foreach ( $currencies as $k => $v ) {
									echo '		<option value="' . $k . '"' . ( $k == $sel_currency ? ' selected' : '' ) . '>' . esc_html( $v, true ) . '</option>' . "\n";
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'PayPal Mode', 'ppw' ) ?></th>
						<td>
							<select name="ppw[gateways][paypal-express][mode]">
								<option value="sandbox"<?php selected( @$settings['gateways']['paypal-express']['mode'], 'sandbox' ) ?>><?php _e( 'Sandbox', 'ppw' ) ?></option>
								<option value="live"<?php selected( @$settings['gateways']['paypal-express']['mode'], 'live' ) ?>><?php _e( 'Live', 'ppw' ) ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'PayPal Merchant E-mail', 'ppw' ) ?></th>
						<td>
							<input value="<?php echo esc_attr( @$settings['gateways']['paypal-express']['merchant_email'] ); ?>" size="30" name="ppw[gateways][paypal-express][merchant_email]" type="text"/>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'PayPal API Credentials', 'ppw' ) ?></th>
						<td>
							<span class="description"><?php _e( 'You must login to PayPal and create an API signature to get your credentials. <a target="_blank" href="https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_ECAPICredentials">Instructions &raquo;</a>', 'ppw' ) ?></span>

							<p><label><?php _e( 'API Username', 'ppw' ) ?><br/>
									<input value="<?php echo esc_attr( @$settings['gateways']['paypal-express']['api_user'] ); ?>" size="30" name="ppw[gateways][paypal-express][api_user]" type="text"/>
								</label></p>

							<p><label><?php _e( 'API Password', 'ppw' ) ?><br/>
									<input value="<?php echo esc_attr( @$settings['gateways']['paypal-express']['api_pass'] ); ?>" size="30" name="ppw[gateways][paypal-express][api_pass]" type="text"/>
								</label></p>

							<p><label><?php _e( 'Signature', 'ppw' ) ?><br/>
									<input value="<?php echo esc_attr( @$settings['gateways']['paypal-express']['api_sig'] ); ?>" size="70" name="ppw[gateways][paypal-express][api_sig]" type="text"/>
								</label></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Payment Data Transfer', 'ppw' ) ?></th>
						<td>
							<span class="description"><?php _e( 'PDT allows you to receive instant notification of payments. Turn on Auto Return in order to use Payment Data Transfer.<br />Specify <b>' . admin_url( 'admin-ajax.php?action=ppw_paypal_ipn' ) . '</b> as Return URL.<br />You can configure Auto return settings at <a target="_blank" href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_profile-website-payments">https://www.paypal.com/us/cgi-bin/webscr?cmd=_profile-website-payments</a>', 'ppw' ) ?></span>

							<p><label><?php _e( 'Identity Token', 'ppw' ) ?><br/>
									<input value="<?php echo esc_attr( @$settings['gateways']['paypal-express']['identity_token'] ); ?>" size="30" name="ppw[gateways][paypal-express][identity_token]" type="text"/>
								</label></p>
						</td>
					</tr>

					<tr>
						<th></th>
						<td>
							<a href="javascript:void(0)" id="display_paypal"><?php _e( 'Click for Paypal Page Display Settings', 'ppw' ) ?></a>
						</td>
					</tr>
					<script type="text/javascript">
						jQuery(document).ready(function ($) {
							$("#display_paypal").click(function () {
								$("#display_paypal").hide();
								$(".paypal_details").show();
							});
						});
					</script>
					<style>
						<!--
						.paypal_details {
							display: none;
						}

						-->
					</style>
					<tr class="paypal_details">
						<th scope="row"><?php _e( 'PayPal Header Image (optional)', 'ppw' ) ?></th>
						<td>
							<span class="description"><?php _e( 'URL for an image you want to appear at the top left of the payment page. The image has a maximum size of 750 pixels wide by 90 pixels high. PayPal recommends that you provide an image that is stored on a secure (https) server. If you do not specify an image, the business name is displayed.', 'ppw' ) ?></span>

							<p>
								<input value="<?php echo esc_attr( @$settings['gateways']['paypal-express']['header_img'] ); ?>" size="80" name="ppw[gateways][paypal-express][header_img]" type="text"/>
							</p>
						</td>
					</tr>
					<tr class="paypal_details">
						<th scope="row"><?php _e( 'PayPal Header Border Color (optional)', 'ppw' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Sets the border color around the header of the payment page. The border is a 2-pixel perimeter around the header space, which is 750 pixels wide by 90 pixels high. By default, the color is black.', 'ppw' ) ?></span>

							<p>
								<input value="<?php echo esc_attr( @$settings['gateways']['paypal-express']['header_border'] ); ?>" size="6" maxlength="6" name="ppw[gateways][paypal-express][header_border]" type="text"/>
							</p>
						</td>
					</tr>
					<tr class="paypal_details">
						<th scope="row"><?php _e( 'PayPal Header Background Color (optional)', 'ppw' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Sets the background color for the header of the payment page. By default, the color is white.', 'ppw' ) ?></span>

							<p>
								<input value="<?php echo esc_attr( @$settings['gateways']['paypal-express']['header_back'] ); ?>" size="6" maxlength="6" name="ppw[gateways][paypal-express][header_back]" type="text"/>
							</p>
						</td>
					</tr>
					<tr class="paypal_details">
						<th scope="row"><?php _e( 'PayPal Page Background Color (optional)', 'ppw' ) ?></th>
						<td>
							<span class="description"><?php _e( 'Sets the background color for the payment page. By default, the color is white.', 'ppw' ) ?></span>

							<p>
								<input value="<?php echo esc_attr( @$settings['gateways']['paypal-express']['page_back'] ); ?>" size="6" maxlength="6" name="ppw[gateways][paypal-express][page_back]" type="text"/>
							</p>
						</td>
					</tr>
				</table>
			</div>
		</div>
	<?php
	}


	/**
	 * Filters posted data from your settings form. Do anything you need to the $settings['gateways']['plugin_name']
	 *  array. Don't forget to return!
	 */
	function process_gateway_settings( $settings ) {

		return $settings;
	}

	/**
	 * Use to handle any payment returns from your gateway to the ipn_url. Do not echo anything here. If you encounter errors
	 *  return the proper headers to your ipn sender. Exits after.
	 */
	function process_ipn_return() {
		global $ppw;

		// PayPal IPN handling code
		if ( isset( $_POST['payment_status'] ) || isset( $_POST['txn_type'] ) ) {
			$settings = get_option( 'ppw_options' );

			if ( $settings['gateways']['paypal-express']['mode'] == 'sandbox' ) {
				$domain = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			} else {
				$domain = 'https://www.paypal.com/cgi-bin/webscr';
			}

			$req = 'cmd=_notify-validate';
			if ( ! isset( $_POST ) ) {
				$_POST = $HTTP_POST_VARS;
			}
			foreach ( $_POST as $k => $v ) {
				if ( get_magic_quotes_gpc() ) {
					$v = stripslashes( $v );
				}
				$req .= '&' . $k . '=' . urlencode( $v );
			}

			$args['user-agent'] = "MarketPress/{$mp->version}: http://premium.wpmudev.org/project/e-commerce | PayPal Express Plugin/{$mp->version}";
			$args['body']       = $req;
			$args['sslverify']  = false;
			$args['timeout']    = 30;

			//use built in WP http class to work with most server setups
			$response = wp_remote_post( $domain, $args );

			//check results
			if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 || $response['body'] != 'VERIFIED' ) {
				header( "HTTP/1.1 503 Service Unavailable" );
				_e( 'There was a problem verifying the IPN string with PayPal. Please try again.', 'ppw' );
				exit;
			}

			// process PayPal response
			switch ( $_POST['payment_status'] ) {

				case 'Canceled-Reversal':
					$status = __( 'A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.', 'ppw' );
					$paid   = true;
					break;

				case 'Expired':
					$status = __( 'The authorization period for this payment has been reached.', 'ppw' );
					$paid   = false;
					break;

				case 'Voided':
					$status = __( 'An authorization for this transaction has been voided.', 'ppw' );
					$paid   = false;
					break;

				case 'Failed':
					$status = __( "The payment has failed. This happens only if the payment was made from your customer's bank account.", 'ppw' );
					$paid   = false;
					break;

				case 'Partially-Refunded':
					$status = __( 'The payment has been partially refunded.', 'ppw' );
					$paid   = true;
					break;

				case 'In-Progress':
					$status = __( 'The transaction has not terminated, e.g. an authorization may be awaiting completion.', 'ppw' );
					$paid   = false;
					break;

				case 'Completed':
					$status = __( 'The payment has been completed, and the funds have been added successfully to your account balance.', 'ppw' );
					$paid   = true;
					break;

				case 'Processed':
					$status = __( 'A payment has been accepted.', 'ppw' );
					$paid   = true;
					break;

				case 'Reversed':
					$status          = __( 'A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer:', 'ppw' );
					$reverse_reasons = array(
						'none'            => '',
						'chargeback'      => __( 'A reversal has occurred on this transaction due to a chargeback by your customer.', 'ppw' ),
						'guarantee'       => __( 'A reversal has occurred on this transaction due to your customer triggering a money-back guarantee.', 'ppw' ),
						'buyer-complaint' => __( 'A reversal has occurred on this transaction due to a complaint about the transaction from your customer.', 'ppw' ),
						'refund'          => __( 'A reversal has occurred on this transaction because you have given the customer a refund.', 'ppw' ),
						'other'           => __( 'A reversal has occurred on this transaction due to an unknown reason.', 'ppw' )
					);
					$status .= '<br />' . $reverse_reasons[ $result["PAYMENTINFO_0_REASONCODE"] ];
					$paid = false;
					break;

				case 'Refunded':
					$status = __( 'You refunded the payment.', 'ppw' );
					$paid   = false;
					break;

				case 'Denied':
					$status = __( 'You denied the payment when it was marked as pending.', 'ppw' );
					$paid   = false;
					break;

				case 'Pending':
					$pending_str = array(
						'address'        => __( 'The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences  section of your Profile.', 'ppw' ),
						'authorization'  => __( 'The payment is pending because it has been authorized but not settled. You must capture the funds first.', 'ppw' ),
						'echeck'         => __( 'The payment is pending because it was made by an eCheck that has not yet cleared.', 'ppw' ),
						'intl'           => __( 'The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'ppw' ),
						'multi-currency' => __( 'You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'ppw' ),
						'order'          => __( 'The payment is pending because it is part of an order that has been authorized but not settled.', 'ppw' ),
						'paymentreview'  => __( 'The payment is pending while it is being reviewed by PayPal for risk.', 'ppw' ),
						'unilateral'     => __( 'The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'ppw' ),
						'upgrade'        => __( 'The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. It can also mean that you have reached the monthly limit for transactions on your account.', 'ppw' ),
						'verify'         => __( 'The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'ppw' ),
						'other'          => __( 'The payment is pending for an unknown reason. For more information, contact PayPal customer service.', 'ppw' ),
						'*'              => ''
					);
					$status      = __( 'The payment is pending.', 'ppw' );
					$status .= '<br />' . $pending_str[ $_POST["pending_reason"] ];
					$paid = false;
					break;

				default:
					// case: various error cases
			}
			$status = $_POST['payment_status'] . ': ' . $status;

			//record transaction
			$mp->update_order_payment_status( $_POST['invoice'], $status, $paid );

		} else {
			// Did not find expected POST variables. Possible access attempt from a non PayPal site.
			header( 'Status: 404 Not Found' );
			echo 'Error: Missing POST variables. Identification is not possible.';
			exit;
		}
	}

	/**** PayPal API methods *****/


	//Purpose: 	Prepares the parameters for the SetExpressCheckout API Call.
	function SetExpressCheckout( $global_cart ) {

		$settings = get_option( 'ppw_options' );

		$nvpstr = "";
		$nvpstr .= "&ReturnUrl=" . $this->returnURL;
		$nvpstr .= "&CANCELURL=" . $this->cancelURL;
		$nvpstr .= "&NOSHIPPING=1";
		$nvpstr .= "&LANDINGPAGE=Billing";
		$nvpstr .= "&SOLUTIONTYPE=Sole";
		$nvpstr .= "&LOCALECODE=" . $this->locale;

		//formatting
		$nvpstr .= "&HDRIMG=" . urlencode( $settings['gateways']['paypal-express']['header_img'] );
		$nvpstr .= "&HDRBORDERCOLOR=" . urlencode( $settings['gateways']['paypal-express']['header_border'] );
		$nvpstr .= "&HDRBACKCOLOR=" . urlencode( $settings['gateways']['paypal-express']['header_back'] );
		$nvpstr .= "&PAYFLOWCOLOR=" . urlencode( $settings['gateways']['paypal-express']['page_back'] );

		$nvpstr .= "&PAYMENTREQUEST_0_PAYMENTACTION=Sale";
		$nvpstr .= "&PAYMENTREQUEST_0_CURRENCYCODE=" . $this->currencyCode; // Fix in V1.2.1
		$post = get_post( $this->post_id );
		$nvpstr .= "&PAYMENTREQUEST_0_DESC=" . urlencode( substr( wp_trim_words( remove_accents( $post->post_title ), 10, '' ), 0, 120 ) );
		// Item details
		$nvpstr .= "&L_PAYMENTREQUEST_0_NAME0=" . urlencode( substr( wp_trim_words( remove_accents( $post->post_title ), 10, '' ), 0, 120 ) );
		$nvpstr .= "&L_PAYMENTREQUEST_0_AMT0=" . $_SESSION["ppw_total_amt"];
		$nvpstr .= "&L_PAYMENTREQUEST_0_QTY0=1";
		// Item Total
		$nvpstr .= "&PAYMENTREQUEST_ITEMAMT=" . $_SESSION["ppw_total_amt"];
		// Grand Total
		$nvpstr .= "&PAYMENTREQUEST_0_AMT=" . $_SESSION["ppw_total_amt"];
		$nvpstr .= "&PAYMENTREQUEST_0_QTY=1";

		//'---------------------------------------------------------------------------------------------------------------
		//' Make the API call to PayPal
		//' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.
		//' If an error occured, show the resulting errors
		//'---------------------------------------------------------------------------------------------------------------
		$resArray = $this->api_call( "SetExpressCheckout", $nvpstr );
		$ack      = strtoupper( $resArray["ACK"] );
		if ( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" ) {
			$token             = urldecode( $resArray["TOKEN"] );
			$_SESSION['TOKEN'] = $token;
		}

		return $resArray;
	}

	//Purpose: 	Prepares the parameters for the GetExpressCheckoutDetails API Call.
	function GetExpressCheckoutDetails( $token ) {
		//'--------------------------------------------------------------
		//' At this point, the buyer has completed authorizing the payment
		//' at PayPal.  The function will call PayPal to obtain the details
		//' of the authorization, incuding any shipping information of the
		//' buyer.  Remember, the authorization is not a completed transaction
		//' at this state - the buyer still needs an additional step to finalize
		//' the transaction
		//'--------------------------------------------------------------

		//'---------------------------------------------------------------------------
		//' Build a second API request to PayPal, using the token as the
		//'  ID to get the details on the payment authorization
		//'---------------------------------------------------------------------------
		$nvpstr = "&TOKEN=" . $token;

		//'---------------------------------------------------------------------------
		//' Make the API call and store the results in an array.
		//'	If the call was a success, show the authorization details, and provide
		//' 	an action to complete the payment.
		//'	If failed, show the error
		//'---------------------------------------------------------------------------
		$resArray = $this->api_call( "GetExpressCheckoutDetails", $nvpstr );
		$ack      = strtoupper( $resArray["ACK"] );
		if ( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" ) {
			$_SESSION['payer_id'] = $resArray['PAYERID'];
		}

		return $resArray;
	}


	//Purpose: 	Prepares the parameters for the DoExpressCheckoutPayment API Call.
	function DoExpressCheckoutPayment( $token, $payer_id, $final_amts, $seller_paypal_accounts, $ipns, $prs ) {
		$nvpstr = '&TOKEN=' . urlencode( $token );
		$nvpstr .= '&PAYERID=' . urlencode( $payer_id );
		$nvpstr .= '&BUTTONSOURCE=incsub_SP';

		$nvpstr .= "&PAYMENTREQUEST_0_AMT=" . $final_amts[0];
		$nvpstr .= "&PAYMENTREQUEST_0_CURRENCYCODE=" . $this->currencyCode;
		$nvpstr .= "&PAYMENTREQUEST_0_PAYMENTACTION=" . $this->payment_action;
		$nvpstr .= "&PAYMENTREQUEST_0_NOTIFYURL=" . $ipns[0];
		$nvpstr .= "&PAYMENTREQUEST_0_SELLERPAYPALACCOUNTID=" . $seller_paypal_accounts[0];
		$nvpstr .= "&PAYMENTREQUEST_0_PAYMENTREQUESTID=" . $prs[0];

		/* Make the call to PayPal to finalize payment
		*/

		return $this->api_call( "DoExpressCheckoutPayment", $nvpstr );
	}

	//Purpose: 	Prepares the parameters for the DoAuthorization API Call.
	function DoAuthorization( $transaction_id, $final_amt ) {

		$nvpstr = '&TRANSACTIONID=' . urlencode( $transaction_id );
		$nvpstr .= '&AMT=' . $final_amt;
		$nvpstr .= '&TRANSACTIONENTITY=Order';
		$nvpstr .= '&CURRENCYCODE=' . $this->currencyCode;

		/* Make the call to PayPal to finalize payment
		*/

		return $this->api_call( "DoAuthorization", $nvpstr );
	}

	//Purpose: 	Prepares the parameters for the DoCapture API Call.
	function DoCapture( $transaction_id, $final_amt ) {

		$nvpstr = '&AUTHORIZATIONID=' . urlencode( $transaction_id );
		$nvpstr .= '&AMT=' . $final_amt;
		$nvpstr .= '&CURRENCYCODE=' . $this->currencyCode;
		$nvpstr .= '&COMPLETETYPE=Complete';

		/* Make the call to PayPal to finalize payment
		*/

		return $this->api_call( "DoCapture", $nvpstr );
	}

	/**
	 * '-------------------------------------------------------------------------------------------------------------------------------------------
	 * $this->api_call: Function to perform the API call to PayPal using API signature
	 * @methodName is name of API  method.
	 * @nvpStr is nvp string.
	 * returns an associtive array containing the response from the server.
	 * '-------------------------------------------------------------------------------------------------------------------------------------------
	 */
	function api_call( $methodName, $nvpStr ) {
		global $ppw;

		//NVPRequest for submitting to server
		$query_string = "METHOD=" . urlencode( $methodName ) . "&VERSION=" . urlencode( $this->version ) . "&PWD=" . urlencode( $this->API_Password ) . "&USER=" . urlencode( $this->API_Username ) . "&SIGNATURE=" . urlencode( $this->API_Signature ) . $nvpStr;
		//build args
		$args['user-agent']  = "Pay Per View: http://premium.wpmudev.org/project/pay-per-view | PayPal Express Plugin";
		$args['body']        = $query_string;
		$args['sslverify']   = false;
		$args['timeout']     = 60;
		$args['httpversion'] = '1.1';

		//use built in WP http class to work with most server setups
		$response = wp_remote_post( $this->API_Endpoint, $args );
		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			$this->cart_checkout_error( __( 'There was a problem connecting to PayPal. Please try again.', 'ppw' ) );

			return false;
		} else {
			//convert NVPResponse to an Associative Array
			$nvpResArray = $this->deformatNVP( $response['body'] );

			return $nvpResArray;
		}
	}

	/*'----------------------------------------------------------------------------------
	Purpose: Redirects to PayPal.com site.
	Inputs:  NVP string.
	Returns:
	----------------------------------------------------------------------------------
	*/
	function RedirectToPayPal( $token ) {
		// Redirect to paypal.com here
		$payPalURL = $this->paypalURL . $token;
		wp_redirect( $payPalURL );
		exit;
	}


	//This function will take NVPString and convert it to an Associative Array and it will decode the response.
	function deformatNVP( $nvpstr ) {
		parse_str( $nvpstr, $nvpArray );

		return $nvpArray;
	}

	function trim_name( $name, $length = 127 ) {
		while ( strlen( urlencode( $name ) ) > $length ) {
			$name = substr( $name, 0, - 1 );
		}

		return urlencode( $name );
	}

	/**
	 * Send a get request to Paypal with Transaction id and auth, to get the last transaction details
	 * @param $tx
	 * @param $auth
	 *
	 * @return array|bool|WP_Error
	 */
	function pdt_get_transaction_details( $tx, $auth ) {
		if ( empty( $tx ) || empty( $auth ) ) {
			return false;
		}
		$settings = get_option( 'ppw_options' );
		$cmd      = '_notify-synch';

		if ( $settings['gateways']['paypal-express']['mode'] == 'sandbox' ) {
			$domain = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		} else {
			$domain = 'https://www.paypal.com/cgi-bin/webscr';
		}
		$args     = array(
			'tx'  => $tx,
			'at'  => $auth,
			'cmd' => $cmd
		);
		$domain = add_query_arg(
			$args,
			$domain
		);
		$response = wp_remote_post( $domain, $args );

		//Check if we successfully retrieved the transaction details
		if( !empty( $response['response'] )  && !empty( $response['response']['code'] ) && $response['response']['code'] == '200' ) {
			$response = wp_remote_retrieve_body( $response );
		}else{
			$response = sprintf( __( "Transaction Id: %d <br/ >Something went wrong with PayPal, while trying to fetch the payment information, please refresh the page in a while to check if the payment was successful. ", 'ppw' ), $tx );
		}

		return $response;
	}
}