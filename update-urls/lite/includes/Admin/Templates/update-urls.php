<?php

$nav_menus['search'] = [
        'title' => __( 'Search / Replace', 'update-urls' ),
        'link'  => add_query_arg( [ 'tab' => 'search' ], admin_url( 'tools.php?page=update-urls' ) ),
];

$nav_menus['help'] = [
        'title' => __( 'Help', 'update-urls' ),
        'link'  => add_query_arg( [ 'tab' => 'help' ], admin_url( 'tools.php?page=update-urls' ) ),
];


$tab = ! empty( $_GET['tab'] ) ? \KaizenCoders\UpdateURLS\Helper::clean( $_GET['tab'] ) : 'search';

?>


<div class="wrap">

    <h2>Update URLs</h2>

    <h2 class="nav-tab-wrapper">
        <?php
        foreach ( $nav_menus as $id => $menu ) { ?>
            <a href="<?php
            echo $menu['link']; ?>" class="nav-tab wpsf-tab-link <?php
            if ( $id === $tab ) {
                echo "nav-tab-active";
            } ?>">
                <?php
                echo $menu['title']; ?>
            </a>
        <?php
        } ?>
    </h2>

    <div class="bg-white">
        <?php
        if ( 'search' === $tab ) {
            include_once KC_UU_ADMIN_TEMPLATES_DIR . '/search-replace.php';
        } elseif ( 'help' === $tab ) {
            include_once KC_UU_ADMIN_TEMPLATES_DIR . '/help.php';
        }
        ?>
    </div>
</div>