<?php ?>
<!-- Upgrade Notice -->
<div class="overflow-hidden bg-gray-800 py-24 sm:py-32 mt-10">
	<div class="relative isolate">
		<div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
			<div class="relative w-full overflow-hidden rounded-2xl shadow-xl bg-white items-center">
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

			<div class="mx-auto flex max-w-2xl flex-col gap-16 bg-white/3 px-6 py-16 ring-1 ring-white/10 sm:rounded-3xl sm:p-8 lg:mx-0 lg:max-w-none lg:flex-row lg:items-center lg:py-20 xl:gap-x-20 xl:px-20">

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