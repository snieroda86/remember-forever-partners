<div class="account-has_access-partner-wrapper">
	
	<div class="row g-5">
		<div class="col-md-4">
			<?php 
			$user_id = get_current_user_id();

			$company_name = get_user_meta($user_id, 'company_name', true);
			$company_address = get_user_meta($user_id, 'company_address', true);
			$company_nip = get_user_meta($user_id, 'company_nip', true);
			$partner_access = get_user_meta($user_id, 'partner_has_access', true);
			$partner_discount = get_user_meta($user_id, 'partner_percentage_discount', true);
			?>

			<table class="table table-bordered">
			    
			    <tbody>
			        <tr>
			            <th class="bg-light"><?php _e('Nazwa firmy', 'remember-forever'); ?></th>
			            <td><?php echo esc_html($company_name); ?></td>
			        </tr>
			        <tr>
			            <th class="bg-light"><?php _e('Adres firmy', 'remember-forever'); ?></th>
			            <td><?php echo esc_html($company_address); ?></td>
			        </tr>
			        <tr>
			            <th class="bg-light"><?php _e('NIP', 'remember-forever'); ?></th>
			            <td><?php echo esc_html($company_nip); ?></td>
			        </tr>
			        
			        <tr>
			            <th class="bg-light"><?php _e('Rabat(%)', 'remember-forever'); ?></th>
			            <td><?php echo esc_html($partner_discount); ?>%</td>
			        </tr>
			    </tbody>
			</table>
		</div>
		<div class="col-md-8">
			<h5 class="pb-4">
				<?php _e('Witaj', 'remember-forever'); ?> <?php echo ($current_user) ? esc_html($current_user->user_login) : ''; ?>
			</h5>
		</div>
	</div>
	
</div>
