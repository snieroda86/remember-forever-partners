<div class="account-has_access-partner-wrapper">
	<?php 


	 ?>
	<div class="row g-5">
		<div class="col-md-4">
			<?php 
			$current_lang = apply_filters( 'wpml_current_language', NULL );
			$user_id = get_current_user_id();
			$current_currency = get_woocommerce_currency();

			$company_name = get_user_meta($user_id, 'company_name', true);
			$company_address = get_user_meta($user_id, 'company_address', true);
			$company_nip = get_user_meta($user_id, 'company_nip', true);
			$partner_access = get_user_meta($user_id, 'partner_has_access', true);
			$partner_discount = get_user_meta($user_id, 'partner_percentage_discount', true);
			?>

			<table class="table table-bordered">
			    
			    <tbody>
				    <tr>
				        <th class="bg-light"><?php _e('Company name', 'remember-forever'); ?></th>
				        <td><?php echo esc_html($company_name); ?></td>
				    </tr>
				    <tr>
				        <th class="bg-light"><?php _e('Company address', 'remember-forever'); ?></th>
				        <td><?php echo esc_html($company_address); ?></td>
				    </tr>
				    <tr>
				        <th class="bg-light"><?php _e('VAT number', 'remember-forever'); ?></th>
				        <td><?php echo esc_html($company_nip); ?></td>
				    </tr>
				    
				    <!-- <tr>
				        <th class="bg-light"><?php _e('Discount (%)', 'remember-forever'); ?></th>
				        <td><?php echo esc_html($partner_discount); ?>%</td>
				    </tr> -->
				</tbody>
			    
			</table>

			<div class="pt-3">
				<?php $logout_url = wp_logout_url(home_url()); ?>
				<a href="<?php echo esc_url($logout_url) ?>" class="btn brn-danger">
					<?php _e('Log out' , 'remember-forever') ?>
				</a>
			</div>
		</div>
		<div class="col-md-8">
			<h5 class="pb-4">
			    <?php _e('Welcome', 'remember-forever'); ?> <?php echo ($current_user) ? esc_html($current_user->user_login) : ''; ?>
			</h5>
			<p>
			    <?php _e('Below you can generate a product catalog in PDF format for which we have granted you access.', 'remember-forever'); ?>
			</p>

			<div class="alert alert-info mb-2 mt-2">
			    <?php _e('Product prices generated in the catalog may differ from the prices shown next to the products due to currency exchange rate differences.', 'remember-forever'); ?>
			</div>

			<!-- Error display -->
			<div>
				<?php 
				if (isset($_GET['no_products']) && $_GET['no_products'] == '1') {
				    echo '<div class="alert alert-danger mt-3"><p>' . esc_html__('No products selected.', 'remember-forever') . '</p></div>';

				}
				 ?>

			</div>

			<!-- Generate catalog form -->
			<div class="pt-3">
				<?php global $sitepress; 
				if ( isset( $sitepress ) ) :
				$enabled_languages = $sitepress->get_active_languages(); 
				?>
				<div class="lang-cataloge-switch mb-3 mt-1 bg-light p-3">
					<div class="row g-3">
						<div class="col-12">
							<div><?php _e('Choose language' , 'remember-forever'); ?></div>
							<div><?php echo do_shortcode('[wpml_language_switcher type="widget" flags=1 native=1 translated=1][/wpml_language_switcher]'); ?></div>
						</div>
						
					</div>
				</div>
				<?php endif; ?>


				<?php  
				// Enabled languages
				
				if ( isset( $sitepress ) ) { 
					

					?>
				    <div>
				    	
				    	<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
							<input type="hidden" name="action" value="generuj_pdf">
							<?php wp_nonce_field('formularz_pdf', 'pdf_nonce'); ?>
								<input type="hidden" name="choosen_lang"  value="<?php echo esc_html($current_lang); ?>">
								<input type="hidden" name="current_currency" value="<?php echo esc_html($current_currency); ?>">
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
								   
							        <table class="table table-bordered partner-products-table-sn">
							            <thead>
										    <tr>
										        <th><?php _e('Product name', 'remember-forever') ?></th>
										        <th><?php _e('Price', 'remember-forever'); ?></th>
										        <th><?php _e('Image', 'remember-forever'); ?></th>
										        <th><?php _e('Select', 'remember-forever'); ?></th>
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
							                        <td>
							                        	<p><?php echo $product->get_price_html(); ?></p>
							                        	<p>
							                        		<?php 
							                        		// Get discoutn
							                        		global $wpdb;
															$table_name = $wpdb->prefix . 'partners_product_discount';

															$product_discount = $wpdb->get_var(
															    $wpdb->prepare(
															        "SELECT discount_percentage FROM $table_name WHERE user_id = %d AND product_id = %d",
															        $user_id,
															        $product_id
															    )
															);


							                        		 ?>
							                        		<?php 
														    $original_price = (float) $product->get_price();

														    if ($product_discount !== null && is_numeric($product_discount) && $product_discount >= 1) {
														        $discounted_price = $original_price * (1 - ($product_discount / 100));
														        $discounted_price = round($discounted_price); 
														        $formatted_price = number_format($discounted_price, 2, '.', ''); 

														       echo '<span class="pe-1">' . __('Price after discount:', 'remember-forever') . '</span>';

														        echo '<span>' . wc_price($formatted_price) . '</span>';
														    }
														?>

							                        		
							                        		
							                        	</p>
							                        </td>
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
								    <p><?php _e('No products found' , 'remember-forever'); ?></p>
								<?php endif; ?>

								</div>	

								<!-- Products end -->

								<div class="d-flex">
									<div class="pe-5 pt-2">
										
										<label>
											
											<input class="form-check-input" type="checkbox"  name="discount_apply_rm">
											<?php _e('Do not display prices in the catalog', 'remember-forever'); ?>

										</label>
										
									</div>
									<div>
										<input value="<?php _e('Generate PDF catalog', 'remember-forever'); ?>" class="btn btn-primary" type="submit" name="generate_catalog_rm_submit">

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
			
		});


	</script>
	
</div>
