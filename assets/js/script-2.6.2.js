//hide left side of the menu if there is image
var menuElements = document.getElementsByClassName("mega-menu-content");
for (var i = 0; i < menuElements.length; i++) {
    var el = menuElements[i];
    var imageColumn = el.querySelector(".col-category-images");
    if (imageColumn && imageColumn.innerHTML.trim() === "") {
        el.classList.add("mega-menu-content-no-image");
    }
}

//swiper slider
document.addEventListener('DOMContentLoaded', function () {
    function animateSlide(slide) {
        const elementsToAnimate = slide.querySelectorAll('[data-animation]');
        elementsToAnimate.forEach((el, index) => {
            const animationName = el.getAttribute('data-animation');
            const delay = el.getAttribute('data-delay');

            el.style.animationDelay = delay || `${index * 0.1}s`;
            el.classList.add('animate__animated', animationName);
            el.style.opacity = '1';
        });
    }

    function hideAnimatedElements(slide) {
        const elementsToAnimate = slide.querySelectorAll('[data-animation]');
        elementsToAnimate.forEach(el => {
            const animationName = el.getAttribute('data-animation');
            el.classList.remove('animate__animated', animationName);
            el.style.opacity = '0';
        });
    }

    document.querySelectorAll('.main-slider').forEach(sliderContainer => {
        const nextButton = sliderContainer.querySelector('.swiper-button-next');
        const prevButton = sliderContainer.querySelector('.swiper-button-prev');

        const swiperInstance = new Swiper(sliderContainer, {
            direction: 'horizontal',
            loop: true,
            allowTouchMove: true,
            effect: (MdsConfig.sliderFadeEffect == 1) ? 'fade' : 'slide',
            speed: 500,
            navigation: {
                nextEl: nextButton,
                prevEl: prevButton,
            },
            lazy: {
                loadOnTransitionStart: true,
                loadPrevNext: true,
            },
            preloadImages: false,
            autoplay: {
                delay: 9000,
                disableOnInteraction: false,
            },
            on: {
                init: function () {
                    animateSlide(this.slides[this.activeIndex]);
                },
                slideChangeTransitionStart: function () {
                    this.slides.forEach(slide => hideAnimatedElements(slide));
                },
                slideChangeTransitionEnd: function () {
                    animateSlide(this.slides[this.activeIndex]);
                }
            }
        });

        // Apply the RTL navigation fix ONLY if this slider has dir="rtl" and loop is true
        if (sliderContainer.getAttribute('dir') === 'rtl' && swiperInstance.params.loop) {
            if (nextButton && prevButton) {
                nextButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    swiperInstance.slidePrev();
                }, true);

                prevButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    swiperInstance.slideNext();
                }, true);
            }
        }
    });

    //product carousel
    const swiperCarouselProduct = new Swiper('.swiper-carousel-product', {
        slidesPerView: 2,
        spaceBetween: 15,
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            576: {
                slidesPerView: 3,
                spaceBetween: 15
            },
            768: {
                slidesPerView: 4,
                spaceBetween: 20
            },
            1200: {
                slidesPerView: MdsConfig.indexProductsPerRow == 5 ? 5 : 6,
                spaceBetween: 20
            }
        }
    });

    //brands slider
    const swiperCarouselBrand = new Swiper('.swiper-carousel-brand', {
        slidesPerView: 3,
        loop: true,
        autoplay: {
            delay: 2500
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            576: {
                slidesPerView: 4
            },
            768: {
                slidesPerView: 6
            },
            992: {
                slidesPerView: 8
            }
        }
    });

    //blog slider
    const swiperCarouselBlog = new Swiper('.swiper-carousel-blog', {
        slidesPerView: 1,
        spaceBetween: 0,
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            576: {
                slidesPerView: 2,
                spaceBetween: 15
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 20
            },
            992: {
                slidesPerView: 4,
                spaceBetween: 20
            }
        }
    });

    document.querySelectorAll('.swiper-carousel-product').forEach(function (el) {
        el.classList.add('visible');
    });

});

//product details slider
let productSwiper;
let productThumbSwiper;

document.addEventListener('DOMContentLoaded', function () {
    const sliderWrapper = document.querySelector('.product-slider-wrapper');
    const mainSliderContainer = document.querySelector('.slider-for-container');
    const thumbSliderWrapper = document.querySelector('.thumb-slider-wrapper');
    const mainSlidesCount = document.querySelectorAll('.product-slider .swiper-slide').length;

    function adjustSliderHeight() {
        if (window.innerWidth > 767) {
            const mainSliderHeight = mainSliderContainer.offsetHeight;
            thumbSliderWrapper.style.height = `${mainSliderHeight}px`;
        } else {
            thumbSliderWrapper.style.height = 'auto';
        }
        if (productThumbSwiper && !productThumbSwiper.destroyed) {
            productThumbSwiper.update();
        }
    }

    productThumbSwiper = new Swiper(".product-slider-wrapper .thumb-slider", {
        spaceBetween: 3,
        slidesPerView: 8,
        freeMode: true,
        watchSlidesProgress: true,
        mousewheel: true,
        grabCursor: true,
        breakpoints: {
            768: {
                direction: "vertical",
                slidesPerView: 8,
                spaceBetween: 3,
            }
        }
    });

    productSwiper = new Swiper(".product-slider-wrapper .product-slider", {
        spaceBetween: 10,
        effect: 'slide',
        fadeEffect: {
            crossFade: true
        },
        thumbs: {
            swiper: productThumbSwiper,
        },
        nested: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
            enabled: mainSlidesCount > 1
        },
        allowTouchMove: true,
        breakpoints: {
            768: {
                allowTouchMove: false,
            }
        },
        on: {
            init: function () {
                adjustSliderHeight();
                sliderWrapper.classList.add('loaded');
            },
            resize: function () {
                adjustSliderHeight();
            }
        }
    });

    document.querySelectorAll('.thumb-slider .no-thumb-sync').forEach(function (slide) {
        slide.addEventListener('pointerdown', function (event) {
            event.stopPropagation();
        }, true);
    });

});

//rate product
$(document).on('click', '.rating-stars .label-star', function () {
    $('#user_rating').val($(this).attr('data-star'));
});

//mobile memu
$(document).on('click', '.btn-open-mobile-nav', function () {
    if ($("#navMobile").hasClass('nav-mobile-open')) {
        $("#navMobile").removeClass('nav-mobile-open');
        $('#overlay_bg').hide();
    } else {
        $("#navMobile").addClass('nav-mobile-open');
        $('#overlay_bg').show();
    }
});

$(document).on('click', '#overlay_bg', function () {
    $("#navMobile").removeClass('nav-mobile-open');
    $('#overlay_bg').hide();
});

//close menu
$(document).on('click', '.close-menu-click', function () {
    $("#navMobile").removeClass('nav-mobile-open');
    $('#overlay_bg').hide();
});

//mobile menu
const objMobileNav = {
    id: 0,
    name: '',
    parent_id: 0,
    parent_name: '',
    back_button: 0
};

$(document).on('click', '#navbar_mobile_categories li button', function () {
    objMobileNav.id = $(this).data('id');
    objMobileNav.name = $(this).text().trim() || '';
    objMobileNav.parent_id = $(this).data('parent-id') || 0;
    objMobileNav.back_button = 1;
    renderMobileMenu();
});

$(document).on('click', '#navbar_mobile_back_button button', function () {
    objMobileNav.id = $(this).data('id');
    objMobileNav.name = $(this).data('category-name') || '';
    objMobileNav.parent_id = $(this).data('parent-id') || 0;
    objMobileNav.back_button = objMobileNav.id === 0 ? 0 : 1;
    renderMobileMenu();
});

function renderMobileMenu() {
    const $categoriesContainer = $("#navbar_mobile_categories");
    const $backButtonContainer = $("#navbar_mobile_back_button");
    const $childLinks = $(`.mega-menu li a[data-parent-id="${objMobileNav.id}"]`);

    if (!$childLinks.length) return;

    $categoriesContainer.empty();
    $backButtonContainer.empty();

    if (objMobileNav.back_button) {
        const backName = objMobileNav.parent_id === 0
            ? objMobileNav.name
            : $(`.mega-menu li a[data-id="${objMobileNav.parent_id}"]`).text().trim();

        const backButtonHtml = `
            <button type="button" class="nav-link button-link"
                    data-id="${objMobileNav.parent_id}"
                    data-category-name="${backName}">
                <strong><i class="icon-angle-left"></i> ${objMobileNav.name}</strong>
            </button>`;
        $backButtonContainer.html(backButtonHtml);
    }

    $childLinks.each(function () {
        const $el = $(this);
        const id = $el.data("id");
        const text = $el.text().trim();
        const href = $el.attr("href");
        const hasSub = !!$(`.navbar-nav a[data-parent-id="${id}"]`).length;

        if ($el.data("has-sb") == 1 && hasSub) {
            $categoriesContainer.append(`
                <li class="nav-item">
                    <button type="button" class="nav-link button-link"
                            data-id="${id}"
                            data-parent-id="${objMobileNav.id}">
                        ${text} <i class="icon-arrow-right"></i>
                    </button>
                </li>
            `);
        } else {
            $categoriesContainer.append(`
                <li class="nav-item">
                    <a href="${href}" class="nav-link">${text}</a>
                </li>
            `);
        }
    });

    if (objMobileNav.back_button) {
        const currentLink = $(`.mega-menu li a[data-id="${objMobileNav.id}"]`).attr("href");
        const allLinkHtml = `<li class="nav-item"><a href="${currentLink}" class="nav-link">${MdsConfig.text.viewAll}</a></li>`;
        $categoriesContainer.append(allLinkHtml);
    }

    const $navLinks = $(".nav-mobile-links");
}

//prevent menu overflows
$(document).ready(function () {
    const menu = $('.mega-menu');
    const btnLeft = $('.scroll-btn-left');
    const btnRight = $('.scroll-btn-right');
    const isRtl = MdsConfig.rtl;
    let clientWidth = 0;

    function manageScrollButtons() {
        const scrollLeft = menu.prop('scrollLeft');
        const scrollWidth = menu.prop('scrollWidth');
        clientWidth = menu.prop('clientWidth');
        const tolerance = 2;

        if (scrollWidth <= clientWidth + tolerance) {
            btnLeft.removeClass('visible');
            btnRight.removeClass('visible');
            return;
        }

        const canScrollToStart = scrollLeft > tolerance;
        const canScrollToEnd = scrollLeft < scrollWidth - clientWidth - tolerance;

        btnLeft.toggleClass('visible', canScrollToStart);
        btnRight.toggleClass('visible', canScrollToEnd);
    }

    const physicalRightButton = isRtl ? btnLeft : btnRight;
    const physicalLeftButton = isRtl ? btnRight : btnLeft;

    physicalRightButton.on('click', function () {
        const scrollAmount = clientWidth * 0.75;
        menu.animate({scrollLeft: menu.scrollLeft() + scrollAmount}, 300);
    });

    physicalLeftButton.on('click', function () {
        const scrollAmount = clientWidth * 0.75;
        menu.animate({scrollLeft: menu.scrollLeft() - scrollAmount}, 300);
    });

    menu.on('scroll', function () {
        let scrollTimer;
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(manageScrollButtons, 50);
    });

    let resizeTimer;
    $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(manageScrollButtons, 150);
    });

    setTimeout(function () {
        if (isRtl) {
            menu.scrollLeft(menu.prop('scrollWidth') - menu.prop('clientWidth'));
        }
        manageScrollButtons();
    }, 250);
});

//search
$(document).on('click', '.nav-mobile-header-container .a-search-icon', function () {
    if ($(".mobile-search-form").hasClass("display-block")) {
        $(".mobile-search-form").removeClass("display-block");
        $("#searchIconMobile").removeClass("icon-close");
        $("#searchIconMobile").addClass("icon-search")
    } else {
        $(".mobile-search-form").addClass("display-block");
        $("#searchIconMobile").removeClass("icon-search");
        $("#searchIconMobile").addClass("icon-close")
    }
});

/*mega menu*/
$(".mega-menu .nav-item").hover(function () {
    var menu_id = $(this).attr('data-category-id');
    $("#mega_menu_content_" + menu_id).addClass("show");
    $(".large-menu-item").removeClass('active');
    $(".large-menu-item-first").addClass('active');
    $(".large-menu-content-first").addClass('active');
}, function () {
    var menu_id = $(this).attr('data-category-id');
    $("#mega_menu_content_" + menu_id).removeClass("show");
});

$(".mega-menu .dropdown-menu").hover(function () {
    $(this).show();
}, function () {
});

$(".large-menu-item").hover(function () {
    var menu_id = $(this).attr('data-subcategory-id');
    $(".large-menu-item").removeClass('active');
    $(this).addClass('active');
    $(".large-menu-content").removeClass('active');
    $("#large_menu_content_" + menu_id).addClass('active');
}, function () {
});
$(document).ready(function () {
    $('.row-img-product-list').hover(
        function () {
            var $img = $(this).find('img.img-product');
            var secondImageSrc = $img.data('second');
            $img.stop().fadeTo(50, 0, function () {
                $img.attr('src', secondImageSrc).fadeTo(50, 1);
            });
        },
        function () {
            var $img = $(this).find('img.img-product');
            var firstImageSrc = $img.data('first');
            $img.stop().fadeTo(50, 0, function () {
                $img.attr('src', firstImageSrc).fadeTo(50, 1);
            });
        }
    );
});

//custom scrollbar
$(function () {
    $('.search-categories').overlayScrollbars({});
    $('.custom-scrollbar').overlayScrollbars({});
});

//scrollup
$(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
        $(".scrollup").fadeIn()
    } else {
        $(".scrollup").fadeOut()
    }
});
$(".scrollup").click(function () {
    $("html, body").animate({scrollTop: 0}, 700);
    return false
});

$(document).on('click', '.quantity-select-product .dropdown-menu .dropdown-item', function () {
    $(".quantity-select-product .btn span").text($(this).text());
    $("input[name='product_quantity']").val($(this).text());
});

//show phone number
$(document).on('click', '#show_phone_number', function () {
    $(this).hide();
    $("#phone_number").show();
});

$(document).on('click', '#show_phone_number_profile', function () {
    $(this).hide();
    $("#phone_number_profile").show();
});


$(document).ready(function () {
    $(".select2").select2({
        placeholder: $(this).attr('data-placeholder'),
        height: 42,
        dir: MdsConfig.rtl == true ? "rtl" : "ltr",
        "language": {
            "noResults": function () {
                return MdsConfig.text.noResultsFound;
            }
        },
    });
});

$(document).bind('ready ajaxComplete', function () {
    var startFromLeft = true;
    if (MdsConfig.rtl == true) {
        startFromLeft = false;
    }
    const lightbox = GLightbox({
        selector: '.glightbox-product',
        touchNavigation: true,
        loop: true,
        zoomable: false,
        draggable: true
    });
});

//on click product details reviews text
$(document).on('click', '#btnGoToReviews', function () {
    $('#tab_reviews').tab('show');
    var target = $('#product_description_content');
    $('html, body').animate({
        scrollTop: $(target).offset().top
    }, 400);
});

/*
 * --------------------------------------------------------------------
 * Auth Functions
 * --------------------------------------------------------------------
 */

//login
$(document).ready(function () {
    $("#form_login").submit(function (event) {
        $('#result-login').empty();
        var form = $(this);
        if (form[0].checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            event.preventDefault();
            var inputs = form.find("input, select, button, textarea");
            var serializedData = form.serializeArray();
            serializedData = setSerializedData(serializedData);
            $.ajax({
                type: 'POST',
                url: generateUrl('login-post'),
                data: serializedData,
                success: function (response) {
                    if (response.result == 1) {
                        location.reload();
                    } else if (response.result == 0) {
                        document.getElementById("result-login").innerHTML = response.response;
                    }
                }
            });
        }
        form[0].classList.add('was-validated');
    });
});

//send activation email
function sendActivationEmail(token, type) {
    document.getElementById("confirmation-result-" + type).innerHTML = '';
    var data = {
        'token': token,
        'type': type
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Auth/sendActivationEmailPost'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                setTimeout(function () {
                    document.getElementById("confirmation-result-" + type).innerHTML = response.successMessage;
                }, 500);
            } else {
                location.reload();
            }
        }
    });
}

//delete cover image
function deleteCoverImage() {
    $.ajax({
        type: 'POST',
        url: generateUrl('Profile/deleteCoverImagePost'),
        data: {},
        success: function (response) {
            location.reload();
        }
    });
}

//show image preview
function showImagePreview(input, showAsBackground) {
    var divId = $(input).attr('data-img-id');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            if (showAsBackground) {
                $('#' + divId).css('background-image', 'url(' + e.target.result + ')');
            } else {
                $('#' + divId).attr('src', e.target.result);
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

/*
 * --------------------------------------------------------------------
 * Number Spinner Functions
 * --------------------------------------------------------------------
 */

//number spinner
$(document).on('click', '.product-add-to-cart-container .number-spinner button', function () {
    update_number_spinner($(this));
});

function update_number_spinner(btn) {
    var btn = btn,
        oldValue = btn.closest('.number-spinner').find('input').val().trim(),
        newVal = 0;
    if (btn.attr('data-dir') == 'up') {
        newVal = parseInt(oldValue) + 1;
    } else {
        if (oldValue > 1) {
            newVal = parseInt(oldValue) - 1;
        } else {
            newVal = 1;
        }
    }
    btn.closest('.number-spinner').find('input').val(newVal);
}

$(document).on("input keyup paste change", ".number-spinner input", function () {
    var val = $(this).val();
    val = val.replace(",", "");
    val = val.replace(".", "");
    if (!$.isNumeric(val)) {
        val = 1;
    }
    if (isNaN(val)) {
        val = 1;
    }
    $(this).val(val);
});

$(document).on("input paste change", ".cart-item-quantity .number-spinner input", function () {
    var data = {
        'product_id': $(this).attr('data-product-id'),
        'cart_item_id': $(this).attr('data-cart-item-id'),
        'quantity': $(this).val(),
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('cart/update-quantity'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
});

$(document).on("click", ".cart-item-quantity .btn-spinner-minus", function () {
    update_number_spinner($(this));
    var cart_id = $(this).attr("data-cart-item-id");
    if ($("#q-" + cart_id).val() != 0) {
        $("#q-" + cart_id).change();
    }
});

$(document).on("click", ".cart-item-quantity .btn-spinner-plus", function () {
    update_number_spinner($(this));
    var cart_id = $(this).attr("data-cart-item-id");
    $("#q-" + cart_id).change();
});

function fixProductQuantityValue($input) {
    $input = $($input);
    let min = parseInt($input.attr("min"), 10);
    let max = parseInt($input.attr("max"), 10);
    let val = parseInt($input.val(), 10);

    if (isNaN(val)) return;

    if (val > max) {
        $input.val(max);
    } else if (val < min) {
        $input.val(min);
    }
}

$(document).on("input paste change", "#input_product_quantity", function () {
    fixProductQuantityValue(this);
});

$(document).on("click", ".product-add-to-cart-container button", function () {
    var $input = $("#input_product_quantity");
    fixProductQuantityValue($input);
});

function removeCartDiscountCoupon() {
    $.ajax({
        type: 'POST',
        url: generateUrl('Cart/removeCartDiscountCoupon'),
        data: {},
        success: function (response) {
            location.reload();
        }
    });
}


/*
 * --------------------------------------------------------------------
 * Review Functions
 * --------------------------------------------------------------------
 */

$(function () {
    const reviewModal = $('#reviewModal');
    const modalRatingDisplay = $('#modal-rating-display');
    const modalRatingValueInput = $('#modal-rating-value');
    const modalProductIdInput = $('#modal-product-id');

    const openReviewModal = (productId, rating = 0) => {
        modalProductIdInput.val(productId);
        modalRatingValueInput.val(rating);
        updateStars(modalRatingDisplay, rating);
        $('#reviewText').val('').removeClass('is-invalid');
        modalRatingDisplay.removeClass('is-invalid-stars');
        reviewModal.modal('show');
    };

    const updateStars = (starContainer, rating, isHover = false) => {
        const stars = starContainer.find('i');
        stars.each(function () {
            const star = $(this);
            const starRating = parseInt(star.data('rating'));
            star.removeClass('icon-star icon-star-o selected hover-active');

            if (starRating <= rating) {
                star.addClass('icon-star');
                if (isHover) {
                    star.addClass('hover-active');
                } else {
                    star.addClass('selected');
                }
            } else {
                star.addClass('icon-star-o');
            }
        });
    };

    $('.rating-widget').each(function () {
        const widget = $(this);
        const starsContainer = widget.find('.rating-stars');
        const ratingValueInput = widget.find('.rating-value');

        const initialRating = parseInt(ratingValueInput.val()) || 0;
        updateStars(starsContainer, initialRating);

        starsContainer.on('click', 'i', function () {
            const productId = widget.data('product-id');
            const rating = parseInt($(this).data('rating'));
            ratingValueInput.val(rating);
            updateStars(starsContainer, rating);
            openReviewModal(productId, rating);
        });

        starsContainer.on('mouseover', 'i', function () {
            const hoverRating = parseInt($(this).data('rating'));
            updateStars(starsContainer, hoverRating, true);
        });

        starsContainer.on('mouseleave', function () {
            const currentRating = parseInt(ratingValueInput.val()) || 0;
            updateStars(starsContainer, currentRating);
        });
    });

    $(document).on('click', '.js-open-review-modal', function () {
        const productId = $(this).data('product-id');
        openReviewModal(productId, 0);
    });

    modalRatingDisplay.on('click', 'i', function () {
        const rating = parseInt($(this).data('rating'));
        modalRatingValueInput.val(rating);
        updateStars(modalRatingDisplay, rating);
        modalRatingDisplay.removeClass('is-invalid-stars');
    });

    modalRatingDisplay.on('mouseover', 'i', function () {
        const hoverRating = parseInt($(this).data('rating'));
        updateStars(modalRatingDisplay, hoverRating, true);
    });

    modalRatingDisplay.on('mouseleave', function () {
        const currentRating = parseInt(modalRatingValueInput.val());
        updateStars(modalRatingDisplay, currentRating);
    });

    $('#reviewText').on('input', function () {
        if ($(this).val().trim() !== '') {
            $(this).removeClass('is-invalid');
        }
    });

    $('#submitReviewBtn').on('click', function (e) {
        e.preventDefault();

        const ratingInput = modalRatingValueInput.val();
        const reviewInput = $('#reviewText');
        let isValid = true;

        if (ratingInput <= 0) {
            modalRatingDisplay.addClass('is-invalid-stars');
            isValid = false;
        } else {
            modalRatingDisplay.removeClass('is-invalid-stars');
        }

        if (reviewInput.val().trim() === '') {
            reviewInput.addClass('is-invalid');
            isValid = false;
        } else {
            reviewInput.removeClass('is-invalid');
        }

        if (isValid) {
            const data = {
                'rating': ratingInput,
                'product_id': modalProductIdInput.val(),
                'review': reviewInput.val()
            };

            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/addReviewPost'),
                data: data,
                success: function (response) {
                    location.reload();
                },
                error: function () {
                    console.error("An error occurred while submitting the review.");
                }
            });
        }
    });

    reviewModal.on('hidden.bs.modal', function () {
        updateStars(modalRatingDisplay, 0);
        modalProductIdInput.val('');
        modalRatingValueInput.val(0);
        $('#reviewText').removeClass('is-invalid');
        modalRatingDisplay.removeClass('is-invalid-stars');
    });
});

function deleteReview(id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'id': id
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/deleteReview'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
}

/*
 * --------------------------------------------------------------------
 * Product Comment Functions
 * --------------------------------------------------------------------
 */

//load more reviews & comments
let reviewsLoadOffset = MdsConfig.reviewsLoadLimit;
let commentsLoadOffset = MdsConfig.commentsLoadLimit;
let commentSubFormId = 0;

$("#formAddComment button[type='submit']").on('click', function (event) {
    event.preventDefault();

    var button = $(this);
    var form = $("#formAddComment");

    addProductComment(form, button);
});

//add subcomment
$(document).on('click', '.btn-submit-subcomment', function (event) {
    event.preventDefault();

    if (!MdsConfig.isloggedIn) {
        return false;
    }
    var button = $(this);

    var commentId = $(this).attr("data-comment-id");
    var form = $("#formAddSubcomment" + commentId);

    addProductComment(form, button);
});

//add comment
function addProductComment(form, submitButton) {

    if (submitButton) {
        submitButton.prop('disabled', true);
    }

    const isLoggedIn = MdsConfig.isloggedIn == 1;
    const isTurnstileRequired = MdsConfig.isTurnstileEnabled && !isLoggedIn;
    const turnstileError = document.getElementById('turnstile-error');
    let isValid = true;

    const validateInput = (selector, condition) => {
        const field = form.find(selector);
        if (condition) {
            field.removeClass("is-invalid");
        } else {
            field.addClass("is-invalid");
            isValid = false;
        }
    };

    if (!isLoggedIn) {
        validateInput('input[name="name"]', form.find('input[name="name"]').val().length > 0);
        validateInput('input[name="email"]', form.find('input[name="email"]').val().length > 0);
    }

    validateInput('textarea[name="comment"]', form.find('textarea[name="comment"]').val().length > 0);

    if (isTurnstileRequired) {
        const turnstileToken = form.find('[name="cf-turnstile-response"]').val();
        const turnstileWidget = form.find(".cf-turnstile");
        if (!turnstileToken) {
            turnstileError.style.visibility = 'visible';
            isValid = false;
        } else {
            turnstileError.style.visibility = 'hidden';
        }
    }

    if (!isValid) {
        if (submitButton) {
            submitButton.prop('disabled', false);
        }
        return false;
    }

    let formSerialized = form.serialize();
    formSerialized += '&limit=' + commentsLoadOffset;

    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/addComment'),
        data: formSerialized,
        success: function (response) {
            form[0].reset();

            if (response.status == 1) {
                if (response.type == 'message') {
                    Swal.fire({text: response.message, icon: 'success', confirmButtonText: MdsConfig.text.ok});
                } else {
                    $("#productCommentsListContainer").html(response.htmlContent);
                }
                $('.visible-sub-comment').empty();
                $('.no-comments-found').hide();
            } else {
                Swal.fire({text: response.message || 'An error occurred.', icon: 'error', confirmButtonText: MdsConfig.text.ok});
            }
        },
        error: function () {
            Swal.fire({text: 'Could not connect to the server. Please try again.', icon: 'error', confirmButtonText: MdsConfig.text.ok});
        },
        complete: function () {
            submitButton.prop('disabled', false);

            // Reset the Turnstile widget
            if (isTurnstileRequired) {
                const turnstileWidgetElement = form.find(".cf-turnstile")[0];
                if (turnstileWidgetElement && typeof turnstile !== 'undefined') {
                    turnstile.reset(turnstileWidgetElement);
                }
            }
        }
    });
}

//show comment box
function showCommentForm(commentId) {
    if (commentSubFormId == commentId) {
        $('#subCommentForm' + commentId).empty();
        commentSubFormId = 0;
    } else {
        $('.visible-sub-comment').empty();
        var data = {
            'comment_id': commentId,
        };
        $.ajax({
            type: 'POST',
            url: generateUrl('Ajax/loadSubCommentForm'),
            data: data,
            success: function (response) {
                if (response.status == 1) {
                    commentSubFormId = commentId;
                    $('#subCommentForm' + commentId).append(response.htmlContent);
                }
            }
        });
    }
}

//load more reviews
$(document).on('click', '#btnLoadMoreProductReviews', function () {
    var button = $(this);
    var total = Number($(this).attr('data-total'));
    reviewsLoadOffset = Number(reviewsLoadOffset);
    var data = {
        'product_id': $(this).attr('data-product'),
        'offset': reviewsLoadOffset
    };
    button.find('svg').toggleClass('rotate');
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/loadMoreReviews'),
        data: data,
        success: function (response) {
            if (response.status == 1) {
                setTimeout(function () {
                    button.find('svg').toggleClass('rotate');
                    $('#productReviewsListContainer').append(response.htmlContent);
                    reviewsLoadOffset += Number(MdsConfig.reviewsLoadLimit);

                    if (reviewsLoadOffset >= total) {
                        button.hide();
                    }

                }, 300);
            }
        }
    });
});

//load more comments
$(document).on('click', '#btnLoadMoreProductComments', function () {
    var button = $(this);
    var total = Number($(this).attr('data-total'));
    commentsLoadOffset = Number(commentsLoadOffset);
    var data = {
        'product_id': $(this).attr('data-product'),
        'offset': commentsLoadOffset
    };
    button.find('svg').toggleClass('rotate');
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/loadMoreComments'),
        data: data,
        success: function (response) {
            if (response.status == 1) {
                setTimeout(function () {
                    button.find('svg').toggleClass('rotate');
                    $('#productCommentsListContainer').append(response.htmlContent);
                    commentsLoadOffset += Number(MdsConfig.commentsLoadLimit);

                    if (commentsLoadOffset >= total) {
                        button.hide();
                    }

                }, 300);
            }
        }
    });
});

//delete comment
function deleteComment(commentId, type, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'id': commentId
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/deleteComment'),
                data: data,
                success: function (response) {
                    $('#li-' + type + '-' + commentId).remove();
                }
            });
        }
    });
}

//create affiliate link
$(document).on('click', '#btnCreateAffiliateLink', function () {
    var data = {
        'product_id': $(this).attr('data-id'),
        'lang_id': MdsConfig.sysLangId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/createAffiliateLink'),
        data: data,
        success: function (response) {
            if (response.status == 1) {
                $('#spanAffLink').text(response.response);
                $('#affliateLinkModal').modal('show');
            }
        }
    });
});

//copy affiliate link
$(document).on('click', '#btnCopyAffLink', function () {
    var link = $('#spanAffLink').text();
    navigator.clipboard.writeText(link);
    $('#btnCopyAffLink').text(MdsConfig.text.copied);
    setTimeout(function () {
        $('#btnCopyAffLink').text(MdsConfig.text.copyLink);
    }, 2000);
});

//validate email
function isEmail(email) {
    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (!regex.test(email)) {
        return false;
    } else {
        return true;
    }
}

//get string lenght
function strLenght(str) {
    if (str == '' || str == null) {
        return 0;
    }
    str = str.trim();
    return str.length;
}

/*
 * --------------------------------------------------------------------
 * Blog Comments Functions
 * --------------------------------------------------------------------
 */

$(document).ready(function () {
    //add comment
    $("#form_add_blog_comment").on('click', 'button[type="submit"]', function (event) {
        event.preventDefault();

        const submitButton = $(this);
        const form = submitButton.closest('form');

        submitButton.prop('disabled', true);

        const isLoggedIn = form.find("#comment_name").length === 0;
        const isTurnstileRequired = MdsConfig.isTurnstileEnabled && !isLoggedIn;
        const turnstileError = document.getElementById('turnstile-error');
        let isValid = true;

        const validateInput = (selector, condition) => {
            const field = form.find(selector);
            if (condition) {
                field.removeClass("is-invalid");
            } else {
                field.addClass("is-invalid");
                isValid = false;
            }
        };

        // Validate fields for guests
        if (!isLoggedIn) {
            validateInput('#comment_name', form.find('#comment_name').val().length > 0);
            validateInput('#comment_email', form.find('#comment_email').val().length > 0);
        }
        // Validate comment for everyone
        validateInput('#comment_text', form.find('#comment_text').val().length > 0);

        // Validate Turnstile for guests
        if (isTurnstileRequired) {
            const turnstileToken = form.find('[name="cf-turnstile-response"]').val();
            if (!turnstileToken) {
                turnstileError.style.visibility = 'visible';
                isValid = false;
            } else {
                turnstileError.style.visibility = 'hidden';
            }
        }

        if (!isValid) {
            submitButton.prop('disabled', false);
            return;
        }

        let formSerialized = form.serializeArray();
        formSerialized.push({name: 'limit', value: parseInt($("#blog_comment_limit").val()) || 0});
        formSerialized = setSerializedData(formSerialized);

        $.ajax({
            type: 'POST',
            url: generateUrl('Ajax/addBlogComment'),
            data: formSerialized,
            success: function (response) {
                form[0].reset();
                if (response.type == 'message') {
                    Swal.fire({text: response.message, icon: 'success', confirmButtonText: MdsConfig.text.ok});
                } else {
                    $("#comment-result").html(response.htmlContent);
                }
            },
            error: function () {
                Swal.fire({text: 'An error occurred while submitting your comment.', icon: 'error', confirmButtonText: MdsConfig.text.ok});
            },
            complete: function () {
                submitButton.prop('disabled', false);

                // Reset the Turnstile widget
                if (isTurnstileRequired) {
                    const widgetContainer = form.find('.cf-turnstile');
                    if (widgetContainer.length > 0 && typeof turnstile !== 'undefined') {
                        turnstile.reset(widgetContainer[0]);
                    }
                }
            }
        });
    });
});

//load more blog comment
function loadMoreBlogComments(postId) {
    var limit = parseInt($("#blog_comment_limit").val());
    var data = {
        'post_id': postId,
        'limit': limit
    };
    $("#load_comment_spinner").show();
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/loadMoreBlogComments'),
        data: data,
        success: function (response) {
            if (response.type == 'comments') {
                setTimeout(function () {
                    $("#load_comment_spinner").hide();
                    document.getElementById("comment-result").innerHTML = response.htmlContent;
                }, 500);
            }
        }
    });
}

//delete blog comment
function deleteBlogComment(commentId, postId, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var limit = parseInt($("#blog_comment_limit").val());
            var data = {
                'comment_id': commentId,
                'post_id': postId,
                'limit': limit
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/deleteBlogComment'),
                data: data,
                success: function (response) {
                    if (response.type == 'comments') {
                        document.getElementById("comment-result").innerHTML = response.htmlContent;
                    }
                }
            });
        }
    });
}

/*
 * --------------------------------------------------------------------
 * Chat Functions
 * --------------------------------------------------------------------
 */

//send message
$("#formSendChatMessage").submit(function (event) {
    event.preventDefault();
    var inputSubject = $("#formSendChatMessage input[name=subject]");
    var inputMessage = $("#formSendChatMessage textarea[name=message]");
    if (inputSubject.val().length < 1) {
        inputSubject.addClass("is-invalid");
        return false;
    } else {
        inputSubject.removeClass("is-invalid");
    }
    if (inputMessage.val().length < 1) {
        inputMessage.addClass("is-invalid");
        return false;
    } else {
        inputMessage.removeClass("is-invalid");
    }
    $("#formSendChatMessage .form-group :input").prop("disabled", true);
    var data = {
        'subject': inputSubject.val(),
        'message': inputMessage.val(),
        'receiver_id': $("#formSendChatMessage input[name=receiver_id]").val(),
        'product_id': $("#formSendChatMessage input[name=product_id]").val()
    }
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/addChatPost'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                document.getElementById("chatSendMessageResult").innerHTML = response.htmlContent;
                inputMessage.val('');
            }
            $("#formSendChatMessage .form-group :input").prop("disabled", false);
        }
    });
});

let lastKnownStateId = 0;

//load chat
$(document).on('click', '.chat .chat-contact', function () {
    $('.chat-contact').removeClass('active');
    $(this).addClass('active');
    let chatId = $(this).attr('data-chat-id');
    var data = {
        'chat_id': chatId,
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/loadChatPost'),
        data: data,
        success: function (response) {
            if (response.status === 1) {
                $('#inputChatId').val(chatId);
                $('#inputChatReceiverId').val(response.receiverId);
                $('#inputChatMessage').val('');
                document.getElementById("chatContactsContainer").innerHTML = response.htmlContacts;
                document.getElementById("chatUserContainer").innerHTML = response.htmlchatUser;
                document.getElementById("chatMessagesContainer").innerHTML = response.htmlContentMessages;
                document.getElementById("chatInputContainer").innerHTML = response.htmlChatForm;
                $('#mdsChat').addClass('chat-mobile-open');
                $('#mdsChat').removeClass('chat-empty');
                $("#messagesContainer" + chatId).scrollTop($("#messagesContainer" + chatId)[0].scrollHeight);
                $('#chatBadge' + chatId).hide();
            }
        }
    });
});

//update chat
const mdsChat = document.getElementById('mdsChat');
if (mdsChat) {
    setInterval(function () {
        const chatId = $('#inputChatId').val() ?? null;
        const lastMessageId = $('#messagesContainer' + chatId + ' .message[data-message-id]:last').data('message-id') || 0;
        var data = {
            'chatId': chatId,
            'lastChatMessageId': lastMessageId,
            'lastKnownStateId': lastKnownStateId,
        };
        $.ajax({
            type: 'POST',
            url: generateUrl('service/chat/sync'),
            data: data,
            success: function (response) {
                if (response.status === 1) {
                    lastKnownStateId = response.currentStateId;
                    document.getElementById("chatContactsContainer").innerHTML = response.htmlContacts;

                    if (chatId) {
                        appendNewChatMessages(response.arrayMessages, response.chatId);
                        $("#messagesContainer" + chatId).scrollTop($("#messagesContainer" + chatId)[0].scrollHeight);
                    }

                    searchChatContacts();
                }
            }
        });
    }, MdsConfig.chatUpdateTime * 1000);
}

//send message
$(document).on('click', '#btnChatSubmit', function () {
    sendChatMessage();
});

function sendChatMessage() {
    var chatId = $("#formChat input[name=chat_id]").val();
    var receiverId = $("#formChat input[name=receiver_id]").val();
    var message = $("#formChat input[name=message]").val();
    if (message.trim().length < 1) {
        return false;
    }
    $("#inputChatMessage").prop('disabled', true);
    $("#formChat button").prop('disabled', true);

    const lastMessageId = $('#messagesContainer' + chatId + ' .message[data-message-id]:last').data('message-id') || 0;

    var data = {
        'chatId': chatId,
        'receiver_id': receiverId,
        'message': message,
        'lastChatMessageId': lastMessageId,
    }
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/sendMessagePost'),
        data: data,
        success: function (response) {
            if (response.status === 1) {
                appendNewChatMessages(response.arrayMessages, response.chatId);
                $("#messagesContainer" + response.chatId).scrollTop($("#messagesContainer" + response.chatId)[0].scrollHeight);
                $("#inputChatMessage").prop('disabled', false);
                $("#formChat button").prop('disabled', false);
                $("#inputChatMessage").val('');
            }
        }
    });
}

//append new messages
function appendNewChatMessages(messages, chatId) {
    if (!messages || messages.length === 0) return;

    const containerId = `messagesContainer${chatId}`;
    const $container = $(`#${containerId}`);

    if ($container.length === 0) return;

    for (const msg of messages) {
        const messageId = `chatMessage${msg.id}`;
        if (document.getElementById(messageId)) continue;

        const isRight = msg.isRight === true;
        const userHtml = `
            <div class="flex-item item-user">
                <div class="user-img">
                    <img src="${msg.avatar}" alt="" class="img-profile">
                </div>
            </div>`;

        const messageTextHtml = `
            <div class="flex-item">
                <div class="message-text">${msg.message}</div>
                <div class="time"><span>${msg.time}</span></div>
            </div>`;

        const htmlContent = `
            <div id="${messageId}" data-message-id="${msg.id}" class="message${isRight ? ' message-right' : ''}">
                ${isRight ? messageTextHtml + userHtml : userHtml + messageTextHtml}
            </div>`;

        $container.append(htmlContent);
    }
}

//search product filters
$(document).on('change keyup paste', '#chatSearchContacts', function () {
    searchChatContacts();
});

function searchChatContacts() {
    var input = $('#chatSearchContacts').val().toLowerCase();
    var listItems = $('.chat-contacts .chat-contact');
    listItems.each(function (idx, item) {
        var username = $(this).find('.username').text().toLowerCase();
        var subject = $(this).find('.subject').text().toLowerCase();
        if (username.indexOf(input) > -1 || subject.indexOf(input) > -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

$(document).on('click', '#btnOpenChatContacts', function () {
    $('#mdsChat').removeClass('chat-mobile-open');
});

//delete chat
function deleteChat(chatId, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'chat_id': chatId,
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Ajax/deleteChatPost'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
}


/*
 * --------------------------------------------------------------------
 * Cart Functions
 * --------------------------------------------------------------------
 */

$("#form-add-to-cart").submit(function (event) {
    var form = $(this);
    var hasCheckboxGroupError = false;
    var groupedCheckboxes = {};

    form.find('input.validate-group-required[type="checkbox"]').each(function () {
        var name = $(this).attr('name');
        if (!groupedCheckboxes[name]) {
            groupedCheckboxes[name] = [];
        }
        groupedCheckboxes[name].push(this);
    });

    $.each(groupedCheckboxes, function (name, group) {
        var isAnyChecked = group.some(cb => cb.checked);
        if (!isAnyChecked) {
            hasCheckboxGroupError = true;
            $(group).addClass('is-invalid');
        } else {
            $(group).removeClass('is-invalid');
        }
    });

    if (form[0].checkValidity() === false || hasCheckboxGroupError) {
        event.preventDefault();
        event.stopPropagation();
    } else {
        event.preventDefault();
        $('#form-add-to-cart .btn-product-cart').prop('disabled', true);
        $('#form-add-to-cart .btn-product-cart .btn-cart-icon').html('<span class="spinner-border spinner-border-add-cart"></span>');

        var serializedData = form.serializeArray();
        serializedData = setSerializedData(serializedData);
        $.ajax({
            type: 'POST',
            url: generateUrl('cart/add-to-cart'),
            data: serializedData,
            success: function (response) {
                if (response.result == 1) {
                    setTimeout(function () {
                        document.getElementById("contentModalCartProduct").innerHTML = response.htmlCartProduct;
                        $('#form-add-to-cart .btn-product-cart').html('<i class="icon-check"></i>' + MdsConfig.text.addedToCart);
                        $('.span_cart_product_count').html(response.productCount);
                        $('.span_cart_product_count').removeClass('visibility-hidden').addClass('visibility-visible');
                        $('#modalAddToCart').modal('show');
                    }, 400);
                    setTimeout(function () {
                        $('#form-add-to-cart .btn-product-cart').html('<span class="btn-cart-icon"><i class="icon-cart-solid"></i></span>' + MdsConfig.text.addToCart);
                        $('#form-add-to-cart .btn-product-cart').prop('disabled', false);
                    }, 1000);
                } else {
                    $('#form-add-to-cart .btn-product-cart').html('<span class="btn-cart-icon"><i class="icon-cart-solid"></i></span>' + MdsConfig.text.addToCart);
                    $('#form-add-to-cart .btn-product-cart').prop('disabled', false);
                }
            }
        });
    }
    form[0].classList.add('was-validated');
});


$(document).on('click', '.btn-item-add-to-cart', function () {
    var productId = $(this).attr("data-product-id");
    var buttonId = $(this).attr("data-id");
    document.getElementById("btn_add_cart_" + buttonId).innerHTML = '<div class="spinner-border spinner-border-add-cart-list"></div>';
    var data = {
        'product_id': productId,
        'is_ajax': true
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('cart/add-to-cart'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                setTimeout(function () {
                    document.getElementById("contentModalCartProduct").innerHTML = response.htmlCartProduct;
                    $('#btn_add_cart_' + buttonId).css('background-color', 'rgb(40, 167, 69, .7)');
                    document.getElementById("btn_add_cart_" + buttonId).innerHTML =
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">\n' +
                        '<path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>\n' +
                        '</svg>';
                    $('.span_cart_product_count').html(response.productCount);
                    $('.span_cart_product_count').removeClass('visibility-hidden');
                    $('.span_cart_product_count').addClass('visibility-visible');
                    $('#modalAddToCart').modal('show');
                }, 400);
                setTimeout(function () {
                    $('#btn_add_cart_' + buttonId).css('background-color', 'rgba(255, 255, 255, .7)');
                    document.getElementById("btn_add_cart_" + buttonId).innerHTML = '<i class="icon-cart"></i>';
                }, 2000);
            }
        }
    });
});

//remove from cart
function removeFromCart(cartItemId) {
    var data = {
        'cart_item_id': cartItemId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Cart/removeFromCart'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
};

$(document).ready(function () {
    $('#use_same_address_for_billing').change(function () {
        if ($(this).is(":checked")) {
            $('.cart-form-billing-address').hide();
            $('.cart-form-billing-address select').removeClass('select2-req');
        } else {
            $('.cart-form-billing-address').show();
            $('.cart-form-billing-address select').addClass('select2-req');
        }
    });
});

//approve order product
function approveOrderProduct(id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'order_product_id': id,
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Order/approveOrderProductPost'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//cancel order
function cancelOrder(id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'order_id': id
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Order/cancelOrderPost'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//delete affiliate link
function deleteAffiliateLink(id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'link_id': id
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Profile/deleteAffiliateLinkPost'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//on location modal open
$('#locationModal').on('shown.bs.modal', function () {
    var countryId = $(this).data('country-id') || '';
    var data = {
        'country_id': countryId
    };

    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/loadActiveCountries'),
        data: data,
        success: function (response) {
            if (response.status && response.htmlContent) {
                document.getElementById("select_countries_filter").innerHTML = response.htmlContent;
            }
        }
    });
});

//location modal
$(document).on("click", ".btn-modal-location-header", function () {
    $('#locationModal').removeClass('location-modal-estimated-delivery');
    $('#locationModal').find('input[name="form_type"]').val("filter");
});

$(document).on("click", ".btn-modal-location-product", function () {
    $('#locationModal').addClass('location-modal-estimated-delivery');
    $('#locationModal').find('input[name="form_type"]').val("set_user_location");
});

//get shipping methods by location
function getShippingFeeByDistrict(districtId) {
    $('#cart_shipping_methods_container').hide();
    $('.cart-shipping-loader').show();
    var data = {
        'districtId': districtId,
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Cart/getShippingFeeByDistrict'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                document.getElementById("cart_shipping_methods_container").innerHTML = response.htmlContent;
                setTimeout(function () {
                    $('#cart_shipping_methods_container').show();
                    $('.cart-shipping-loader').hide();
                }, 400);
            }
        },
        error:()=>{
            $('#cart_shipping_methods_container').show();
        }
    });
};

$(document).on("click", "#btnShowCartShippingError", function () {
    $("#cartShippingError").show();
    setTimeout(function () {
        $("#cartShippingError").hide();
    }, 5000);
});

function validateFileInput(id) {
    var val = $('#' + id).val();
    if (!val) {
        $('#' + id + '_flash_error').show();
        setTimeout(function () {
            $('#' + id + '_flash_error').hide();
        }, 5000);
    }
}

/*
 * --------------------------------------------------------------------
 * Abuse Reports
 * --------------------------------------------------------------------
 */

//report product
$("#form_report_product").submit(function (event) {
    event.preventDefault();
    reportAbuse("form_report_product", "product");
});

//report seller
$("#form_report_seller").submit(function (event) {
    event.preventDefault();
    reportAbuse("form_report_seller", "seller");
});

//report review
$("#form_report_review").submit(function (event) {
    event.preventDefault();
    reportAbuse("form_report_review", "review");
});

//report comment
$("#form_report_comment").submit(function (event) {
    event.preventDefault();
    reportAbuse("form_report_comment", "comment");
});

function reportAbuse(form_id, item_type) {
    var formSerialized = $("#" + form_id).serializeArray();
    formSerialized.push({name: "item_type", value: item_type});
    formSerialized = setSerializedData(formSerialized);
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/reportAbusePost'),
        data: formSerialized,
        success: function (response) {
            if (response.message != '') {
                document.getElementById("response_" + form_id).innerHTML = response.message;
                $("#" + form_id)[0].reset();
            }
        }
    });
}

if ($(".profile-cover-image")[0]) {
    document.addEventListener('lazybeforeunveil', function (e) {
        var bg = e.target.getAttribute('data-bg-cover');
        if (bg) {
            e.target.style.backgroundImage = 'url(' + bg + ')';
        }
    });
}

/*
 * --------------------------------------------------------------------
 * Other Functions
 * --------------------------------------------------------------------
 */

//AJAX search
let searchTimeout;
$(document).on("input", ".ajax-search-input", function () {
    const inputValue = $(this).val();
    const device = $(this).data('device');

    clearTimeout(searchTimeout);

    const contentId = device === 'mobile' ? 'response_search_results_mobile' : 'response_search_results';

    if (inputValue.length < 3) {
        $('#' + contentId).hide();
        return;
    }

    searchTimeout = setTimeout(function () {
        search(inputValue, device, contentId);
    }, 200);
});

function search(inputValue, device, contentId) {
    const data = {
        'input_value': inputValue,
        'lang_base_url': MdsConfig.langBaseUrl
    };

    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/ajaxSearch'),
        data: data,
        success: function (response) {
            if (response.status && response.htmlContent) {
                document.getElementById(contentId).innerHTML = response.htmlContent;
                $('#' + contentId).show();
            } else {
                document.getElementById(contentId).innerHTML = '';
                $('#' + contentId).hide();
            }
        },
        error: function () {
            $('#' + contentId).hide();
        }
    });
}

//search in filter options
$(document).on("change keyup paste", ".filter-search-input", function () {
    var filter_id = $(this).attr('data-filter-id');
    var input = $(this).val().toLowerCase();
    var list_items = $("#" + filter_id + " li");
    list_items.each(function (idx, li) {
        var text = $(this).find('label').text().toLowerCase();
        if (text.indexOf(input) > -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

//load products
$(document).ready(function () {
    let prLoadRunning = false;
    let prLoadHasMore = $('#productListContent').attr('data-has-more');
    let prLoadUserId = $('#productListContent').attr('data-user-id');
    let prLoadCouponId = $('#productListContent').attr('data-coupon-id');

    function loadProducts() {
        if (prLoadRunning == true) {
            return false;
        }
        if (prLoadHasMore == false) {
            return false;
        }
        const urlParams = new URLSearchParams(window.location.search);
        let params = {};
        for (const [key, value] of urlParams.entries()) {
            params[key] = value;
        }
        if (params['page'] === undefined) {
            params['page'] = 1;
        } else {
            if (isNaN(params['page']) || params['page'] <= 0) {
                return false;
            }
        }
        params['page'] = Number(params['page']) + 1;
        prLoadRunning = true;
        $('#loadProductsSpinner').show();
        var data = {
            'category_id': $('#productListContent').attr('data-category'),
            'user_id': prLoadUserId,
            'coupon_id': prLoadCouponId,
            'params': params,
            'sysLangId': MdsConfig.sysLangId
        };
        $.ajax({
            type: 'GET',
            url: generateUrl('Ajax/loadProducts'),
            data: data,
            success: function (response) {
                setTimeout(function () {
                    if (response.result == 1) {
                        $('#productListResultContainer').append(response.htmlContent);
                        updatePageNumberInUrl(response.pageNumber);
                    }
                    prLoadHasMore = response.hasMore;
                    prLoadRunning = false;
                    $('#loadProductsSpinner').hide();
                }, 150);
            },
            error: function () {
                prLoadRunning = false;
                $('#loadProductsSpinner').hide();
            }
        });
    }

    if ($('#productListContent').length && prLoadHasMore == 1) {
        const prLoadMoreTrigger = document.querySelector('#footer');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadProducts();
                }
            });
        }, {
            rootMargin: '0px',
            threshold: 0.1
        });
        observer.observe(prLoadMoreTrigger);
    }
});

//show previous products
$(document).on("click", "#btnShowPreviousProducts", function () {
    const currentUrl = window.location.href;
    const currentUrlObj = new URL(window.location.href);
    let previousURL = '';

    const params = new URLSearchParams(currentUrlObj.search);
    let currentPage = parseInt(params.get('page')) || 1;
    const previousPage = currentPage > 1 ? currentPage - 1 : 1;

    previousURL = removeQueryParam(currentUrl, 'page');
    previousURL = addQueryParam(previousURL, 'page', previousPage);
    window.location.href = previousURL;
});

$(document).on("click", ".dropdownSortOptions button", function () {
    var pageUrl = window.location.href;
    var action = $(this).attr('data-action');
    if (action == "most_recent" || action == "lowest_price" || action == "highest_price" || action == "highest_rating") {
        pageUrl = removeQueryParam(pageUrl, 'sort');
        pageUrl = removeQueryParam(pageUrl, 'page');
        pageUrl = addQueryParam(pageUrl, 'sort', action);
        if ($('#productListProfile').length) {
            if (!pageUrl.includes('#products')) {
                pageUrl = pageUrl + '#products';
            }
        }
        window.location.href = pageUrl;
    }
});

/*
 * --------------------------------------------------------------------
 * Product Filters
 * --------------------------------------------------------------------
 */

$(function () {
    const debounce = (func, delay) => {
        let timeout;
        return function (...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    };

    const filterLoadingStates = {};

    const loadFilterOptionsViaAjax = function ($listElement, searchTerm = '', isSearchAction = false) {
        const listId = $listElement.attr('id');
        if (!listId || filterLoadingStates[listId]) {
            return;
        }

        if (isSearchAction) {
            $listElement.data('all-loaded', false);
            $listElement.data('offset', 0);
        }

        if ($listElement.data('all-loaded') === true && !isSearchAction) {
            return;
        }

        filterLoadingStates[listId] = true;

        var requestData = {
            filter_type: $listElement.data('filter-type'),
            offset: $listElement.data('offset') || 0,
            limit: $listElement.data('limit') || 20,
            search_term: searchTerm,
            filter_id: $listElement.data('filter-id'),
            category_id: $listElement.data('category-id'),
            current_url: window.location.href
        };

        $.ajax({
            url: generateUrl('Ajax/loadFilterOptions'),
            type: 'POST',
            dataType: 'json',
            data: requestData
        }).done(function (response) {
            if (isSearchAction) {
                $listElement.empty();
            }

            if (response && response.status && Array.isArray(response.options)) {
                if (response.options.length > 0) {
                    var itemsHtml = response.options.map(function (option) {
                        var checkedAttr = option.isChecked ? 'checked' : '';
                        return `<li data-key="${option.filter_key}" data-value="${option.option_key}">
                                    <div class="custom-control custom-checkbox custom-checkbox-sm">
                                        <input type="checkbox" class="custom-control-input" ${checkedAttr} readonly>
                                        <label class="custom-control-label">${option.name}</label>
                                    </div>
                                </li>`;
                    }).join('');
                    $listElement.append(itemsHtml);
                }

                if (response.hasMore === false) {
                    $listElement.data('all-loaded', true);
                }

                var newOffset = (requestData.offset || 0) + response.options.length;
                $listElement.data('offset', newOffset);

            } else {
                $listElement.data('all-loaded', true);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $listElement.data('all-loaded', true);
        }).always(function () {
            filterLoadingStates[listId] = false;
        });
    };

    /**
     * Sets up AJAX-based infinite scroll and server-side search for a given list.
     */
    const setupAjaxHandlers = function ($list, $searchInput) {
        // AJAX Scroll Handler
        $list.on('scroll', function () {
            const threshold = 40;
            if (this.scrollHeight - this.scrollTop - $list.innerHeight() < threshold) {
                const searchTerm = $list.data('current-search') || '';
                loadFilterOptionsViaAjax($list, searchTerm);
            }
        });

        // AJAX Search Handler
        $searchInput.on('input', debounce(function () {
            const searchTerm = $(this).val().trim().toLowerCase();
            $list.data('current-search', searchTerm);
            loadFilterOptionsViaAjax($list, searchTerm, true);
        }, 350));
    };

    /**
     * Sets up fast, client-side-only search for a given list.
     */
    const setupClientSideSearch = function ($list, $searchInput) {
        $searchInput.on('input', debounce(function () {
            const searchTerm = $(this).val().trim().toLowerCase();
            $list.find('li').each(function () {
                const $li = $(this);
                const labelText = $li.find('.custom-control-label').text().toLowerCase();
                if (labelText.includes(searchTerm)) {
                    $li.show();
                } else {
                    $li.hide();
                }
            });
        }, 200));
    };

    $('.filter-options-list').each(function () {
        const $list = $(this);
        const listId = $list.attr('id');
        if (!listId) {
            return;
        }

        const $searchInput = $('.filter-search-input[data-target-list="#' + listId + '"]');
        if (!$searchInput.length) {
            return;
        }

        const hasMoreOptions = $list.data('has-more');

        if (hasMoreOptions === true) {
            setupAjaxHandlers($list, $searchInput);
        } else {
            setupClientSideSearch($list, $searchInput);
        }
    });
});

$(function () {
    $('.product-filters-container .filter-list').on('click', '.custom-control', function (e) {
        const $li = $(this).closest('li');
        const filterKey = $li.data('key');
        const filterValue = $li.data('value').toString();

        const url = new URL(window.location.href);
        const params = url.searchParams;

        const existingValues = params.get(filterKey) ? params.get(filterKey).split(',') : [];
        const valueIndex = existingValues.indexOf(filterValue);

        if (valueIndex > -1) {
            existingValues.splice(valueIndex, 1);
        } else {
            existingValues.push(filterValue);
        }

        if (existingValues.length > 0) {
            params.set(filterKey, existingValues.join(','));
        } else {
            params.delete(filterKey);
        }

        window.location.href = url.toString();
    });
});

//remove active product filter
$(document).on('click', '.btn-remove-active-product-filter', function (e) {
    e.preventDefault();

    const $button = $(this);
    const filterKey = $button.data('key');
    const filterValue = $button.data('value');

    if (!filterKey || filterValue === undefined) {
        return;
    }

    const url = new URL(window.location.href);
    const params = url.searchParams;
    const valueStr = filterValue.toString();

    if (!params.has(filterKey)) {
        return;
    }

    const existingValues = params.get(filterKey).split(',');
    const valueIndex = existingValues.indexOf(valueStr);
    if (valueIndex > -1) {
        existingValues.splice(valueIndex, 1);
    }

    if (existingValues.length > 0 && existingValues.join('') !== '') {
        params.set(filterKey, existingValues.join(','));
    } else {
        params.delete(filterKey);
    }

    window.location.href = url.toString();
});

$(document).on("click", "#btnFilterByKeyword", function () {
    var currentPageUrl = window.location.href;
    var pageUrl = currentPageUrl;
    var keyword = $('#input_filter_keyword').val().trim();
    // Check if the input is invalid
    let isPriceValid = true;
    $('#price_min').removeClass('is-invalid');
    $('#price_max').removeClass('is-invalid');
    if (!$('#price_min')[0].validity.valid) {
        isPriceValid = false;
        $('#price_min').addClass('is-invalid');
    }
    if (!$('#price_max')[0].validity.valid) {
        isPriceValid = false;
        $('#price_max').addClass('is-invalid');
    }
    var priceMin = parseFloat($('#price_min').val());
    var priceMax = parseFloat($('#price_max').val());
    if (priceMin >= priceMax) {
        isPriceValid = false;
        $('#price_min').addClass('is-invalid');
        $('#price_max').addClass('is-invalid');
    }
    if (isPriceValid == false) {
        return false;
    }
    pageUrl = removeQueryParam(pageUrl, 'p_min');
    pageUrl = removeQueryParam(pageUrl, 'p_max');
    pageUrl = removeQueryParam(pageUrl, 'search');
    if (priceMin !== '' && priceMin > 0) {
        pageUrl = addQueryParam(pageUrl, 'p_min', priceMin);
    }
    if (priceMax !== '' && priceMax > 0) {
        pageUrl = addQueryParam(pageUrl, 'p_max', priceMax);
    }
    if (keyword !== '') {
        keyword = removeUnsafeCharacters(keyword);
        pageUrl = addQueryParam(pageUrl, 'search', keyword);
    }
    if ($('#productListProfile').length) {
        if (!pageUrl.includes('#products')) {
            pageUrl = pageUrl + '#products';
        }
    }
    if (pageUrl != currentPageUrl) {
        pageUrl = removeQueryParam(pageUrl, 'page');
    }
    window.location.href = pageUrl;
});

//add query param to url
function addQueryParam(url, param, value) {
    const urlObj = new URL(url);
    const searchParams = new URLSearchParams(urlObj.search);
    searchParams.set(param, value);
    urlObj.search = searchParams.toString();
    return urlObj.toString();
}

//remove param from a url
function removeQueryParam(url, paramToRemove) {
    const urlObj = new URL(url);
    const searchParams = new URLSearchParams(urlObj.search);
    searchParams.delete(paramToRemove);
    urlObj.search = searchParams.toString();
    return urlObj.toString();
}

//update page number in url
function updatePageNumberInUrl(pageNumber) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', pageNumber);
    window.history.replaceState({}, '', url);
}

function removeUnsafeCharacters(input) {
    return input.replace(/[&<>"'/#%?=@+,:;*[\]{}|\\^~`()[\]$!]/g, '');
}

//load more products
var pagePromotedProducts = 1;

function loadMorePromotedProducts() {
    $("#load_promoted_spinner").show();
    pagePromotedProducts++;
    var data = {
        'page': pagePromotedProducts
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/loadMorePromotedProducts'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                setTimeout(function () {
                    $("#row_promoted_products").append(response.htmlContent);
                    $("#load_promoted_spinner").hide();
                    if (response.hasMore == false) {
                        $(".promoted-load-more-container").hide();
                    }
                }, 200);
            } else {
                setTimeout(function () {
                    $("#load_promoted_spinner").hide();
                    if (response.hasMore == false) {
                        $(".promoted-load-more-container").hide();
                    }
                }, 200);
            }
        }
    });
}

// var addressSelected={
//   provinceId: "",
//   provinceName: "",
//   cityId: "",
//   cityName: "",
//   districtId: "",
//   districtName: ""
// }
// $('.select2').on('select2:opening', function(e){
//     var id = $(this).attr('id');
//     if (id && id.startsWith('select_cities_')) {
//         var addressPrefix= id.replace("select_cities_","")
//         getCities($("#select_provinces_"+addressPrefix).val(), addressPrefix)
//     }
// });
$('.select2').on('select2:opening', function (e) {
    const id = this.id;
    console.log(id,this.value);

    if (id && id.startsWith('select_provinces_')) {
        console.log("opening", 'select_provinces_');
        getProv()
    }
});
function getProv(idSuffix = '') {
    $.ajax({
        type: 'POST',
        url: generateUrl('savecourier'),
        data: {
            csrf_token: $('input[name="csrf_token"]').val(),
        },
        success: function (response) {
            console.log(response);
        }
    });
}
function getCities(val, idSuffix = '') {
    if (idSuffix != '') {
        idSuffix = '_' + idSuffix;
    }
    $.ajax({
        type: 'POST',
        url: generateUrl('getcities/'+val),
        data: {
            csrf_token: $('input[name="csrf_token"]').val()
        },
        success: function (response) {
            const select = document.getElementById("select_cities"+idSuffix);
            const provSelect = document.getElementById("select_provinces"+idSuffix);
            select.innerHTML = '';

            if (response.meta.code === 200 && response.data.length > 0) {
                select.innerHTML += `<option value="">Pilih Kota / Kabupaten</option>`;

                response.data.forEach(item => {
                    select.innerHTML += `
                        <option value="${item.id}">
                            ${item.name}
                        </option>
                    `;
                });
                $('#select_provinces'+idSuffix).find('input[name="province_id"]').val(provSelect.value);
                $('#select_provinces'+idSuffix).find('input[name="province_name"]').val(provSelect.options[provSelect.selectedIndex].text);
            }
        },
        error: function () {
            alert("Gagal memuat data kota")
        }
    });
}
function getDistrict(val, idSuffix = '') {
    if (idSuffix != '') {
        idSuffix = '_' + idSuffix;
    }
    $.ajax({
        type: 'POST',
        url: generateUrl('getdistrict/'+val),
        data: {
            csrf_token: $('input[name="csrf_token"]').val()
        },
        success: function (response) {
            const select = document.getElementById("select_district" + idSuffix);
            const citySelect = document.getElementById("select_cities" + idSuffix);
            const $citySelect = $("#select_cities" + idSuffix); // Versi jQuery untuk ambil hidden input

            // 1. Update Dropdown District (Gunakan jQuery agar UI Select2 sinkron)
            var $districtSelect = $("#select_district" + idSuffix);
            $districtSelect.empty();

            if (response.meta.code === 200 && response.data.length > 0) {
                $districtSelect.append('<option value="">Pilih District</option>');
                response.data.forEach(item => {
                    $districtSelect.append(new Option(item.name, item.id));
                });
                
                // Render ulang UI Select2 District
                $districtSelect.trigger('change');

                // --- UPDATE HIDDEN INPUT DISINI ---
                
                // Ambil value ID Kota
                var selectedCityId = $citySelect.val();
                
                // Ambil text Nama Kota
                var selectedCityName = $citySelect.find('option:selected').text();

                // Update input hidden city_id
                $citySelect.siblings('input[name="city_id"]').val(selectedCityId);
                
                // Update input hidden city_name
                $citySelect.siblings('input[name="city_name"]').val(selectedCityName);

                // Debugging di console
                console.log("Hidden City ID Updated:", $citySelect.siblings('input[name="city_id"]').val());
                console.log("Hidden City Name Updated:", selectedCityName); 
            }
        },
        error: function () {
            alert("Gagal memuat data distrik")
        }
    });
}
$('#btnSaveCourier').on('click', function () {
    let data = $('#courier_select').select2('data');

    if (!data.length) {
        alert('Pilih kurir dulu');
        return;
    }

    // ubah jadi array object
    let couriers = data.map(item => ({
        code: item.id,
        name: item.text.replace(/[^a-zA-Z0-9 &]/g, '')
        .replace(/\s+/g, ' ')
        .trim()
    }));

    let jsonCourier = JSON.stringify(couriers);
    $.ajax({
        type: 'POST',
        url: generateUrl('savecourier'),
        data: {
            csrf_token: $('input[name="csrf_token"]').val(),
            couriers: jsonCourier
        },
        success: function (response) {
            console.log(response);
        }
    });
});
function setAddressPick(val, idSuffix = '') {
    // Gunakan jQuery selector agar lebih mudah memanipulasi input hidden
    const $selectDistrict = $("#select_district_" + idSuffix);
    const selectElement = document.getElementById("select_district_" + idSuffix);

    // Pastikan value tidak kosong (user benar-benar memilih sesuatu)
    if (val) {
        // 1. Ambil Nama District dari option yang sedang terpilih
        // Kita gunakan find('option:selected') agar lebih akurat dengan Select2
        const selectedName = $selectDistrict.find('option:selected').text();

        // 2. Update value ke input hidden district_id
        $selectDistrict.siblings('input[name="district_id"]').val(val);

        // 3. Update value ke input hidden district_name
        $selectDistrict.siblings('input[name="district_name"]').val(selectedName);

        // Debugging untuk memastikan di Console
        console.log("District Updated:", {
            id: val,
            name: selectedName,
            suffix: idSuffix
        });
    } else {
        // Jika user memilih opsi kosong, kosongkan juga hidden input-nya
        $selectDistrict.siblings('input[name="district_id"]').val('');
        $selectDistrict.siblings('input[name="district_name"]').val('');
    }
}
$(document).on('click', '.btn-add-remove-wishlist', function () {
    var productId = $(this).attr("data-product-id");
    var dataType = $(this).attr("data-type");
    if (dataType == 'list') {
        if ($(this).find("i").hasClass("icon-heart-o")) {
            $(this).find("i").removeClass("icon-heart-o");
            $(this).find("i").addClass("icon-heart");
        } else {
            $(this).find("i").removeClass("icon-heart");
            $(this).find("i").addClass("icon-heart-o");
        }
    }
    if (dataType == 'details') {
        if ($(this).find("i").hasClass("icon-heart-o")) {
            $('.product-add-to-cart-container .btn-add-remove-wishlist').html('<i class="icon-heart"></i><span>' + MdsConfig.text.removeFromWishlist + '</span>');
        } else {
            $('.product-add-to-cart-container .btn-add-remove-wishlist').html('<i class="icon-heart-o"></i><span>' + MdsConfig.text.addToWishlist + '</span>');
        }
    }
    var data = {
        'product_id': productId,
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/addRemoveWishlist'),
        data: data,
        success: function (response) {
        }
    });
});

$("#form_validate").submit(function () {
    $('.custom-control-validate-input').removeClass('custom-control-validate-error');
    setTimeout(function () {
        $('.custom-control-validate-input .error').each(function () {
            var name = $(this).attr('name');
            if ($(this).is(":visible")) {
                name = name.replace('[]', '');
                $('.label_validate_' + name).addClass('custom-control-validate-error');
            }
        });
    }, 100);
});

$('.custom-control-validate-input input').click(function () {
    var name = $(this).attr('name');
    name = name.replace('[]', '');
    $('.label_validate_' + name).removeClass('custom-control-validate-error');
});

//hide cookies warning
function hideCookiesWarning() {
    $(".cookies-warning").hide();
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/hideCookiesWarning'),
        data: {},
        success: function (response) {
        }
    });
}

$(document).ready(function () {
    if ($(".validate-form").length > 0) {
        $('.validate-form').each(function (i, obj) {
            var id = $(this).attr('id');
            $("#" + id).validate();
        });
    }
});

//validate select2
$(".validate-form").submit(function () {
    $('.select2-req').each(function (i, obj) {
        var id = $(this).attr('id');
        var val = $(this).val();
        if (val == "" || val == null || val == undefined) {
            $('.select2-selection[aria-controls="select2-' + id + '-container"]').addClass('error');
        } else {
            $('.select2-selection[aria-controls="select2-' + id + '-container"]').removeClass('error');
        }
    });
});

$(document).on('change', '.select2-req', function () {
    var id = $(this).attr('id');
    if ($('.select2-selection[aria-controls="select2-' + id + '-container"]').hasClass("error")) {
        $('.select2-selection[aria-controls="select2-' + id + '-container"]').removeClass('error');
    }
});

function checkStateSelected(id) {
    var val = $('#' + id).val();
    if (!val) {
        $("[aria-controls='select2-" + id + "-container']").addClass('error');
    } else {
        $("[aria-controls='select2-" + id + "-container']").removeClass('error');
    }
}

$('#input_vendor_files').on('change', function (e) {
    $('#container_vendor_files').empty();
    $('#label_vendor_files').html("");
    var files = $(this).prop('files');
    for (var i = 0; i < files.length; i++) {
        var item = "<span class='badge badge-secondary-light font-600 m-t-10 font-size-13'><svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='#888888' viewBox='0 0 16 16'>\n" +
            "<path d='M4 0h5.293A1 1 0 0 1 10 .293L13.707 4a1 1 0 0 1 .293.707V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2m5.5 1.5v2a1 1 0 0 0 1 1h2z'/>\n" +
            "</svg>&nbsp;" + files[i].name + "</span><br>";
        $('#container_vendor_files').append(item);
    }
});

$("#form_validate").validate();
$("#form-add-to-cart").validate();
$("#form_request_quote").validate();
$("#form_validate_checkout").validate();

$(document).on('click', '.custom-control-variation input', function () {
    var name = $(this).attr('name');
    $('.custom-control-variation label').each(function () {
        if ($(this).attr('data-input-name') == name) {
            $(this).removeClass('is-invalid');
        }
    });
});

$(document).ready(function () {
    $('.validate_terms').submit(function (e) {
        $('.custom-control-validate-input p').remove();
        if (!$('.custom-control-validate-input input').is(":checked")) {
            e.preventDefault();
            $('.custom-control-validate-input').addClass('custom-control-validate-error');
            $('.custom-control-validate-input').append("<p class='text-danger'>" + MdsConfig.text.acceptTerms + "</p>");
        } else {
            $('.custom-control-validate-input').removeClass('custom-control-validate-error');
        }
    });
});

$(document).on("input keyup paste change", ".validate_price .price-input", function () {
    var val = $(this).val();
    val = val.replace(',', '.');
    if ($.isNumeric(val) && val != 0) {
        $(this).removeClass('is-invalid');
    } else {
        $(this).addClass('is-invalid');
    }
});

$(document).ready(function () {
    $('.validate_price').submit(function (e) {
        $('.validate_price .validate-price-input').each(function () {
            var val = $(this).val();
            if (val != '') {
                val = val.replace(',', '.');
                if ($.isNumeric(val) && val != 0) {
                    $(this).removeClass('is-invalid');
                } else {
                    e.preventDefault();
                    $(this).addClass('is-invalid');
                    $(this).focus();
                }
            }
        });
    });
});

$(document).on("input keyup paste change", ".price-input", function () {
    var val = $(this).val();
    var subst = '';
    var regex = /[^\d.]|\.(?=.*\.)/g;
    val = val.replace(regex, subst);
    $(this).val(val);
});

//full screen
$(document).ready(function () {
    $("iframe").attr("allowfullscreen", "")
});

//delete quote request
function deleteQuoteRequest(id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'id': id,
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Order/deleteQuoteRequest'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
}

function getProductShippingCost(val, productId) {
    $("#product_shipping_cost_container").empty();
    $(".product-shipping-loader").show();
    var data = {
        'state_id': val,
        'product_id': productId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/getProductShippingCost'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                setTimeout(function () {
                    document.getElementById("product_shipping_cost_container").innerHTML = response.response;
                    $(".product-shipping-loader").hide();
                }, 300);
            }
        }
    });
}

function deleteShippingAddress(id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'id': id
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Profile/deleteShippingAddressPost'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
}

//delete attachment
function deleteSupportAttachment(id) {
    var data = {
        'id': id,
        'ticket_type': 'client'
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Support/deleteSupportAttachmentPost'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                document.getElementById("response_uploaded_files").innerHTML = response.response;
            }
        }
    });
}

//close support ticket
function closeSupportTicket(id) {
    var data = {
        'id': id,
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Support/closeTicketPost'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
}

$(document).ready(function () {
    $('#form_newsletter_footer, #form_newsletter_modal').submit(function (event) {
        event.preventDefault();
        var $form = $(this);
        var serializedData = $form.serializeArray();
        serializedData = setSerializedData(serializedData);
        $.ajax({
            type: 'POST',
            url: generateUrl('Ajax/addToNewsletter'),
            data: serializedData,
            success: function (response) {
                if (response.result == 1) {
                    Swal.fire({text: response.message, icon: response.isSuccess == 1 ? 'success' : 'warning', confirmButtonText: MdsConfig.text.ok});
                    $form.find('input').val('');
                }
            }
        });
    });
});


$(document).on("change", ".input-show-selected", function () {
    var id = $(this).attr("data-id");
    var val = $(this).val();
    $("#" + id).html(val.replace(/.*[\/\\]/, ''));
});

if ($('.fb-comments').length > 0) {
    $(".fb-comments").attr("data-href", window.location.href);
}

if ($('.post-text-responsive').length > 0) {
    $(function () {
        $('.post-text-responsive iframe').wrap('<div class="embed-responsive embed-responsive-16by9"></div>');
        $('.post-text-responsive iframe').addClass('embed-responsive-item');
    });
}

//load product shop location map
function loadProductShopLocationMap() {
    var address = $("#span_shop_location_address").text();
    address = encodeURIComponent(address);
    var mapLang = 'en';
    if (MdsConfig.langShort) {
        mapLang = MdsConfig.langShort;
    }
    document.getElementById("iframe_shop_location_address").setAttribute("src", "https://maps.google.com/maps?width=100%&height=600&hl=" + mapLang + "&q=" + address + "&ie=UTF8&t=&z=8&iwloc=B&output=embed&disableDefaultUI=true");
}

//player modal preview
$('#productVideoModal').on('hidden.bs.modal', function (e) {
    $(this).find('video')[0].pause();
});

$('#productAudioModal').on('hidden.bs.modal', function (e) {
    Amplitude.pause();
});

//shops page filters
document.addEventListener('DOMContentLoaded', function () {
    const affiliateSelect = document.getElementById('affiliate-select');
    if (affiliateSelect) {
        affiliateSelect.addEventListener('change', function () {
            document.getElementById('shops-filter-form').submit();
        });
    }
});

//payment completed circle
$(document).ready(function () {
    $('.circle-loader').toggleClass('load-complete');
    $('.checkmark').toggle();
});

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

// Make the entire option list card clickable, scoped to the container
$(document).on('click', '.cart-options-list .option-card', function () {
    var $radio = $(this).find('.option-radio');
    if (!$radio.is(':checked')) {
        $radio.prop('checked', true).trigger('change');
    }
});

$(document).on('change', '.cart-options-list .option-radio', function () {
    var groupName = $(this).attr('name');
    $('.cart-options-list input[type="radio"][name="' + groupName + '"]').each(function () {
        $(this).closest('.option-card').removeClass('is-selected');
    });

    if ($(this).is(':checked')) {
        $(this).closest('.option-card').addClass('is-selected');
    }
});

$(document).on('click', '.cart-options-list .option-card .custom-control', function () {
    e.stopPropagation();
});


//set initial scroll for breadcrumb
$(function () {
    if ($(window).width() < 992) {
        const breadcrumbContainer = $('.nav-breadcrumb .breadcrumb');
        if (breadcrumbContainer.length) {
            breadcrumbContainer.scrollLeft(breadcrumbContainer[0].scrollWidth);
        }
    }
});