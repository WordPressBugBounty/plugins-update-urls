<?php

$nav_menus['search'] = [
        'title' => __( 'Search / Replace', 'update-urls' ),
        'link'  => add_query_arg( [ 'tab' => 'search' ], admin_url( 'admin.php?page=update-urls' ) ),
];

$nav_menus['history'] = [
        'title' => __( 'History', 'update-urls' ),
        'link'  => add_query_arg( [ 'tab' => 'history' ], admin_url( 'admin.php?page=update-urls' ) ),
        'order' => 2,
];

$nav_menus['settings'] = [
        'title' => __( 'Settings', 'update-urls' ),
        'link'  => add_query_arg( [ 'tab' => 'settings' ], admin_url( 'admin.php?page=update-urls' ) ),
];

$nav_menus['backup_import'] = [
        'title' => __( 'Backup / Import', 'update-urls' ),
        'link'  => add_query_arg( [ 'tab' => 'backup_import' ], admin_url( 'admin.php?page=update-urls' ) ),
];

/**
 * Filter admin tabs for PRO extensions.
 *
 * @param  array  $nav_menus  The navigation menu tabs.
 */
$nav_menus = apply_filters( 'kc_uu_admin_tabs', $nav_menus );

$nav_menus['help'] = [
        'title' => __( 'Help', 'update-urls' ),
        'link'  => add_query_arg( [ 'tab' => 'help' ], admin_url( 'admin.php?page=update-urls' ) ),
];

$tab = ! empty( $_GET['tab'] ) ? \KaizenCoders\UpdateURLS\Helper::clean( $_GET['tab'] ) : 'search';

?>


<div class="wrap">

    <h2><?php
        esc_html_e( 'Search & Replace', 'update-urls' ); ?></h2>

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
        /**
         * Hook before tab content for PRO to render results.
         *
         * @param  string  $tab  The current active tab.
         */
        do_action( 'kc_uu_before_tab_content', $tab );

        if ( 'search' === $tab ) {
            $template = KC_UU_ADMIN_TEMPLATES_DIR . '/search-replace.php';

            /**
             * Filter the search/replace template path for PRO override.
             *
             * @param  string  $template  The template file path.
             */
            $template = apply_filters( 'kc_uu_search_replace_template', $template );

            include_once $template;
        } elseif ( 'help' === $tab ) {
            include_once KC_UU_ADMIN_TEMPLATES_DIR . '/help.php';
        } else {
            if ( ! UU()->is_pro() ) {
                include_once KC_UU_ADMIN_TEMPLATES_DIR . '/pro-promo.php';
            } else {
                /**
                 * Allow PRO to render custom tab content.
                 *
                 * @param  string  $tab  The current active tab.
                 */
                do_action( 'kc_uu_render_tab_content', $tab );
            }
        }
        ?>
    </div>
</div>
