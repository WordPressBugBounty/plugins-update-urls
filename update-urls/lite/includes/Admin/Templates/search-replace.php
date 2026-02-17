<?php

$options = [
	'content'  => [
		'label' => __( 'Content', 'update-urls' ),
		'desc'  => __( 'Search in page content (posts, pages, custom post types, revisions)', 'update-urls' ),
	],
	'excerpts' => [
		'label' => __( 'Excerpts', 'update-urls' ),
		'desc'  => __( 'Search in excerpts', 'update-urls' ),
	],

	'attachments' => [
		'label' => __( 'Attachments', 'update-urls' ),
		'desc'  => __( 'Search in attachments (images, documents, general media)', 'update-urls' ),
	],

	'links' => [
		'label' => __( 'Links', 'update-urls' ),
		'desc'  => __( 'Search in links', 'update-urls' ),
	],

	'custom' => [
		'label' => __( 'Custom', 'update-urls' ),
		'desc'  => __( 'Search in custom fields and meta boxes', 'update-urls' ),
	],
];

$additional_settings = [
//	'case_insensitive' => [
//		'label' => __( 'Case-Insensitive', 'update-urls' ),
//		'desc'  => __( 'Searches are case-sensitive by default.', 'update-urls' ),
//	],

	'guids' => [
		'label' => __( 'Replace GUIDs', 'update-urls' ),
		'desc'  => sprintf( __( 'Update ALL GUIDs GUIDs for posts should only be changed on development sites. <a href="%s" target="_blank">Learn More</a>.',
			'update-urls' ), 'http://codex.wordpress.org/Changing_The_Site_URL#Important_GUID_Note' ),
	],

//	'dry_run' => [
//		'label' => __( 'Run as dry run', 'update-urls' ),
//		'desc'  => __( 'If checked, no changes will be made to the database, allowing you to check the results beforehand.', 'update-urls' ),
//	],
];

if ( isset( $_POST['kc_uu_settings_submit'] ) && ! check_admin_referer( 'kc_uu_submit', 'kc_uu_nonce' ) ) {
	if ( isset( $_POST['search_for'] ) && isset( $_POST['replace_with'] ) ) {
		$search_for = esc_url_raw( wp_unslash( $_POST['search_for'] ) );
		$replace_with = esc_url_raw( wp_unslash( $_POST['replace_with'] ) );
	}
	echo '<div id="message" class="error fade"><p><strong>' . esc_html__( 'ERROR',
			'update-urls' ) . ' - ' . esc_html__( 'Please try again.', 'update-urls' ) . '</strong></p></div>';
} elseif ( isset( $_POST['kc_uu_settings_submit'] ) && ! isset( $_POST['kc_uu_update_links'] ) ) {
	if ( isset( $_POST['search_for'] ) && isset( $_POST['replace_with'] ) ) {
		$search_for = esc_url_raw( wp_unslash( $_POST['search_for'] ) );
		$replace_with = esc_url_raw( wp_unslash( $_POST['replace_with'] ) );
	}
	echo '<div id="message" class="error fade"><p><strong>' . esc_html__( 'ERROR',
			'update-urls' ) . ' - ' . esc_html__( 'Your URLs have not been updated.',
			'update-urls' ) . '</p></strong><p>' . esc_html__( 'Please select at least one checkbox.',
			'update-urls' ) . '</p></div>';
}
elseif ( isset( $_POST['kc_uu_settings_submit'] ) ) {

$kc_uu_update_links = isset( $_POST['kc_uu_update_links'] ) ? (array) $_POST['kc_uu_update_links'] : [];

$kc_uu_update_links = array_map( 'esc_attr', $kc_uu_update_links );

if ( isset( $_POST['search_for'] ) && isset( $_POST['replace_with'] ) ) {
	$search_for = esc_url_raw( wp_unslash( $_POST['search_for'] ) );
	$replace_with = esc_url_raw( wp_unslash( $_POST['replace_with'] ) );
}
if ( ( $search_for && $search_for != 'http://www.oldurl.com' && trim( $search_for ) != '' ) && ( $replace_with && $replace_with != 'http://www.newurl.com' && trim( $replace_with ) != '' ) ) {
$results = \KaizenCoders\UpdateURLS\Helper::UpdateURLS( $kc_uu_update_links, $search_for, $replace_with );


$empty       = true;
$emptystring = '<strong>' . __( 'Why do the results show 0 URLs updated?',
		'update-urls' ) . '</strong><br/>' . __( 'This happens if a URL is incorrect OR if it is not found in the content. Check your URLs and try again.',
		'update-urls' );

$resultstring = '';
foreach ( $results as $result ) {
	$empty        = ( $result[0] != 0 || $empty == false ) ? false : true;
	$resultstring .= '<br/><strong>' . $result[0] . '</strong> ' . $result[1];
}

if ( $empty ) :
?>
<div id="message" class="error fade">
    <table>
        <tr>
            <td><p><strong>
						<?php _e( 'ERROR: Something may have gone wrong.', 'update-urls' ); ?>
                    </strong><br/>
					<?php _e( 'No search found.', 'update-urls' ); ?>
                </p>
				<?php
				else :
				?>
                <div id="message" class="updated fade">
                    <table>
                        <tr>
                            <td><p><strong>
										<?php _e( 'Success! data have been updated.', 'update-urls' ); ?>
                                    </strong></p>
								<?php
								endif;
								?>
                                <p><u>
										<?php _e( 'Results', 'update-urls' ); ?>
                                    </u><?php echo $resultstring; ?></p>
								<?php echo ( $empty ) ? '<p>' . $emptystring . '</p>' : ''; ?></td>
                            <td width="60"></td>
                            <td align="center"><?php if ( ! $empty ) : ?>
                                    <p>
									<?php // You can now uninstall this plugin.<br/> ?>
								<?php endif; ?></td>
                        </tr>
                    </table>
                </div>
				<?php
				} else {
					echo '<div id="message" class="error fade"><p><strong>' . esc_html__( 'ERROR',
							'update-urls' ) . ' - ' . esc_html__( 'Your data have not been updated.',
							'update-urls' ) . '</p></strong><p>' . esc_html_e( 'Please enter values for both search for and replace with.',
							'update-urls' ) . '</p></div>';
				}
				}
				?>


                <div class="bg-white">
                    <div class=" flex flex-auto">

                        <form method="post" action="" class="p-10 min-h-full">
							<?php wp_nonce_field( 'kc_uu_submit', 'kc_uu_nonce' ); ?>

                            <!-- Important Notice -->
                            <div class="section bg-gray-100 p-5 mb-5 border-2">
                                <p class="text-xl bold-text text-center mb-5 underline">Important Note</p>
                                <ul>
                                    <li class="text-red-500 bold-text">
                                        <?php esc_html_e( 'WE RECOMMEND THAT YOU BACKUP YOUR WEBSITE.',
                                                'update-urls' ); ?> </li><p><?php esc_html_e( 'You may need to restore it if incorrect data are entered in the fields below.',
											'update-urls' ); ?></p>
                                </ul>
                            </div>

                            <!-- Search / Replace -->
                            <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 m-5 md:grid-cols-4">
                                <div>
                                    <h2 class="text-base font-semibold leading-7 text-gray-900"><?php esc_html_e( 'Search / Replace',
											'update-urls' ); ?></h2>
                                    <p class="mt-1 text-sm leading-6 text-gray-600"></p>
                                </div>

                                <div class="grid grid-cols max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                                    <div class="sm:col-span-3">
                                        <label for="last-name"
                                               class="block text-sm font-medium leading-6 text-gray-900">
											<?php esc_html_e( 'Search For', 'update-urls' ); ?>
                                        </label>
                                        <div class="mt-2">
                                            <input id=""
                                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                   placeholder=""
                                                   name="search_for"
                                                   value=""
                                                   size="30" maxlength="100"/>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label for="last-name"
                                               class="block text-sm font-medium leading-6 text-gray-900">
											<?php esc_html_e( 'Replace With', 'update-urls' ); ?>
                                        </label>
                                        <div class="mt-2">
                                            <input id=""
                                                   class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                   placeholder=""
                                                   name="replace_with"
                                                   value=""
                                                   size="30" maxlength="100"/>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Where To Update -->
                            <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-4 m-5">
                                <div class="">
                                    <h2 class="text-base font-semibold leading-7 text-gray-900"><?php esc_html_e( 'Where to Update?',
											'update-urls' ); ?></h2>
                                    <p class="mt-1 text-sm leading-6 text-gray-600"></p>
                                </div>

                                <div class="max-w-2xl space-y-10 md:col-span-2">
                                    <fieldset>
                                        <div class="space-y-6">
											<?php foreach ( $options as $key => $option ) { ?>
                                                <div class="relative flex gap-x-3">
                                                    <div class="flex h-8 items-center">
                                                        <input id="<?php echo $key; ?>" name="kc_uu_update_links[]"
                                                               type="checkbox"
                                                               class="h-4 w-4 form-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                               value="<?php echo $key; ?>"/>
                                                    </div>
                                                    <div class="text-sm leading-6">
                                                        <label for="<?php echo $key; ?>"
                                                               class="font-medium text-gray-900"><?php echo $option['label'] ?></label>
                                                        <p class="text-gray-500"><?php echo $option['desc']; ?></p>
                                                    </div>
                                                </div>
											<?php } ?>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <!-- Additional Settings -->
                            <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-b border-gray-900/10 pb-12 md:grid-cols-4 m-5">
                                <div class="">
                                    <h2 class="text-base font-semibold leading-7 text-gray-900"><?php esc_html_e( 'Additional Settings',
											'update-urls' ); ?></h2>
                                    <p class="mt-1 text-sm leading-6 text-gray-600"></p>
                                </div>

                                <div class="max-w-2xl space-y-10 md:col-span-2">
                                    <fieldset>
                                        <div class="space-y-6">
											<?php foreach ( $additional_settings as $key => $option ) { ?>
                                                <div class="relative flex gap-x-3">
                                                    <div class="flex h-8 items-center">
                                                        <input id="<?php echo $key; ?>" name="kc_uu_update_links[]"
                                                               type="checkbox"
                                                               class="h-4 w-4 form-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                                               value="<?php echo $key; ?>"/>
                                                    </div>
                                                    <div class="text-sm leading-6">
                                                        <label for="<?php echo $key; ?>"
                                                               class="font-medium text-gray-900"><?php echo $option['label'] ?></label>
                                                        <p class="text-gray-500"><?php echo $option['desc']; ?></p>
                                                    </div>
                                                </div>
											<?php } ?>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>

                            <div class="flex flex-row border-b border-gray-100 mt-10">
                                <div class="flex w-1/5">
                                    <div class="ml-4">
                                        <input class="button-primary" name="kc_uu_settings_submit"
                                               value="<?php esc_attr_e( 'Run Search/Replace', 'update-urls' ); ?>"
                                               type="submit"/>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Upgrade Notice -->
    <div class="overflow-hidden bg-gray-800 py-24 sm:py-32">
        <div class="relative isolate">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mx-auto flex max-w-2xl flex-col gap-16 bg-white/3 px-6 py-16 ring-1 ring-white/10 sm:rounded-3xl sm:p-8 lg:mx-0 lg:max-w-none lg:flex-row lg:items-center lg:py-20 xl:gap-x-20 xl:px-20">
                    <div class="relative w-full max-w-3xl overflow-hidden rounded-2xl shadow-xl bg-white">
                        <!-- Slides Wrapper -->
                        <div id="carousel" class="flex transition-transform duration-500 ease-in-out">
                            <img src="<?php echo KC_UU_PLUGIN_ASSETS_DIR_URL . '/images/screenshot-2.png' ; ?>" class="w-full flex-shrink-0 object-cover" />
                            <img src="<?php echo KC_UU_PLUGIN_ASSETS_DIR_URL . '/images/screenshot-3.png' ; ?>" class="w-full flex-shrink-0 object-cover" />
                            <img src="<?php echo KC_UU_PLUGIN_ASSETS_DIR_URL . '/images/screenshot-4.png' ; ?>" class="w-full flex-shrink-0 object-cover" />
                            <img src="<?php echo KC_UU_PLUGIN_ASSETS_DIR_URL . '/images/screenshot-5.png' ; ?>" class="w-full flex-shrink-0 object-cover" />
                            <img src="<?php echo KC_UU_PLUGIN_ASSETS_DIR_URL . '/images/screenshot-6.png' ; ?>" class="w-full flex-shrink-0 object-cover" />
                            <img src="<?php echo KC_UU_PLUGIN_ASSETS_DIR_URL . '/images/screenshot-7.png' ; ?>" class="w-full flex-shrink-0 object-cover" />
                        </div>

                        <!-- Previous Button -->
                        <button onclick="prevSlide()"
                                class="absolute top-1/2 left-4 -translate-y-1/2 bg-white/70 hover:bg-white text-black p-2 rounded-full shadow">
                            ❮
                        </button>

                        <!-- Next Button -->
                        <button onclick="nextSlide()"
                                class="absolute top-1/2 right-4 -translate-y-1/2 bg-white/70 hover:bg-white text-black p-2 rounded-full shadow">
                            ❯
                        </button>

                    </div>
                    <div class="w-full flex-auto">
                        <h2 class="text-4xl font-semibold tracking-tight text-pretty text-white sm:text-5xl">Upgrade TO PRO</h2>
                        <p class="mt-6 text-lg/8 text-pretty text-gray-400">With Update URLs PRO, You get powerful built-in safety tools so you don’t have to rely on manual backups:</p>
                        <ul role="list" class="mt-10 grid grid-cols-1 gap-x-8 gap-y-3 text-base/7 text-gray-200 sm:grid-cols-2">
                            <li class="flex gap-x-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-7 w-5 flex-none text-gray-200">
                                    <path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                One-Click Database Export & Import
                            </li>
                            <li class="flex gap-x-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-7 w-5 flex-none text-gray-200">
                                    <path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Search/Replace History
                            </li>
                            <li class="flex gap-x-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-7 w-5 flex-none text-gray-200">
                                    <path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                One-Click Undo
                            </li>
                            <li class="flex gap-x-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-7 w-5 flex-none text-gray-200">
                                    <path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Allow Table Selection For Search/Replace
                            </li>
                            <li class="flex gap-x-3">
                                <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="h-7 w-5 flex-none text-gray-200">
                                    <path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" fill-rule="evenodd" />
                                </svg>
                                Replace Only Selected Results
                            </li>
                        </ul>
                        <div class="mt-10 flex">
                            <a href="https://kaizencoders.com/update-urls?utm-campaign=upgrade-to-pro&utm-medium=in_app" class="button-primary bg-indigo-500 font-semibold text-white hover:text-white">
                                Upgrade to PRO Now
                                <span aria-hidden="true">&rarr;</span>
                            </a> <br />
                        </div>
                        <p class="mt-5 text-sm/5 font-semibold text-white hover:text-indigo-300">Limited time flat 50% off. Use Coupon Code: <span class="text-red-500">SPECIAL50</span></p>
                    </div>
                </div>
            </div>
            <div aria-hidden="true" class="absolute inset-x-0 -top-16 -z-10 flex transform-gpu justify-center overflow-hidden blur-3xl">
                <div style="clip-path: polygon(73.6% 51.7%, 91.7% 11.8%, 100% 46.4%, 97.4% 82.2%, 92.5% 84.9%, 75.7% 64%, 55.3% 47.5%, 46.5% 49.4%, 45% 62.9%, 50.3% 87.2%, 21.3% 64.1%, 0.1% 100%, 5.4% 51.1%, 21.4% 63.9%, 58.9% 0.2%, 73.6% 51.7%)" class="aspect-1318/752 w-329.5 flex-none bg-linear-to-r from-[#80caff] to-[#4f46e5] opacity-20"></div>
            </div>
        </div>
    </div>



    <script>
		const carousel = document.getElementById('carousel');
		const slides = carousel.children;
		let currentIndex = 0;

		function updateSlide() {
			const width = slides[0].clientWidth;
			carousel.style.transform = `translateX(-${currentIndex * width}px)`;
		}

		function nextSlide() {
			currentIndex = (currentIndex + 1) % slides.length;
			updateSlide();
		}

		function prevSlide() {
			currentIndex = (currentIndex - 1 + slides.length) % slides.length;
			updateSlide();
		}

		window.addEventListener('resize', updateSlide);
    </script>


