/*
Author       : Dreamstechnologies
Template Name: Dreams Estate - Bootstrap Template
*/

(function () {
    "use strict";

    // Sidebar

	function bindMobileMenu() {
	// Unbind previous events to avoid duplicates
	$(document).off('click.mobileMenu');

		// Only bind for mobile view
		if ($(window).width() <= 991) {
			$(document).on('click.mobileMenu', '.main-nav a', function (e) {
				var $this = $(this);

				if ($this.parent().hasClass('has-submenu')) {
					e.preventDefault();

					if (!$this.hasClass('submenu')) {
						// Close other open submenus
						$this.closest('ul').find('ul').slideUp(350);
						$this.closest('ul').find('a').removeClass('submenu');

						// Open current submenu
						$this.next('ul').slideDown(350);
						$this.addClass('submenu');
					} else {
						$this.removeClass('submenu');
						$this.next('ul').slideUp(350);
					}
				}
			});
		}
	}

	// Initial binding
	bindMobileMenu();

	// Re-bind on window resize
	$(window).on('resize', function () {
		bindMobileMenu();
	});


	// Mobile menu sidebar overlay

	$('header').append('<div class="sidebar-overlay"></div>');
	$(document).on('click', '#mobile_btn', function () {
		$('main-wrapper').toggleClass('slide-nav');
		$('.sidebar-overlay').toggleClass('opened');
		$('html').toggleClass('menu-opened');
		return false;
	});

    $(document).on('click', '.sidebar-overlay', function() {
		$('html').removeClass('menu-opened');
		$(this).removeClass('opened');
		$('main-wrapper').removeClass('slide-nav');
	});
	
	$(document).on('click', '#menu_close', function() {
		$('html').removeClass('menu-opened');
		$('.sidebar-overlay').removeClass('opened');
		$('main-wrapper').removeClass('slide-nav');
	});

	// theiaStickySidebar

	if ($(window).width() > 767) {
		if($('.theiaStickySidebar').length > 0) {
			$('.theiaStickySidebar').theiaStickySidebar({
			  // Settings
			  additionalMarginTop: 30
			});
		}
	}

	// View all Show hide One

	if($('.more-menu').length > 0) {
		$(".more-menu").hide();
		$(".viewall-button").on("click", function() {
			$(this).text($(this).text() === "Less" ? "See More" : "Less");
			$(".more-menu").slideToggle(900);
		});	  	
	}

	// Select 2

	if($('.select').length > 0) {
		$('.select').select2({
			minimumResultsForSearch: -1,
			width: '100%'
		});
		$(document).on('select2:open', function() {
			if (window.location.hash) {
				history.replaceState(null, null, window.location.pathname + window.location.search);
			}
		});
	}

	// Select Favourite — handled in footer-scripts

	// Modal

	if($('.topbar-search').length > 0) {
		document.querySelector('.topbar-search').addEventListener('click', function () {
		document.body.classList.add('modal-search');
	});
	}

	if($('.close').length > 0) {
	document.querySelector('.close').addEventListener('click', function () {
		document.body.classList.remove('modal-search');
	});
	}

	// Custom Country Code Selector

	if ($('#phone').length > 0 && typeof window.intlTelInput !== 'undefined') {
		var input = document.querySelector("#phone");
		window.intlTelInput(input, {
			utilsScript: "/build/plugins/intltelinput/js/utils.js", separateDialCode: true, initialCountry: "am", preferredCountries: [],
		});
	}

	/// cart count
	document.addEventListener("DOMContentLoaded", function () {
	if (!document.getElementById("cart-wrap")) return; // Exit if not on the right page

	const btnCovers = document.querySelectorAll(".btn-cover");

	btnCovers.forEach((container) => {
		const input = container.querySelector(".quantity-input");
		const plusBtn = container.querySelector(".add-btn");
		const minusBtn = container.querySelector(".minus-btn");

		plusBtn.addEventListener("click", () => {
			let currentValue = parseInt(input.value) || 0;
			input.value = currentValue + 1;
		});

		minusBtn.addEventListener("click", () => {
			let currentValue = parseInt(input.value) || 0;
			if (currentValue > 1) {
				input.value = currentValue - 1;
			}
		});
	});	

	});

	// Datetimepicker
	if($('.datetimepicker').length > 0 ){
		$('.datetimepicker').datetimepicker({
			format: 'DD MMM YYYY',
			icons: {
				up: "fas fa-angle-up",
				down: "fas fa-angle-down",
				next: 'fas fa-angle-right',
				previous: 'fas fa-angle-left'
			}
		});
	}

	// Add new Scedule
     $(".add-floor-plan-btn").on('click', function () {
			
		var addcontent = `
			<!-- start row -->
			<div class="row add-count">

				<div class="col-sm-6">
					<div class="mb-3">
						<label class="form-label">Property Name</label>
						<input type="text" class="form-control">
					</div>
				</div> <!-- end col -->

				<div class="col-sm-6">
					<div class="mb-3">
						<label class="form-label">Property Type</label>
						<select class="select">
							<option>Select</option>
							<option>Buy</option>
							<option>Sale</option>
						</select>
					</div>
				</div> <!-- end col -->

				<div class="col-sm-6">
					<div class="mb-3">
						<label class="form-label">Property Category</label>
						<select class="select">
							<option>Select</option>
							<option>Apartment</option>
							<option>Villa</option>
							<option>Condo</option>
							<option>Residency</option>
						</select>
					</div>
				</div> <!-- end col -->

				<div class="col-sm-6">
					<div class="mb-3">
						<label class="form-label">Currency Type</label>
						<select class="select">
							<option>Select</option>
							<option>Cash</option>
							<option>Bank Transfer</option>
						</select>
					</div>
				</div> <!-- end col -->

				<div class="col-sm-6">
					<div class="mb-3">
						<label class="form-label">Sale Price</label>
						<input type="text" class="form-control">
					</div>
				</div> <!-- end col -->

				<div class="col-sm-6">
					<div class="mb-3">
						<label class="form-label">Offer Price</label>
						<input type="text" class="form-control">
					</div>
				</div> <!-- end col -->

				<div class="col-sm-12">
					<div class="mb-3">
						<label class="form-label">Description of Property</label>
						<textarea class="form-control" rows="3">Description</textarea>
					</div>
				</div> <!-- end col -->
				
				<div class="col-sm-12">
					<div class="mb-3">
						<label class="form-label">Photo</label>
						<div class="file-uploader">
							<input type="file" class="form-control">
							<a href="#" class="input-file">
								<span class="browse-btn">Browse Files</span>
								<span class="browse-text">3 Photos Selected</span>
							</a>
						</div>
					</div>
				</div> <!-- end col -->

				<div class="col-sm-12">
					<div class="d-flex justify-content-end mb-3">
						<a href="javascript:void(0);" class="trash delete-icon"><i class="material-icons-outlined">delete</i></a>
					</div>
				</div>
			</div>
			<!-- end row -->`

        $(".add-floor-list").append(addcontent);

		$('.select').select2({
			minimumResultsForSearch: -1,
			width: '100%'
		});

        return false;		
		
    });

	 $(".add-floor-list").on('click','.trash', function () {
		$(this).closest('.add-count').remove();
		return false;
    });

	// JQuery CounterUp

	if($('.counter-up').length > 0) {
		$('.counter-up').counterUp({
			delay: 15,
			time: 1500
		});
	}

	// Horizontal Slide

	document.addEventListener("DOMContentLoaded", function () {
		const scrollers = document.querySelectorAll(".horizontal-slide");
		scrollers.forEach((scroller) => {
		scroller.setAttribute("data-animated", true);
		const scrollerInner = scroller.querySelector(".slide-list");
		const scrollerContent = Array.from(scrollerInner.children);
		scrollerContent.forEach((item) => {
			const duplicatedItem = item.cloneNode(true);
			duplicatedItem.setAttribute("aria-hidden", true);
			scrollerInner.appendChild(duplicatedItem);
		});
		});
	});

		document.querySelectorAll('input[type="radio"][name="pay-tab"]').forEach(radio => {
    radio.addEventListener('change', function () {
      const target = this.nextElementSibling.getAttribute('data-bs-target');
      const tabs = document.querySelectorAll('.tab-pane');
      tabs.forEach(tab => {
        tab.classList.remove('show', 'active');
      });
      document.querySelector(target).classList.add('show', 'active');
    });
  });

  //   toggle-passwords

	if($('.toggle-passwords').length > 0) {
		$(document).on('click', '.toggle-passwords', function() {
			$(this).toggleClass("fa-eye fa-eye-slash");
			var input = $(".pass-inputs");
			if (input.attr("type") == "password") {
				input.attr("type", "text");
			} else {
				input.attr("type", "password");
			}
		});
	}
	
	if($('.toggle-password').length > 0) {
		$(document).on('click', '.toggle-password', function() {
			$(this).toggleClass("fa-eye fa-eye-slash");
			var input = $(".pass-input");
			if (input.attr("type") == "password") {
				input.attr("type", "text");
			} else {
				input.attr("type", "password");
			}
		});
	}
	// Faq card
	document.addEventListener("DOMContentLoaded", function () {
    // Select all collapsible elements
    document.querySelectorAll(".card-collapse").forEach(function (collapseEl) {
        collapseEl.addEventListener("show.bs.collapse", function () {
            // Add 'active' class to parent .faq-card when expanded
            this.closest(".faq-card").classList.add("active");
        });

        collapseEl.addEventListener("hide.bs.collapse", function () {
            // Remove 'active' class when collapsed
            this.closest(".faq-card").classList.remove("active");
        });
    });
});


	// Slider — thumbnails must init first for asNavFor to work
	$(document).ready(function () {
		// Safety: never let the gallery skeleton stick, even if slick fails to init
		$(window).on('load', function () {
			$('.slider-card.gallery-loading').removeClass('gallery-loading');
		});

		if ($('.slider-nav-thumbnails').length > 0) {
			$('.slider-nav-thumbnails').slick({
				// Active thumbnail stays centered; show as many side thumbs as fit (odd counts keep symmetry)
				slidesToShow: 7,
				slidesToScroll: 1,
				asNavFor: '.service-slider',
				dots: false,
				infinite: true,
				arrows: true,
				centerMode: true,
				centerPadding: '0px',
				focusOnSelect: true,
				responsive: [
				{
					breakpoint: 1200,
					settings: { slidesToShow: 5 }
				},
				{
					breakpoint: 992,
					settings: { slidesToShow: 5 }
				},
				{
					breakpoint: 768,
					settings: { slidesToShow: 3 }
				},
				{
					breakpoint: 576,
					settings: { slidesToShow: 3 }
				},
				{
					breakpoint: 400,
					settings: { slidesToShow: 3 }
				}
				]
			});
		}

		if ($('.service-slider').length > 0) {
			$('.service-slider').slick({
				slidesToShow: 1,
				slidesToScroll: 1,
				arrows: false,
				infinite: true,
				fade: false,
				speed: 450,
				cssEase: 'cubic-bezier(0.4, 0, 0.2, 1)',
				asNavFor: '.slider-nav-thumbnails'
			});
		}

		// Gallery skeleton: slick has initialized → reveal the real slider, drop the skeleton
		$('.slider-card.gallery-loading').removeClass('gallery-loading');

		// Click zones overlaying the left/right edges of the main image
		$(document).on('click', '.service-slider-zone--prev', function (e) {
			e.preventDefault();
			$(this).closest('.service-slider-wrap').find('.service-slider').slick('slickPrev');
		});
		$(document).on('click', '.service-slider-zone--next', function (e) {
			e.preventDefault();
			$(this).closest('.service-slider-wrap').find('.service-slider').slick('slickNext');
		});
	});

	// Gallery Slider
	$('.gallery-slider').each(function () {
		const $slider = $(this);
		if ($slider.hasClass('slick-initialized')) return;

		const count = parseInt($slider.data('slides-count'), 10) || $slider.children().length;
		const cap = (n) => Math.min(n, count);

		$slider.slick({
			dots: false,
			infinite: false,
			speed: 400,
			slidesToShow: cap(5),
			slidesToScroll: 1,
			autoplay: false,
			arrows: true,
			prevArrow: '<button type="button" class="slick-prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>',
			nextArrow: '<button type="button" class="slick-next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>',
			responsive: [
				{ breakpoint: 1300, settings: { slidesToShow: cap(4), slidesToScroll: 1 } },
				{ breakpoint: 992,  settings: { slidesToShow: cap(3), slidesToScroll: 1 } },
				{ breakpoint: 768,  settings: { slidesToShow: cap(2), slidesToScroll: 1 } },
				{ breakpoint: 480,  settings: { slidesToShow: 1,      slidesToScroll: 1 } }
			]
		});
	});

	// Fancybox v5 — click on the main slider image opens a lightbox (modal + thumbnail carousel).
	// Built from the slider's non-clone slides so Slick's infinite clones don't create dupes.
	if (typeof Fancybox !== 'undefined') {
		$(document).on('click', '.service-slider .service-img-wrap img', function () {
			const $slider = $('.service-slider');
			let slides = $slider.find('.slick-slide:not(.slick-cloned) .service-img-wrap img')
				.map(function () {
					return { src: this.getAttribute('src'), type: 'image',
						caption: this.getAttribute('alt') || '' };
				}).get();
			if (!slides.length) {
				slides = [{ src: this.getAttribute('src'), type: 'image' }];
			}
			const index = $slider.hasClass('slick-initialized')
				? ($slider.slick('slickCurrentSlide') || 0) : 0;
			Fancybox.show(slides, {
				startIndex: index,
				Thumbs: { type: 'classic' }
			});
		});
	}

	// timepicker
	if ($('.timepicker').length > 0) {
		$('.timepicker').datetimepicker({
			format: 'HH:mm A',
			icons: {
				up: "fas fa-angle-up",
				down: "fas fa-angle-down",
				next: 'fas fa-angle-right',
				previous: 'fas fa-angle-left'
			}
		});
	}

	// AOS Animation

	if($('.main-wrapper .aos').length > 0) {
	    AOS.init({
		  duration: 1200,
		  once: true,
		});
	}


	// heart fill — handled in footer-scripts

// Custom Select dropdowns (replaces native <select class="filter-select">)
window.initCustomSelects = function () {
	document.querySelectorAll('select.filter-select').forEach(function (select) {
		if (select.closest('.custom-select')) return; // already initialized

		// Wrap in container
		var wrapper = document.createElement('div');
		wrapper.className = 'custom-select';
		select.parentNode.insertBefore(wrapper, select);
		wrapper.appendChild(select);

		// Trigger (shows current selection)
		var trigger = document.createElement('div');
		trigger.className = 'custom-select__trigger';
		trigger.textContent = select.options[select.selectedIndex]
			? select.options[select.selectedIndex].text
			: '';
		wrapper.insertBefore(trigger, select);

		// Options list
		var optionsList = document.createElement('div');
		optionsList.className = 'custom-select__options';
		Array.from(select.options).forEach(function (opt) {
			var item = document.createElement('div');
			item.className = 'custom-select__option';
			if (opt.selected) item.classList.add('selected');
			item.dataset.value = opt.value;
			item.textContent = opt.text;
			item.addEventListener('click', function () {
				select.value = opt.value;
				select.dispatchEvent(new Event('change', { bubbles: true }));
				trigger.textContent = opt.text;
				optionsList.querySelectorAll('.selected').forEach(function (o) {
					o.classList.remove('selected');
				});
				item.classList.add('selected');
				wrapper.classList.remove('open');
				closeOptions();
			});
			optionsList.appendChild(item);
		});
		// Move optionsList to body so it escapes any stacking context (backdrop-filter, overflow)
		document.body.appendChild(optionsList);
		optionsList.style.maxHeight = '0';
		optionsList.style.overflowY = 'hidden';
		optionsList.style.opacity = '0';
		optionsList.style.transition = 'max-height 0.25s ease, opacity 0.15s ease';
		optionsList.style.display = 'block';

		function positionOptions() {
			var rect = trigger.getBoundingClientRect();
			optionsList.style.top = (rect.bottom + 4) + 'px';
			optionsList.style.left = rect.left + 'px';
			optionsList.style.width = rect.width + 'px';
		}

		function openOptions() {
			optionsList.style.position = 'fixed';
			optionsList.style.right = 'auto';
			optionsList.style.zIndex = '99999';
			positionOptions();
			optionsList.style.maxHeight = '240px';
			optionsList.style.overflowY = 'auto';
			optionsList.style.opacity = '1';
		}

		function closeOptions() {
			optionsList.style.maxHeight = '0';
			optionsList.style.overflowY = 'hidden';
			optionsList.style.opacity = '0';
		}

		// Reposition on scroll/resize while open
		window.addEventListener('scroll', function () { if (wrapper.classList.contains('open')) positionOptions(); }, true);
		window.addEventListener('resize', function () { if (wrapper.classList.contains('open')) positionOptions(); });

		// Toggle open/close
		trigger.addEventListener('click', function (e) {
			e.stopPropagation();
			document.querySelectorAll('.custom-select.open').forEach(function (cs) {
				if (cs !== wrapper) {
					cs.classList.remove('open');
					if (cs._optionsList) { cs._optionsList.style.maxHeight = '0'; cs._optionsList.style.opacity = '0'; }
				}
			});
			var isOpen = wrapper.classList.contains('open');
			wrapper.classList.toggle('open');
			if (!isOpen) openOptions(); else closeOptions();
		});
		wrapper._optionsList = optionsList;

		// Hide original select
		select.style.position = 'absolute';
		select.style.opacity = '0';
		select.style.pointerEvents = 'none';
		select.style.height = '0';

		// Sync: if select.value changes externally (reset, session restore)
		select.addEventListener('change', function () {
			var opt = select.options[select.selectedIndex];
			if (opt) {
				trigger.textContent = opt.text;
				optionsList.querySelectorAll('.selected').forEach(function (o) {
					o.classList.remove('selected');
				});
				var match = optionsList.querySelector('[data-value="' + opt.value + '"]');
				if (match) match.classList.add('selected');
			}
		});
	});

	// Close on outside click (register once)
	if (!window._customSelectOutsideClickBound) {
		document.addEventListener('click', function (e) {
			if (!e.target.closest('.custom-select') && !e.target.closest('.custom-select__options')) {
				document.querySelectorAll('.custom-select.open').forEach(function (cs) {
					cs.classList.remove('open');
					if (cs._optionsList) cs._optionsList.style.display = 'none';
				});
			}
		});
		window._customSelectOutsideClickBound = true;
	}
};

})(); // End: Dreams Estate Template Main JS

// intl-tel-input (v17 has no built-in search) — inject a filter box into the country dropdown
document.addEventListener('DOMContentLoaded', function () {
	setTimeout(function () {
		document.querySelectorAll('.iti__country-list').forEach(function (list) {
			if (list.querySelector('.iti__search')) return;
			var iti = list.closest('.iti');
			var inp = iti && iti.querySelector('input');
			var search = document.createElement('input');
			search.type = 'text';
			search.className = 'iti__search';
			search.autocomplete = 'off';
			search.placeholder = (inp && inp.getAttribute('data-search-placeholder')) || 'Search';
			list.prepend(search);
			search.addEventListener('click', function (e) { e.stopPropagation(); });
			search.addEventListener('keydown', function (e) { e.stopPropagation(); });
			search.addEventListener('input', function () {
				var q = this.value.trim().toLowerCase();
				list.querySelectorAll('.iti__country').forEach(function (li) {
					var n = li.querySelector('.iti__country-name');
					li.style.display = (!q || (li.textContent.toLowerCase().indexOf(q) !== -1)) ? '' : 'none';
				});
				list.querySelectorAll('.iti__divider').forEach(function (d) { d.style.display = q ? 'none' : ''; });
			});
			var flag = iti && iti.querySelector('.iti__selected-flag');
			if (flag) flag.addEventListener('click', function () {
				setTimeout(function () { search.value = ''; search.dispatchEvent(new Event('input')); search.focus(); }, 0);
			});
		});
	}, 0);
});
