<?php
if ($error = get_transient('rmf_login_error')) {
    if ($error === 'role') {
        echo '<div class="alert alert-danger">' . esc_html__('Masz nieprawidłową rolę użytkownika.', 'remember-forever') . '</div>';
    } elseif ($error === 'credentials') {
        echo '<div class="alert alert-danger">' . esc_html__('Nieprawidłowy email lub hasło.', 'remember-forever') . '</div>';
    }
    delete_transient('rmf_login_error');
}
?>

<?php if(!is_user_logged_in()): ?>
<div class="rmf-partners-login-form-wrap">
    <div class="justify-content-center">
        <div class="rmf-partners-form-container">
            <div class="alert alert-info text-center">
                <?php _e('Zaloguj sie na swoje konto i wygeneruj katalog produktów w formacie PDF', 'remember-forever'); ?>
            </div>

            <div class="partner-login-error">
                <?php do_action('rmf_partner_login_error'); ?>
            </div>

            <form method="post" class="border p-4 rounded bg-light shadow-sm">
                
                <div class="mb-3">
                    <label for="email" class="form-label"><?php _e('Email', 'remember-forever'); ?></label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label"><?php _e('Hasło', 'remember-forever'); ?></label>
                    <input type="password" name="password" minlength="6" id="password" class="form-control" required>
                </div>

                <div class="d-grid">
                    <?php wp_nonce_field('rmf_partner_login_action', 'rmf_partner_login_nonce'); ?>

                    <button type="submit" name="partner_login" class="btn btn-primary">
                        <?php _e('Zaloguj się', 'remember-forever'); ?>
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
        <h2>Jesteś zalogowany jako partner</h2>
    <?php else: ?>
        <h2>Nie masz roli partnera biznesowego</h2>
    <?php endif; ?>

<?php endif; ?>
