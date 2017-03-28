<?php
/**
 * Remote module.
 * Manages all remote access from Hub to the local WordPress site;
 *
 * @since   4.3.0
 * @package WPMUDEV_Dashboard
 */

/**
 * The remote-module class.
 */
class WPMUDEV_Dashboard_Remote {

	/**
	 * Stores registered remote access actions and their callbacks.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Set up the Remote module. Here we load and initialize the settings.
	 *
	 * @internal
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'run_request' ) );
	}

	/**
	 * Check signature hash of
	 *
	 * @since  4.0.0
	 *
	 * @param  string $req_id         The request id as passed by Hub
	 * @param  string $json           The full json body that hash was created on
	 * @param  bool   $die_on_failure If set to false the function returns a bool.
	 *
	 * @return bool    True on success.
	 */
	public function validate_hash( $req_id, $json, $die_on_failure = true ) {
		if ( defined( 'WPMUDEV_IS_REMOTE' ) && ! WPMUDEV_IS_REMOTE ) {
			if ( $die_on_failure ) {
				wp_send_json_error(
					array( 'message' => 'Remote calls are disabled in wp-config.php' )
				);
			} else {
				return false;
			}
		}

		if ( empty( $_SERVER['HTTP_WDP_AUTH'] ) ) {
			if ( $die_on_failure ) {
				wp_send_json_error(
					array( 'message' => 'Missing authentication header' )
				);
			} else {
				return false;
			}
		}

		$hash    = $_SERVER['HTTP_WDP_AUTH'];
		$api_key = WPMUDEV_Dashboard::$api->get_key();

		$hash_string = $req_id . $json;

		$valid = hash_hmac( 'sha256', $hash_string, $api_key );

		$is_valid = hash_equals( $valid, $hash ); //Timing attack safe string comparison, PHP <5.6 compat added in WP 3.9.2
		if ( ! $is_valid && $die_on_failure ) {
			wp_send_json_error(
				array( 'message' => 'Incorrect authentication' )
			);
		}

		//check nonce to prevent replay attacks
		list( $req_id, $timestamp ) = explode( '-', $req_id );
		$nonce = WPMUDEV_Dashboard::$site->get_option( 'hub_nonce' );
		if ( floatval( $timestamp ) > $nonce ) {
			WPMUDEV_Dashboard::$site->set_option( 'hub_nonce', floatval( $timestamp ) );
		} else {
			wp_send_json_error(
				array( 'message' => 'Nonce check failed' )
			);
		}


		if ( ! defined( 'WPMUDEV_IS_REMOTE' ) ) {
			define( 'WPMUDEV_IS_REMOTE', $is_valid );
		}

		return $is_valid;
	}

	/**
	 * Registers a Hub api action and callback for it
	 *
	 * @param          $action
	 * @param callable $callback The name of the function you wish to be called.
	 */
	public function register_action( $action, $callback ) {
		$this->actions[ $action ] = $callback;
	}

	/**
	 * Entry point for all Hub cloud requests to the plugin
	 *
	 * @internal
	 */
	public function run_request() {
		// Do nothing if we don't
		if ( empty( $_GET['wpmudev-hub'] ) ) {
			return;
		}

		$this->register_internal_actions();
		$this->register_plugin_actions();

		//get the json
		$raw_json = file_get_contents( 'php://input' );

		$this->validate_hash( $_GET['wpmudev-hub'], $raw_json );

		$body = json_decode( stripslashes( $raw_json ) );
		if ( ! isset( $body->action ) ) {
			wp_send_json_error( array( 'message' => 'The "action" parameter is missing' ) );
		}
		if ( ! isset( $body->params ) ) {
			wp_send_json_error( array( 'message' => 'The "params" object is missing' ) );
		}

		if ( isset( $this->actions[ $body->action ] ) ) {
			//log it if turned on
			if ( WPMUDEV_API_DEBUG ) {
				$log = '[Hub API call] %s %s';
				$log .= "\n   Request params: %s\n";

				$msg = sprintf(
					$log,
					$_GET['wpmudev-hub'],
					$body->action,
					json_encode( $body->params, JSON_PRETTY_PRINT )
				);
				error_log( $msg );
			}

			call_user_func( $this->actions[ $body->action ], $body->params, $body->action );

			wp_send_json_success(); //send success in case the callback didn't respond
		}

		// When the callback function did not send a response assume error.
		wp_send_json_error( array( 'message' => 'This action is not registered.' ) );
	}

	/**
	 * Register actions that are used by the Dashboard plugin
	 */
	protected function register_internal_actions() {
		$this->register_action( 'registered_actions', array( $this, 'action_registered' ) );
		$this->register_action( 'sync', array( $this, 'action_sync' ) );
		$this->register_action( 'status', array( $this, 'action_status' ) );
		$this->register_action( 'logout', array( $this, 'action_logout' ) );
		$this->register_action( 'activate', array( $this, 'action_activate' ) );
		$this->register_action( 'deactivate', array( $this, 'action_deactivate' ) );
		$this->register_action( 'install', array( $this, 'action_install' ) );
		$this->register_action( 'upgrade', array( $this, 'action_upgrade' ) );
		$this->register_action( 'delete', array( $this, 'action_delete' ) );
	}

	/**
	 * Registers custom Hub actions from other DEV plugins
	 *
	 * Other plugins should use the wdp_register_hub_action filter to add an item to
	 *  the associative array as 'action_name' => 'callback'
	 */
	protected function register_plugin_actions() {
		/**
		 * Registers a Hub api action and callback for it
		 *
		 * @param          $action
		 * @param callable $callback The name of the function you wish to be called.
		 */
		$actions = apply_filters( 'wdp_register_hub_action', array() );
		foreach ( $actions as $action => $callback ) {
			//check action is not already registered and valid
			if ( ! isset( $this->actions[ $action ] ) && is_callable( $callback ) ) {
				$this->register_action( $action, $callback );
			}
		}
	}

	/*
	 * *********************************************************************** *
	 * *     INTERNAL ACTION METHODS
	 * *********************************************************************** *
	 */

	/**
	 * Get a list of registered Hub actions that can be called
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function action_registered( $params, $action ) {
		$actions = $this->actions;

		//make class names human readable
		foreach ( $actions as $action => $callback ) {
			if ( is_array( $callback ) ) {
				$actions[ $action ] = array( get_class( $callback[0] ), $callback[1] );
			} else if ( is_object( $callback ) ) {
				$actions[ $action ] = 'Closure';
			} else {
				$actions[ $action ] = trim( $callback ); //cleans up lambda function names
			}
		}

		wp_send_json_success( $actions );
	}

	/**
	 * Force a ping of the latest site status (plugins, themes, etc)
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function action_sync( $params, $action ) {
		// Simply refresh the membership details.
		WPMUDEV_Dashboard::$api->refresh_membership_data();
		wp_send_json_success();
	}

	/**
	 * Get the latest site status (plugins, themes, etc)
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function action_status( $params, $action ) {
		wp_send_json_success( WPMUDEV_Dashboard::$api->build_api_data( false ) );
	}

	/**
	 * Logout of this site, removing it from the Hub
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function action_logout( $params, $action ) {
		WPMUDEV_Dashboard::$site->logout( false );
		wp_send_json_success();
	}

	/**
	 * Activates a list of plugins and themes by pid or slug. Handles multiple, but should normally
	 * be called with only one package at a time.
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function action_activate( $params, $action ) {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$activated = $errors = array(); //init

		//do plugins
		if ( isset( $params->plugins ) && is_array( $params->plugins ) ) {
			foreach ( $params->plugins as $plugin ) {
				if ( is_numeric( $plugin ) ) {
					$local    = WPMUDEV_Dashboard::$site->get_cached_projects( $plugin );
					$filename = $local['filename'];
				} else {
					$filename = $plugin;
				}

				//this checks if it's valid already
				$result = activate_plugin( $filename, '', is_multisite() );
				if ( is_wp_error( $result ) ) {
					$errors[] = array(
						'file'  => $plugin,
						'error' => $result->get_error_message()
					);
				} else {
					WPMUDEV_Dashboard::$site->schedule_shutdown_refresh();
					$activated[] = array( 'file' => $plugin );
				}
			}
		}

		//do themes
		if ( isset( $params->themes ) && is_array( $params->themes ) ) {
			foreach ( $params->themes as $theme ) {
				if ( is_numeric( $theme ) ) {
					$local = WPMUDEV_Dashboard::$site->get_cached_projects( $theme );
					$slug  = $local['slug'];
				} else {
					$slug = $theme;
				}

				//wp_get_theme does not return an error for empty slugs
				if ( empty( $slug ) ) {
					$slug = "wpmudev_theme_$theme";
				}

				//check that this is a valid theme
				$check_theme = wp_get_theme( $slug );
				if ( ! $check_theme->exists() ) {
					$errors[] = array(
						'file'  => $theme,
						'error' => $check_theme->errors()->get_error_message()
					);
					continue;
				}

				if ( is_multisite() ) {
					// Allow theme network wide.
					$allowed_themes          = get_site_option( 'allowedthemes' );
					$allowed_themes[ $slug ] = true;
					update_site_option( 'allowedthemes', $allowed_themes );
				} else {
					switch_theme( $slug );
				}
				WPMUDEV_Dashboard::$site->schedule_shutdown_refresh();
				$activated[] = array( 'file' => $theme );
			}
		}

		if ( count( $activated ) ) {
			wp_send_json_success( compact( 'activated', 'errors' ) );
		} else {
			wp_send_json_error( compact( 'activated', 'errors' ) );
		}
	}

	/**
	 * Deactivates a list of plugins and themes by pid or slug. Handles multiple, but should normally
	 * be called with only one package at a time.
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function action_deactivate( $params, $action ) {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$deactivated = $errors = array(); //init

		//do plugins
		if ( isset( $params->plugins ) && is_array( $params->plugins ) ) {
			foreach ( $params->plugins as $plugin ) {
				if ( is_numeric( $plugin ) ) {
					$local    = WPMUDEV_Dashboard::$site->get_cached_projects( $plugin );
					$filename = $local['filename'];
				} else {
					$filename = $plugin;
				}

				//Check that it's a valid plugin
				$valid = validate_plugin( $filename );
				if ( is_wp_error( $valid ) ) {
					$errors[] = array(
						'file'  => $plugin,
						'error' => $valid->get_error_message()
					);
					continue;
				}

				deactivate_plugins( $filename, false, is_multisite() );
				//there is no return so we always call it a success
				WPMUDEV_Dashboard::$site->schedule_shutdown_refresh();
				$deactivated[] = array( 'file' => $plugin );
			}
		}

		//do themes
		if ( isset( $params->themes ) && is_array( $params->themes ) ) {
			foreach ( $params->themes as $theme ) {
				if ( is_numeric( $theme ) ) {
					$local = WPMUDEV_Dashboard::$site->get_cached_projects( $theme );
					$slug  = $local['slug'];
				} else {
					$slug = $theme;
				}

				//wp_get_theme does not return an error for empty slugs
				if ( empty( $slug ) ) {
					$slug = "wpmudev_theme_$theme";
				}

				//check that this is a valid theme
				$check_theme = wp_get_theme( $slug );
				if ( ! $check_theme->exists() ) {
					$errors[] = array(
						'file'  => $theme,
						'error' => $check_theme->errors()->get_error_message()
					);
					continue;
				}

				if ( is_multisite() ) {
					// Disallow theme network wide.
					$allowed_themes = get_site_option( 'allowedthemes' );
					unset( $allowed_themes[ $slug ] );
					update_site_option( 'allowedthemes', $allowed_themes );

					WPMUDEV_Dashboard::$site->schedule_shutdown_refresh();
					$deactivated[] = array( 'file' => $theme );
				}
			}
		}

		if ( count( $deactivated ) ) {
			wp_send_json_success( compact( 'deactivated', 'errors' ) );
		} else {
			wp_send_json_error( compact( 'deactivated', 'errors' ) );
		}
	}

	/**
	 * Installs a list of plugins and themes by pid or slug. Handles multiple, but should normally
	 * be called with only one package at a time.
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function action_install( $params, $action ) {

		$installed = $errors = array(); //init

		//do plugins
		if ( isset( $params->plugins ) && is_array( $params->plugins ) ) {
			foreach ( $params->plugins as $plugin ) {
				$pid     = is_numeric( $plugin ) ? $plugin : "plugin:{$plugin}";
				$success = WPMUDEV_Dashboard::$upgrader->install( $pid );
				if ( $success ) {
					$installed[] = array( 'file' => $plugin, 'log' => WPMUDEV_Dashboard::$upgrader->get_log() );
				} else {
					$error = WPMUDEV_Dashboard::$upgrader->get_error();
					$errors[] = array(
						'file'  => $plugin,
						'error' => $error['message'],
						'log'   => WPMUDEV_Dashboard::$upgrader->get_log()
					);
				}
			}
		}

		//do themes
		if ( isset( $params->themes ) && is_array( $params->themes ) ) {
			foreach ( $params->themes as $theme ) {
				$pid     = is_numeric( $theme ) ? $theme : "theme:{$theme}";
				$success = WPMUDEV_Dashboard::$upgrader->install( $pid );
				if ( $success ) {
					$installed[] = array( 'file' => $theme, 'log' => WPMUDEV_Dashboard::$upgrader->get_log() );
				} else {
					$error = WPMUDEV_Dashboard::$upgrader->get_error();
					$errors[] = array(
						'file'  => $theme,
						'error' => $error['message'],
						'log'   => WPMUDEV_Dashboard::$upgrader->get_log()
					);
				}
			}
		}

		if ( count( $installed ) ) {
			wp_send_json_success( compact( 'installed', 'errors' ) );
		} else {
			wp_send_json_error( compact( 'installed', 'errors' ) );
		}
	}

	/**
	 * Upgrades a list of plugins and themes by pid or slug. Handles multiple, but should normally
	 * be called with only one package at a time.
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function action_upgrade( $params, $action ) {

		$upgraded = $errors = array(); //init

		//do plugins
		if ( isset( $params->plugins ) && is_array( $params->plugins ) ) {
			foreach ( $params->plugins as $plugin ) {
				$pid     = is_numeric( $plugin ) ? $plugin : "plugin:{$plugin}";
				$success = WPMUDEV_Dashboard::$upgrader->upgrade( $pid );
				if ( $success ) {
					$upgraded[] = array( 'file'        => $plugin,
					                     'log'         => WPMUDEV_Dashboard::$upgrader->get_log(),
					                     'new_version' => WPMUDEV_Dashboard::$upgrader->get_version()
					);
				} else {
					$error    = WPMUDEV_Dashboard::$upgrader->get_error();
					$errors[] = array(
						'file'  => $plugin,
						'error' => $error['message'],
						'log'   => WPMUDEV_Dashboard::$upgrader->get_log()
					);
				}
			}
		}

		//do themes
		if ( isset( $params->themes ) && is_array( $params->themes ) ) {
			foreach ( $params->themes as $theme ) {
				$pid     = is_numeric( $theme ) ? $theme : "theme:{$theme}";
				$success = WPMUDEV_Dashboard::$upgrader->upgrade( $pid );
				if ( $success ) {
					$upgraded[] = array( 'file'        => $theme,
					                     'log'         => WPMUDEV_Dashboard::$upgrader->get_log(),
					                     'new_version' => WPMUDEV_Dashboard::$upgrader->get_version()
					);
				} else {
					$error    = WPMUDEV_Dashboard::$upgrader->get_error();
					$errors[] = array(
						'file'  => $theme,
						'error' => $error['message'],
						'log'   => WPMUDEV_Dashboard::$upgrader->get_log()
					);
				}
			}
		}

		if ( count( $upgraded ) ) {
			wp_send_json_success( compact( 'upgraded', 'errors' ) );
		} else {
			wp_send_json_error( compact( 'upgraded', 'errors' ) );
		}
	}

	/**
	 * Deletes a list of plugins and themes by pid or slug. Handles multiple, but should normally
	 * be called with only one package at a time. Logic copied from ajax-actions.php
	 *
	 * @param object $params Parameters passed in json body
	 * @param string $action The action name that was called
	 */
	public function action_delete( $params, $action ) {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		include_once( ABSPATH . 'wp-admin/includes/theme.php' );
		include_once( ABSPATH . 'wp-admin/includes/file.php' );

		$deleted = $errors = array(); //init

		//do plugins
		if ( isset( $params->plugins ) && is_array( $params->plugins ) ) {
			foreach ( $params->plugins as $plugin ) {
				if ( is_numeric( $plugin ) ) {
					$local    = WPMUDEV_Dashboard::$site->get_cached_projects( $plugin );
					$filename = $local['filename'];
				} else {
					$filename = $plugin;
				}

				$filename = plugin_basename( sanitize_text_field( $filename ) );

				//Check that it's a valid plugin
				$valid = validate_plugin( $filename );
				if ( is_wp_error( $valid ) ) {
					$errors[] = array(
						'file'  => $plugin,
						'error' => $valid->get_error_message()
					);
					continue;
				}

				if ( is_plugin_active( $filename ) ) {
					$errors[] = array(
						'file'  => $plugin,
						'error' => __( 'You cannot delete a plugin while it is active on the main site.' )
					);
					continue;
				}

				// Check filesystem credentials. `delete_plugins()` will bail otherwise.
				$url = wp_nonce_url( 'plugins.php?action=delete-selected&verify-delete=1&checked[]=' . $filename, 'bulk-plugins' );
				ob_start();
				$credentials = request_filesystem_credentials( $url );
				ob_end_clean();
				if ( false === $credentials || ! WP_Filesystem( $credentials ) ) {
					global $wp_filesystem;

					$error = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

					// Pass through the error from WP_Filesystem if one was raised.
					if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
						$error = esc_html( $wp_filesystem->errors->get_error_message() );
					}

					$errors[] = array(
						'file'  => $plugin,
						'error' => $error
					);
					continue;
				}

				$result = delete_plugins( array( $filename ) );

				if ( true === $result ) {
					wp_clean_plugins_cache( false );
					WPMUDEV_Dashboard::$site->schedule_shutdown_refresh();
					$deleted[] = array( 'file' => $plugin );
				} elseif ( is_wp_error( $result ) ) {
					$errors[] = array(
						'file'  => $plugin,
						'error' => $result->get_error_message()
					);
					continue;
				} else {
					$errors[] = array(
						'file'  => $plugin,
						'error' => __( 'Plugin could not be deleted.' )
					);
					continue;
				}
			}
		}

		//do themes
		if ( isset( $params->themes ) && is_array( $params->themes ) ) {
			foreach ( $params->themes as $theme ) {
				if ( is_numeric( $theme ) ) {
					$local = WPMUDEV_Dashboard::$site->get_cached_projects( $theme );
					$slug  = $local['slug'];
				} else {
					$slug = $theme;
				}

				//wp_get_theme does not return an error for empty slugs
				if ( empty( $slug ) ) {
					$slug = "wpmudev_theme_$theme";
				}

				//check that this is a valid theme
				$check_theme = wp_get_theme( $slug );
				if ( ! $check_theme->exists() ) {
					$errors[] = array(
						'file'  => $theme,
						'error' => $check_theme->errors()->get_error_message()
					);
					continue;
				}

				// Check filesystem credentials. `delete_theme()` will bail otherwise.
				$url = wp_nonce_url( 'themes.php?action=delete&stylesheet=' . urlencode( $slug ), 'delete-theme_' . $slug );
				ob_start();
				$credentials = request_filesystem_credentials( $url );
				ob_end_clean();
				if ( false === $credentials || ! WP_Filesystem( $credentials ) ) {
					global $wp_filesystem;

					$error = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

					// Pass through the error from WP_Filesystem if one was raised.
					if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
						$error = esc_html( $wp_filesystem->errors->get_error_message() );
					}

					$errors[] = array(
						'file'  => $theme,
						'error' => $error
					);
					continue;
				}

				$result = delete_theme( $slug );

				if ( is_wp_error( $result ) ) {
					$errors[] = array(
						'file'  => $theme,
						'error' => $result->get_error_message()
					);
					continue;
				} elseif ( false === $result ) {
					$errors[] = array(
						'file'  => $theme,
						'error' => __( 'Theme could not be deleted.' )
					);
					continue;
				}

				WPMUDEV_Dashboard::$site->schedule_shutdown_refresh();
				$deleted[] = array( 'file' => $theme );
			}
		}

		if ( count( $deleted ) ) {
			wp_send_json_success( compact( 'deleted', 'errors' ) );
		} else {
			wp_send_json_error( compact( 'deleted', 'errors' ) );
		}
	}
}