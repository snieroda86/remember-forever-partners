<?php
if ($error = get_transient('rmf_login_error')) {
    if ($error === 'role') {
        echo '<div class="alert alert-danger">' . esc_html__('You have an invalid user role.', 'remember-forever') . '</div>';
    } elseif ($error === 'credentials') {
        echo '<div class="alert alert-danger">' . esc_html__('Invalid email or password.', 'remember-forever') . '</div>';
    }

    delete_transient('rmf_login_error');
}
?>

<?php if(!is_user_logged_in()): ?>
<div class="rmf-partners-login-form-wrap">
    <div class="justify-content-center">
        <div class="rmf-partners-form-container">
            <div class="alert alert-info text-center">
                <?php _e('Log in to your account and generate a product catalog in PDF format', 'remember-forever'); ?>

            </div>

            <div class="partner-login-error">
                <?php do_action('rmf_partner_login_error'); ?>
            </div>

            <form method="post" class="border p-4 rounded bg-light shadow-sm">
                
                <div class="mb-3">
                    <label for="email" class="form-label"><?php _e('Email address', 'remember-forever'); ?></label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label"><?php _e('Password', 'remember-forever'); ?></label>
                    <input type="password" name="password" minlength="6" id="password" class="form-control" required>
                </div>

                <div class="d-grid">
                    <?php wp_nonce_field('rmf_partner_login_action', 'rmf_partner_login_nonce'); ?>

                    <button type="submit" name="partner_login" class="btn btn-primary">
                        <?php _e('Log in', 'remember-forever'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php else: ?>

    <?php 
    $current_user = wp_get_current_user(); 
    if (in_array('partner_biznesowy', (array) $current_user->roles)): ?>
        
        <?php 
        $current_user_access = get_user_meta($current_user->ID , 'partner_has_access' , true); 
        if((int)$current_user_access == 0){
            $file = RMF_SN_PATH . 'views/account/account-access-danied.php';
            if (file_exists($file)) {
                require $file;
            } else {
                echo 'Access file missing.';
            }
        }elseif((int)$current_user_access == 1){
            $file = RMF_SN_PATH . 'views/account/account-full-access.php';
            if (file_exists($file)) {
                require $file;
            } else {
                echo 'Access file missing.';
            }
        }else{
            $file = RMF_SN_PATH . 'views/account/account-access-danied.php';
            if (file_exists($file)) {
                require $file;
            } else {
                echo 'Access file missing.';
            }
        }
        
        ?>

    <?php else: ?>
        <h2><?php _e('You do not have a business partner role', 'remember-forever'); ?></h2>

    <?php endif; ?>

<?php endif; ?>
