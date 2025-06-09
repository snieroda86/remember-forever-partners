<?php 

if(!class_exists('RMB_Forever_Registration')){
	class RMB_Forever_Registration{

		private $admin_email;

		public function __construct(){
			$this->admin_email = get_option('admin_email');
			add_shortcode( 'rmb_forever_partners', array($this , 'rmb_forever_form_shortcode') );
		}

		public function rmb_forever_form_shortcode(){
			ob_start();
			wp_enqueue_style( 'rmf-partners-style' );

			if (isset($_POST['partner_register'])) {

				// Verift nonce
				 if (
			        !isset($_POST['rmf_partner_register_nonce']) ||
			        !wp_verify_nonce($_POST['rmf_partner_register_nonce'], 'rmf_partner_register_action')
			    ) {
			        echo '<div class="alert alert-danger">' . esc_html__('Security error: invalid token.', 'remember-forever') . '</div>';
			        return ob_get_clean(); 
			    }
		        
		        $company_name      = sanitize_text_field($_POST['company_name'] ?? '');
				$company_address   = sanitize_text_field($_POST['company_address'] ?? '');
				$company_nip       = sanitize_text_field($_POST['company_nip'] ?? '');
				$company_user_login = sanitize_user($_POST['company_user_login'] ?? '');
				$email             = sanitize_email($_POST['email'] ?? '');
				$password          = $_POST['password'] ?? '';

				$rmf_errors = [];

				// Nazwa firmy
				if (empty($company_name)) {
				    $rmf_errors['company_name'] = __('The company name cannot be empty.', 'remember-forever');
				}

				// Adres firmy
				if (empty($company_address)) {
				    $rmf_errors['company_address'] = __('The company address cannot be empty.
', 'remember-forever');
				}

				// NIP 
				if (empty($company_nip)) {
				    $rmf_errors['company_nip'] = __('The VAT number cannot be empty.
', 'remember-forever');
				} 

				// Nazwa użytkownika
				if (empty($company_user_login)) {
				    $rmf_errors['company_user_login'] = __('The username is required.
', 'remember-forever');
				}elseif (username_exists($company_user_login)) {
				    $rmf_errors['company_user_login'] = __('This username is already taken.
', 'remember-forever');
				}

				// Email
				if (empty($email)) {
				    $rmf_errors['email'] = __('The email address is required.', 'remember-forever');
				} elseif (!is_email($email)) {
				    $rmf_errors['email'] = __('Please provide a valid email address.', 'remember-forever');
				} elseif (email_exists($email)) {
				    $rmf_errors['email'] = __('This email address is already registered.', 'remember-forever');
				}

				// Hasło
				if (empty($password)) {
				    $rmf_errors['password'] = __('Password is required', 'remember-forever');
				} elseif (strlen($password) < 6) {
				    $rmf_errors['password'] = __('The password must be at least 6 characters long.', 'remember-forever');
				}

		      	if (empty($rmf_errors)) {

		      		$result = wp_create_user($company_user_login, $password, $email);
					if (is_wp_error($result)) {
					    echo '<div class="alert alert-danger">' . esc_html($result->get_error_message()) . '</div>';
					}else{

						$user_id = $result;
						wp_update_user(['ID' => $user_id, 'role' => 'partner_biznesowy']);
				    
					    update_user_meta($user_id, 'company_name', $company_name);
					    update_user_meta($user_id, 'company_address', $company_address);
					    update_user_meta($user_id, 'company_nip', $company_nip);
					    update_user_meta($user_id, 'partner_has_access', 0);
					    update_user_meta($user_id, 'partner_percentage_discount', 0);


					    // Send email to admin
					    $admin_subject = 'Rejestracja nowego partnera biznesowego na stronie rememberme-forever.com';
						$admin_message = 'Nowa rejestracja użytkownika na stronie.<br><br>';
						$admin_message .= 'Nazwa firmy: ' . esc_html($company_name) . '<br>';
						$admin_message .= 'Adres e-mail: ' . esc_html($email) . '<br>';


					    $headers = ['Content-Type: text/html; charset=UTF-8'];
					    wp_mail( $this->admin_email , $admin_subject, $admin_message, $headers );

					    // Send email to user
						$user_subject = __( 'Registration confirmation on rememberme-forever.com', 'remember-forever' );

						$user_message  = __( 'Congratulations!', 'remember-forever' ) . '<br><br>';
						$user_message .= __( 'You have successfully registered as our partner.', 'remember-forever' ) . '<br>';
						$user_message .= __( 'After verifying your data, you will be granted access to your account, where you will be able to generate a catalog of products from our offer.', 'remember-forever' ) . '<br><br>';
						$user_message .= __( 'Your username: ', 'remember-forever' ) . sanitize_text_field($company_user_login) . '<br><br>';
						$user_message .= __( 'Best regards,', 'remember-forever' ) . '<br>' . __( 'The rememberme-forever team', 'remember-forever' ) . '<br>';

						wp_mail( $email, $user_subject, $user_message, $headers );


					    echo '<div class="alert alert-success text-center">' . esc_html__('Registration completed successfully! After verifying your data, you will receive a confirmation email granting access.', 'remember-forever') . '</div>';


					}

				    
				} else {
				    echo '<div class="alert alert-danger">';
				    echo '<ul>';

				    foreach ($rmf_errors as $error) {
				    	echo '<li>';
				    	echo $error;
				    	echo '</li>';

				    }
				    echo '</ul>';
				    echo '</div>';
				}
  

		      
		    }
		    ?>
		    <?php require RMF_SN_PATH.'/views/business-partner-registration-form.php'; ?>
		    <?php
		    return ob_get_clean();
		}

		// Email  to admin 
		private function sendEmailToAdmin(){

		}



	}
}