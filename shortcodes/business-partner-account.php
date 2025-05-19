<?php 

if(!class_exists('RMB_Forever_Account')){
	class RMB_Forever_Account{

		public function __construct(){
			add_shortcode( 'rm_partner_account', array($this , 'rmb_forever_account_shortcode') );
			add_action('template_redirect', array($this, 'handle_login'));
			add_action('admin_post_generuj_pdf', [$this, 'rmf_generuj_pdf']);
		}

		public function rmb_forever_account_shortcode(){
			ob_start();
			wp_enqueue_style( 'rmf-partners-account-style' );
			?>
			
		    <?php require RMF_SN_PATH.'/views/business-partner-login-form.php'; ?>
		    <?php
		    return ob_get_clean();
		}

		public function handle_login() {
			if (
		        isset($_POST['partner_login']) &&
		        isset($_POST['rmf_partner_login_nonce']) &&
		        wp_verify_nonce($_POST['rmf_partner_login_nonce'], 'rmf_partner_login_action')
		    ) {
		        $email    = sanitize_email($_POST['email']);
		        $password = $_POST['password'];
		        $user     = get_user_by('email', $email);

		        if ($user && wp_check_password($password, $user->user_pass, $user->ID)) {
		            if (in_array('partner_biznesowy', (array) $user->roles)) {
		                wp_set_current_user($user->ID);
		                wp_set_auth_cookie($user->ID);
		                do_action('wp_login', $user->user_login, $user);
		                return;
		            } else {
		                set_transient('rmf_login_error', 'role', 30);
		                return;
		            }
		        } else {
		            set_transient('rmf_login_error', 'credentials', 30);
		            return;
		        }
		    }
		}


		// Generat pdf catalogue
		public function rmf_generuj_pdf(){
			if (!isset($_POST['pdf_nonce']) || !wp_verify_nonce($_POST['pdf_nonce'], 'formularz_pdf')) {
		        wp_die('Błąd weryfikacji');
		    }

		    // $imie = sanitize_text_field($_POST['imie']);
		    // $nazwisko = sanitize_text_field($_POST['nazwisko']);

		    require_once RMF_SN_PATH . 'vendor/autoload.php';

		    $mpdf = new \Mpdf\Mpdf(['default_font' => 'dejavusans']);
		    
		    ob_start();
			require RMF_SN_PATH . 'views/account/generate-pdf-process.php';
			$html = ob_get_clean();

		    $mpdf->WriteHTML($html);
		    $mpdf->Output("dokument.pdf", "D"); // 'I' = otworzy sie w przegladarce, 'D' = automatycznie się pobierze 
		}




	}
}