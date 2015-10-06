<?php
class MS_View_Shortcode_Login extends MS_View {

	/**
	 * Returns the HTML code.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function to_html() {
		$res_html = '';
		$res_form = '';
		$html = '';

		$valid_forms = array(
			'login',
			'logout',
			'reset',
			'lost',
		);

		extract( $this->data );

		if ( ! isset( $form ) || ! in_array( $form, $valid_forms ) ) {
			if ( MS_Model_Member::is_logged_in() ) {
				$form = 'logout';
			} elseif ( isset( $action ) && 'resetpass' === $action ) {
				$form = 'reset';
			} else {
				$form = 'login';
			}

			$this->data['form'] = $form;
		}

		/**
		 * Provide a customized login form.
		 *
		 * Possible filters to provide a customized login form:
		 * - 'ms_shortcode_custom_form-login'
		 * - 'ms_shortcode_custom_form-logout'
		 * - 'ms_shortcode_custom_form-reset'
		 * - 'ms_shortcode_custom_form-lost'
		 *
		 * @since  1.0.0
		 */
		$html = apply_filters(
			'ms_shortcode_custom_form-' . $form,
			'',
			$this->data
		);

		if ( ! empty( $html ) ) {
			return $html;
		} else {
			$html = '';
		}

		if ( 'logout' === $form ) {
			return $this->logout_form();
		} elseif ( 'reset' === $form ) {
			return $this->reset_form();
		} else {
			if ( empty( $redirect_login ) ) {
				$redirect_login = MS_Helper_Utility::get_current_url();
			}

			// Build the Login Form.
			$res_form .= $prefix;
			$res_form .= $this->login_form( $redirect_login );
			$res_form .= $this->lostpass_form();

			// Wrap form in optional wrappers.
			if ( ! empty( $wrapwith ) ) {
				$res_form .= sprintf( '<%s class="%s">', esc_attr( $wrapwith ), esc_attr( $wrapwithclass ) );
				$res_form = sprintf(
					'<%1$s class="%2$s">%3$s</%1$s>',
					esc_attr( $wrapwith ),
					esc_attr( $wrapwithclass ),
					$res_form
				);
			}
			if ( ! empty( $item ) ) {
				$res_form = sprintf(
					'<%1$s class="%2$s">%3$s</%1$s>',
					esc_attr( $item ),
					esc_attr( $itemclass ),
					$res_form
				);
			}
			if ( ! empty( $holder ) ) {
				$res_form = sprintf(
					'<%1$s class="%2$s">%3$s</%1$s>',
					esc_attr( $holder ),
					esc_attr( $holderclass ),
					$res_form
				);
			}

			// Complete the HTML output.
			if ( $header ) {
				$html .= $this->login_header_html();
			}
			$html .= $res_form;

			if ( $register && ! MS_Model_Member::is_logged_in() ) {
				if ( MS_Model_Member::can_register() ) {
					$link = sprintf(
						'<a href="%1$s">%2$s</a>',
						MS_Controller_Frontend::get_registration_url( 'register' ),
						__( 'Register', MS_TEXT_DOMAIN )
					);

					/**
					 * Filter documented in wp-includes/general-template.php
					 */
					$html .= apply_filters( 'register', $link );
				}
			}

			// Load the ajax script that handles the Ajax login functions.
			wp_enqueue_script( 'ms-ajax-login' );

			lib2()->ui->data(
				'ms_ajax_login',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'loadingmessage' => __( 'Please wait...', MS_TEXT_DOMAIN ),
					'errormessage' => __( 'Request failed, please try again.', MS_TEXT_DOMAIN ),
				)
			);
		}
		// Remove linebreaks to bypass the "wpautop" filter.
		$html = str_replace( array( "\r\n", "\r", "\n" ), '', $html );

		$html = '<div class="ms-membership-form-wrapper">' . $html . '</div>';
		$html = apply_filters( 'ms_compact_code', $html );

		/*
		 * Possible filters to provide a customized login form:
		 * - 'ms_shortcode_form-login'
		 * - 'ms_shortcode_form-logout'
		 * - 'ms_shortcode_form-reset'
		 * - 'ms_shortcode_form-lost'
		 */
		return apply_filters(
			'ms_shortcode_form-' . $form,
			$html,
			$this->data
		);
	}

	/**
	 * Returns HTML partial with the header of the login form.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function login_header_html() {
		extract( $this->data );

		ob_start();
		?>
		<legend><?php echo esc_html( $title ); ?></legend>
		<?php if ( $show_note ) : ?>
		<div class="ms-alert-box ms-alert-error">
			<?php _e( 'Please log in to access this page.', MS_TEXT_DOMAIN ); ?>
		</div>
		<?php endif;

		return ob_get_clean();
	}

	/**
	 * Returns HTML partial with the actual login form.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $redirect_to URL to redirect to right after login.
	 * @return string
	 */
	private function login_form( $redirect_to = null ) {
		if ( empty( $redirect_to ) ) {
			// Default redirect is back to the current page
			$redirect_to = lib2()->net->current_url();
		}

		$defaults = array(
			'redirect_login' => $redirect_to,
			'label_username' => __( 'Username', MS_TEXT_DOMAIN ),
			'label_password' => __( 'Password', MS_TEXT_DOMAIN ),
			'label_remember' => __( 'Remember Me', MS_TEXT_DOMAIN ),
			'label_log_in' => __( 'Log In', MS_TEXT_DOMAIN ),
			'id_login_form' => 'loginform',
			'id_username' => 'user_login',
			'id_password' => 'user_pass',
			'id_remember' => 'rememberme',
			'id_login' => 'wp-submit',
			'show_remember' => true,
			'value_username' => '',
			'value_remember' => false, // Set this to true to default the "Remember me" checkbox to checked
		);

		/**
		 * Filter the default login form output arguments.
		 *
		 * @since  1.0.0
		 *
		 * @see wp_login_form()
		 *
		 * @param array $defaults An array of default login form arguments.
		 */
		$args = wp_parse_args(
			$this->data,
			apply_filters( 'login_form_defaults', $defaults )
		);

		extract( $args );

		$show_form = 'login' === $form ? '' : 'display:none';
		$form_class = 'ms-form ms-form-login';
		if ( $show_labels ) {
			$form_class .= ' ms-has-labels';
		} else {
			$form_class .= ' ms-no-labels';
		}
		if ( $autofocus ) {
			$form_class .= ' autofocus';
		}

		ob_start();
		?>
		<form
			name="<?php echo esc_attr( $id_login_form ); ?>"
			id="<?php echo esc_attr( $id_login_form ); ?>"
			action="login" method="post"
			class="<?php echo esc_attr( $form_class ); ?>"
			style="<?php echo esc_attr( $show_form ); ?>">

			<div class="form">
				<?php wp_nonce_field( 'ms-ajax-login' ); ?>
				<?php echo apply_filters( 'login_form_top', '', $args ); ?>
				<?php if ( 'top' === $nav_pos ) : ?>
					<div class="status" style="display:none"></div>
				<?php endif; ?>
				<p class="login-username ms-field">
					<?php if ( $show_labels ) : ?>
						<label for="<?php echo esc_attr( $id_username ); ?>">
						<?php echo esc_html( $label_username ); ?>
						</label>
					<?php endif; ?>
					<input
						type="text"
						name="log"
						id="<?php echo esc_attr( $id_username ); ?>"
						class="input focus"
						value="<?php echo esc_attr( $value_username ); ?>"
						size="20"
						placeholder="<?php echo esc_html( $label_username ); ?>">
				</p>
				<p class="login-password ms-field">
					<?php if ( $show_labels ) : ?>
						<label for="<?php echo esc_attr( $id_password ); ?>">
						<?php echo esc_html( $label_password ); ?>
						</label>
					<?php endif; ?>
					<input
						type="password"
						name="pwd"
						id="<?php echo esc_attr( $id_password ); ?>"
						class="input"
						value=""
						size="20"
						placeholder="<?php echo esc_html( $label_password ); ?>">
				</p>
				<?php echo apply_filters( 'login_form_middle', '', $args ); ?>
				<?php if ( 'top' === $nav_pos ) : ?>
					<div class="nav">
						<p><a class="lost" href="#lostpassword">
							<?php _e( 'Lost your password?', MS_TEXT_DOMAIN ); ?>
						</a></p>
					</div>
				<?php endif; ?>
				<?php if ( $show_remember ) : ?>
				<p class="login-remember ms-field">
					<input
						name="rememberme"
						type="checkbox"
						id="<?php echo esc_attr( $id_remember ); ?>"
						value="forever"
						<?php checked( $value_remember ); ?> />
					<label for="<?php echo esc_attr( $id_remember ); ?>">
						<?php echo esc_html( $label_remember ); ?>
					</label>
				</p>
				<?php endif; ?>
				<p class="login-submit">
					<input
						type="submit"
						name="wp-submit"
						id="<?php echo esc_attr( $id_login ); ?>"
						class="button-primary"
						value="<?php echo esc_attr( $label_log_in ); ?>" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_login ); ?>" />
				</p>
				<?php echo apply_filters( 'login_form_bottom', '', $args ); ?>
			<?php if ( 'bottom' === $nav_pos ) : ?>
				<div class="status" style="display:none"></div>
			</div>
			<div class="nav">
				<p><a class="lost" href="#lostpassword"><?php _e( 'Lost your password?', MS_TEXT_DOMAIN ); ?></a></p>
			<?php endif; ?>
			</div>
		</form>
		<?php
		$html = ob_get_clean();
		$html = apply_filters( 'ms_compact_code', $html );
		return $html;
	}

	/**
	 * Returns the HTML partial of the lost-password form
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function lostpass_form() {
		$defaults = array(
			'label_lost_username' => __( 'Username or E-mail', MS_TEXT_DOMAIN ),
			'label_lostpass' => __( 'Reset Password', MS_TEXT_DOMAIN ),
			'id_lost_form' => 'lostpasswordform',
			'id_lost_username' => 'user_login',
			'id_lostpass' => 'wp-submit',
			'value_username' => '',
		);

		/**
		 * Filter the default login form output arguments.
		 *
		 * @since  1.0.0
		 *
		 * @see wp_login_form()
		 *
		 * @param array $defaults An array of default login form arguments.
		 */
		$args = wp_parse_args( $this->data, apply_filters( 'login_form_defaults', $defaults ) );

		extract( $args );

		$show_form = 'lost' === $form ? '' : 'display:none';
		$form_class = 'ms-form ms-form-lost';
		if ( $show_labels ) {
			$form_class .= ' ms-has-labels';
		} else {
			$form_class .= ' ms-no-labels';
		}

		ob_start();
		do_action( 'lost_password' );
		?>
		<form
			name="<?php echo esc_attr( $id_lost_form ); ?>"
			id="<?php echo esc_attr( $id_lost_form ); ?>"
			action="lostpassword"
			method="post"
			class="<?php echo esc_attr( $form_class ); ?>"
			style="<?php echo esc_attr( $show_form ); ?>">
			<div class="form">
				<?php wp_nonce_field( 'ms-ajax-lostpass' ); ?>
				<?php echo apply_filters( 'lostpass_form_top', '', $args ); ?>
				<?php if ( 'top' === $nav_pos ) : ?>
					<div class="status" style="display:none"></div>
				<?php endif; ?>
				<p class="lostpassword-username ms-field">
					<?php if ( $show_labels ) : ?>
						<label for="<?php echo esc_attr( $id_lost_username ); ?>" >
						<?php echo esc_html( $label_lost_username ); ?>
						</label>
					<?php endif; ?>
					<input
						type="text"
						name="user_login"
						id="<?php echo esc_attr( $id_lost_username ); ?>"
						class="input focus"
						value="<?php echo esc_attr( $value_username ); ?>"
						size="20"
						placeholder="<?php echo esc_html( $label_lost_username ); ?>" />
				</p>
				<?php echo apply_filters( 'lostpass_form_middle', '', $args ); ?>
				<?php if ( 'top' === $nav_pos ) : ?>
					<div class="nav">
						<p><a class="login" href="#login"><?php _e( 'Log in', MS_TEXT_DOMAIN ); ?></a></p>
					</div>
				<?php endif; ?>
				<?php
				/**
				 * Fires inside the lostpassword <form> tags, before the hidden fields.
				 *
				 * @since  1.0.0
				 */
				do_action( 'lostpassword_form' ); ?>
				<p class="submit">
					<input
						type="submit"
						name="wp-submit"
						id="<?php echo esc_attr( $id_lostpass ); ?>"
						class="button-primary"
						value="<?php echo esc_attr( $label_lostpass ); ?>" />
				</p>
				<?php echo apply_filters( 'lostpass_form_bottom', '', $args ); ?>
			<?php if ( 'bottom' === $nav_pos ) : ?>
				<div class="status" style="display:none"></div>
			</div>
			<div class="nav">
				<p><a class="login" href="#login"><?php _e( 'Log in', MS_TEXT_DOMAIN ); ?></a></p>
			<?php endif; ?>
			</div>
		</form>
		<?php
		$html = ob_get_clean();
		$html = apply_filters( 'ms_compact_code', $html );

		return $html;
	}

	/**
	 * Returns HTML partial that contains the logout form
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function logout_form() {
		if ( ! MS_Model_Member::is_logged_in() ) { return ''; }
		$member = MS_Model_Member::get_current_member();

		extract( $this->data );

		if ( empty( $redirect_logout ) ) {
			$redirect_logout = home_url();
		}

		$yourname = sprintf(
			__( 'You are logged in as %s.', MS_TEXT_DOMAIN ),
			ucfirst( $member->name )
		);

		$html = sprintf(
			'%1$s <a class="login_button" href="%2$s">%3$s</a>',
			$yourname,
			wp_logout_url( $redirect_logout ),
			__( 'Logout', MS_TEXT_DOMAIN )
		);

		if ( ! empty( $holder ) ) {
			$html = sprintf(
				'<%1$s class="%2$s">%3$s</%1$s>',
				esc_attr( $holder ),
				esc_attr( $holderclass ),
				$html
			);
		}

		return $html;
	}

	/**
	 * Returns HTML partial that contains password-reset form.
	 * Based on WordPress core code from wp-login.php
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function reset_form() {
		ob_start();

		lib2()->array->equip_get( 'login', 'key' );
		$rp_login = wp_unslash( $_GET['login'] );
		$rp_key = wp_unslash( $_GET['key'] );
		$err_msg = new WP_Error();

		// Get the user object and validate the key.
		if ( $rp_login && $rp_key ) {
			$user = check_password_reset_key( $rp_key, $rp_login );
		} else {
			$user = false;
		}

		lib2()->array->strip_slashes( $_POST, 'pass1', 'pass2' );

		// If the user was not found then redirect to an error page.
		if ( ! $user || is_wp_error( $user ) ) {
			if ( $user && 'expired_key' == $user->get_error_code() ) {
				$err_msg->add( 'password_expired_key', __( 'The password-reset key is already expired.', MS_TEXT_DOMAIN ) );
			} else {
				$err_msg->add( 'password_invalid_key', __( 'The password-reset key is invalid or missing.', MS_TEXT_DOMAIN ) );
			}
			$url = esc_url_raw(
				remove_query_arg( array( 'action', 'key', 'login' ) )
			);

			return sprintf(
				'<p>%s</p><p><a href="%s">%s</a>',
				$err_msg,
				$url,
				__( 'Request a new password-reset key', MS_TEXT_DOMAIN )
			);
		} else {
			// If the user provided a new password, then check it now.
			if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] ) {
				$err_msg->add( 'password_reset_mismatch', __( 'The passwords do not match.', MS_TEXT_DOMAIN ) );
			}
		}

		// This action is documented in wp-login.php
		do_action( 'validate_password_reset', $err_msg, $user );

		if ( ! count( $err_msg->errors )
			&& isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] )
		) {
			reset_password( $user, $_POST['pass1'] );

			// All done!
			return __( 'Your Password has been reset.', MS_TEXT_DOMAIN );
		}

		wp_enqueue_script( 'utils' );
		wp_enqueue_script( 'user-profile' );

		if ( count( $err_msg->errors ) ) {
			echo '<p class="error">' . implode( '<br/>', $err_msg->get_error_messages() ) . '</p>';
		}
		?>
		<form name="resetpassform" id="resetpassform" action="" method="post" autocomplete="off">
			<input type="hidden" id="user_login" value="<?php echo esc_attr( $rp_login ); ?>" autocomplete="off"/>

			<p>
				<label for="pass1"><?php _e( 'New password', MS_TEXT_DOMAIN ) ?><br/>
					<input type="password" name="pass1" id="pass1" class="input" size="20" value="" autocomplete="off"/></label>
			</p>

			<p>
				<label for="pass2"><?php _e( 'Confirm new password', MS_TEXT_DOMAIN ) ?><br/>
					<input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off"/></label>
			</p>

			<div id="pass-strength-result"
				class="hide-if-no-js"><?php _e( 'Strength indicator', MS_TEXT_DOMAIN ); ?></div>
			<p class="description indicator-hint"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).', MS_TEXT_DOMAIN ); ?></p>

			<br class="clear"/>

			<?php
			// This action is documented in wp-login.php
			do_action( 'resetpass_form', $user );
			?>
			<p class="submit"><input type="submit" name="wp-submit" id="wp-submit"
				class="button button-primary button-large"
				value="<?php esc_attr_e( 'Reset Password' ); ?>"/></p>
		</form>
		<?php
		$html = ob_get_clean();
		$html = apply_filters( 'ms_compact_code', $html );

		return $html;
	}
}