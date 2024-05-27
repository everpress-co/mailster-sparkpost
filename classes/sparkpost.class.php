<?php

class MailsterSparkPost {

	private $plugin_path;
	private $plugin_url;
	private $apikey;
	private $subaccount;

	/**
	 *
	 */
	public function __construct() {

		$this->plugin_path = plugin_dir_path( MAILSTER_SPARKPOST_FILE );
		$this->plugin_url  = plugin_dir_url( MAILSTER_SPARKPOST_FILE );

		register_activation_hook( MAILSTER_SPARKPOST_FILE, array( &$this, 'activate' ) );
		register_deactivation_hook( MAILSTER_SPARKPOST_FILE, array( &$this, 'deactivate' ) );

		load_plugin_textdomain( 'mailster-sparkpost' );

		add_action( 'init', array( &$this, 'init' ), 1 );
	}


	/*
	 * init the plugin
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		if ( ! function_exists( 'mailster' ) ) {

			add_action( 'admin_notices', array( &$this, 'notice' ) );

		} else {

			add_filter( 'mailster_delivery_methods', array( &$this, 'delivery_method' ) );
			add_action( 'mailster_deliverymethod_tab_sparkpost', array( &$this, 'deliverytab' ) );

			add_filter( 'mailster_verify_options', array( &$this, 'verify_options' ) );

			if ( mailster_option( 'deliverymethod' ) == 'sparkpost' ) {
				add_action( 'mailster_initsend', array( &$this, 'initsend' ) );
				add_action( 'mailster_presend', array( &$this, 'presend' ) );
				add_action( 'mailster_dosend', array( &$this, 'dosend' ) );
				add_action( 'mailster_cron_worker', array( &$this, 'check_bounces' ), -1 );
				add_action( 'mailster_check_bounces', array( &$this, 'check_bounces' ) );
				add_action( 'mailster_section_tab_bounce', array( &$this, 'section_tab_bounce' ) );
				add_filter( 'mailster_subscriber_errors', array( $this, 'subscriber_errors' ) );
			}
		}
	}


	/**
	 * initsend function.
	 *
	 * uses mailster_initsend hook to set initial settings
	 *
	 * @access public
	 * @return void
	 * @param mixed $mailobject
	 */
	public function initsend( $mailobject ) {

		$method = mailster_option( 'sparkpost_api' );

		if ( $method == 'smtp' ) {

			$username = 'SMTP_Injection';
			if ( $subaccount = mailster_option( 'sparkpost_subaccount' ) ) {
				$username .= ':X-MSYS-SUBACCOUNT=' . $subaccount;
			}

			if ( 'eu' == mailster_option( 'sparkpost_endpoint' ) ) {
				$mailobject->mailer->Host = 'smtp.eu.sparkpostmail.com';
			} else {
				$mailobject->mailer->Host = 'smtp.sparkpostmail.com';
			}

			$mailobject->mailer->Mailer        = 'smtp';
			$mailobject->mailer->SMTPSecure    = 'tls';
			$mailobject->mailer->Port          = mailster_option( 'sparkpost_port' );
			$mailobject->mailer->SMTPAuth      = 'LOGIN';
			$mailobject->mailer->Username      = $username;
			$mailobject->mailer->Password      = mailster_option( 'sparkpost_apikey' );
			$mailobject->mailer->SMTPKeepAlive = true;

		} elseif ( $method == 'web' ) {

		}

		// SparkPost will handle DKIM integration
		$mailobject->dkim = false;
	}


	/**
	 * presend function.
	 *
	 * uses the mailster_presend hook to apply settings before each mail
	 *
	 * @access public
	 * @return void
	 * @param mixed $mailobject
	 */
	public function presend( $mailobject ) {

		$method = mailster_option( 'sparkpost_api' );

		$mailobject->pre_send();

		$mailobject->sparkpost_object = array(
			'options'     => array(
				'transactional' => ! empty( $mailobject->campaignID ),
				'ip_pool'       => 'sp_shared',
				'inline_css'    => false,
			),
			'metadata'    => array(
				'mailster_id'   => mailster_option( 'ID' ),
				'campaign_id'   => $mailobject->campaignID,
				'index'         => $mailobject->index,
				'subscriber_id' => $mailobject->subscriberID,
				'message_id'    => $mailobject->messageID,
			),
			'campaign_id' => $mailobject->campaignID ? substr( '#' . $mailobject->campaignID . ' ' . esc_attr( get_the_title( $mailobject->campaignID ) ), 0, 64 ) : null,
		);

		// do not track in test campaigns
		if ( '00000000000000000000000000000000' == $mailobject->hash ) {
			$mailobject->sparkpost_object['options']['open_tracking']  = false;
			$mailobject->sparkpost_object['options']['click_tracking'] = false;
		} elseif ( $tracking_options = mailster_option( 'sparkpost_track' ) ) {
			$open_tracking  = 'opens' == $tracking_options || 'opens,clicks' == $tracking_options;
			$click_tracking = 'clicks' == $tracking_options || 'opens,clicks' == $tracking_options;
			$mailobject->sparkpost_object['options']['open_tracking']  = $open_tracking;
			$mailobject->sparkpost_object['options']['click_tracking'] = $click_tracking;
		}

		if ( $tags = mailster_option( 'sparkpost_tags' ) ) {
			$mailobject->sparkpost_object['tags'] = array_map( 'trim', explode( ',', $tags ) );
		}

		if ( $ip_pool = mailster_option( 'sparkpost_ip_pool' ) ) {
			$mailobject->sparkpost_object['options']['ip_pool'] = $ip_pool;
		}

		if ( $method == 'smtp' ) {

		} elseif ( $method == 'web' ) {

			$recipients = array();

			foreach ( $mailobject->to as $i => $to ) {
				$recipients[] = array(
					'address' => array(
						'name'  => $mailobject->to_name[ $i ] ? $mailobject->to_name[ $i ] : null,
						'email' => $mailobject->to[ $i ] ? $mailobject->to[ $i ] : null,
					),
				);

			}

			$reply_to = is_array( $mailobject->reply_to ) ? reset( $mailobject->reply_to ) : $mailobject->reply_to;

			if ( isset( $mailobject->headers['X-Mailster-Campaign'] ) ) {
				$mailobject->headers['X-Mailster-Campaign'] = (string) $mailobject->headers['X-Mailster-Campaign'];
			}

			$mailobject->sparkpost_object['recipients'] = $recipients;
			$mailobject->sparkpost_object['content']    = array(
				'from'     => array(
					'name'  => $mailobject->from_name,
					'email' => $mailobject->from,
				),
				'subject'  => $mailobject->subject ? $mailobject->subject : '[' . __( 'no subject', 'mailster-sparkpost' ) . ']',
				'reply_to' => $reply_to,
				'text'     => $mailobject->mailer->AltBody,
				'html'     => $mailobject->mailer->Body,
				'headers'  => $mailobject->headers,
			);

			if ( $mailobject->embed_images ) {

				$org_attachments = $mailobject->mailer->getAttachments();
				$inline_images   = array();

				foreach ( $org_attachments as $attachment ) {

					if ( 'inline' != $attachment[6] ) {
						continue;
					}
					$inline_images[ $attachment[7] ] = array(
						'type' => $attachment[4],
						'name' => $attachment[7],
						'data' => base64_encode( file_get_contents( $attachment[0] ) ),
					);
				}

				if ( ! empty( $inline_images ) ) {
					$mailobject->sparkpost_object['content']['inline_images'] = array_values( $inline_images );
				}
			}

			if ( ! empty( $mailobject->attachments ) ) {

				$org_attachments = $mailobject->mailer->getAttachments();
				$attachments     = array();

				foreach ( $org_attachments as $attachment ) {

					if ( 'attachment' != $attachment[6] ) {
						continue;
					}
					$attachments[] = array(
						'type' => $attachment[4],
						'name' => $attachment[2],
						'data' => base64_encode( file_get_contents( $attachment[0] ) ),
					);
				}

				if ( ! empty( $attachments ) ) {
					$mailobject->sparkpost_object['content']['attachments'] = $attachments;
				}
			}
		}

		$mailobject->sparkpost_object = apply_filters( 'mailster_sparkpost_object', $mailobject->sparkpost_object, $mailobject );
		if ( $method == 'smtp' ) {
			$mailobject->mailer->addCustomHeader( 'X-MSYS-API: ' . json_encode( $mailobject->sparkpost_object ) );
		}
	}


	/**
	 * dosend function.
	 *
	 * uses the mailster_dosend hook and triggers the send
	 *
	 * @access public
	 * @param mixed $mailobject
	 * @return void
	 */
	public function dosend( $mailobject ) {

		$method = mailster_option( 'sparkpost_api' );

		if ( $method == 'smtp' ) {

			// use send from the main class
			$mailobject->do_send();

		} elseif ( $method == 'web' ) {

			if ( ! isset( $mailobject->sparkpost_object ) ) {
				$mailobject->set_error( __( 'SparkPost options not defined', 'mailster-sparkpost' ) );
				$mailobject->sent = false;
				return false;
			}

			$response = $this->do_post( 'transmissions', $mailobject->sparkpost_object, 60 );

			if ( is_wp_error( $response ) ) {
				$code = $response->get_error_code();
				if ( 403 == $code ) {
					$errormessage = __( 'Not able to send message. Make sure your API Key is allowed to read and write Transmissions!', 'mailster-sparkpost' );
				} else {
					$errormessage = $response->get_error_message();
				}
				$mailobject->set_error( $errormessage );
				$mailobject->sent = false;
			} elseif ( $response->results->total_rejected_recipients ) {
					$mailobject->set_error( sprintf( __( 'SparkPost rejected the following receivers: %s', 'mailster-sparkpost' ), implode( ', ', $mailobject->to ) ) );
					$mailobject->sent = false;
			} else {
				$mailobject->sent = true;
			}
		}
	}



	/**
	 * delivery_method function.
	 *
	 * add the delivery method to the options
	 *
	 * @access public
	 * @param mixed $delivery_methods
	 * @return void
	 */
	public function delivery_method( $delivery_methods ) {
		$delivery_methods['sparkpost'] = 'SparkPost';
		return $delivery_methods;
	}


	/**
	 * deliverytab function.
	 *
	 * the content of the tab for the options
	 *
	 * @access public
	 * @return void
	 */
	public function deliverytab() {

		$verified = mailster_option( 'sparkpost_verified' );

		include $this->plugin_path . '/views/settings.php';
	}


	public function do_get( $endpoint, $args = array(), $timeout = 15 ) {
		return $this->do_call( 'GET', $endpoint, $args, $timeout );
	}
	public function do_post( $endpoint, $args = array(), $timeout = 15 ) {
		return $this->do_call( 'POST', $endpoint, $args, $timeout );
	}


	/**
	 *
	 * @access public
	 * @param unknown $apikey  (optional)
	 * @return void
	 */
	private function do_call( $method, $endpoint, $args = array(), $timeout = 15 ) {

		if ( 'eu' == mailster_option( 'sparkpost_endpoint' ) ) {
			$url = 'https://api.eu.sparkpost.com/api/v1/' . $endpoint;
		} else {
			$url = 'https://api.sparkpost.com/api/v1/' . $endpoint;
		}

		$args       = wp_parse_args( $args, array() );
		$body       = null;
		$apikey     = isset( $this->apikey ) ? $this->apikey : mailster_option( 'sparkpost_apikey' );
		$subaccount = isset( $this->subaccount ) ? $this->subaccount : mailster_option( 'sparkpost_subaccount', 0 );

		if ( 'GET' == $method ) {
			$url = add_query_arg( $args, $url );
		} elseif ( 'POST' == $method ) {
			$body = json_encode( $args );
		} else {
			return new WP_Error( 'method_not_allowed', 'This method is not allowed' );
		}

		$headers = array(
			'Authorization' => $apikey,
			'Accept'        => 'application/json',
		);

		if ( $subaccount && 'subaccounts' != $endpoint ) {
			$headers['X-MSYS-SUBACCOUNT'] = $subaccount;
		}

		$response = wp_remote_request(
			$url,
			array(
				'method'  => $method,
				'headers' => $headers,
				'timeout' => $timeout,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 200 != $code ) {

			$errormessage = $body->errors[0]->message;
			if ( isset( $body->errors[0]->description ) ) {
				$errormessage .= ' ' . $body->errors[0]->description;
			}

			return new WP_Error( $code, $errormessage );

		}

		return $body;
	}


	/**
	 *
	 * @access public
	 * @return void
	 */
	public function verify( $apikey = null, $subaccount = null ) {

		if ( ! is_null( $apikey ) ) {
			$this->apikey = $apikey;
		}
		if ( ! is_null( $subaccount ) ) {
			$this->subaccount = $subaccount;
		}
		$response = $this->do_get( 'account', 'include=usage' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return $response;
	}



	/**
	 *
	 * @access public
	 * @return void
	 */
	public function get_sending_domains() {

		$response = $this->do_get( 'sending-domains' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$domains = $response->results;

		return $domains;
	}

	/**
	 *
	 * @access public
	 * @return void
	 */
	public function get_subaccounts() {

		$response = $this->do_get( 'subaccounts' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$accounts = $response->results;

		return $accounts;
	}



	/**
	 *
	 * @access public
	 * @param mixed $options
	 * @return void
	 */
	public function verify_options( $options ) {

		if ( $options['deliverymethod'] == 'sparkpost' ) {

			$old_apikey          = mailster_option( 'sparkpost_apikey' );
			$old_delivery_method = mailster_option( 'deliverymethod' );

			if ( $old_apikey != $options['sparkpost_apikey'] || ! $options['sparkpost_verified'] || $old_delivery_method != 'sparkpost' ) {
				$response = $this->verify( $options['sparkpost_apikey'], $options['sparkpost_subaccount'] );

				if ( is_wp_error( $response ) ) {
					$options['sparkpost_verified'] = false;
					add_settings_error( 'mailster_options', 'mailster_options', __( 'Not able to get Account details. Make sure your API Key is correct and allowed to read Account details!', 'mailster-sparkpost' ) );
				} else {

					$usage = $response->results->usage;

					$options['send_limit'] = $usage->day->limit;

					update_option( '_transient__mailster_send_period', $usage->day->used );

					$options['sparkpost_verified'] = true;
				}

				if ( isset( $options['sparkpost_api'] ) && $options['sparkpost_api'] == 'smtp' ) {
					if ( function_exists( 'fsockopen' ) ) {
						$host = 'smtp.sparkpostmail.com';
						$port = $options['sparkpost_port'];
						$conn = fsockopen( $host, $port, $errno, $errstr, 15 );

						if ( is_resource( $conn ) ) {

							fclose( $conn );

						} else {

							add_settings_error( 'mailster_options', 'mailster_options', sprintf( __( 'Not able to use SparkPost with SMTP API cause of the blocked port %s! Please send with the WEB API, use a different port or choose a different delivery method!', 'mailster-sparkpost' ), $port ) );

						}
					}
				} else {

				}
			}
		}

		return $options;
	}


	/**
	 * check_bounces function.
	 *
	 * @access public
	 * @return void
	 */
	public function check_bounces() {

		if ( get_transient( 'mailster_check_bounces_lock' ) || ! mailster_option( 'sparkpost_verified' ) ) {
			return false;
		}

		// round as the SparkPost API only accepts minutes values
		$now = floor( time() / 60 ) * 60;

		if ( ! ( $last_bounce_check = get_transient( '_mailster_sparkpost_last_bounce_check' ) ) ) {
			set_transient( '_mailster_sparkpost_last_bounce_check', $now );
			$last_bounce_check = $now;
		}

		// SparkPost has some delay when adding bounces so we have to get messages from an earlier time
		$sparkpost_offset = MINUTE_IN_SECONDS;

		$args = array(
			'from'   => date( 'Y-m-d\TH:i', $last_bounce_check - $sparkpost_offset ),
			'to'     => date( 'Y-m-d\TH:i', $last_bounce_check + $sparkpost_offset ),
			'events' => 'bounce,delay,policy_rejection,generation_failure,generation_rejection,spam_complaint,list_unsubscribe,link_unsubscribe,out_of_band',
		);

		$response = $this->do_get( 'events/message', $args, 30 );

		if ( is_wp_error( $response ) ) {
			mailster_notice( sprintf( __( 'Not able to check bounces via SparkPost: %s', 'mailster-sparkpost' ), $response->get_error_message() ), 'error', false, 'mailster_sparkpost_bounce_error' );
			return;
		} else {
			mailster_remove_notice( 'mailster_sparkpost_bounce_error' );
		}

		$MID = mailster_option( 'ID' );

		foreach ( $response->results as $result ) {

			if ( ! isset( $result->rcpt_meta->mailster_id ) || $result->rcpt_meta->mailster_id != $MID ) {
				continue;
			}

			if ( isset( $result->rcpt_meta->subscriber_id ) ) {
				$subscriber = mailster( 'subscribers' )->get( $result->rcpt_meta->subscriber_id );
			} else {
				$subscriber = mailster( 'subscribers' )->get_by_mail( $result->rcpt_to );
			}
			if ( ! $subscriber ) {
				continue;
			}
			if ( isset( $result->rcpt_meta->campaign_id ) ) {
				$campaign_id = $result->rcpt_meta->campaign_id;
			} elseif ( preg_match( '/^#(\d+)/', $result->campaign_id, $campaign ) ) {
				$campaign_id = $campaign[1];
			} else {
				$campaign_id = null;
			}
			if ( isset( $result->rcpt_meta->index ) ) {
				$campaign_index = $result->rcpt_meta->index;
			} else {
				$campaign_index = null;
			}

			$is_hard_bounce = true;

			switch ( $result->type ) {
				case 'out_of_band':
					$is_hard_bounce = false;
				case 'bounce':
				case 'generation_failure':
				case 'generation_rejection':
				case 'policy_rejection':
					if ( version_compare( MAILSTER_VERSION, '3.0', '<' ) ) {
						mailster( 'subscribers' )->bounce( $subscriber->ID, $campaign_id, $is_hard_bounce, $status );
					} else {
						mailster( 'subscribers' )->bounce( $subscriber->ID, $campaign_id, $is_hard_bounce, $status, $campaign_index );
					}
					break;
				case 'list_unsubscribe':
				case 'link_unsubscribe':
				case 'spam_complaint':
					if ( version_compare( MAILSTER_VERSION, '3.0', '<' ) ) {
						mailster( 'subscribers' )->bounce( $subscriber->ID, $campaign_id, true, $result->type );
					} else {
						mailster( 'subscribers' )->bounce( $subscriber->ID, $campaign_id, true, $result->type, $campaign_index );
					}
					break;
				case 'delay':
					// soft bounces are handled by SparkPost
					break;
				default:
					break;
			}
		}

		set_transient( '_mailster_sparkpost_last_bounce_check', $now );
	}


	public function subscriber_errors( $errors ) {
		$errors[] = 'Message generation rejected';
		return $errors;
	}


	/**
	 * section_tab_bounce function.
	 *
	 * displays a note on the bounce tab
	 *
	 * @access public
	 * @param mixed $options
	 * @return void
	 */
	public function section_tab_bounce() {

		?>
		<div class="error inline"><p><strong><?php _e( 'Bouncing is handled by SparkPost so all your settings will be ignored', 'mailster-sparkpost' ); ?></strong></p></div>

		<?php
	}



	/**
	 * Notice if Mailster is not available
	 *
	 * @access public
	 * @return void
	 */
	public function notice() {
		?>
	<div id="message" class="error">
		<p>
		<strong>SparkPost integration for Mailster</strong> requires the <a href="https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=plugin&utm_term=SparkPost">Mailster Newsletter Plugin</a>, at least version <strong><?php echo MAILSTER_SPARKPOST_REQUIRED_VERSION; ?></strong>.
		</p>
	</div>
		<?php
	}



	/**
	 * activate function
	 *
	 * @access public
	 * @return void
	 */
	public function activate() {

		if ( function_exists( 'mailster' ) ) {

			mailster_notice( sprintf( __( 'Change the delivery method on the %s!', 'mailster-sparkpost' ), '<a href="edit.php?post_type=newsletter&page=mailster_settings&mailster_remove_notice=delivery_method#delivery">Settings Page</a>' ), '', 360, 'delivery_method' );

			$defaults = array(
				'sparkpost_apikey'     => null,
				'sparkpost_subaccount' => null,
				'sparkpost_api'        => 'web',
				'sparkpost_port'       => 587,
				'sparkpost_track'      => 0,
				'sparkpost_tags'       => '',
				'sparkpost_verified'   => false,
			);

			$mailster_options = mailster_options();

			foreach ( $defaults as $key => $value ) {
				if ( ! isset( $mailster_options[ $key ] ) ) {
					mailster_update_option( $key, $value );
				}
			}
		}
	}


	/**
	 * deactivate function
	 *
	 * @access public
	 * @return void
	 */
	public function deactivate() {

		if ( function_exists( 'mailster' ) ) {
			if ( mailster_option( 'deliverymethod' ) == 'sparkpost' ) {
				mailster_update_option( 'deliverymethod', 'simple' );
				mailster_notice( sprintf( __( 'Change the delivery method on the %s!', 'mailster-sparkpost' ), '<a href="edit.php?post_type=newsletter&page=mailster_settings&mailster_remove_notice=delivery_method#delivery">Settings Page</a>' ), '', 360, 'delivery_method' );
			}
		}
	}
}
