<?php 
$business_partner_account_page_id = get_option('business_partner_account_page_id');
if($business_partner_account_page_id){

    $business_partner_login_form = get_permalink($business_partner_account_page_id);
} 
?>
<div class=" rmf-partners-form-wrap">
    <div class="justify-content-center">
        <div class="rmf-partners-form-container">
            <div class="alert alert-info text-center">
                <?php _e('Załóż konto klienta biznesowego i zostań naszym partnerem!', 'remember-forever'); ?>

                <br>
                <?php if($business_partner_login_form): ?>
                <?php _e('Masz już konto partnera?', 'remember-forever'); ?>
                <a href="<?php echo esc_url($business_partner_login_form) ?>"><?php _e('Zaloguj się', 'remember-forever'); ?></a>
                <?php endif; ?>
            </div>

            <form method="post" class="border p-4 rounded bg-light shadow-sm">
                <div class="mb-3">
                    <label for="company_name" class="form-label"><?php _e('Nazwa firmy', 'remember-forever'); ?></label>
                    <input type="text" name="company_name" id="company_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="company_address" class="form-label"><?php _e('Adres firmy', 'remember-forever'); ?></label>
                    <input type="text" name="company_address" id="company_address" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="company_nip" class="form-label"><?php _e('Numer NIP', 'remember-forever'); ?></label>
                    <input type="text" name="company_nip" id="company_nip" class="form-control" required>
                </div>

                 <div class="mb-3">
                    <label for="company_user_Login" class="form-label"><?php _e('nazwa użytkownika', 'remember-forever'); ?></label>
                    <input type="text" name="company_user_login" id="company_user_login" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label"><?php _e('Email', 'remember-forever'); ?></label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label"><?php _e('Hasło', 'remember-forever'); ?></label>
                    <input type="password" name="password" minlength="6" id="password" class="form-control" required>
                </div>

                <div class="d-grid">
                    <?php wp_nonce_field('rmf_partner_register_action', 'rmf_partner_register_nonce'); ?>

                    <button type="submit" name="partner_register" class="btn btn-primary">
                        <?php _e('Zarejestruj się', 'remember-forever'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
