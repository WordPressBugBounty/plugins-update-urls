<?php

$template = KC_UU_ADMIN_TEMPLATES_DIR . '/search-replace.php';
$template = apply_filters( 'kc_uu_search_replace_template', $template );

?>

<div class="wrap">

    <h2><?php esc_html_e( 'Search & Replace', 'update-urls' ); ?></h2>

    <div class="bg-white">
        <?php
        do_action( 'kc_uu_before_tab_content', 'search' );
        include_once $template;
        ?>
    </div>
</div>
