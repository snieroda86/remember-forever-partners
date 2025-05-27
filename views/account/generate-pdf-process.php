<?php
if (!defined('ABSPATH')) {
    exit;
}

function get_eur_exchange_rate_from_nbp() {
    $response = wp_remote_get('https://api.nbp.pl/api/exchangerates/rates/A/EUR/?format=json');

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

function get_cached_eur_rate() {
    $cached = get_transient('eur_exchange_rate_nbp');
    if ($cached !== false) {
        return $cached;
    }

    $rate = get_eur_exchange_rate_from_nbp();
    if ($rate !== false) {
        set_transient('eur_exchange_rate_nbp', $rate, 12 * HOUR_IN_SECONDS);
    }

    return $rate ?: 4.25; // Tu ustawić kurs euro na wypadek jak by pobierani z api nie zadziałało
}

// Dane z forma
if (isset($_POST['generate_catalog_rm_submit'])) {
    $choosen_lang = $_POST['choosen_lang'] ?? '';
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

if (empty($partner_tags_ids) || !is_array($partner_tags_ids)) {
    echo '<p>'.__('Brak produktów do wyświetlenia.' , 'remember-forever').'</p>';
    return;
}

// Zmiana jezyka WPML
if (!empty($choosen_lang)) {
    do_action('wpml_switch_language', $choosen_lang);

    switch ($choosen_lang) {
        case 'pl':
            $currency = 'PLN';
            $dzielnik = 1;
            break;
        default:
            $currency = 'EUR';
            $dzielnik = get_cached_eur_rate();
            break;
    }
} else {
    $currency = 'PLN';
    $dzielnik = 1;
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

$query = new WP_Query($args);

if ($query->have_posts()) : ?>
    <h1><?php _e('Katalog produktów' , 'remember-forever') ?></h1>
    <ul style="list-style:none; padding:0;">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <?php
            global $product;
            $title = get_the_title();
            $price_raw = floatval($product->get_price());

            // Rabat na cenie
            if (!is_null($discount_apply_rm) && is_numeric($discount_apply_rm) && $discount_apply_rm > 0) {
                $price_raw = $price_raw * (1 - ($discount_apply_rm / 100));
            }

            $converted_price = number_format($price_raw / $dzielnik, 2);

            $image = wp_get_attachment_image($product->get_image_id(), 'medium');
            ?>
            <li style="margin-bottom:20px;">
                <h2><?php echo esc_html($title); ?></h2>
                <?php echo $image; ?>
                <p><?php _e('Cena produktu', 'remember-forever') ?> <?php echo $converted_price . ' ' . $currency; ?></p>

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
                    echo '<th style="text-align: left; border-bottom: 1px solid #ccc; padding: 4px;">' . __('Cecha', 'remember-forever') . '</th>';
                    echo '<th style="text-align: left; border-bottom: 1px solid #ccc; padding: 4px;">' . __('Wartość', 'remember-forever') . '</th>';
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
