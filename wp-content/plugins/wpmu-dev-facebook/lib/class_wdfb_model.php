<?php

/**
 * Handles all data - both Facebook requests and local WP database reuests.
 */
class Wdfb_Model {
	var $fb;
	var $db;
	var $data;
	var $log;

	private $_batch_page_size;

	function __sleep() {
		unset( $this->db );
		unset( $this->log );
		unset( $this->data );
		unset( $this->log );
	}

	function __construct() {
		global $wpdb;
		$this->data = Wdfb_OptionsRegistry::get_instance();
		$this->db   = $wpdb;

		if ( isset( Facebook::$CURL_OPTS ) ) {
			Facebook::$CURL_OPTS = apply_filters( 'wdfb-fb_core-facebook_curl_options', Facebook::$CURL_OPTS );
			if ( ! ( defined( 'WDFB_FACEBOOK_SSL_CERTIFICATE' ) && WDFB_FACEBOOK_SSL_CERTIFICATE ) ) {
				Facebook::$CURL_OPTS[ CURLOPT_SSL_VERIFYHOST ] = 0;
				Facebook::$CURL_OPTS[ CURLOPT_SSL_VERIFYPEER ] = 0;
			}
		}
		$this->fb = new Facebook( array(
			'appId'  => trim( $this->data->get_option( 'wdfb_api', 'app_key' ) ),
			'secret' => trim( $this->data->get_option( 'wdfb_api', 'secret_key' ) ),
			'cookie' => true,
		) );
		$this->fb->getLoginUrl(); // Generate the CSRF sig stuff yay

		$this->log = new Wdfb_ErrorLog;

		$this->_batch_page_size = apply_filters( 'wdfb-fb_core-batch_request-page_size',
			( defined( 'WDFB_BATCH_REQUEST_PAGE_SIZE' ) && WDFB_BATCH_REQUEST_PAGE_SIZE ? WDFB_BATCH_REQUEST_PAGE_SIZE : 25 )
		);
	}

	function Wdfb_Model() {
		$this->__construct();
	}

	function get_known_fb_fields_map() {
		return apply_filters( 'wdfb-profile_sync-known_fb_fields', array(
			'_nothing'                   => __( 'Nothing', 'wdfb' ),
			'name'                       => __( 'Name', 'wdfb' ),
			'first_name'                 => __( 'First name', 'wdfb' ),
			'middle_name'                => __( 'Middle name', 'wdfb' ),
			'last_name'                  => __( 'Last name', 'wdfb' ),
			'gender'                     => __( 'Gender', 'wdfb' ),
			'bio'                        => __( 'Bio', 'wdfb' ),
			'birthday'                   => __( 'Birthday', 'wdfb' ),
			'about'                      => __( 'About', 'wdfb' ),
			'hometown'                   => __( 'Hometown', 'wdfb' ),
			'location'                   => __( 'Location', 'wdfb' ),
			'link'                       => __( 'Facebook profile', 'wdfb' ),
			'locale'                     => __( 'Locale', 'wdfb' ),
			'languages'                  => __( 'Languages', 'wdfb' ),
			'username'                   => __( 'Facebook username', 'wdfb' ),
			'email'                      => __( 'Email', 'wdfb' ),
			'interested_in'              => __( 'Gender interest', 'wdfb' ),
			'relationship_status'        => __( 'Relationship status', 'wdfb' ),
			'significant_other'          => __( 'Significant other', 'wdfb' ),
			'political'                  => __( 'Political view', 'wdfb' ),
			'religion'                   => __( 'Religion', 'wdfb' ),
			'favorite_athletes'          => __( 'Favorite athletes', 'wdfb' ),
			'favorite_teams'             => __( 'Favorite teams', 'wdfb' ),
			'quotes'                     => __( 'Favorite quotes', 'wdfb' ),
			/* Complex fields - Education */
			'education/schools'          => __( 'Education history (schools)', 'wdfb' ),
			'education/graduation_dates' => __( 'Education history (with graduation years)', 'wdfb' ),
			'education/subjects'         => __( 'Education history (subjects)', 'wdfb' ),
			/* Complex fields - Work history */
			'work/employers'             => __( 'Work history (employers)', 'wdfb' ),
			'work/position_history'      => __( 'Work history (with positions)', 'wdfb' ),
			'work/employer_history'      => __( 'Work history (with dates)', 'wdfb' ),
			/* Complex fields - Misc */
			//'devices' => __('Devices', 'wdfb'),
			/* Compound fields */
			'connection/books'           => __( 'Favorite books', 'wdfb' ),
			'connection/games'           => __( 'Favorite games', 'wdfb' ),
			'connection/movies'          => __( 'Favorite movies', 'wdfb' ),
			'connection/music'           => __( 'Favorite music', 'wdfb' ),
			'connection/television'      => __( 'Favorite TV shows', 'wdfb' ),
			'connection/interests'       => __( 'Interests', 'wdfb' ),
		) );
	}

	/**
	 * Returns all blogs on the current site.
	 */
	function get_blog_ids() {
		global $current_blog;
		$site_id = 0;
		if ( $current_blog ) {
			$site_id = $current_blog->site_id;
		}
		$sql = "SELECT blog_id FROM " . $this->db->blogs . " WHERE site_id={$site_id} AND public='1' AND archived= '0' AND spam='0' AND deleted='0' ORDER BY registered DESC";

		return $this->db->get_results( $sql, ARRAY_A );
	}

	/**
	 * Returns all blogs on the current network, as paged resources.
	 */
	function get_paged_blog_ids( $page ) {
		global $current_blog;
		$site_id = 0;
		if ( $current_blog ) {
			$site_id = $current_blog->site_id;
		}

		$_limit = defined( 'WDFB_NETWORK_BLOGS_PAGE_SIZE' ) && WDFB_NETWORK_BLOGS_PAGE_SIZE
			? WDFB_NETWORK_BLOGS_PAGE_SIZE
			: 25;

		$start = $page * $_limit;
		$end   = $start + $_limit;
		$sql   = "SELECT blog_id FROM " . $this->db->blogs . " WHERE site_id={$site_id} AND public='1' AND archived= '0' AND spam='0' AND deleted='0' ORDER BY registered DESC LIMIT {$start}, {$end}";

		return $this->db->get_results( $sql, ARRAY_A );
	}

	/**
	 * Logs the user out of the site and Facebook.
	 */
	function wp_logout( $redirect = false ) {
		setcookie( 'fbsr_' . $this->fb->getAppId(), '', time() - 100, '/', COOKIE_DOMAIN ); // Yay for retardness in FB SDK
		@session_unset();
		@session_destroy();
		unset( $_SESSION );
		wp_logout();
		wp_set_current_user( 0 );
		if ( $redirect ) {
			wp_redirect( $redirect );
		}
	}

	/**
	 * Lists registered BuddyPress profile fields.
	 */
	function get_bp_xprofile_fields() {
		if ( ! defined( 'BP_VERSION' ) ) {
			return true;
		}
		$tbl_pfx = function_exists( 'bp_core_get_table_prefix' ) ? bp_core_get_table_prefix() : apply_filters( 'bp_core_get_table_prefix', $this->db->base_prefix );
		$sql     = "SELECT id, name FROM {$tbl_pfx}bp_xprofile_fields WHERE parent_id=0";

		return $this->db->get_results( $sql, ARRAY_A );
	}

	/**
	 * Create/update the BuddyPress profile field.
	 */
	function set_bp_xprofile_field( $field_id, $user_id, $data ) {
		if ( ! defined( 'BP_VERSION' ) ) {
			return true;
		}

		$field_id = (int) $field_id;
		$user_id  = (int) $user_id;
		if ( ! $field_id || ! $user_id ) {
			return false;
		}

		//if (is_array($data)) $data = $data['name']; // For complex FB fields that return JSON objects
		if ( is_array( $data ) && isset( $data['name'] ) ) {
			$data = $data['name'];
		} else if ( is_array( $data ) && isset( $data[0] ) && isset( $data[0]['name'] ) ) {
			$data = join( ', ', array_map( create_function( '$m', 'return $m["name"];' ), $data ) );
		}
		$data = apply_filters( 'wdfb-profile_sync-bp-field_value', $data, $field_id, $user_id );

		if ( ! $data ) {
			return false;
		} // Don't waste cycles if we don't need to
		$tbl_pfx = function_exists( 'bp_core_get_table_prefix' ) ? bp_core_get_table_prefix() : apply_filters( 'bp_core_get_table_prefix', $this->db->base_prefix );
		$sql     = "SELECT id FROM {$tbl_pfx}bp_xprofile_data WHERE field_id={$field_id} AND user_id={$user_id}";
		$id      = $this->db->get_var( $sql );

		if ( $id ) {
			$sql = "UPDATE {$tbl_pfx}bp_xprofile_data SET value='" . $data . "' WHERE id={$id}";
		} else {
			$sql = "INSERT INTO {$tbl_pfx}bp_xprofile_data (field_id, user_id, value, last_updated) VALUES (" .
			       (int) $field_id . ', ' . (int) $user_id . ", '" . $data . "', '" . date( 'Y-m-d H:i:s' ) . "')";
		}

		return $this->db->query( $sql );
	}

	/**
	 * Gets FB profile image and sets it as BuddyPress avatar.
	 */
	function set_fb_image_as_bp_avatar( $user_id, $me ) {
		if ( ! defined( 'BP_VERSION' ) || !class_exists('BuddyPress') ) {
			return true;
		}
		if ( ! function_exists( 'bp_core_avatar_upload_path' ) ) {
			return true;
		}
		if ( ! $me || ! @$me['id'] ) {
			return false;
		}

		$fb_uid = $me['id'];

		if ( function_exists( 'xprofile_avatar_upload_dir' ) ) {
			$xpath = xprofile_avatar_upload_dir( false, $user_id );
			$path  = $xpath['path'];
		}
		if ( ! function_exists( 'xprofile_avatar_upload_dir' ) || empty( $path ) ) {
			$object     = 'user';
			$avatar_dir = apply_filters( 'bp_core_avatar_dir', 'avatars', $object );
			$path       = bp_core_avatar_upload_path() . "/{$avatar_dir}/" . $user_id;
			$path       = apply_filters( 'bp_core_avatar_folder_dir', $path, $user_id, $object, $avatar_dir );
			if ( ! realpath( $path ) ) {
				@wp_mkdir_p( $path );
			}
		}
		//If directory not exists
		if( !empty( $path) && !file_exists( $path ) ) {
			@wp_mkdir_p($path);
		}

		// Get FB picture
		//$fb_img = file_get_contents("http://graph.facebook.com/{$fb_uid}/picture?type=large");
		$page = wp_remote_get( "http://graph.facebook.com/{$fb_uid}/picture?type=large", array(
			'method'      => 'GET',
			'timeout'     => '5',
			'redirection' => '5',
			'user-agent'  => 'wdfb',
			'blocking'    => true,
			'compress'    => false,
			'decompress'  => true,
			'sslverify'   => false
		) );
		if ( is_wp_error( $page ) ) {
			return false;
		} // Request fail
		if ( (int) $page['response']['code'] != 200 ) {
			return false;
		} // Request fail
		$fb_img = $page['body'];

		$filename = md5( $fb_uid );
		$filepath = "{$path}/{$filename}";
		file_put_contents( $filepath, $fb_img );

		// Determine the right extension
		$info      = getimagesize( $filepath );
		$extension = false;

		if ( function_exists( 'image_type_to_extension' ) ) {
			$extension = image_type_to_extension( $info[2], false );
		} else {
			switch ( $info[2] ) {
				case IMAGETYPE_GIF:
					$extension = 'gif';
					break;
				case IMAGETYPE_JPEG:
					$extension = 'jpg';
					break;
				case IMAGETYPE_PNG:
					$extension = 'png';
					break;
			}
		}
		// Unknown file type, clean up
		if ( ! $extension ) {
			@unlink( $filepath );

			return false;
		}
		$extension = 'jpeg' == strtolower( $extension ) ? 'jpg' : $extension; // Forcing .jpg extension for JPEGs

		// Clear old avatars
		$imgs = glob( $path . '/*.{gif,png,jpg}', GLOB_BRACE );
		if ( is_array( $imgs ) ) {
			foreach ( $imgs as $old ) {
				@unlink( $old );
			}
		}

		// Create and set new avatar
		if ( defined( 'WDFB_BP_AVATAR_AUTO_CROP' ) && WDFB_BP_AVATAR_AUTO_CROP ) {
			// Explicitly requested thumbnail processing
			// First, determine the centering position for cropping
			if ( $info && isset( $info[0] ) && $info[0] && isset( $info[1] ) && $info[1] ) {
				$full = apply_filters( 'wdfb-avatar-auto_crop', array(
					'x'      => (int) ( ( $info[0] - bp_core_avatar_full_width() ) / 2 ),
					'y'      => (int) ( ( $info[1] - bp_core_avatar_full_height() ) / 2 ),
					'width'  => bp_core_avatar_full_width(),
					'height' => bp_core_avatar_full_height(),
				), $filepath, $info );
			}
			$crop = $full
				? wp_crop_image( $filepath, $full['x'], $full['y'], $full['width'], $full['height'], bp_core_avatar_full_width(), bp_core_avatar_full_height(), false, "{$filepath}-bpfull.{$extension}" )
				: false;
			if ( ! $crop ) {
				@unlink( $filepath );

				return false;
			}
			// Now, the thumbnail. First, try to resize the full avatar
			$thumb_file = wp_create_thumbnail( "{$filepath}-bpfull.{$extension}", bp_core_avatar_thumb_width() );
			if ( ! is_wp_error( $thumb_file ) ) {
				// All good! We're done - clean up
				copy( $thumb_file, "{$filepath}-bpthumb.{$extension}" );
				@unlink( $thumb_file );
			} else {
				// Sigh. Let's just fake it by using the original file then.
				copy( "{$filepath}-bpfull.{$extension}", "{$filepath}-bpthumb.{$extension}" );
			}
			@unlink( $filepath );

			return true;
		} else {
			// No auto-crop, move on
			copy( $filepath, "{$filepath}-bpfull.{$extension}" );
			copy( $filepath, "{$filepath}-bpthumb.{$extension}" );
			@unlink( $filepath );

			return true;
		}

		return false;
	}

	function get_all_user_tokens() {
		$sql = "SELECT * FROM " . $this->db->base_prefix . "usermeta WHERE meta_key='wdfb_api_accounts'";

		return $this->db->get_results( $sql, ARRAY_A );
	}

	function get_api_accounts( $user_id ) {
		$fb_accounts = get_user_meta( $user_id, 'wdfb_api_accounts', true );
		$fb_accounts = isset( $fb_accounts['auth_accounts'] ) ? $fb_accounts['auth_accounts'] : array();

		return apply_filters( 'wdfb-api-connected_accounts', $fb_accounts, $user_id );
	}

	/**
	 * Gets an existing app token for the user.
	 */
	function get_user_api_token( $fb_uid ) {
		$wdfb_api = get_option( 'wdfb_api' );
		$token    = isset( $wdfb_api['auth_tokens'] ) ? $wdfb_api['auth_tokens'] : array();
		if ( ! $token || empty( $fb_uid ) ) {
			if( !$token ) {
				$message = 'Token not found';
			}else {
				$message = 'Missing Facebook user id';
			}
			$this->log->error( 'WDFBModel::get_user_api_token', $message );

			return false;
		}
		if ( ! isset( $token[ $fb_uid ] ) ) {
			$this->log->error( 'WDFBModel::get_user_api_token', 'No valid Facebook token found for the given Facebook user id' );

			return false;
		}

		return $token[ $fb_uid ];
	}

	function comment_already_imported( $fb_cid ) {
		if ( ! $fb_cid ) {
			return false;
		}
		$key = '%s:13:"fb_comment_id";s:' . strlen( $fb_cid ) . ':"' . $fb_cid . '";%';
		$sql = "SELECT meta_id FROM " . $this->db->prefix . "commentmeta WHERE meta_value LIKE '{$key}'";

		return $this->db->get_var( $sql );
	}

	/**
	 * @return User ID or False
	 */
	function get_wp_user_from_fb() {
		$fb_user_id = $this->fb->getUser();

		$sql = "SELECT user_id FROM " . $this->db->base_prefix . "usermeta WHERE meta_key='wdfb_fb_uid' AND meta_value=%s";
		$res = $this->db->get_results( $this->db->prepare( $sql, $fb_user_id ), ARRAY_A );
		if ( $res ) {
			return $res[0]['user_id'];
		}

		// User not yet linked. Try finding by email.
		$me = false;
		try {
			$me = $this->fb->api( '/me' );
		} catch ( Exception $e ) {
			return false;
		}
		if ( ! $me || ! isset( $me['email'] ) ) {
			return false;
		}

		$sql = "SELECT ID FROM " . $this->db->base_prefix . "users WHERE user_email=%s";
		$res = $this->db->get_results( $this->db->prepare( $sql, $me['email'] ), ARRAY_A );

		if ( ! $res ) {
			return false;
		}

		return $this->map_fb_to_wp_user( $res[0]['ID'] );
	}

	function get_fb_user_from_wp( $wp_uid ) {
		$fb_uid = get_user_meta( $wp_uid, 'wdfb_fb_uid', true );

		return $fb_uid;
	}

	function map_fb_to_wp_user( $wp_uid ) {
		if ( ! $wp_uid ) {
			return false;
		}
		update_user_meta( $wp_uid, 'wdfb_fb_uid', $this->fb->getUser() );

		return $wp_uid;
	}

	function map_fb_to_current_wp_user() {
		$user = wp_get_current_user();
		$id   = $user->ID;
		$this->map_fb_to_wp_user( $id );

	}

	function registration_allowed() {
		$registration_allowed = true;
		//Check if registrations are allowed
		if ( is_multisite() ) {
			$reg = get_site_option( 'registration' );
			//user and blog registrations are not allowed
			if ( 'all' != $reg && 'user' != $reg ) {
				$registration_allowed = false;
			}
		} else {
			if ( ! (int) get_option( 'users_can_register' ) ) {
				$registration_allowed = false;
			}
		}

		return $registration_allowed;
	}

	function register_fb_user() {
		$uid = $this->get_wp_user_from_fb();
		if ( $uid ) {
			return $this->map_fb_to_wp_user( $uid );
		}
		$registration_allowed = $this->registration_allowed();

		if ( $registration_allowed ) {
			return $this->create_new_wp_user_from_fb();
		} else {
			return false;
		}
	}

	function delete_wp_user( $uid ) {
		$uid = (int) $uid;
		if ( ! $uid ) {
			return false;
		}
		$this->db->query( "DELETE FROM {$this->db->users} WHERE ID={$uid}" );
		$this->db->query( "DELETE FROM {$this->db->usermeta} WHERE user_id={$uid}" );
	}
	function get_user_token() {
		$this->data = Wdfb_OptionsRegistry::get_instance();
		$fb_uid     = $this->fb->getUser();
		$app_id     = trim( $this->data->get_option( 'wdfb_api', 'app_key' ) );
		$app_secret = trim( $this->data->get_option( 'wdfb_api', 'secret_key' ) );
		if ( ! $app_id || ! $app_secret ) {
			return false;
		} // Plugin not yet configured

		// Token is now long-term token
		$token = $this->get_user_api_token( $fb_uid );

		// Make sure it is
		$user_token = preg_match( '/^' . preg_quote( "{$app_id}|" ) . '/', $token ) ? false : $token;

		// Just force the token reset, for now
		$token = false;
		if ( ! $token ) {
			// Get temporary token
			$token = $this->fb->getAccessToken();

			$user_token = preg_match( '/^' . preg_quote( "{$app_id}|" ) . '/', $token ) ? $user_token : $token;

			if ( ! $token ) {
				return false;
			}

			// Exchange it for the actual long-term token
			$url  = "https://graph.facebook.com/oauth/access_token?client_id={$app_id}&client_secret={$app_secret}&grant_type=fb_exchange_token&fb_exchange_token={$token}&access_token={$user_token}";
			$args = array(
				'method'      => 'GET',
				'timeout'     => '5',
				'redirection' => '5',
				'user-agent'  => 'wdfb',
				'blocking'    => true,
				'compress'    => false,
				'decompress'  => true,
				'sslverify'   => false
			);
			$page = wp_remote_get( $url, $args );
			if ( is_wp_error( $page ) ) {
				return false;
			} // Request fail
			if ( (int) $page['response']['code'] != 200 ) {

				return false;
			} // Request fail

			parse_str( $page['body'], $response );
			$token = isset( $response['access_token'] ) ? $response['access_token'] : false;
			if ( ! $token ) {
				return false;
			}
			return $token;
		}
	}

	function create_new_wp_user_from_fb() {
		$send_email   = false;
		$reg          = (array) ( ( isset( $this->fb->registration ) && $this->fb->registration ) ? $this->fb : $this->fb->getSignedRequest() );
		$registration = isset( $reg['registration'] ) ? $reg['registration'] : array();
		try {
			$me = $this->fb->api( '/me' );
		} catch ( Exception $e ) {
			$me         = $registration;
			$send_email = true; // we'll need to notify the user
			$me['id']   = $this->fb->user_id;
		}

		if ( ! $me || empty( $me['id'] ) ) {
			return false;
		}
		//Get token
		$user_token = $this->get_user_token();

		//Get User email
		try {
			$me = $this->fb->api( '/' . $me['id'],
				array(
					'access_token' => $user_token,
					'fields' => 'email, first_name, last_name, name, id'
				) );
		} catch ( Exception $e ) {
			$this->log->error( __FUNCTION__, new Exception( $e->get_error_message() ) );
		}
		//Validate email
		if ( empty( $me['email'] ) || ! filter_var( $me['email'], FILTER_VALIDATE_EMAIL ) || email_exists( $me['email'] ) ) {
			return false;
		}
		$username = $this->_create_username_from_fb_response( $me );
		$password = wp_generate_password( 12, false );
		$user_id  = wp_create_user( $username, $password, $me['email'] );

		if ( is_wp_error( $user_id ) ) {
			$this->log->error( __FUNCTION__, new Exception( $user_id->get_error_message() ) );

			return false;
		} else if ( !empty( $user_id ) ) {
			if( !empty( $me['first_name'] ) ) {
				update_user_meta($user_id, 'first_name', $me['first_name']);
			}
			if( !empty( $me['last_name'] ) ) {
				update_user_meta($user_id, 'first_name', $me['last_name']);
			}
			wp_new_user_notification( $user_id, $password );
			do_action( 'wdfb-registration_email_sent' );
		}

		// Allow others to process the fields
		do_action( 'wdfb-user_registered', $user_id, $registration );

		// Allow other actions - e.g. posting to Facebook, upon registration
		do_action( 'wdfb-user_registered-postprocess', $user_id, $me, $registration, $this );

		if ( defined( 'BP_VERSION' ) && class_exists('BuddyPress') ) {
			$this->populate_bp_fields_from_fb( $user_id, $me );
		} // BuddyPress
		else {
			$this->populate_wp_fields_from_fb( $user_id, $me );
		} // WordPress

		return $this->map_fb_to_wp_user( $user_id );
	}

	function populate_bp_fields_from_fb( $user_id, $me = false ) {
		if ( ! defined( 'BP_VERSION' ) ) {
			return true;
		}
		if ( ! $me ) {
			try {
				$me = $this->fb->api( '/me' );
			} catch ( Exception $e ) {
				return false;
			}
			if ( ! $me ) {
				return false;
			}
		}

		if ( ! $this->data->get_option( 'wdfb_connect', 'skip_fb_avatars' ) ) {
			$this->set_fb_image_as_bp_avatar( $user_id, $me );
		}

		$bp_fields  = $this->get_bp_xprofile_fields();
		$fields_map = array();
		if ( is_array( $bp_fields ) ) {
			foreach ( $bp_fields as $bpf ) {
				$fb_value = $this->data->get_option( 'wdfb_connect', 'buddypress_registration_fields_' . $bpf['id'] );
				if ( $fb_value ) {
					$fields_map[ $bpf['id'] ] = $fb_value;
				}
			}
		}

		foreach ( $fields_map as $bpfid => $field_name ) {
			$field_value = false;
			if ( false !== strstr( $field_name, '/' ) ) {
				list( $field_name, $field_processor ) = explode( '/', $field_name );
				$field_value = apply_filters( "wdfb-profile_sync-{$field_name}-{$field_processor}", $me[ $field_name ], $field_name, $this );
			}
			if ( ! $field_value && empty( $me[ $field_name ] ) ) {
				continue;
			}
			$field_value = $field_value ? $field_value : $me[ $field_name ];
			$this->set_bp_xprofile_field( $bpfid, $user_id, $field_value );
		}

		return true;
	}

	function populate_wp_fields_from_fb( $user_id, $me = false ) {
		$wp_mappings = $this->data->get_option( 'wdfb_connect', 'wordpress_registration_fields' );
		if ( empty( $wp_mappings ) ) {
			return true;
		}
		if ( ! is_array( $wp_mappings ) ) {
			return false;
		}

		if ( ! $me ) {
			try {
				$me = $this->fb->api( '/me' );
			} catch ( Exception $e ) {
				return false;
			}
			if ( ! $me ) {
				return false;
			}
		}

		foreach ( $wp_mappings as $map ) {
			$field_value = false;

			if ( empty( $map['wp'] ) || empty( $map['fb'] ) ) {
				continue;
			}
			$field_name = $map['fb'];

			if ( false !== strstr( $field_name, '/' ) ) {
				list( $field_name, $field_processor ) = explode( '/', $field_name );
				$field_value = apply_filters( "wdfb-profile_sync-{$field_name}-{$field_processor}", $me[ $field_name ], $field_name, $this );
			}
			if ( ! $field_value && empty( $me[ $field_name ] ) ) {
				continue;
			}
			$field_value = $field_value ? $field_value : $me[ $field_name ];

			if ( is_array( $field_value ) && isset( $field_value['name'] ) ) {
				$data = $field_value['name'];
			} else if ( is_array( $field_value ) && isset( $field_value[0] ) && isset( $field_value[0]['name'] ) ) {
				$data = join( ', ', array_map( create_function( '$m', 'return $m["name"];' ), $field_value ) );
			} else {
				$data = $field_value;
			}

			$data = apply_filters( 'wdfb-profile_sync-wp-field_value', $data, $map['wp'], $user_id );
			update_user_meta( $user_id, $map['wp'], $data );
		}

		return true;
	}

	function get_user_data_for( $for = false ) {
		if ( ! $for ) {
			return false;
		}
		try {
			$data = $this->fb->api( "/{$for}" );
		} catch ( Exception $e ) {
			return false;
		}

		return $data;
	}

	function get_current_wp_user_data() {
		$user = wp_get_current_user();
		if ( ! $user || ! $user->ID ) {
			return false;
		} // User not logged into WP, skip
		$fb_uid = get_user_meta( $user->ID, 'wdfb_fb_uid', true );

		return $this->get_user_data_for( $fb_uid );
	}

	function get_current_fb_user_data() {
		return $this->get_user_data_for( 'me' );
	}

	/**
	 * Return a FBid for user with access token
	 * @return bool|int|mixed|string
	 */
	function get_current_user_fb_id() {
		//Get all details for the blog
		$wdfb_api = get_option( 'wdfb_api' );

		$fb_uid = $this->fb->getUser();
		if ( $fb_uid ) {
			return $fb_uid;
		} // User is logged into FB, use that

		//If a user is logged in, check if they have authorized app
		$user = get_current_user_id();
		//if user logged in and we have a auth token respective th their FB id, return the fb id
		if ( ! empty( $user ) ) {
			$fb_uid = get_user_meta( $user, 'wdfb_fb_uid', true );
			if ( ! empty( $fb_uid ) && ! empty( $wdfb_api['auth_tokens'] ) && ! empty( $wdfb_api['auth_tokens'][ $fb_uid ] ) ) {
				return $fb_uid;
			} else {
				$fb_uid = '';
			}
		}

		//no auth token available for logged in user
		if ( empty( $fb_uid ) ) {
			if ( empty( $wdfb_api ) || empty( $wdfb_api['auth_accounts'] ) ) {
				return false;
			}
			foreach ( $wdfb_api['auth_accounts'] as $id => $auth_account ) {
				//Search for auth tokens for user, looking for 10120020120 => ME(10120020120) in auth tokens array
				if ( strpos( $auth_account, $id ) !== false && ! empty( $wdfb_api['auth_tokens'][ $id ] ) ) {
					return $id;
				} else {
					//return the first id, for which we have auth token
					return key( $wdfb_api['auth_tokens'] );
				}
			}

		}
	}

	function get_pages_tokens( $token = false ) {
		$fid = $this->get_current_user_fb_id();
		if ( ! $fid ) {
			return false;
		}

		$token = $token ? $token : $this->get_user_api_token( $fid );
		/*
		$token = $token ? "?access_token={$token}" : '';
		try {
			//$ret = $this->fb->api('/' . $fid . '/accounts/');
			$ret = $this->fb->api('/' . $fid . '/accounts/' . $token);
		} catch (Exception $e) {
			return false;
		}
		return $ret;
		*/
		$url  = "https://graph.facebook.com/{$fid}/accounts?access_token={$token}";
		$page = wp_remote_get( $url, array(
			'method'      => 'GET',
			'timeout'     => '5',
			'redirection' => '5',
			'user-agent'  => 'wdfb',
			'blocking'    => true,
			'compress'    => false,
			'decompress'  => true,
			'sslverify'   => false
		) );

		if ( is_wp_error( $page ) ) {
			return false;
		} // Request fail
		if ( (int) $page['response']['code'] != 200 ) {
			return false;
		} // Request fail

		return (array) @json_decode( $page['body'] );
	}

	function post_on_facebook( $type, $fid, $post, $as_page = false ) {
		$type = $type ? $type : 'feed';

		if ( empty( $post ) ) {
			return false;
		}

		$fid   = $fid ? $fid : $this->get_current_user_fb_id();
		$token = $this->get_user_api_token( $fid );

		//$post['auth_token'] = $tokens[$fid];
		if ( ! $token ) {
			$tokens = $this->data->get_option( 'wdfb_api', 'auth_tokens' );
			$token  = $tokens[ $fid ];
		}
		$post['access_token'] = $token;

		$title = ( 'feed' == $type ) ? @$post['message'] : '';
		$_ap   = $as_page ? 'as page' : '';
		$this->log->notice( "Posting {$title} to Facebook [{$type}] - [{$fid}] {$_ap} [{$token}]." );
		/*
		if ($as_page) {
			try {
				$resp = $this->fb->api($fid, array('auth_token' => $tokens[$fid]));
			} catch (Exception $e) {
				$this->log->notice("Unable to post to Facebook as page.");
			}

			// can_post checks perms for posting as user
			if (@$resp['can_post']) $post['access_token'] = $tokens[$fid];
			else $this->log->notice("Unable to post to Facebook as page.");
		}
		*/
		try {
			$ret = $this->fb->api( '/' . $fid . '/' . $type . '/', 'POST', $post );
		} catch ( Exception $e ) {
			$this->log->error( __FUNCTION__, $e );

			return false;
		}
		$this->log->notice( "Posting to Facebook finished." );

		return $ret;
	}

	function get_events_for( $for, $limit = false, $offset = false ) {
		if ( ! $for ) {
			return false;
		}

		$tokens = $this->data->get_option( 'wdfb_api', 'auth_tokens' );
		$token  = isset ( $tokens[ $for ] ) ? $tokens[ $for ] : '';

		if ( empty ( $token ) ) {
			//No token found for user id, either userid is wrong or permissions are missing
			return false;
		}
		$page_size = 5;
		$max_limit = apply_filters( 'wdfb-albums-max_photos_limit',
			( defined( 'WDFB_EVENTS_MAX_EVENT_LIMIT' ) && WDFB_EVENTS_MAX_EVENT_LIMIT ? WDFB_EVENTS_MAX_EVENT_LIMIT : 200 )
		);

		$tokens = $this->data->get_option( 'wdfb_api', 'auth_tokens' );
		$token  = ! empty( $tokens[ $for ] ) ? $tokens[ $for ] : $this->fb->getAppId() . '|' . $this->fb->getAppSecret();

		if ( $limit && $limit > $page_size ) {
			$limit = $limit > $max_limit ? $max_limit : $limit;
			$batch = array();
			for ( $i = 0; $i < $limit; $i += $page_size ) {
				$batch[] = json_encode( array(
					'method'       => 'GET',
					'relative_url' => "/{$for}/events/?limit={$page_size}&offset={$i}&fields=id,name,description,start_time,end_time,location,venue,picture,ticket_uri,owner,privacy,timezone"
				) );
			}
			try {
				$res = $this->fb->api( '/', 'POST', array(
					'batch'        => '[' . implode( ',', $batch ) . ']',
					'access_token' => $token
				) );
			} catch ( Exception $e ) {
				$this->log->error( __FUNCTION__, $e );

				return false;
			}
			$return = array();
			foreach ( $res as $key => $data ) {
				if ( ! $data || ! isset( $data['body'] ) ) {
					continue;
				}
				$data = json_decode( $data['body'], true );
				if ( ! empty( $data ) && is_array( $data['data'] ) ) {
					$return = array_merge( $return, $data['data'] );
				}
			}

			return array( 'data' => $return );
		} else {
			try {
				//$res = $this->fb->api("/{$for}/events/?access_token={$token}");
				$res = $this->fb->api( "/{$for}/events/", 'GET', array( 'access_token' => $token ) );
			} catch ( Exception $e ) {
				return false;
			}

			return $res;
		}

		return false;
	}

	function get_albums_for( $for ) {
		if ( ! $for ) {
			return false;
		}

		$tokens = $this->data->get_option( 'wdfb_api', 'auth_tokens' );
		$token  = $tokens[ $for ];

		try {
			$res = $this->fb->api( '/' . $for . '/albums/', array(
					'access_token' => $token
				)
			);
		} catch ( Exception $e ) {
			return false;
		}

		return $res;
	}

	function get_current_albums() {
		$user        = wp_get_current_user();
		$fb_accounts = get_user_meta( $user->ID, 'wdfb_api_accounts', true );
		$fb_accounts = isset( $fb_accounts['auth_accounts'] ) ? $fb_accounts['auth_accounts'] : false;
		if ( ! $fb_accounts ) {
			return false;
		}
		$albums = array( 'data' => array() );
		foreach ( $fb_accounts as $fid => $label ) {
			$res = $this->get_albums_for( $fid );
			if ( ! $res ) {
				continue;
			}
			$albums['data'] = array_merge( $albums['data'], $res['data'] );
		}

		return $albums ? $albums : false;
	}

	/**
	 * Fetches a public album details for the provided album id
	 * @param $albm_id Album id
	 *
	 * @return bool|mixed|null Album Content
	 */
	function get_album_details( $albm_id, $cover = true ) {
		if ( empty( $albm_id ) ) {
			return null;
		}
		$fid   = $this->get_current_user_fb_id();
		$token = $this->get_user_api_token( $fid );
		try {
			$res = $this->fb->api( '/' . $albm_id, array(
				'access_token' => $token
			) );
		} catch ( Exception $e ) {
			$this->log->error( __FUNCTION__, $e );

			return false;
		}
		if( !empty( $res['cover_photo'] ) && $cover ) {
			//Get cover
			try {
				$cover_url = $this->fb->api( '/' . $res['cover_photo'] , array(
					'access_token' => $token
				) );
			} catch ( Exception $e ) {
				$this->log->error( __FUNCTION__, $e );

				return false;
			}
		}
		$res['cover'] = $cover_url;
		return $res;
	}
	/**
	 * Fetch photos for a provided album ID
	 *
	 * @param $aid , Album ID
	 * @param bool $limit ,max no. og photos
	 * @param bool $offset , Skip the no. of photos
	 *
	 * @return bool|mixed
	 */
	function get_album_photos( $aid, $limit = false, $offset = false ) {
		if ( ! $aid ) {
			return false;
		}
		$page_size = $this->_batch_page_size;
		$max_limit = apply_filters( 'wdfb-albums-max_photos_limit',
			( defined( 'WDFB_ALBUMS_MAX_PHOTOS_LIMIT' ) && WDFB_ALBUMS_MAX_PHOTOS_LIMIT ? WDFB_ALBUMS_MAX_PHOTOS_LIMIT : 200 )
		);

		$fid   = $this->get_current_user_fb_id();
		$token = $this->get_user_api_token( $fid );
		$limit = ! empty( $limit ) ? $limit : 200;
		if ( $limit && $limit > $page_size ) {
			$limit = $limit > $max_limit ? $max_limit : $limit;
			$batch = array();
			for ( $i = 0; $i < $limit; $i += $page_size ) {
				$batch[] = json_encode( array(
					'method'       => 'GET',
					'relative_url' => "/{$aid}/photos/?limit={$page_size}&offset={$i}&fields=created_time,height,icon,id,images,link,name,picture,source,updated_time,width"
				) );
			}
			try {
				$res = $this->fb->api( '/', 'POST', array(
					'access_token' => $token,
					'batch'        => '[' . implode( ',', $batch ) . ']'
				) );
			} catch ( Exception $e ) {
				$this->log->error( __FUNCTION__, $e );

				return false;
			}
			$return = array();
			foreach ( $res as $key => $data ) {
				//Skip loop if response code is not 200 ot the res array do not contains body
				if ( ! $data || ! isset( $data['body'] ) || $data['code'] != 200 ) {
					continue;
				}
				$data   = json_decode( $data['body'], true );
				$return = ! empty( $return ) ? array_merge( $return, $data['data'] ) : $data['data'];
			}

			return array( 'data' => $return );
		} else {
			try {
				$res = $this->fb->api( '/' . $aid . '/photos/', array(
					'access_token' => $token,
					'limit'        => $limit,
					'fields'       => 'created_time,height,icon,id,images,link,name,picture,source,updated_time,width'
				) );
			} catch ( Exception $e ) {
				$this->log->error( __FUNCTION__, $e );

				return false;
			}

			return $res;
		}
	}

	function get_feed_for( $uid, $limit = false ) {
		if ( ! $uid ) {
			return false;
		}
		$limit = $limit ? '?limit=' . $limit : '';

		$tokens = $this->data->get_option( 'wdfb_api', 'auth_tokens' );
		$token  = ! empty( $tokens[ $uid ] ) ? $tokens[ $uid ] : reset( $tokens ); // Use any token, if there's no token we need

//		$old_token = $this->fb->getAccessToken();
//		$this->fb->setAccessToken( $token );
		try {
			$res = $this->fb->api( "/{$uid}/feed/{$limit}" );
		} catch ( Exception $e ) {
			$this->log->error( __FUNCTION__, $e );

			return false;
		}

//		$this->fb->setAccessToken( $old_token );

		return $res;
	}

	function get_item_comments( $for ) {
		$uid = $this->get_current_user_fb_id();

		$tokens    = $this->data->get_option( 'wdfb_api', 'auth_tokens' );
		$token     = isset( $tokens[ $uid ] ) ? $tokens[ $uid ] : false;
		$page_size = $this->_batch_page_size;
		$max_limit = apply_filters( 'wdfb-comments-max_comments_limit',
			( defined( 'WDFB_COMMENTS_MAX_COMMENTS_LIMIT' ) && WDFB_COMMENTS_MAX_COMMENTS_LIMIT ? WDFB_COMMENTS_MAX_COMMENTS_LIMIT : 200 )
		);
		if ( $max_limit < $page_size ) {
			try {
				$res = $this->fb->api( '/' . $for . '/comments/', array(
					'auth_token' => $token
				) );
			} catch ( Exception $e ) {
				$this->log->error( __FUNCTION__, $e );

				return false;
			}

			return $res;
		} else {
			$batch = array();
			for ( $i = 0; $i < $max_limit; $i += $page_size ) {
				$batch[] = json_encode( array(
					'method'       => 'GET',
					'relative_url' => "/{$for}/comments/?limit={$page_size}&offset={$i}"
				) );
			}
			try {
				$res = $this->fb->api( '/', 'POST', array( 'batch' => '[' . implode( ',', $batch ) . ']' ) );
//				$res = $this->fb->api( '/', 'POST', array( 'access_token' => $token, 'batch' => '[' . implode( ',', $batch ) . ']' ) );
			} catch ( Exception $e ) {
				$this->log->error( __FUNCTION__, $e );

				return false;
			}
			$return = array();
			foreach ( $res as $key => $data ) {
				if ( ! $data || ! isset( $data['body'] ) ) {
					continue;
				}
				$data   = json_decode( $data['body'], true );
				$return = array_merge( $return, $data['data'] );
			}

			return array( 'data' => $return );
		}
	}

	function get_current_user_groups() {
		$groups = array();
		try {
			$groups = $this->fb->api( '/me/groups' );
		} catch ( Exception $e ) {
			$groups = array();
		}

		return ! empty( $groups['data'] )
			? $groups['data']
			: array();
	}

	function get( $what ) {
		$response = false;
		try {
			$response = $this->fb->api( $what, 'GET' );
		} catch ( Exception $e ) {
			$response = false;
		}

		return $response;
	}

	function map_bp_group_info( $bp_group, $fb_group, $token = false ) {
		if ( ! function_exists( 'groups_edit_base_group_details' ) ) {
			return false;
		}
		$data = false;
		//$access_token = $token ? "?access_token={$token}" : "";
		if ( $token ) {
			$old_token = $this->fb->getAccessToken();
			$this->fb->setAccessToken( $token );
		}
		try {
			$data = $this->fb->api( "/{$fb_group}/" );
		} catch ( Exception $e ) {
		}
		if ( $token ) {
			$this->fb->setAccessToken( $old_token );
		}

		if ( ! $data ) {
			return false;
		}
		$group = groups_get_group( array( 'group_id' => $bp_group ) );
		if ( ! $group ) {
			return false;
		}

		// Privacy settings
		if ( $this->data->get_option( 'wdfb_groups', 'allow_group_privacy_sync' ) ) {
			$status = ! empty( $data['privacy'] ) ? $data['privacy'] : $group->status;
			if ( 'OPEN' == $status ) {
				$status = 'public';
			}
			if ( 'CLOSED' == $status ) {
				$status = 'private';
			}
			if ( 'SECRET' == $status ) {
				$status = 'hidden';
			}
			$group->status = $status;
		}

		$group->name        = ! empty( $data['name'] ) ? $data['name'] : $group->name;
		$group->description = ! empty( $data['description'] ) ? $data['description'] : $group->description;
		$group->save();

		if ( $this->data->get_option( 'wdfb_groups', 'notify_members' ) ) {
			groups_notification_group_updated( $group->id );
		}
	}

	function _create_username_from_fb_response( $me ) {
		if ( @$me['first_name'] && @$me['last_name'] ) {
			$name = preg_replace( '/[^a-zA-Z0-9_]+/', '', ucfirst( $me['first_name'] ) . '_' . ucfirst( $me['last_name'] ) );
		} else if ( isset( $me['name'] ) ) {
			$name = $me['name'];
		} else {
			list( $name, $rest ) = explode( '@', $me['email'] );
		}
		$username = strtolower( preg_replace( '/[^a-zA-Z0-9_]+/', '', $name ) );
		while ( username_exists( $username ) ) {
			$username .= rand();
		}

		return apply_filters( 'wdfb-registration-username', $username, $me );
	}

}