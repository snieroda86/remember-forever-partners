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
                <?php _e('Create a business customer account and become our partner!', 'remember-forever'); ?>


                <br>
                <?php if($business_partner_login_form): ?>
                <?php _e('Already have a partner account?', 'remember-forever'); ?>

                <a href="<?php echo esc_url($business_partner_login_form) ?>"><?php _e('Log in', 'remember-forever'); ?></a>
                <?php endif; ?>
            </div>

            <form method="post" class="border p-4 rounded bg-light shadow-sm">
                <div class="mb-3">
                    <label for="company_name" class="form-label"><?php _e('Company name', 'remember-forever'); ?></label>
                    <input type="text" name="company_name" id="company_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="company_address" class="form-label"><?php _e('Company address', 'remember-forever'); ?></label>
                    <input type="text" name="company_address" id="company_address" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="company_nip" class="form-label"><?php _e('VAT number', 'remember-forever'); ?></label>
                    <input type="text" name="company_nip" id="company_nip" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="company_user_login" class="form-label"><?php _e('Username', 'remember-forever'); ?></label>
                    <input type="text" name="company_user_login" id="company_user_login" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label"><?php _e('Email', 'remember-forever'); ?></label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label"><?php _e('Password', 'remember-forever'); ?></label>
                    <input type="password" name="password" minlength="6" id="password" class="form-control" required>
                </div>

                <div class="d-grid">
                    <?php wp_nonce_field('rmf_partner_register_action', 'rmf_partner_register_nonce'); ?>

                    <button type="submit" name="partner_register" class="btn btn-primary">
                        <?php _e('Register', 'remember-forever'); ?>
                    </button>
                </div>
            </form>


            
        </div>
    </div>
</div>
