<div class="account-partner-wrapper">
	<h4 class="text-center">
	    <?php _e('Welcome', 'remember-forever'); ?> <?php echo ($current_user) ? esc_html($current_user->user_login) : ''; ?>
	</h4>
	<div class="alert alert-danger text-center">
	    <?php _e('Your account has not yet been verified. After approval by the administrator, you will receive a notification via email.', 'remember-forever'); ?>
	</div>

</div>
