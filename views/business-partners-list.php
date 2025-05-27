<div class="wrap">
	<h1 class="wp-heading-inline">Partnerzy biznesowi</h1>

	<table class="widefat fixed" cellspacing="0">
	    <thead>
	    <tr>

            <th id="cb" class="manage-column column-cb check-column" scope="col">L.P.</th> 
            <th id="columnname" class="manage-column column-columnname" scope="col">
            	Nazwa firmy
            </th>
            <th id="columnname" class="manage-column column-columnname" scope="col">
            	Dostęp
            </th>
            <!--  <th id="columnname" class="manage-column column-columnname" scope="col">
            	Rabat ( % )
            </th> -->
            <th id="columnname" class="manage-column column-columnname num" scope="col" style="text-align: right;">
            	Akcja
            </th> 

	    </tr>
	    </thead>

	    <tbody>
	    	<?php if ( isset($users) && !empty($users)) : ?>
	        	<?php foreach ($users as $key => $user) : ?>
	        		<tr class="alternate business-partner-<?php echo $user->ID ?>">
			            <th class="check-column" scope="row" style="text-align: center;">
			            	#<?php echo $key + 1; ?>
			            </th>
			            <td class="column-columnname">
			            	<?php 
			            	$user_company = get_user_meta($user->ID , 'company_name' , true); 
			            	if($user_company){
			            		echo $user_company; 
			            	}
			            	?>
			            </td>
			            <td class="column-columnname">
			            	
			            	<?php 
			            	$partner_has_access = get_user_meta($user->ID , 'partner_has_access' , true); 

							if ((int) $partner_has_access === 0) {
							    echo '<span style="display:inline-block; padding:3px 8px; background-color:#dc3545; color:#fff; border-radius:5px;">Brak dostępu</span>';
							} else {
							    echo '<span style="display:inline-block; padding:3px 8px; background-color:#28a745; color:#fff; border-radius:5px;">Dostęp przyznany</span>';
							}

			            	
			            	?>
			            </td>
			            
			           <!--  <td>
						    <?php 
						    $partner_percentage_discount = get_user_meta($user->ID , 'partner_percentage_discount' , true); 

						    if ((int) $partner_percentage_discount === 0 || is_null($partner_percentage_discount)) {
						        echo 'Brak rabatu';
						    } else {
						        echo esc_html($partner_percentage_discount) . '%';
						    }
						    ?>
						</td> -->


			            <td class="column-columnname" style="text-align: right;">
			            	<a href="<?php echo admin_url('admin.php?page=remember_forever_partner_details&user_id=' . $user->ID); ?>" class="button">Szczegóły</a>

			            </td>

			        </tr>
	        	<?php endforeach; ?>
	        <?php endif; ?>
	    </tbody>
</table>

</div>