<div class="account-partner-wrapper">
	<h4 class="text-center">
		<?php _e('Witaj', 'remember-forever'); ?> <?php echo ($current_user) ? esc_html($current_user->user_login) : ''; ?>
	</h4>
	<div class="alert alert-danger text-center">
		<?php _e('Twoje konto nie zostało jeszcze zweryfikowane. Po zatwierdzeniu przez administratora otrzymasz powiadomienie drogą mailową.', 'remember-forever'); ?>
	</div>
</div>
