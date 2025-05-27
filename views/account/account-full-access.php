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

			<!-- Error display -->
			<div>
				<?php 
				if (isset($_GET['no_products']) && $_GET['no_products'] == '1') {
				    echo '<div class="alert alert-danger mt-3"><p>' . esc_html__('Nie wybrano produktów.', 'remember-forever') . '</p></div>';
				}
				 ?>

			</div>

			<!-- Generate catalog form -->
			<div class="pt-3">
				<?php global $sitepress; 
				if ( isset( $sitepress ) ) :
				$enabled_languages = $sitepress->get_active_languages(); 
				?>
				<div class="lang-cataloge-switch pb-2">
					<?php echo do_shortcode('[wpml_language_switcher type="widget" flags=1 native=1 translated=1][/wpml_language_switcher]'); ?>
				</div>
				<?php endif; ?>


				<?php  
				// Enabled languages
				
				if ( isset( $sitepress ) ) { 
					$current_lang = apply_filters( 'wpml_current_language', NULL );

					?>
				    <div>
				    	
				    	<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
							<input type="hidden" name="action" value="generuj_pdf">
							<?php wp_nonce_field('formularz_pdf', 'pdf_nonce'); ?>
								<input type="hidden" name="choosen_lang"  value="<?php echo esc_html($current_lang); ?>">
								<?php 
								$partner_tags_ids = get_user_meta($user_id , $current_lang.'_partner_p_tags_assigned' , true);
								if( !empty($partner_tags_ids) && is_array($partner_tags_ids)){
									$partner_tags_ids_json = json_encode($partner_tags_ids);
								}else{
								$partner_tags_ids_json = json_encode([]);
								}
								?>
								<input type="hidden" name="partner_tags_ids"  value="<?php echo esc_attr($partner_tags_ids_json); ?>">
								<!-- Products -->

								<div>
									<?php

								
								$args = array(
								        'post_type'      => 'product',
									    'posts_per_page' => -1,
									    'orderby'        => 'date',
									    'order'          => 'DESC',
									    'suppress_filters' => false,
										'tax_query' => array(
									        array(
									            'taxonomy' => 'product_tag',
									            'field'    => 'term_id',
									            'terms'    => $partner_tags_ids,
									        ),
								    ),
								);


								$query = new WP_Query($args);
								?>

								<?php if ($query->have_posts()) : ?>
								   
							        <table class="table table-bordered">
							            <thead>
							                <tr>
							                    <th>Nazwa produktu</th>
							                    <th>Obrazek</th>
							                    <th>Wybierz</th>
							                </tr>
							            </thead>
							            <tbody>
							                <?php while ($query->have_posts()) : $query->the_post(); ?>
							                    <?php
							                    global $product;
							                    $product_id = $product->get_id();
							                    $title = get_the_title();
							                    $image = wp_get_attachment_image($product->get_image_id(), 'thumbnail');
							                    ?>
							                    <tr>
							                        <td><?php echo esc_html($title); ?></td>
							                        <td><?php echo $image; ?></td>
							                        <td>
							                            <input type="checkbox" name="selected_products[]" value="<?php echo esc_attr($product_id); ?>">
							                        </td>
							                    </tr>
							                <?php endwhile; ?>
							            </tbody>
							        </table>
								   
								    <?php wp_reset_postdata(); ?>
								<?php else : ?>
								    <p><?php _e('Brak produktów' , 'remember-forever'); ?></p>
								<?php endif; ?>

								</div>

								<!-- Products end -->

								<div class="d-flex">
									<div class="pe-5 pt-2">
										
										<label>
											
											<input class="form-check-input" type="checkbox"  name="discount_apply_rm">
											<?php _e('Nie wyświetlaj cen w katalogu' , 'remember-forever'); ?>
										</label>
										
									</div>
									<div>
										<input value="<?php _e('Generuj katalog PDF' , 'remember-forever'); ?>" class="btn btn-primary" type="submit" name="generate_catalog_rm_submit">
									</div>

								</div>
							</form>
						<!-- Form end -->
						
				    </div>
				<?php       
				} else {
				    echo 'Wymagana jest wtyczka WPML';
				}
				?>
			</div>

		</div>


	</div>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			// var tabsNavItems = document.querySelectorAll('.generate-catalog-tabs li');
			// var tabContents = document.querySelectorAll('.tab-content');

			
			// tabsNavItems.forEach(function(item) {
			// 	item.classList.remove('active');
			// });
			// tabContents.forEach(function(content) {
			// 	content.style.display = 'none';
			// });

			
			// if (tabsNavItems.length > 0) {
			// 	var firstTab = tabsNavItems[0];
			// 	firstTab.classList.add('active');

			// 	var lang = firstTab.getAttribute('data-lang-tab');
			// 	var firstContent = document.querySelector('.tab-content[data-lang="' + lang + '"]');
			// 	if (firstContent) {
			// 		firstContent.style.display = 'block';
			// 	}
			// }

			
			// tabsNavItems.forEach(function(item){
			// 	item.addEventListener("click", function(e){
			// 		e.preventDefault();

					
			// 		tabsNavItems.forEach(function(el) { el.classList.remove('active'); });
			// 		tabContents.forEach(function(content) { content.style.display = 'none'; });

					
			// 		this.classList.add('active');

					
			// 		var lang = this.getAttribute('data-lang-tab');
			// 		var selectedContent = document.querySelector('.tab-content[data-lang="' + lang + '"]');
			// 		if (selectedContent) {
			// 			selectedContent.style.display = 'block';
			// 		}
			// 	});
			// });
		});


	</script>
	
</div>
