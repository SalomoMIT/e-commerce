(function () {
    'use strict';

    const LANG = configProductVariants.activeLang;
    const MESSAGES = {
        inStock: configProductVariants.txtInStock,
        outOfStock: configProductVariants.txtOutOfStock,
        addToCart: configProductVariants.btnAddToCart,
        noImage: ''
    };

    // Initial product data and all product images from the global scope
    var productData = configProductVariants.initialProductData_json;
    var allProductImages = configProductVariants.allProductImages_json;

    document.addEventListener('DOMContentLoaded', () => {
        // Use a short timeout to ensure all other DOM elements are ready
        setTimeout(() => {
            // Application state to hold the current selection
            const state = {currentSelection: {}};

            // Helper function to check if an option type is a variant selector
            const isVariantOption = (type) => ['dropdown', 'radio', 'swatch-color', 'swatch-image'].includes(type);

            const elements = {
                variantOptionsContainer: document.getElementById('variant-options-container'),
                extraOptionsContainer: document.getElementById('extra-options-container'),
                gallery: document.getElementById('product-slider'),
                galleryNav: document.getElementById('product-thumb-slider'),
                price: document.getElementById('div-product-price'),
                priceDiscounted: document.getElementById('div-product-discounted-price'),
                priceDiscountRate: document.getElementById('div-product-discount-rate'),
                sku: document.getElementById('product-sku'),
                stockStatus: document.getElementById('span-product-stock-status'),
                addToCartButton: document.getElementById('add-to-cart-button'),
                variantIdInput: document.getElementById('selected-variant-id')
            };

            let lightbox;

            // Renders all product options based on the productData.
            function renderOptions() {
                if (!productData || !productData.options) return;

                productData.options.forEach(option => {
                    if (option.is_enabled == 1) {
                        const optionGroup = document.createElement('div');
                        optionGroup.className = 'mb-4';
                        const container = isVariantOption(option.type) ? elements.variantOptionsContainer : elements.extraOptionsContainer;
                        let valueElementHTML = '';
                        const optionName = option.name_translations[LANG] || option.name_translations['en'];

                        switch (option.type) {
                            case 'swatch-image':
                                valueElementHTML = option.values.map(value => {
                                    const valueName = value.name_translations[LANG] || value.name_translations['en'];
                                    const primaryImage = allProductImages.find(img => img.id === value.primary_swatch_id);
                                    const imageUrl = primaryImage ? primaryImage.url_thumb : '';
                                    return `<div class="option-base option-image-swatch" data-option-id="${option.option_server_id}" data-value-id="${value.value_server_id}" title="${valueName}"><img src="${imageUrl}" alt="${valueName}"></div>`;
                                }).join('');
                                optionGroup.innerHTML = `<div class="title-option">${optionName}:<span class="selected-option-name"></span></div><div class="d-flex flex-wrap" style="gap: 0.75rem;">${valueElementHTML}</div>`;
                                break;
                            case 'swatch-color':
                                valueElementHTML = option.values.map(value => {
                                    const valueName = value.name_translations[LANG] || value.name_translations['en'];
                                    return `<div class="option-base option-swatch rounded-circle" data-option-id="${option.option_server_id}" data-value-id="${value.value_server_id}" title="${valueName}" style="background-color:${value.color || '#ccc'}"></div>`
                                }).join('');
                                optionGroup.innerHTML = `<div class="title-option">${optionName}:<span class="selected-option-name"></span></div><div class="d-flex flex-wrap" style="gap: 0.75rem;">${valueElementHTML}</div>`;
                                break;
                            case 'radio':
                                valueElementHTML = option.values.map(value => {
                                    const valueName = value.name_translations[LANG] || value.name_translations['en'];
                                    return `<label class="option-base option-radio-label" data-option-id="${option.option_server_id}" data-value-id="${value.value_server_id}"><input type="radio" class="option-radio-input" name="option-${option.option_server_id}"> ${valueName}</label>`
                                }).join('');
                                optionGroup.innerHTML = `<div class="title-option">${optionName}:<span class="selected-option-name"></span></div><div class="d-flex flex-wrap" style="gap: 0.75rem;">${valueElementHTML}</div>`;
                                break;
                            case 'dropdown':
                                let optionsHTML = '';
                                optionsHTML += option.values.map(value => {
                                    const valueName = value.name_translations[LANG] || value.name_translations['en'];
                                    return `<option value="${value.value_server_id}">${valueName}</option>`;
                                }).join('');
                                optionGroup.innerHTML = `<div class="form-group"><label for="option-${option.option_server_id}" class="title-option">${optionName}</label><select id="option-${option.option_server_id}" data-option-id="${option.option_server_id}" class="custom-select custom-select-lg option-dropdown">${optionsHTML}</select></div>`;
                                break;
                            case 'checkbox':
                                valueElementHTML = option.values.map(value => {
                                    const valueName = value.name_translations[LANG] || value.name_translations['en'];
                                    return `<div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input validate-group-required" id="option-${value.value_server_id}" name="extra_options[${option.option_server_id}][]" value="${value.value_server_id}"><label class="custom-control-label" for="option-${value.value_server_id}">${valueName}</label></div>`;
                                }).join('');
                                optionGroup.innerHTML = `<div class="title-option">${optionName}</div><div>${valueElementHTML}</div>`;
                                break;
                            case 'text':
                                optionGroup.innerHTML = `<div class="form-group"><label for="option-${option.option_server_id}" class="title-option">${optionName}${option.required ? ' <span class="text-danger">*</span>' : ''}</label><input type="text" id="option-${option.option_server_id}" name="extra_options[${option.option_server_id}]" class="form-control form-input" required placeholder="${optionName}"></div>`;
                                break;
                            case 'number':
                                optionGroup.innerHTML = `<div class="form-group"><label for="option-${option.option_server_id}" class="title-option">${optionName}${option.required ? ' <span class="text-danger">*</span>' : ''}</label><input type="number" id="option-${option.option_server_id}" name="extra_options[${option.option_server_id}]" class="form-control form-input" required placeholder="${optionName}" min="0" max="999999"></div>`;
                                break;
                        }
                        container.appendChild(optionGroup);
                    }
                });
            }

            // Update the product image gallery with a new set of images.
            function updateGallery(imageIds) {
                if (!productSwiper || !productThumbSwiper) {
                    return;
                }

                // Remove all existing slides from both sliders
                productSwiper.removeAllSlides();
                productThumbSwiper.removeAllSlides();

                // Map image IDs to full image objects from your global image list
                const imageObjects = imageIds.map(id => allProductImages.find(img => img.id === id)).filter(img => img);

                if (imageObjects.length > 0) {
                    // Create an array of HTML strings for the new slides
                    const mainSlidesHTML = imageObjects.map(img => `
                    <div class="swiper-slide">
                        <a href="${img.url_full}" class="glightbox">
                            <img alt="Product Image" class="img-product-slider" src="${img.url_main}">
                        </a>
                    </div>`);

                    const navSlidesHTML = imageObjects.map(img => `
                    <div class="swiper-slide">
                        <img src="${img.url_thumb}" alt="Product thumbnail">
                    </div>`);

                    // Append the new slides to Swiper
                    productSwiper.appendSlide(mainSlidesHTML);
                    productThumbSwiper.appendSlide(navSlidesHTML);

                } else {
                    // If no images, add a placeholder slide
                    const placeholderHTML = `
                        <div class="swiper-slide">
                            <div class="product-image-placeholder">
                                <span>${MESSAGES.noImage}</span>
                            </div>
                        </div>`;
                    productSwiper.appendSlide(placeholderHTML);
                }

                // Go to the first slide and update Swiper's internal state
                productSwiper.slideTo(0, 0); // (index, speed)
                productThumbSwiper.slideTo(0, 0);

                productSwiper.update();
                productThumbSwiper.update();

                if (typeof lightbox !== 'undefined' && lightbox.reload) {
                    lightbox.reload();
                }
            }

            // Update the browser URL's query string with the selected variant's SKU.
            function updateUrlWithSku(sku) {
                try {
                    const url = new URL(window.location);
                    if (sku) {
                        url.searchParams.set('sku', sku);
                    } else {
                        url.searchParams.delete('sku');
                    }
                    window.history.replaceState({sku: sku}, '', url);
                } catch (error) {
                }
            }

            // Update the UI elements (price, stock, etc.) based on the selected variant.
            function updateUI(variant, forceGalleryUpdate) {
                if (variant) {
                    let imagesToDisplay = [];

                    // Priority 1: Images directly linked to the selected variant
                    if (variant.image_ids && variant.image_ids.length > 0) {
                        imagesToDisplay = variant.image_ids;
                    } else {
                        // If variant itself doesn't have images, check selected *variant* options
                        const selectedOptionImageIds = [];
                        productData.options.forEach(option => {
                            // Only consider options that are part of variant selection and are currently selected
                            if (isVariantOption(option.type) && state.currentSelection[option.option_server_id]) {
                                const selectedValueId = state.currentSelection[option.option_server_id];
                                const selectedValue = option.values.find(val => val.value_server_id == selectedValueId);
                                if (selectedValue && selectedValue.image_ids && selectedValue.image_ids.length > 0) {
                                    selectedOptionImageIds.push(...selectedValue.image_ids);
                                }
                            }
                        });

                        // Use unique image IDs from selected options
                        if (selectedOptionImageIds.length > 0) {
                            imagesToDisplay = [...new Set(selectedOptionImageIds)]; // Get unique IDs
                        }
                    }

                    if (forceGalleryUpdate && imagesToDisplay.length > 0) {
                        updateGallery(imagesToDisplay);
                    }

                    updateUrlWithSku(variant.sku);
                    if (elements.priceDiscounted) {
                        elements.priceDiscounted.innerHTML = `<span class="final-price">${variant.final_variant_price}</span>`;

                        if (variant.discount_rate > 0) {
                            if (elements.price) {
                                elements.price.innerHTML = `<span class="original-price">${variant.price}</span>`;
                                elements.price.style.display = '';
                            }
                            if (elements.priceDiscountRate) {
                                elements.priceDiscountRate.innerHTML = `<span class="discount-rate">-${variant.discount_rate}%</span>`;
                                elements.priceDiscountRate.style.display = '';
                            }
                            elements.priceDiscounted.classList.add('text-product-discounted');
                        } else {
                            elements.priceDiscounted.classList.remove('text-product-discounted');
                            if (elements.price) elements.price.style.display = 'none';
                            if (elements.priceDiscountRate) elements.priceDiscountRate.style.display = 'none';
                        }
                    }

                    elements.sku.textContent = `${variant.sku}`;
                    if (variant.quantity > 0) {
                        elements.stockStatus.textContent = MESSAGES.inStock;
                        elements.stockStatus.className = 'text-product-discounted';
                        elements.addToCartButton.disabled = false;
                    } else {
                        elements.stockStatus.textContent = MESSAGES.outOfStock;
                        elements.stockStatus.className = 'text-danger';
                        elements.addToCartButton.disabled = true;
                    }
                    elements.variantIdInput.value = variant.id;
                }
            }

            // Check the availability of all option values based on the current selection
            function checkAvailability() {
                // Handle non-dropdown elements (swatches, radios)
                const swatchElements = elements.variantOptionsContainer.querySelectorAll('[data-value-id]:not(select)');
                swatchElements.forEach(element => {
                    const optionId = element.dataset.optionId;
                    const valueId = element.dataset.valueId;

                    const otherSelections = {...state.currentSelection};
                    delete otherSelections[optionId]; // Check availability based on selections in *other* groups
                    const testSelectionValues = Object.values(otherSelections);

                    const isAvailable = productData.variants.some(variant => {
                        const variantKeyValues = variant.stable_key.split('_');
                        const othersMatch = testSelectionValues.every(selId => variantKeyValues.includes(String(selId)));
                        const currentMatches = variantKeyValues.includes(String(valueId));
                        // Check if a variant with this combination exists and has stock.
                        return othersMatch && currentMatches && variant.quantity > 0;
                    });

                    // Toggles a visual 'disabled' class, but doesn't prevent clicking.
                    element.classList.toggle('disabled', !isAvailable);
                });

                // Handle dropdowns separately
                const dropdowns = elements.variantOptionsContainer.querySelectorAll('select.option-dropdown');
                dropdowns.forEach(select => {
                    const optionId = select.dataset.optionId;
                    const selectOptions = select.querySelectorAll('option');

                    selectOptions.forEach(optionElement => {
                        const valueId = optionElement.value;
                        if (!valueId) return; // Skip the placeholder option

                        const otherSelections = {...state.currentSelection};
                        delete otherSelections[optionId];
                        const testSelectionValues = Object.values(otherSelections);

                        const isAvailable = productData.variants.some(variant => {
                            const variantKeyValues = variant.stable_key.split('_');
                            const othersMatch = testSelectionValues.every(selId => variantKeyValues.includes(String(selId)));
                            const currentMatches = variantKeyValues.includes(String(valueId));
                            return othersMatch && currentMatches && variant.quantity > 0;
                        });

                        //optionElement.disabled = !isAvailable;
                    });
                });
            }

            // Handle the user's selection of a variant option.
            function handleVariantSelection(event) {
                const target = event.currentTarget;

                // For dropdowns (`SELECT`), the value comes from `target.value`.
                // For other types, it comes from `dataset.valueId`.
                const optionId = target.dataset.optionId;
                const valueId = target.tagName === 'SELECT' ? target.value : target.dataset.valueId;

                // Prevent action only if a disabled dropdown option is selected.
                if (target.tagName === 'SELECT' && target.options[target.selectedIndex].disabled) {
                    return;
                }

                const optionData = productData.options.find(opt => opt.option_server_id == optionId);
                const optionType = optionData?.type;

                state.currentSelection[optionId] = valueId;

                // Update the label text for swatch/radio options
                if (optionData && ['swatch-color', 'swatch-image', 'radio'].includes(optionType)) {
                    const value = optionData.values.find(val => val.value_server_id == valueId);
                    if (value) {
                        const valueName = value.name_translations[LANG] || value.name_translations['en'];
                        const optionGroupContainer = target.closest('.mb-4');
                        if (optionGroupContainer) {
                            optionGroupContainer.querySelector('.selected-option-name').textContent = ' ' + valueName;
                        }
                    }
                }

                // Update visual 'selected' state for non-dropdowns
                if (target.tagName !== 'SELECT') {
                    elements.variantOptionsContainer.querySelectorAll(`[data-option-id="${optionId}"]`).forEach(el => el.classList.remove('selected'));
                    target.classList.add('selected');
                }

                const quantityInput = document.getElementById('input_product_quantity');

                // Check if all variant options are selected
                const variantOptions = productData.options.filter(opt => isVariantOption(opt.type) && opt.is_enabled == 1);
                if (Object.keys(state.currentSelection).length === variantOptions.length) {
                    const selectedIds = Object.values(state.currentSelection).map(id => String(id));
                    const matchedVariant = productData.variants.find(v => {
                        const variantIds = v.stable_key.split('_');
                        return selectedIds.length === variantIds.length && selectedIds.every(id => variantIds.includes(id));
                    });

                    // If a match is found (even out of stock), update the UI
                    if (matchedVariant) {
                        // Always force gallery update when a full variant match is found.
                        updateUI(matchedVariant, true);

                        // Update the quantity input's max attribute based on stock.
                        if (quantityInput) {
                            const stock = parseInt(matchedVariant.quantity, 10) || 0;

                            // Set the max attribute to the available stock.
                            quantityInput.setAttribute('max', stock);

                            // If the current value in the input is higher than the new max stock,
                            // reset the value to the maximum available stock.
                            if (parseInt(quantityInput.value, 10) > stock) {
                                quantityInput.value = stock > 0 ? stock : 1;
                            }

                            // Disable the input if the variant is out of stock.
                            quantityInput.disabled = stock <= 0;
                        }

                    } else {
                        // This case is unlikely if logic is correct, but as a fallback:
                        elements.addToCartButton.disabled = true;
                        elements.stockStatus.className = 'text-danger';
                        elements.stockStatus.textContent = MESSAGES.outOfStock;
                    }

                } else {
                    // If selection is incomplete, show "required fields" message
                    elements.addToCartButton.disabled = true;
                    elements.stockStatus.className = 'text-danger';
                    elements.stockStatus.textContent = MESSAGES.outOfStock;
                    // Reset SKU and prices
                    elements.sku.textContent = '-';
                    elements.price.textContent = '-';
                    elements.priceDiscounted.textContent = '-';
                    elements.priceDiscountRate.textContent = '';
                }

                // After every selection, re-check the availability of all other options.
                checkAvailability();
            }

            // Select an initial variant on page load, based on URL SKU or first available.
            function selectInitialVariant() {

                let initialVariant = null;
                const urlParams = new URLSearchParams(window.location.search);
                const skuFromUrl = urlParams.get('sku');

                // Try to find variant from URL, even if out of stock
                if (skuFromUrl) {
                    initialVariant = productData.variants.find(v => v.sku === skuFromUrl);
                }

                // If no SKU in URL, or SKU not found, find the default variant.
                if (!initialVariant) {
                    initialVariant = productData.variants.find(v => v.is_default === true || v.is_default === 1);
                }

                // If for some reason no variant is found
                if (!initialVariant && productData.variants && productData.variants.length > 0) {
                    initialVariant = productData.variants[0];
                }

                if (!initialVariant) {
                    return;
                }

                const initialValueIds = initialVariant.stable_key.split('_');
                const variantOptions = productData.options.filter(opt => isVariantOption(opt.type));

                variantOptions.forEach((option) => {
                    const valueIdToSelect = option.values.find(v => initialValueIds.includes(String(v.value_server_id)))?.value_server_id;
                    if (!valueIdToSelect) return;

                    state.currentSelection[option.option_server_id] = String(valueIdToSelect);

                    // Handle selection for dropdowns and other types differently.
                    if (option.type === 'dropdown') {
                        const selectElement = elements.variantOptionsContainer.querySelector(`#option-${option.option_server_id}`);
                        if (selectElement) {
                            selectElement.value = valueIdToSelect;
                        }
                    } else {
                        const elementToSelect = elements.variantOptionsContainer.querySelector(`[data-option-id='${option.option_server_id}'][data-value-id='${valueIdToSelect}']`);
                        if (elementToSelect) {
                            elementToSelect.classList.add('selected');
                            if (['swatch-color', 'swatch-image', 'radio'].includes(option.type)) {
                                const value = option.values.find(val => val.value_server_id == valueIdToSelect);
                                if (value) {
                                    const valueName = value.name_translations[LANG] || value.name_translations['en'];
                                    const optionGroupContainer = elementToSelect.closest('.mb-4');
                                    if (optionGroupContainer) {
                                        optionGroupContainer.querySelector('.selected-option-name').textContent = ' ' + valueName;
                                    }
                                }
                            }
                        }
                    }
                });

                if (initialVariant) {
                    updateUI(initialVariant, false);
                    checkAvailability();
                }

            }

            // Initialize the script by rendering options, attaching event listeners, and selecting an initial variant.
            function initialize() {
                if (!productData || !productData.options) {
                    return;
                }
                renderOptions();

                // Use event delegation for all variant selections for better performance and simplicity.
                elements.variantOptionsContainer.addEventListener('click', (event) => {
                    // Find the closest parent element that is a selectable option but not a select dropdown.
                    const target = event.target.closest('[data-value-id]:not(select)');
                    if (target) {
                        handleVariantSelection({currentTarget: target});
                    }
                });

                elements.variantOptionsContainer.addEventListener('change', (event) => {
                    // Find the target if it's a select dropdown.
                    const target = event.target.closest('select.option-dropdown');
                    if (target) {
                        handleVariantSelection({currentTarget: target});
                    }
                });

                lightbox = GLightbox({
                    selector: '.glightbox',
                    touchNavigation: true,
                    loop: false,
                    zoomable: false,
                    draggable: false
                });

                selectInitialVariant();
                checkAvailability();
            }

            initialize();
        }, 100);
    });
})();
