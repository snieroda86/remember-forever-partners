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
			        echo '<div class="alert alert-danger">' . esc_html__('Błąd bezpieczeństwa: nieprawidłowy token.', 'remember-forever') . '</div>';
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
				    $rmf_errors['company_name'] = __('Nazwa firmy nie może być pusta.', 'remember-forever');
				}

				// Adres firmy
				if (empty($company_address)) {
				    $rmf_errors['company_address'] = __('Adres firmy nie może być pusty.', 'remember-forever');
				}

				// NIP 
				if (empty($company_nip)) {
				    $rmf_errors['company_nip'] = __('Numer NIP nie może być pusty.', 'remember-forever');
				} 

				// Nazwa użytkownika
				if (empty($company_user_login)) {
				    $rmf_errors['company_user_login'] = __('Nazwa użytkownika jest wymagana.', 'remember-forever');
				}elseif (username_exists($company_user_login)) {
				    $rmf_errors['company_user_login'] = __('Ta nazwa użytkownika jest już zajęta.', 'remember-forever');
				}

				// Email
				if (empty($email)) {
				    $rmf_errors['email'] = __('Adres email jest wymagany.', 'remember-forever');
				} elseif (!is_email($email)) {
				    $rmf_errors['email'] = __('Podaj poprawny adres email.', 'remember-forever');
				} elseif (email_exists($email)) {
				    $rmf_errors['email'] = __('Ten adres email jest już zarejestrowany.', 'remember-forever');
				}

				// Hasło
				if (empty($password)) {
				    $rmf_errors['password'] = __('Hasło jest wymagane.', 'remember-forever');
				} elseif (strlen($password) < 6) {
				    $rmf_errors['password'] = __('Hasło musi zawierać co najmniej 6 znaków.', 'remember-forever');
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
					    $user_subject = 'Potwierdzenie rejestracji na stronie rememberme-forever.com';
					    $user_message = 'Gratulacje!<br><br>';
						$user_message .= 'Poprawnie zarejestrowałeś się jako nasz partner.<br>';
						$user_message .= 'Po weryfikacji Twoich danych zostanie przyznany Ci dostęp do konta, gdzie będziesz mógł wygenerować katalog z produktami z naszej oferty.<br><br>';
						$user_message .= 'Twoja nazwa użytkownika: ' . sanitize_text_field($company_user_login) . '<br><br>';
						$user_message .= 'Pozdrawiamy,<br>Zespół rememberme-forever<br>';
						


					    wp_mail( $email , $user_subject, $user_message, $headers );

					    echo '<div class="alert alert-success text-center">' . esc_html__('Rejestracja zakończona sukcesem! Po weryfikacji Twoich danych otrzymasz maila z potwierdzeniem przyznania dostępu.', 'remember-forever') . '</div>';

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