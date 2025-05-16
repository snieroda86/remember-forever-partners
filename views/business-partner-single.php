<?php 

$user = get_userdata($user_id);
if (!$user) {
    echo '<div class="notice notice-error"><p>Użytkownik nie istnieje.</p></div>';
    return;
}

// Enabled languages
global $sitepress;

if ( isset( $sitepress ) ) {
    $enabled_languages = $sitepress->get_active_languages();
    
   
} else {
    echo 'WPML nie jest dostępny.';
}

// Currnet lang code 
$current_lang_code = apply_filters( 'wpml_current_language', null );


// Get tags ids from all languages

$current_lang_product_tags = get_terms( array(
    'taxonomy' => 'product_tag',
    'hide_empty' => true,
) );

// if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
//     foreach ( $terms as $term ) {
//         echo $term->term_id.' , '.'<br>';
//     }
// }



$company_name = get_user_meta($user_id, 'company_name', true);
$company_nip = get_user_meta($user_id, 'company_nip', true);
$company_address = get_user_meta($user_id, 'company_address', true);
$access = get_user_meta($user_id, 'partner_has_access', true);
$partner_percentage_discount = get_user_meta($user_id, 'partner_percentage_discount', true);


// Edit data
if (isset($_POST['update_partner']) && check_admin_referer('update_partner_data', 'update_partner_nonce')) {
    $new_access = isset($_POST['partner_has_access']) ? (int) $_POST['partner_has_access'] : 0;
    $new_discount = isset($_POST['partner_percentage_discount']) ? (int) $_POST['partner_percentage_discount'] : 0;
    $partner_p_tags_assigned = isset($_POST['partner_p_tags_assigned']) ?  $_POST['partner_p_tags_assigned'] : null;

    update_user_meta($user->ID, 'partner_has_access', $new_access);
    update_user_meta($user->ID, 'partner_percentage_discount', $new_discount);

    if(!empty($partner_p_tags_assigned) && !is_null($partner_p_tags_assigned) && !empty($current_lang_code)){
        update_user_meta($user->ID, $current_lang_code.'_partner_p_tags_assigned', $partner_p_tags_assigned);
    }

    echo '<div class="updated notice"><p>Dane zostały zaktualizowane.</p></div>';

    $access = $new_access;
    $partner_percentage_discount = $new_discount;
}


?>

<div class="wrap">
    <h1>Szczegóły : <?php echo esc_html($company_name); ?></h1>
    <table class="widefat striped">
        <tr>
            <th>Nazwa użytkownika</th>
            <td><?php echo esc_html($user->user_login); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo esc_html($user->user_email); ?></td>
        </tr>
        <tr>
            <th>Nazwa firmy</th>
            <td><?php echo esc_html($company_name); ?></td>
        </tr>
        <tr>
            <th>NIP</th>
            <td><?php echo esc_html($company_nip); ?></td>
        </tr>
        <tr>
            <th>Adres</th>
            <td><?php echo esc_html($company_address); ?></td>
        </tr>
        <tr>
            <th>Dostęp</th>
            <td>
                <?php echo (int)$access === 0 ? 'Brak dostępu' : 'Przyznany'; ?>

            </td>
        </tr>
        <tr>
            <th>Rabat ( % )</th>
            <td>
                <?php echo esc_html($partner_percentage_discount ).'%'; ?>
            </td>
        </tr>
    </table>

    <!-- Edit data -->
    <div style="padding-top: 30px;">
        <h2>Edycja ustawień</h2>
        <div>
            <form method="post">
                <?php wp_nonce_field('update_partner_data', 'update_partner_nonce'); ?>

                <p>
                    <label for="partner_has_access">Dostęp do konta:</label><br>
                    <select name="partner_has_access" id="partner_has_access">
                        <option value="1" <?php selected($access, 1); ?>>Dostęp przyznany</option>
                        <option value="0" <?php selected($access, 0 ); ?>>Brak dostępu</option>
                    </select>
                </p>

                <p>
                    <label for="partner_percentage_discount">Rabat procentowy:</label><br>
                    <input type="number" name="partner_percentage_discount" id="partner_percentage_discount"
                           value="<?php echo esc_attr($partner_percentage_discount); ?>" min="0" max="100"> %
                </p>

                <!-- Tags -->
                <p>
                    <label for="partner_has_access">Przypisz TAGI:</label><br>
                    <select multiple name="partner_p_tags_assigned[]" id="partner_p_tags_assigned">
                        <option value=""  selected disabled>--Wybierz tagi--</option>
                        <?php if(!empty($current_lang_product_tags)): ?>
                            <?php foreach ($current_lang_product_tags as $tag) { ?>
                                <option value="<?php echo $tag->term_id ?>"><?php echo $tag->name ?></option>
                            <?php } ?>
                        <?php endif; ?>
                    </select>
                </p>


                <!-- Assigned tags -->
                <table class="widefat striped">
                    <?php if($enabled_languages): ?>
                    <thead style="background: #eee;">
                        <tr>
                            <th>Język</th>
                            <td>Przypisane tagi</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($enabled_languages as $enable_lang): ?>
                        <tr>
                            <th><?php echo $enable_lang['display_name'] ?></th>
                            <td>
                                <?php 
                                $_partner_p_tags_assigned = get_user_meta($user_id, $enable_lang['code'] . '_partner_p_tags_assigned', true);

                                if (!empty($_partner_p_tags_assigned) && is_array($_partner_p_tags_assigned)) {
                                    foreach ($_partner_p_tags_assigned as $tag_id) {
                                        if (is_numeric($tag_id)) {
                                            $lang_tag = get_term((int) $tag_id, 'product_tag');
                                            if ($lang_tag && !is_wp_error($lang_tag)) {
                                                echo '<span style="background: blue; color:#fff;border-radius:3px;padding:3px 8px;margin-right:4px">' . esc_html($lang_tag->name) . '</span>';
                                            }
                                        }
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php endif; ?>
                </table>

                <p>
                    <input type="submit" name="update_partner" class="button button-primary" value="Zapisz zmiany">
                </p>
            </form>

        </div>
    </div>

</div>
