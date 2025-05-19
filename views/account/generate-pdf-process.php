<?php
if (!defined('ABSPATH')) {
    exit; 
}

if(isset($_POST['generate_catalog_rm_submit'])){
	$choosen_lang = $_POST['choosen_lang'] ?? '';
	$partner_tags_ids_json = $_POST['partner_tags_ids'] ?? '[]';
	$partner_tags_ids = json_decode(stripslashes($partner_tags_ids_json), true);
	$discount_apply_rm = $_POST['discount_apply_rm'] ?? null;
}

if (!empty($choosen_lang)) {
    do_action('wpml_switch_language', $choosen_lang);
}

$args = [
    'post_type'      => 'product',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
	'suppress_filters' => false,
	// 'lang' => 'en'
];

$query = new WP_Query($args);

if ($query->have_posts()) : ?>
    <h1>3 najnowsze produkty</h1>
    <ul style="list-style:none; padding:0;">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <?php
            global $product;
            $title = get_the_title();
            $price = $product->get_price_html();
            $image = wp_get_attachment_image($product->get_image_id(), 'thumbnail');
            ?>
            <li style="margin-bottom:20px;">
                <h2><?php echo esc_html($title); ?></h2>
                <?php echo $image; ?>
                <p>Cena: <?php echo $price; ?></p>
            </li>
            <pagebreak />
        <?php endwhile; ?>
    </ul>
    <?php wp_reset_postdata(); ?>
<?php else : ?>
    <p>Brak produktów do wyświetlenia.</p>
<?php endif; ?>
