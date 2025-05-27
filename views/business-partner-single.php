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

<?php

/*
** Zapis rabatów do tabeli dla daego uzytkownika
*/
global $wpdb;

$table = $wpdb->prefix . 'partners_product_discount';

if (isset($_POST['rm_sp_discount']) && is_array($_POST['rm_sp_discount'])) {
    foreach ($_POST['rm_sp_discount'] as $product_id => $data) {
        $product_id = intval($product_id);
        $discount = isset($data['value']) ? intval($data['value']) : 0;

        if ($product_id <= 0 || $discount < 0 || $discount > 99) {
            continue; 
        }

        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d AND product_id = %d",
            $user_id,
            $product_id
        ));

        if ($existing_id) {
            $wpdb->update(
                $table,
                ['discount_percentage' => $discount],
                ['id' => $existing_id],
                ['%d'],
                ['%d']
            );
        } else {
            $wpdb->insert(
                $table,
                [
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'discount_percentage' => $discount
                ],
                ['%d', '%d', '%d']
            );
        }
    }

    echo '<div class="updated notice"><p>Rabaty zaktualizowane.</p></div>';

}
?>


<style type="text/css">
    .rm-partners-tabs{
        display: flex;
        flex-wrap: wrap;
    }

    .rm-partners-tabs li{
        background: #fff;
        padding:10px 15px;
        min-width: 160px;
        text-align: center;
        margin: 2px;
        cursor: pointer;
    }
</style>

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
       <!--  <tr>
            <th>Rabat ( % )</th>
            <td>
                <?php echo esc_html($partner_percentage_discount ).'%'; ?>
            </td>
        </tr> -->
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

               <!--  <p>
                    <label for="partner_percentage_discount">Rabat procentowy:</label><br>
                    <input type="number" name="partner_percentage_discount" id="partner_percentage_discount"
                           value="<?php echo esc_attr($partner_percentage_discount); ?>" min="0" max="100"> %
                </p> -->

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

    <!-- Produkty i rabaty -->

    <div style="padding-top: 10px;">
        <h2>Przypisz rabaty</h2>
        <?php 
        $user_tags_lang =get_user_meta($user_id, $current_lang_code . '_partner_p_tags_assigned', true);
         ?>
        <div>
            

            <?php 
            $args = [
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'suppress_filters' => false,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_tag',
                        'field'    => 'term_id',
                        'terms'    => $user_tags_lang,
                    ),
                ),
            ];


            $query = new WP_Query($args);

            if( array_key_exists('lang', $_GET) && $_GET['lang']=='all'){
                echo "Wybierz język";
                return; 
            } else{
                if ($query->have_posts()) : ?>

                
                    <div>
                        <form method="post">
                            <table class="widefat striped">
                                <thead>
                                    <tr>
                                        <th>Nazwa produktu</th>
                                        <th>Zdjęcie</th>
                                        <th>Cena</th>
                                        <th>Rabat ( % )</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                                        <?php
                                        global $product;
                                        $title = get_the_title();
                                        $image = wp_get_attachment_image($product->get_image_id(), 'thumbnail');
                                        $price = $product->get_price_html();

                                        $product_id = $product->get_id();
                                        $discount = $wpdb->get_var($wpdb->prepare(
                                            "SELECT discount_percentage FROM $table WHERE user_id = %d AND product_id = %d",
                                            $user_id,
                                            $product_id
                                        ));

                                        $discount = $discount !== null ? intval($discount) : '';
                                        
                                        ?>
                                        <tr>
                                            <td>
                                                <h3 id="product-id-<?php echo $product->get_id(); ?>"><?php echo esc_html($title); ?></h3>
                                            </td>
                                            <td>
                                                <?php echo $image; ?>
                                            </td>
                                            <td>
                                                <?php echo $price; ?>
                                            </td>
                                            <td>
                                               <input type="number" min="0" max="99" name="rm_sp_discount[<?php echo $product->get_id(); ?>][value]"  value="<?php echo esc_attr($discount); ?>">

                                            </td>
                                        </tr>

                                    <?php endwhile; ?>
                                    
                                </tbody>
                            </table>
                             <p>
                                <input type="submit" name="save_sp_discount" class="button button-primary" value="Zapisz rabaty">
                            </p>
                        </form>
                    </div>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <p>Brak produktów do wyświetlenia.</p>
                <?php endif; 
            } ?>

        </div>
    </div>

</div>

