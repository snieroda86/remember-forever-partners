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

			<div class="pt-3">
				<?php $logout_url = wp_logout_url(home_url()); ?>
				<a href="<?php echo esc_url($logout_url) ?>" class="btn brn-danger">
					<?php _e('Wyloguj się' , 'remember-forever') ?>
				</a>
			</div>
		</div>
		<div class="col-md-8">
			<h5 class="pb-4">
				<?php _e('Witaj', 'remember-forever'); ?> <?php echo ($current_user) ? esc_html($current_user->user_login) : ''; ?>
			</h5>
			<p>
				<?php _e('Poniżej masz możliwość wygenerowania katalogu produktów  w formacie PDF, do których przyznaliśmy Ci dpostęp.', 'remember-forever'); ?>
				<br>
				<?php _e('Aby zastosować rabat w cenach produktów zaznacz odpowiedni checkbox obok przycisku "Generuj katalog".', 'remember-forever'); ?>
			</p>

			<!-- Generate catalog form -->
			<div class="pt-3">
				<?php global $sitepress; 
				if ( isset( $sitepress ) ) :
				$enabled_languages = $sitepress->get_active_languages(); 

				?>
				<ul class="generate-catalog-tabs">

				<?php if(is_array($enabled_languages)): ?>
				  	<?php foreach($enabled_languages as $lang): ?>
				  		<li class="generate-catalog-tabs-item" data-lang-tab="<?php echo $lang['code'] ?>">
				  			<?php echo $lang['display_name'] ?>
				  		</li>
				  	<?php endforeach; ?>
				<?php endif; ?>
				</ul>
				<?php endif; ?>


				<?php  
				// Enabled languages
				

				if ( isset( $sitepress ) ) {
				    $enabled_languages = $sitepress->get_active_languages();

				    if($enabled_languages){ 
				    	// echo '<pre>';
				    	// print_r($enabled_languages);
				    	// echo '</pre>';

				    	?>
				    	<table class="table table-bordered">
				  			<?php if(is_array($enabled_languages)): ?>
				  				<?php foreach($enabled_languages as $lang): ?>
				  					<tr>
				  						<th>
				  							<span><?php echo $lang['native_name']; ?></span>
				  							<?php
				  							$flag_url = RMF_SN_URL.'assets/flags/'.$lang['code'].'.svg'; 
				  							$flag_path = RMF_SN_PATH . 'assets/flags/' . $lang['code'] . '.svg'; 
				  							if(file_exists($flag_path)){ ?>
				  								<span class="ps-2">
				  									<img style="width:20px;" src="<?php echo esc_url($flag_url ); ?>">
				  								</span>
				  							<?php } 
				  							
				  							?>
				  						</th>

				  						<td>
				  							<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
				  								<input type="hidden" name="action" value="generuj_pdf">
    											<?php wp_nonce_field('formularz_pdf', 'pdf_nonce'); ?>
				  								<input type="hidden" name="choosen_lang"  value="<?php echo esc_html($lang['code']); ?>">
				  								<?php 
				  								$partner_tags_ids = get_user_meta($user_id , $lang['code'].'_partner_p_tags_assigned' , true);
				  								if( !empty($partner_tags_ids) && is_array($partner_tags_ids)){
				  									$partner_tags_ids_json = json_encode($partner_tags_ids);
				  								}else{
													$partner_tags_ids_json = json_encode([]);
				  								}
				  								?>
				  								<input type="hidden" name="partner_tags_ids"  value="<?php echo esc_attr($partner_tags_ids_json); ?>">

				  								<div class="d-flex">
				  									<div class="px-5">
				  										<?php if($partner_discount && $partner_discount > 0 ): ?>
				  											<label>
				  												<?php _e('Zastosuj rabat' , 'remember-forever'); ?>
				  												<input class="form-check-input" type="checkbox"  value="<?php echo $partner_discount ?>" name="discount_apply_rm">
				  											</label>
				  										<?php endif; ?>
				  									</div>
				  									<div>
				  										<input value="<?php _e('Generuj katalog PDF' , 'remember-forever'); ?>" class="btn btn-primary" type="submit" name="generate_catalog_rm_submit">
				  									</div>

				  								</div>
				  							</form>
				  						</td>
				  					</tr>
				  				<?php endforeach; ?>
				  			<?php endif; ?>
						</table>
				    <?php }
				      
				} else {
				    echo 'Wymagana jest wtyczka WPML';
				}
				?>
			</div>

		</div>


	</div>
	
</div>
