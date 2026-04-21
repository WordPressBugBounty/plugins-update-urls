<?php

namespace KaizenCoders\UpdateURLS;

class Settings {
    /**
     * @access private
     * @var array
     */
    private $settings_wrapper;

    /**
     * @access private
     * @var array
     */
    private $settings;

    /**
     * @access private
     * @var array
     */
    private $tabs;

    /**
     * @access private
     * @var string
     */
    private $option_group;

    private $dependent_fields = [];

    /**
     * @access private
     * @var array
     */
    private $settings_page = [];

    /**
     * @access private
     * @var string
     */
    private $options_path;

    /**
     * @access private
     * @var string
     */
    private $options_url;

    /**
     * @access protected
     * @var array
     */
    protected $setting_defaults = [
            'id'          => 'default_field',
            'title'       => '',
            'desc'        => '',
            'std'         => '',
            'type'        => 'text',
            'placeholder' => '',
            'choices'     => [],
            'class'       => '',
            'subfields'   => [],
            'parent' => '',           // ID of parent field
            'show_if' => [],         // Conditions when to show: ['parent_value' => 'expected_value']
            'hide_if' => [],
    ];

    /**
     * WordPressSettingsFramework constructor.
     *
     * @param  null|string  $settings_file  Path to a settings file, or null if you pass the option_group manually and construct your settings with a filter.
     * @param  bool|string  $option_group   Option group name, usually a short slug.
     */
    public function __construct( $settings_file = null, $option_group = false ) {
        $this->option_group = $option_group;

        if ( $settings_file ) {
            if ( ! is_file( $settings_file ) ) {
                return;
            }

            require_once( $settings_file );

            if ( ! $this->option_group ) {
                $this->option_group = preg_replace( "/[^a-z0-9]+/i", "", basename( $settings_file, '.php' ) );
            }
        }

        if ( empty( $this->option_group ) ) {
            return;
        }

        $this->options_path = plugin_dir_path( __FILE__ );
        //$this->options_url  = plugin_dir_url( __FILE__ );
        $this->options_url = KC_UU_PLUGIN_ASSETS_DIR_URL . '/';

        $this->construct_settings();

        if ( is_admin() ) {
            global $pagenow;

            add_action( 'admin_init', [ $this, 'admin_init' ] );
            add_action( 'wpsf_do_settings_sections_' . $this->option_group, [
                    $this,
                    'do_tabless_settings_sections',
            ], 10 );

            if ( isset( $_GET['page'] ) && $_GET['page'] === $this->settings_page['slug'] ) {
                if ( $pagenow !== "options-general.php" ) {
                    add_action( 'admin_notices', [ $this, 'admin_notices' ] );
                }
                add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
            }

            if ( $this->has_tabs() ) {
                add_action( 'wpsf_before_settings_' . $this->option_group, [ $this, 'tab_links' ] );

                remove_action( 'wpsf_do_settings_sections_' . $this->option_group, [
                        $this,
                        'do_tabless_settings_sections',
                ], 10 );

                add_action( 'wpsf_do_settings_sections_' . $this->option_group, [
                        $this,
                        'do_tabbed_settings_sections',
                ], 10 );
            }
        }
    }

    /**
     * Construct Settings.
     */
    public function construct_settings() {
        $this->settings_wrapper = apply_filters( 'wpsf_register_settings_' . $this->option_group, [] );

        if ( ! is_array( $this->settings_wrapper ) ) {
            return new WP_Error( 'broke', __( 'WPSF settings must be an array', 'url-shortify' ) );
        }

        // If "sections" is set, this settings group probably has tabs
        if ( isset( $this->settings_wrapper['sections'] ) ) {
            $this->tabs     = ( isset( $this->settings_wrapper['tabs'] ) ) ? $this->settings_wrapper['tabs'] : [];
            $this->settings = $this->settings_wrapper['sections'];
            // If not, it's probably just an array of settings
        } else {
            $this->settings = $this->settings_wrapper;
        }

        $this->settings_page['slug'] = sprintf( '%s-settings', str_replace( '_', '-', $this->option_group ) );
    }

    /**
     * Get the option group for this instance
     *
     * @return string the "option_group"
     */
    public function get_option_group() {
        return $this->option_group;
    }

    /**
     * Registers the internal WordPress settings
     */
    public function admin_init() {
        register_setting( $this->option_group, $this->option_group . '_settings', [ $this, 'settings_validate' ] );
        $this->process_settings();
    }

    /**
     * Add Settings Page
     *
     * @param  array  $args
     */
    public function add_settings_page( $args ) {
        $defaults = [
                'parent_slug' => false,
                'page_slug'   => "",
                'page_title'  => "",
                'menu_title'  => "",
                'capability'  => 'manage_options',
        ];

        $args = wp_parse_args( $args, $defaults );

        $this->settings_page['title']      = $args['page_title'];
        $this->settings_page['capability'] = $args['capability'];

        if ( $args['parent_slug'] ) {
            add_submenu_page(
                    $args['parent_slug'],
                    $this->settings_page['title'],
                    $args['menu_title'],
                    $args['capability'],
                    $this->settings_page['slug'],
                    [ $this, 'settings_page_content' ]
            );
        } else {
            add_menu_page(
                    $this->settings_page['title'],
                    $args['menu_title'],
                    $args['capability'],
                    $this->settings_page['slug'],
                    [ $this, 'settings_page_content' ],
                    apply_filters( 'wpsf_menu_icon_url_' . $this->option_group, '' ),
                    apply_filters( 'wpsf_menu_position_' . $this->option_group, null )
            );
        }
    }

    /**
     * Settings Page Content
     */

    public function settings_page_content() {
        if ( ! current_user_can( $this->settings_page['capability'] ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'url-shortify' ) );
        }
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h2><?php
                echo $this->settings_page['title']; ?></h2>
            <?php
            // Output your settings form
            $this->settings();
            ?>
        </div>
        <?php
    }

    /**
     * Displays any errors from the WordPress settings API
     */
    public function admin_notices() {
        settings_errors();
    }

    /**
     * Enqueue scripts and styles
     */
    public function admin_enqueue_scripts() {
        // scripts
//		wp_register_script( 'jquery-ui-timepicker', $this->options_url . 'settings/assets/vendor/jquery-timepicker/jquery.ui.timepicker.js', [
//			'jquery',
//			'jquery-ui-core',
//		], false, true );
        wp_register_script( 'wpsf', $this->options_url . 'settings/assets/js/main.js', [ 'jquery' ], false, true );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'farbtastic' );
        wp_enqueue_script( 'media-upload' );
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        // wp_enqueue_script( 'jquery-ui-timepicker' );
        wp_enqueue_script( 'wpsf' );

        // styles
        // wp_register_style( 'jquery-ui-timepicker', $this->options_url . 'settings/assets/vendor/jquery-timepicker/jquery.ui.timepicker.css' );
        wp_register_style( 'wpsf', $this->options_url . 'settings/assets/css/main.css' );
        wp_register_style( 'jquery-ui-css',
                '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/ui-darkness/jquery-ui.css' );

        wp_enqueue_style( 'farbtastic' );
        wp_enqueue_style( 'thickbox' );
        // wp_enqueue_style( 'jquery-ui-timepicker' );
        wp_enqueue_style( 'jquery-ui-css' );
        wp_enqueue_style( 'wpsf' );

        wp_add_inline_script('wpsf', '
        (function($) {
            function updateFieldAndChildren($field, shouldShow) {
                // Update current field visibility
                $field.toggle(shouldShow);
                
                // Find and update all child fields
                var fieldId = $field.find("[id]").first().attr("id");
                if (fieldId) {
                    $("[data-parent=\'" + fieldId + "\']").each(function() {
                        var $childField = $(this);
                        // Recursively update children
                        // If parent is hidden, children should always be hidden
                        // If parent is visible, check child\'s own conditions
                        if (!shouldShow) {
                            updateFieldAndChildren($childField, false);
                        } else {
                            checkAndUpdateField($childField);
                        }
                    });
                }
            }

            function checkAndUpdateField($field) {
                var parentId = $field.data("parent");
                if (!parentId) return;

                var $parent = $("#" + parentId);
                var parentValue = $parent.val();
                
                if ($parent.is(":checkbox")) {
                    parentValue = $parent.is(":checked") ? "1" : "0";
                }

                var showIf = $field.data("show-if");
                var hideIf = $field.data("hide-if");
                var shouldShow = true;

                if (showIf && !Object.entries(showIf).some(([key, value]) => parentValue === value)) {
                    shouldShow = false;
                }

                if (hideIf && Object.entries(hideIf).some(([key, value]) => parentValue === value)) {
                    shouldShow = false;
                }

                // Check if parent is visible
                if (!$parent.closest(".wpsf-field").is(":visible")) {
                    shouldShow = false;
                }

                updateFieldAndChildren($field, shouldShow);
            }

            function recheckAllDependencies() {
                $("[data-parent]").each(function() {
                    checkAndUpdateField($(this));
                });
            }

            function handleFieldDependencies() {
                $("[data-parent]").each(function() {
                    var $field = $(this);
                    var parentId = $field.data("parent");
                    var $parent = $("#" + parentId);

                    $parent.on("change", function() {
                        checkAndUpdateField($field);
                    });
                });

                // Initial check
                recheckAllDependencies();
            }

            $(document).ready(function() {
                handleFieldDependencies();

                // Re-check dependencies when switching tabs,
                // since hidden tabs cause :visible checks to fail
                $(document).on("click", ".wpsf-tab-link", function() {
                    setTimeout(recheckAllDependencies, 50);
                });
            });
        })(jQuery);
    ');
    }

    /**
     * Adds a filter for settings validation.
     *
     * @param $input
     *
     * @return array
     */
    public function settings_validate( $input ) {
        return apply_filters( $this->option_group . '_settings_validate', $input );
    }

    /**
     * Displays the "section_description" if specified in $this->settings
     *
     * @param  array callback args from add_settings_section()
     */
    public function section_intro( $args ) {
        if ( ! empty( $this->settings ) ) {
            foreach ( $this->settings as $section ) {
                if ( $section['section_id'] == $args['id'] ) {
                    if ( isset( $section['section_description'] ) && $section['section_description'] ) {
                        echo '<div class="wpsf-section-description wpsf-section-description--' . esc_attr( $section['section_id'] ) . '">' . $section['section_description'] . '</div>';
                    }
                    break;
                }
            }
        }
    }

    /**
     * Add dependency attributes to field wrapper
     */
    private function generate_field_wrapper( $args, $content ) {
        $attributes = [];

        if ( ! empty( $args['parent'] ) ) {
            $attributes[] = 'data-parent="' . esc_attr( $args['parent'] ) . '"';

            if ( ! empty( $args['show_if'] ) ) {
                $attributes[] = 'data-show-if="' . esc_attr( json_encode( $args['show_if'] ) ) . '"';
            }

            if ( ! empty( $args['hide_if'] ) ) {
                $attributes[] = 'data-hide-if="' . esc_attr( json_encode( $args['hide_if'] ) ) . '"';
            }

            //$attributes[] = 'style="display:none;"';
        }

        if ( ! empty( $this->dependent_fields[ $args['id'] ] ) ) {
            $attributes[] = 'data-has-dependencies="true"';
        }

        $wrapper_attrs = ! empty( $attributes ) ? ' ' . implode( ' ', $attributes ) : '';

        return sprintf(
                '<div class="wpsf-field%s"%s>%s</div>',
                ! empty( $args['parent'] ) ? ' wpsf-field--dependent' : '',
                $wrapper_attrs,
                $content
        );
    }

    /**
     * Processes $this->settings and adds the sections and fields via the WordPress settings API
     */
    private function process_settings() {
        if ( ! empty( $this->settings ) ) {

            if (!empty($this->settings)) {
                foreach ($this->settings as $section) {
                    if (isset($section['fields']) && is_array($section['fields'])) {
                        foreach ($section['fields'] as $field) {
                            if (!empty($field['parent'])) {
                                $this->dependent_fields[$field['parent']][] = $field['id'];
                            }
                        }
                    }
                }
            }

            usort( $this->settings, [ $this, 'sort_array' ] );

            $options = get_option( $this->option_group . '_settings' );

            foreach ( $this->settings as $section ) {
                if ( isset( $section['section_id'] ) && $section['section_id'] && isset( $section['section_title'] ) ) {
                    $page_name = ( $this->has_tabs() ) ? sprintf( '%s_%s', $this->option_group,
                            $section['tab_id'] ) : $this->option_group;

                    add_settings_section( $section['section_id'], $section['section_title'], [
                            $this,
                            'section_intro',
                    ], $page_name );

                    if ( isset( $section['fields'] ) && is_array( $section['fields'] ) && ! empty( $section['fields'] ) ) {
                        usort( $section['fields'], [ $this, 'sort_fields' ] );
                        foreach ( $section['fields'] as $field ) {
                            if ( isset( $field['id'] ) && $field['id'] && isset( $field['title'] ) ) {
                                $name = $this->get_field_name_to_get_value( $section['tab_id'], $section['section_id'],
                                        $field['id'] );

                                $value = Helper::get_data( $options, $name, Helper::get_data( $field, 'default', '' ) );

                                $title = ! empty( $field['subtitle'] ) ? sprintf( '%s <span class="wpsf-subtitle">%s</span>',
                                        $field['title'], $field['subtitle'] ) : $field['title'];

                                add_settings_field( $field['id'], $title, [
                                        $this,
                                        'generate_setting',
                                ], $page_name, $section['section_id'],
                                        [
                                                'section' => $section,
                                                'field'   => $field,
                                                'name'    => $name,
                                                'value'   => $value,
                                        ] );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Usort callback. Sorts $this->settings by "section_order"
     *
     * @param $a
     * @param $b
     *
     * @return bool
     */
    public function sort_array( $a, $b ) {
        if ( ! isset( $a['section_order'] ) || ! isset( $b['section_order'] ) ) {
            return 0;
        }

        return ( $a['section_order'] == $b['section_order'] ? 0 : ( $a['section_order'] > $b['section_order'] ? 1 : - 1 ) );
    }

    /**
     * Sort fields by order.
     *
     * @param $a
     * @param $b
     *
     * @return false|int
     */
    public function sort_fields( $a, $b ) {
        if ( ! isset( $a['order'] ) ) {
            $a['order'] = 0;
        }

        if ( ! isset( $b['order'] ) ) {
            $b['order'] = 0;
        }

        return ( $a['order'] == $b['order'] ? 0 : ( $a['order'] > $b['order'] ? 1 : - 1 ) );
    }

    /**
     * Generates the HTML output of the settings fields
     *
     * @param  array callback args from add_settings_field()
     */
    public function generate_setting( $args ) {
        $section                = $args['section'];
        $this->setting_defaults = apply_filters( 'wpsf_defaults_' . $this->option_group, $this->setting_defaults );

        $value = $args['value'];
        $args  = wp_parse_args( $args['field'], $this->setting_defaults );

        $name = $this->generate_field_name( $section['tab_id'], $section['section_id'], $args['id'] );

        $args['id']    = $this->has_tabs() ? sprintf( '%s_%s_%s', $section['tab_id'], $section['section_id'],
                $args['id'] ) : sprintf( '%s_%s', $section['section_id'], $args['id'] );
        $args['name']  = $name;
        $args['value'] = $value;

        do_action( 'wpsf_before_field_' . $this->option_group );
        do_action( 'wpsf_before_field_' . $this->option_group . '_' . $args['id'] );

        $this->do_field_method( $args );

        do_action( 'wpsf_after_field_' . $this->option_group );
        do_action( 'wpsf_after_field_' . $this->option_group . '_' . $args['id'] );
    }

    /**
     * Do field method, if it exists
     *
     * @param  array  $args
     */
    public function do_field_method( $args ) {
        $generate_field_method = sprintf( 'generate_%s_field', $args['type'] );

        if ( method_exists( $this, $generate_field_method ) ) {
            $this->$generate_field_method( $args );
        }
    }

    /**
     * Generate: Hidden field.
     *
     * @param  array  $args
     */
    public function generate_hidden_field( $args ) {
        $args['value'] = esc_attr( stripslashes( $args['value'] ) );

        echo '<input type="hidden" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '"  class="hidden-field ' . $args['class'] . '" />';
    }

    /**
     * Generate: Group field
     *
     * Generates a table of subfields, and a javascript template for create new repeatable rows
     *
     * @param  array  $args
     */
    public function generate_group_field( $args ) {
        $value     = (array) $args['value'];
        $row_count = ! empty( $value ) ? count( $value ) : 1;

        $output = '<table class="widefat wpsf-group" cellspacing="0">';

        $output .= "<tbody>";

        for ( $row = 0; $row < $row_count; $row ++ ) {
            $output .= $this->generate_group_row_template( $args, false, $row );
        }

        $output .= "</tbody>";
        $output .= "</table>";
        $output .= sprintf( '<script type="text/html" id="%s_template">%s</script>', $args['id'],
                $this->generate_group_row_template( $args, true ) );

        $output .= $this->generate_description( $args['desc'] );

        echo $output;
    }

    /**
     * Generate: Fieldset field
     *
     * @param $args
     *
     * @return void
     *
     * @since 1.1.7
     */
    public function generate_fieldset_field( $args) {
        $output = '';
        $output .= '<fieldset class="wpsf-fieldset">';

//        if ( ! empty( $args['title'] ) ) {
//            $output .= '<legend class="wpsf-fieldset-legend">' . esc_html( $args['title'] ) . '</legend>';
//        }

        foreach ( $args['subfields'] as $subfield ) {
            $row_template = '';
            $subfield = wp_parse_args( $subfield, $this->setting_defaults );

            $subfield['value'] = ( isset( $args['value'][ $subfield['id'] ] ) ? $args['value'][ $subfield['id'] ] : "" );
            $subfield['name']  = sprintf( '%s[%s]', $args['name'], $subfield['id'] );
            $subfield['id']    = sprintf( '%s_%s', $args['id'], $subfield['id'] );

            // Pass fieldset context for inline_sentence fields.
            $subfield['fieldset_name']  = $args['name'];
            $subfield['fieldset_value'] = $args['value'];

            $class = sprintf( 'wpsf-fieldset__field-wrapper--%s', $subfield['type'] );

            $row_template .= sprintf( '<div class="wpsf-fieldset__field-wrapper %s">', $class );

            if ( ! empty( $subfield['title'] ) ) {
                $row_template .= sprintf( '<label for="%s" class="wpsf-fieldset__field-label">%s</label>', $subfield['id'],
                        $subfield['title'] );
            }

            ob_start();
            $this->do_field_method( $subfield );
            $row_template .= ob_get_clean();

            $row_template .= '</div>';

            $row_template = $this->generate_field_wrapper( $subfield, $row_template );

            $output .= $row_template;
        }
        $output .= '</fieldset>';

        echo $this->generate_field_wrapper( $args, $output );
    }

    /**
     * Generate: Inline Sentence field
     *
     * Renders a sentence with inline input fields embedded in the text.
     * Placeholders in the sentence (e.g. {field_name}) are replaced with input fields.
     * Input values are saved at the fieldset level for backward compatibility.
     *
     * @param array $args
     *
     * @return void
     *
     * @since 1.2.0
     */
    public function generate_inline_sentence_field( $args ) {
        $sentence       = isset( $args['sentence'] ) ? $args['sentence'] : '';
        $inputs         = isset( $args['inputs'] ) ? $args['inputs'] : [];
        $fieldset_name  = isset( $args['fieldset_name'] ) ? $args['fieldset_name'] : '';
        $fieldset_value = isset( $args['fieldset_value'] ) ? $args['fieldset_value'] : [];

        $field_html = '<p class="wpsf-inline-sentence" style="display: flex; align-items: center; flex-wrap: wrap; gap: 4px; font-size: 14px; line-height: 2.5; border: 1px solid #dcdcde; border-radius: 4px; padding: 8px 12px; background: #f9f9f9;">';

        $parts = preg_split( '/\{(\w+)\}/', $sentence, -1, PREG_SPLIT_DELIM_CAPTURE );

        foreach ( $parts as $i => $part ) {
            if ( $i % 2 === 0 ) {
                $field_html .= esc_html( $part );
            } elseif ( isset( $inputs[ $part ] ) ) {
                $input       = $inputs[ $part ];
                $input_name  = sprintf( '%s[%s]', $fieldset_name, $part );
                $input_id    = sprintf( '%s_%s', $args['id'], $part );
                $value       = isset( $fieldset_value[ $part ] ) ? $fieldset_value[ $part ] : ( isset( $input['default'] ) ? $input['default'] : '' );
                $placeholder = isset( $input['placeholder'] ) ? $input['placeholder'] : '';
                $type        = isset( $input['type'] ) ? $input['type'] : 'number';

                $field_html .= sprintf(
                        '<input type="%s" name="%s" id="%s" value="%s" placeholder="%s" class="form-input h-9 text-sm border-gray-400 text-center" style="width: 70px;" />',
                        esc_attr( $type ),
                        esc_attr( $input_name ),
                        esc_attr( $input_id ),
                        esc_attr( $value ),
                        esc_attr( $placeholder )
                );
            }
        }

        $field_html .= '</p>';

        if ( ! empty( $args['desc'] ) ) {
            $field_html .= $this->generate_description( $args['desc'] );
        }

        echo $this->generate_field_wrapper( $args, $field_html );
    }

    public function generate_child_field($args) {
        if (empty($args['children']) || !is_array($args['children'])) {
            return;
        }

        echo '<div class="wpsf-child-fields" data-parent="' . $args['parent'] . '">';
        foreach ($args['children'] as $child) {
            $child = wp_parse_args($child, $this->setting_defaults);

            // Set up the child field
            $options = get_option($this->option_group . '_settings');
            $child['id'] = sprintf('%s_%s', $args['id'], $child['id']);
            $child['name'] = $this->generate_field_name('t', 's', $child['id']);
            $child['value'] = isset($options[$child['id']]) ? $options[$child['id']] :
                    (isset($child['default']) ? $child['default'] : '');

            // Generate the child field wrapper
            echo '<div class="wpsf-child-field">';

            // Only show label and field, no th element
            if (!empty($child['title'])) {
                echo '<label class="wpsf-child-field__label">' . $child['title'] . '</label>';
            }

            // Generate the actual field
            $this->do_field_method($child);

            echo '</div>';
        }

        echo '</div>';

        // Add JavaScript to handle visibility
        $this->add_child_fields_script($args['parent']);
    }

    private function add_child_fields_script($parent_id) {
        ?>
        <script type="text/javascript">
			(function($) {
				$(document).ready(function() {
					var $parent = $('#<?php echo esc_js($parent_id); ?>');
					console.log($parent);
					var $childFields = $('.wpsf-child-fields[data-parent="<?php echo esc_js($parent_id); ?>"]');

					function toggleChildFields() {
						if ($parent.is(':checkbox')) {
							$childFields.toggle($parent.is(':checked'));
						} else {
							$childFields.toggle($parent.val() !== '');
						}
					}

					$parent.on('change', toggleChildFields);
					toggleChildFields(); // Initial state
				});
			})(jQuery);
        </script>
        <?php
    }

    /**
     * Generate group row template
     *
     * @param  array  $args   Field arguments
     * @param  bool   $blank  Blank values
     * @param  int    $row    Iterator
     *
     * @return string|bool
     */
    public function generate_group_row_template( $args, $blank = false, $row = 0 ) {
        $row_template = false;
        $row_id       = ! empty( $args['value'][ $row ]['row_id'] ) ? $args['value'][ $row ]['row_id'] : $row;
        $row_id_value = $blank ? '' : $row_id;

        if ( $args['subfields'] ) {
            $row_class = $row % 2 == 0 ? "alternate" : "";

            $row_template .= sprintf( '<tr class="wpsf-group__row %s">', $row_class );

            $row_template .= sprintf( '<td class="wpsf-group__row-index"><span>%d</span></td>', $row );

            $row_template .= '<td class="wpsf-group__row-fields">';

            $row_template .= '<input type="hidden" class="wpsf-group__row-id" name="' . sprintf( '%s[%d][row_id]',
                            esc_attr( $args['name'] ), esc_attr( $row ) ) . '" value="' . esc_attr( $row_id_value ) . '" />';

            foreach ( $args['subfields'] as $subfield ) {
                $subfield = wp_parse_args( $subfield, $this->setting_defaults );

                $subfield['value'] = ( $blank ) ? "" : ( isset( $args['value'][ $row ][ $subfield['id'] ] ) ? $args['value'][ $row ][ $subfield['id'] ] : "" );
                $subfield['name']  = sprintf( '%s[%d][%s]', $args['name'], $row, $subfield['id'] );
                $subfield['id']    = sprintf( '%s_%d_%s', $args['id'], $row, $subfield['id'] );

                $class = sprintf( 'wpsf-group__field-wrapper--%s', $subfield['type'] );

                $row_template .= sprintf( '<div class="wpsf-group__field-wrapper %s">', $class );
                $row_template .= sprintf( '<label for="%s" class="wpsf-group__field-label">%s</label>', $subfield['id'],
                        $subfield['title'] );

                ob_start();
                $this->do_field_method( $subfield );
                $row_template .= ob_get_clean();

                $row_template .= '</div>';
            }

            $row_template .= "</td>";

            $row_template .= '<td class="wpsf-group__row-actions">';

            $row_template .= sprintf( '<a href="javascript: void(0);" class="wpsf-group__row-add" data-template="%s_template"><span class="dashicons dashicons-plus-alt"></span></a>',
                    $args['id'] );
            $row_template .= '<a href="javascript: void(0);" class="wpsf-group__row-remove"><span class="dashicons dashicons-trash"></span></a>';

            $row_template .= "</td>";

            $row_template .= '</tr>';

            $row_template .= $this->generate_field_wrapper( $args, $row );
        }

        return $row_template;
    }

    /**
     * Generate: Checkboxes field
     *
     * @param  array  $args
     */
    public function generate_checkboxes_field( $args ) {
        echo '<input type="hidden" name="' . $args['name'] . '" value="0" />';

        if ( count( $args['choices'] ) > 6 ) {
            echo '<ul class="wpsf-list wpsf-list--checkboxes columns-3">';
        } else {
            echo '<ul class="wpsf-list wpsf-list--checkboxes">';
        }

        foreach ( $args['choices'] as $value => $text ) {
            $checked  = is_array( $args['value'] ) && in_array( strval( $value ), array_map( 'strval', $args['value'] ),
                    true ) ? 'checked="checked"' : '';
            $field_id = sprintf( '%s_%s', $args['id'], $value );

            echo sprintf( '<li class="mb-3"><label><input type="checkbox" name="%s[]" id="%s" value="%s" class="%s" %s> %s</label></li>',
                    $args['name'], $field_id, $value, $args['class'] . ' form-checkbox text-indigo-600', $checked, $text );
        }

        echo '</ul>';

        $this->generate_description( $args['desc'] );
    }

    /**
     * Generate Social Networks.
     *
     * @param $args
     *
     * @return void
     *
     * @since 1.8.1
     *
     */
    public function generate_social_networks_field( $args ) {
        if ( count( $args['choices'] ) > 6 ) {
            $output = "<ul class='social-linkz-social-networks social-linkz-sortable columns-4'>";
        } else {
            $output = "<ul class='social-linkz-social-networks social-linkz-sortable'>";
        }

        foreach ( $args['choices'] as $id => $details ) {
            $checked = is_array( $args['value'] ) && in_array( strval( $id ), array_map( 'strval', $args['value'] ),
                    true ) ? 'checked="checked"' : '';

            $field_id = sprintf( '%s_%s', $args['id'], $id );

            $output .= "<li class='social-linkz-social-network-" . $id . " mb-3'>";
            $output .= "<label for='social-linkz" . ( ! empty( $args['section'] ) ? "-" . $args['section'] : "" ) . "-social-network-input-" . $id . "' class='" . ( $checked ? "active" : "" ) . "'>";
            $output .= $details['icon'];
            $output .= $details['name'];
            $output .= "<input type='checkbox' id='social-linkz" . ( ! empty( $args['section'] ) ? "-" . $args['section'] : "" ) . "-social-network-input-" . $id . "' name='" . $args['name'] . "[]' value='" . $id . "'" . ( $checked ? " checked" : "" ) . " />";
            $output .= "</label>";
            $output .= "</li>";
        }
        $output .= "</ul>";

        echo $output;

        $this->generate_description( $args['desc'] );
    }

    /**
     * Generate: Text field
     */
    public function generate_text_field($args) {
        $args['value'] = esc_attr(stripslashes($args['value']));

        $field_html = '<input type="text" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" placeholder="' . $args['placeholder'] . '" class="form-input h-9 mb-1 text-sm border-gray-400 w-2/5 ' . $args['class'] . '" />';
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    /**
     * Generate: Number field
     */
    public function generate_number_field($args) {

        $args['value'] = esc_attr(stripslashes($args['value']));

        $field_html = '<input type="number" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" placeholder="' . $args['placeholder'] . '" class="form-input h-9 mb-1 text-sm border-gray-400 w-1/5 ' . $args['class'] . '" />';
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    /**
     * Generate: Select field
     */
    public function generate_select_field($args) {
        $args['value'] = esc_html(esc_attr($args['value']));

        $field_html = '<select name="' . $args['name'] . '" id="' . $args['id'] . '" class="' . $args['class'] . ' form-select rounded-lg w-2/5 h-9 mb-1 border-gray-400">';
        foreach ($args['choices'] as $value => $text) {
            $selected = $value == $args['value'] ? 'selected="selected"' : '';
            $field_html .= sprintf('<option value="%s" %s>%s</option>', $value, $selected, $text);
        }
        $field_html .= '</select>';
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    /**
     * Generate: Radio field
     */
    public function generate_radio_field($args) {
        $args['value'] = esc_html(esc_attr($args['value']));

        $field_html = '<div class="space-y-4">';
        foreach ($args['choices'] as $value => $text) {
            $field_html .= '<div class="flex items-center">';
            $field_id = sprintf('%s_%s', $args['id'], $value);
            $checked = $value == $args['value'] ? 'checked="checked"' : '';

            $field_html .= sprintf(
                    '<input type="radio" name="%s" id="%s" value="%s" class="%s %s" %s><label for="%s" class="ml-1 block text-sm font-medium text-gray-700">%s</label>',
                    $args['name'], $field_id, $value, $args['class'],
                    'form-radio focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300',
                    $checked, $field_id, $text
            );
            $field_html .= '</div>';
        }
        $field_html .= '</div>';
        $field_html .= '<div class="mt-2">' . $this->generate_description($args['desc']) . '</div>';

        echo $this->generate_field_wrapper($args, $field_html);
    }

    /**
     * Generate: Checkbox field
     */
    public function generate_checkbox_field($args) {
        $args['value'] = esc_attr(stripslashes($args['value']));
        $checked = $args['value'] ? 'checked="checked"' : '';

        $field_html = '<input type="hidden" name="' . $args['name'] . '" value="0" />';
        $field_html .= '<label><input type="checkbox" name="' . $args['name'] . '" id="' . $args['id'] . '" value="1" class="' . $args['class'] . ' form-checkbox text-indigo-600" ' . $checked . '> ' . $args['desc'] . '</label>';

        echo $this->generate_field_wrapper($args, $field_html);
    }

    /**
     * Generate: Switch field
     */
    public function generate_switch_field($args) {
        $args['value'] = esc_attr(stripslashes($args['value']));
        $checked = $args['value'] ? 'checked="checked"' : '';

        $field_html = '<label class="inline-flex items-center mb-1 cursor-pointer"><span class="relative">';
        $field_html .= '<input type="hidden" name="' . $args['name'] . '" value="0" />';
        $field_html .= '<input id="' . $args['id'] . '" type="checkbox" name="' . $args['name'] . '"  value="1" class="absolute w-0 h-0 mt-6 opacity-0 kc-us-check-toggle ' . $args['class'] . '" ' . $checked . ' />';
        $field_html .= '<span class="kc-us-mail-toggle-line"></span>';
        $field_html .= '<span class="kc-us-mail-toggle-dot"></span>';
        $field_html .= '</span></label>';

        if ( ! empty( $args['desc'] ) ) {
            $field_html .= '<span class="ml-3 text-sm text-gray-700">' . $args['desc'] . '</span>';
        }

        echo $this->generate_field_wrapper($args, $field_html);
    }

    public function generate_time_field($args) {
        $args['value'] = esc_attr(stripslashes($args['value']));
        $timepicker = !empty($args['timepicker']) ? htmlentities(json_encode($args['timepicker'])) : null;

        $field_html = '<input type="text" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" class="timepicker regular-text ' . $args['class'] . '" data-timepicker="' . $timepicker . '" />';
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    public function generate_date_field($args) {
        $args['value'] = esc_attr(stripslashes($args['value']));
        $datepicker = !empty($args['datepicker']) ? htmlentities(json_encode($args['datepicker'])) : null;

        $field_html = '<input type="text" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" class="datepicker regular-text ' . $args['class'] . '" data-datepicker="' . $datepicker . '" />';
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    public function generate_password_field($args) {
        $args['value'] = esc_attr(stripslashes($args['value']));

        $field_html = '<input type="password" name="' . $args['name'] . '" id="' . $args['id'] . '" value="' . $args['value'] . '" placeholder="' . $args['placeholder'] . '" class="regular-text ' . $args['class'] . '" />';
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    public function generate_textarea_field($args) {
        $args['value'] = esc_html(esc_attr($args['value']));

        $field_html = '<textarea name="' . $args['name'] . '" id="' . $args['id'] . '" placeholder="' . $args['placeholder'] . '" rows="5" cols="60" class="' . $args['class'] . '">' . $args['value'] . '</textarea>';
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    public function generate_color_field($args) {
        $color_picker_id = sprintf('%s_cp', $args['id']);
        $args['value'] = esc_attr(stripslashes($args['value']));

        $field_html = '<div style="position:relative;">';
        $field_html .= sprintf('<input type="text" name="%s" id="%s" value="%s" class="%s">', $args['name'], $args['id'], $args['value'], $args['class']);
        $field_html .= sprintf('<div id="%s" style="position:absolute;top:0;left:190px;background:#fff;z-index:9999;"></div>', $color_picker_id);
        $field_html .= $this->generate_description($args['desc']);
        $field_html .= '<script type="text/javascript">jQuery(document).ready(function($){$("#' . $color_picker_id . '").farbtastic("#' . $args['id'] . '");});</script>';
        $field_html .= '</div>';

        echo $this->generate_field_wrapper($args, $field_html);
    }

    public function generate_file_field($args) {
        $args['value'] = esc_attr($args['value']);
        $button_id = sprintf('%s_button', $args['id']);

        $field_html = sprintf('<input type="text" name="%s" id="%s" value="%s" class="regular-text %s"> ', $args['name'], $args['id'], $args['value'], $args['class']);
        $field_html .= sprintf('<input type="button" class="button wpsf-browse" id="%s" value="Browse" />', $button_id);
        $field_html .= '<script type="text/javascript">jQuery(document).ready(function($){$("#' . $button_id . '").click(function(){tb_show("","media-upload.php?type=image&amp;TB_iframe=true");window.send_to_editor = function(html) {$("#' . $args['id'] . '").val($(html).attr("href"));tb_remove();}return false;})});</script>';
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    /**
     * Generate: Editor field.
     */
    public function generate_editor_field($args) {
        $field_html = '';
        ob_start();
        wp_editor($args['value'], $args['id'], ['textarea_name' => $args['name']]);
        $field_html .= ob_get_clean();
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    public function generate_custom_field($args) {
        $field_html = isset($args['output']) ? $args['output'] : $args['default'];
        echo $this->generate_field_wrapper($args, $field_html);
    }

    public function generate_multiinputs_field($args) {
        $field_titles = array_keys($args['default']);
        $values = array_values($args['value']);

        $field_html = '<div class="wpsf-multifields">';
        $i = 0;
        while ($i < count($values)) {
            $field_html .= '<div class="wpsf-multifields__field">';
            $field_html .= '<div class="wpsf-multifields__field-label">' . $field_titles[$i] . '</div>';
            $field_html .= '<input type="text" name="' . $args['name'] . '[]" value="' . esc_attr($values[$i]) . '" class="regular-text" />';
            $field_html .= '</div>';
            $i++;
        }
        $field_html .= '</div>';
        $field_html .= $this->generate_description($args['desc']);

        echo $this->generate_field_wrapper($args, $field_html);
    }

    /**
     * Generate Field Name.
     *
     * @param $section_id
     * @param $field_id
     *
     * @param $tab_id
     *
     * @return string
     *
     * @since 1.8.1
     *
     */
    public function generate_field_name( $tab_id, $section_id, $field_id ) {
        if ( empty( $tab_id ) ) {
            return sprintf( '%s_settings[%s][%s]', $this->option_group, $section_id, $field_id );
        }

        return sprintf( '%s_settings[%s][%s][%s]', $this->option_group, $tab_id, $section_id, $field_id );
    }

    /**
     * Get field name to get value.
     *
     * @param $section_id
     * @param $field_id
     *
     * @param $tab_id
     *
     * @return string
     *
     * @since 1.8.1
     *
     */
    public function get_field_name_to_get_value( $tab_id, $section_id, $field_id ) {
        if ( empty( $tab_id ) ) {
            return sprintf( '%s|%s', $section_id, $field_id );
        }

        return sprintf( '%s|%s|%s', $tab_id, $section_id, $field_id );
    }

    /**
     * Generate: Description
     *
     * @param  mixed  $description
     */
    public function generate_description( $description ) {
        $desc_output = '';
        if ( $description && $description !== "" ) {
            $desc_output .= '<p class="description">' . $description . '</p>';
        }

        return $desc_output;
    }

    public function generate_heading_field( $args ) {
    }

    /**
     * Output the settings form
     */
    public function settings() {
        do_action( 'wpsf_before_settings_' . $this->option_group );
        ?>
        <form action="options.php" method="post" novalidate>
            <?php
            do_action( 'wpsf_before_settings_fields_' . $this->option_group ); ?>
            <?php
            settings_fields( $this->option_group ); ?>

            <?php
            do_action( 'wpsf_do_settings_sections_' . $this->option_group ); ?>

            <?php
            if ( apply_filters( 'wpsf_show_save_changes_button_' . $this->option_group, true ) ) { ?>
                <p class="submit">
                    <input type="submit" class="align-middle cursor-pointer kc-uu-primary-button"
                           value="<?php
                           echo esc_attr( 'Save Changes' ); ?>"/>
                </p>
                <?php
            } ?>
        </form>
        <?php
        do_action( 'wpsf_after_settings_' . $this->option_group );
    }

    /**
     * Helper: Get Settings
     *
     * @return array
     */
    public function get_settings() {
        $settings_name = $this->option_group . '_settings';

        static $settings = [];

        if ( isset( $settings[ $settings_name ] ) ) {
            return $settings[ $settings_name ];
        }

        $saved_settings             = get_option( $this->option_group . '_settings' );
        $settings[ $settings_name ] = [];

        foreach ( $this->settings as $section ) {
            if ( empty( $section['fields'] ) ) {
                continue;
            }

            foreach ( $section['fields'] as $field ) {
                if ( ! empty( $field['default'] ) && is_array( $field['default'] ) ) {
                    $field['default'] = array_values( $field['default'] );
                }

                $setting_key = $this->has_tabs() ? sprintf( '%s_%s_%s', $section['tab_id'], $section['section_id'],
                        $field['id'] ) : sprintf( '%s_%s', $section['section_id'], $field['id'] );

                if ( isset( $saved_settings[ $setting_key ] ) ) {
                    $settings[ $settings_name ][ $setting_key ] = $saved_settings[ $setting_key ];
                } else {
                    $settings[ $settings_name ][ $setting_key ] = ( isset( $field['default'] ) ) ? $field['default'] : false;
                }
            }
        }

        return $settings[ $settings_name ];
    }

    /**
     * Tabless Settings sections
     */
    public function do_tabless_settings_sections() {
        ?>
        <div class="wpsf-section wpsf-tabless">
            <?php
            $this->do_settings_sections( $this->option_group ); ?>
        </div>
        <?php
    }

    /**
     * Tabbed Settings sections
     */
    public function do_tabbed_settings_sections() {
        $i = 0;
        foreach ( $this->tabs as $tab_data ) {
            ?>
            <div id="tab-<?php
            echo $tab_data['id']; ?>"
                 class="wpsf-section wpsf-tab wpsf-tab--<?php
                 echo $tab_data['id']; ?> <?php
                 if ( $i == 0 ) {
                     echo 'wpsf-tab--active';
                 } ?>">
                <div class="postbox">
                    <?php
                    $this->do_settings_sections( sprintf( '%s_%s', $this->option_group, $tab_data['id'] ) ); ?>
                </div>
            </div>
            <?php
            $i ++;
        }
    }

    /**
     * Output the tab links
     */
    public function tab_links() {
        if ( ! apply_filters( 'wpsf_show_tab_links_' . $this->option_group, true ) ) {
            return;
        }

        do_action( 'wpsf_before_tab_links_' . $this->option_group );
        ?>
        <h2 class="nav-tab-wrapper">
            <?php
            $i = 0;
            foreach ( $this->tabs as $tab_data ) {
                $active = $i == 0 ? 'nav-tab-active' : '';
                ?>
                <a class="nav-tab wpsf-tab-link <?php
                echo $active; ?>"
                   href="#tab-<?php
                   echo $tab_data['id']; ?>"><?php
                    echo $tab_data['title']; ?></a>
                <?php
                $i ++;
            }
            ?>
        </h2>
        <?php
        do_action( 'wpsf_after_tab_links_' . $this->option_group );
    }

    /**
     * Check if this settings instance has tabs
     */
    public function has_tabs() {
        if ( ! empty( $this->tabs ) ) {
            return true;
        }

        return false;
    }

    /**
     * Prints out all settings sections added to a particular settings page
     *
     * Part of the Settings API. Use this in a settings page callback function
     * to output all the sections and fields that were added to that $page with
     * add_settings_section() and add_settings_field()
     *
     * @param  string  $page                 The slug name of the page whose settings sections you want to output.
     *
     * @since 2.7.0
     *
     * @global array   $wp_settings_fields   Storage array of settings fields and info about their pages/sections.
     * @global array   $wp_settings_sections Storage array of all settings sections added to admin pages.
     */
    function do_settings_sections( $page ) {
        global $wp_settings_sections, $wp_settings_fields;

        if ( ! isset( $wp_settings_sections[ $page ] ) ) {
            return;
        }

        foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
            if ( $section['title'] ) {
                echo "<h2>{$section['title']}</h2>\n";
            }

            if ( $section['callback'] ) {
                call_user_func( $section['callback'], $section );
            }

            if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
                continue;
            }
            echo '<table class="form-table" role="presentation">';
            $this->do_settings_fields( $page, $section['id'] );
            echo '</table>';
        }
    }

    /**
     * Print out the settings fields for a particular settings section.
     *
     * Part of the Settings API. Use this in a settings page to output
     * a specific section. Should normally be called by do_settings_sections()
     * rather than directly.
     *
     * @param  string  $section            Slug title of the settings section whose fields you want to show.
     *
     * @param  string  $page               Slug title of the admin page whose settings fields you want to show.
     *
     * @since 2.7.0
     *
     * @global array   $wp_settings_fields Storage array of settings fields and their pages/sections.
     *
     */
    function do_settings_fields( $page, $section ) {
        global $wp_settings_fields;

        if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
            return;
        }

        foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
            $output = '';
            $class = '';

            if ( ! empty( $field['args']['class'] ) ) {
                $class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
            }

            $output .= "<tr{$class}>";

            if ( ! empty( $field['args']['label_for'] ) ) {
                $html = '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'];

                if ( ! empty( $field['args']['field']['tooltip'] ) ) {
                    $html .= Helper::get_tooltip_html( $field['args']['field']['tooltip'] );
                }

                $html .= '</label></th>';
                $output .= $html;
            } elseif ( ! empty( $field['title'] ) ) {
                $html = '<th scope="row">' . $field['title'];

                if ( ! empty( $field['args']['field']['title_desc'] ) ) {
                    $html .= "<p class='mt-1 text-sm/12 text-gray-400'>{$field['args']['field']['title_desc']}</p>";
                }

                if ( ! empty( $field['args']['field']['tooltip'] ) ) {
                    $html .= Helper::get_tooltip_html( $field['args']['field']['tooltip'] );
                }

                $html .= '</th>';
                $output .= $html;
            }

            $output .= '<td>';
            ob_start();
            call_user_func( $field['callback'], $field['args'] );
            $output .= ob_get_clean();
            $output .= '</td>';
            $output .= '</tr>';

            echo $this->generate_field_wrapper( $field['args']['field'], $output );
        }
    }
}

if ( ! function_exists( 'wpsf_get_setting' ) ) {
    /**
     * Get a setting from an option group
     *
     * @param  string  $option_group
     * @param  string  $section_id  May also be prefixed with tab ID
     * @param  string  $field_id
     *
     * @return mixed
     */
    function wpsf_get_setting( $option_group, $section_id, $field_id ) {
        $options = get_option( $option_group . '_settings' );
        if ( isset( $options[ $section_id . '_' . $field_id ] ) ) {
            return $options[ $section_id . '_' . $field_id ];
        }

        return false;
    }
}

if ( ! function_exists( 'wpsf_delete_settings' ) ) {
    /**
     * Delete all the saved settings from a settings file/option group
     *
     * @param  string  $option_group
     */
    function wpsf_delete_settings( $option_group ) {
        delete_option( $option_group . '_settings' );
    }
}