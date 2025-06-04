<?php
if (!defined('ABSPATH')) {
    exit;
}

// Dane z forma
if (isset($_POST['generate_catalog_rm_submit'])) {
    $choosen_lang = $_POST['choosen_lang'] ?? '';
    $current_currency = $_POST['current_currency'] ?? 'GBP';
    $partner_tags_ids_json = $_POST['partner_tags_ids'] ?? '[]';
    $partner_tags_ids = json_decode(stripslashes($partner_tags_ids_json), true);
    $discount_apply_rm = $_POST['discount_apply_rm'] ?? null;
    $selected_products = $_POST['selected_products'] ?? null;

    if (empty($selected_products)) {
        $referer = wp_get_referer();
        $redirect_url = add_query_arg('no_products', '1', $referer);

        wp_safe_redirect($redirect_url);
        exit;
    }

}

function get_currency_exchange_rate_from_nbp($currency) {
    $response = wp_remote_get('https://api.nbp.pl/api/exchangerates/rates/A/' . $currency . '/?format=json');

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['rates'][0]['mid'])) {
        return false;
    }

    return floatval($data['rates'][0]['mid']);
}


function dzielnikWaluty($current_currency) {
    if ($current_currency === 'GBP') {
        return 1.0;
    }

    $kurs_docelowy = get_currency_exchange_rate_from_nbp($current_currency);
    $kurs_gbp = get_currency_exchange_rate_from_nbp('GBP');

    if ($current_currency === 'PLN') {
        if($kurs_gbp){
            return 1 / $kurs_gbp;
        }else{
            return 5.08;
        }
    }

    if (!$kurs_docelowy || !$kurs_gbp || $kurs_gbp == 0) {
        return 1.0;
    }

    return $kurs_docelowy / $kurs_gbp;
}



if (empty($partner_tags_ids) || !is_array($partner_tags_ids)) {
    echo '<p>'.__('Brak produktów do wyświetlenia.' , 'remember-forever').'</p>';
    return;
}

// Zmiana jezyka WPML
if (!empty($choosen_lang)) {
    do_action('wpml_switch_language', $choosen_lang);
}

// Pobieranie produktów z przypisanych tagów
$args = [
    'post_type'        => 'product',
    'posts_per_page'   => -1,
    'orderby'          => 'date',
    'order'            => 'DESC',
    'suppress_filters' => false,
];


if (!empty($selected_products) && is_array($selected_products)) {
    $args['post__in'] = array_map('intval', $selected_products);
} else {
    
    return;
}

if (!empty($partner_tags_ids) && is_array($partner_tags_ids)) {
    $args['tax_query'] = [
        [
            'taxonomy' => 'product_tag',
            'field'    => 'term_id',
            'terms'    => $partner_tags_ids,
        ],
    ];
}

$current_user = wp_get_current_user();
$current_user_id = $current_user->ID;
$dzielnik = dzielnikWaluty($current_currency);

// Teksty statyczne w katalogu - ustawta i zastosujta

if (!empty($choosen_lang)) {
    
    switch ($choosen_lang) {
        case 'pl':
            $naglowek = 'Katalog produktów';
            $podtytul = 'Zapraszamy do zapoznania się z ofertą naszych produktów...';
            $cena = 'Cena produktu:';
            $cecha = 'Cecha';
            $wartosc = 'Wartość';
            break;
        case 'en':
            $naglowek = 'Product catalog';
            $podtytul = 'We invite you to browse our product range...';
            $cena = 'Price:';
            $cecha = 'Attribute';
            $wartosc = 'Value';
            break;
        default:
            $naglowek = 'Product catalog';
            $podtytul = 'We invite you to browse our product range...';
            $cena = 'Price:';
            $cecha = 'Attribute';
            $wartosc = 'Value';
            break;
    }
} 

// Koniec teksty statyczne

$query = new WP_Query($args);

if ($query->have_posts()) : ?>
    <h1><?php echo esc_html($naglowek); ?></h1>
    <h4><?php echo esc_html($podtytul); ?></h4>
    <ul style="list-style:none; padding:0;">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <?php
            global $product;
            $title = get_the_title();
            $price_raw = floatval($product->get_price());
            

            // Apply discount 
            global $wpdb;
            $product_id = $product->get_id();

            $discount_percentage = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT discount_percentage FROM {$wpdb->prefix}partners_product_discount WHERE user_id = %d AND product_id = %d",
                    $current_user_id,
                    $product_id
                )
            );

           if ($discount_percentage !== null && is_numeric($discount_percentage)) {
                $discount = floatval($discount_percentage);

                if ($discount > 0 && $discount < 100) {
                    $discounted_price = $price_raw - ($price_raw * ($discount / 100));

                } else {
                    $discounted_price = $price_raw;
                }
            } else {
                $discounted_price = $price_raw; 
            }

            // $rounded_price = round($discounted_price );
            $converted_price = round($discounted_price / $dzielnik);
            $converted_price = number_format($converted_price, 2, '.', '');




            $image = wp_get_attachment_image($product->get_image_id(), 'medium');
            ?>
            <li style="margin-bottom:20px;">
                <h2><?php echo esc_html($title); ?></h2>
                <?php echo $image; ?>
                <p>
                    <span><?php echo esc_html($cena); ?></span> 
                    <?php if(is_null($discount_apply_rm)): ?>
                    <?php echo $converted_price  . ' ' . $current_currency; ?>
                    <?php endif; ?>
                </p>

               <!-- Atrybuty -->
                <?php
                $desired_attribute_slugs = [
                    'pa_color',
                    'pa_size',
                    
                ];

                $attributes = [];

                foreach ($desired_attribute_slugs as $slug) {
                    $value = $product->get_attribute($slug);
                    if (!empty($value)) {
                        $attributes[$slug] = $value;
                    }
                }

                if (!empty($attributes)) {
                    echo '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th style="text-align: left; border-bottom: 1px solid #ccc; padding: 4px;">' . $cecha . '</th>';
                    echo '<th style="text-align: left; border-bottom: 1px solid #ccc; padding: 4px;">' . $wartosc . '</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    foreach ($attributes as $slug => $value) {
                        $name = wc_attribute_label($slug);

                        echo '<tr>';
                        echo '<td style="padding: 4px; border-bottom: 1px solid #eee;">' . esc_html($name) . '</td>';
                        echo '<td style="padding: 4px; border-bottom: 1px solid #eee;">' . esc_html($value) . '</td>';
                        echo '</tr>';
                    }

                    echo '</tbody>';
                    echo '</table>';
                }
                ?>
                <!-- Koniec atrybutów -->

            </li>
            <?php
            
            if ( $query->current_post + 1 < $query->post_count ) {
                echo '<pagebreak />';
            }
            ?>

        <?php endwhile; ?>
    </ul>
    <?php wp_reset_postdata(); ?>
<?php else : ?>
    <p>Brak produktów do wyświetlenia.</p>
<?php endif; ?>
