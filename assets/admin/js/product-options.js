$(document).ready(function () {
    let isInitialLoadComplete = false;

    function setOptionsChanged() {
        if (!isInitialLoadComplete) {
            return;
        }
        const optionsChangedInput = $('#inputIsOptionsUpdated');
        if (optionsChangedInput.length > 0 && optionsChangedInput.val() !== '1') {
            optionsChangedInput.val('1');
        }
    }

    let optionIdCounter = 0;
    let currentManagingOptionValueEntry = null;
    let tempOptionImages = [];
    let previousVariantData = {};
    let currentAdminLanguage = configOptions.currentAdminLanguage ?? 'en';

    const PLACEHOLDER_SWATCH_IMG = configOptions.placeholderSwatchImg;
    let currentTranslationTargetElement = null;

    Coloris({
        el: '.coloris-input',
        themeMode: 'light',
        alpha: true,
        format: 'hex',
        swatches: [
            '#264653', '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51',
            '#d62828', '#023e8a', '#0077b6', '#0096c7', '#00b4d8',
            '#48cae4', '#90e0ef', '#ade8f4', '#caf0f8'
        ]
    });

    const optionsContainerEl = document.getElementById('options-container');
    new Sortable(optionsContainerEl, {
        animation: 150,
        handle: '.option-drag-handle',
        ghostClass: 'sortable-ghost',
        onEnd: () => {
            setOptionsChanged();
            updateVariants(false)
        }
    });

    function initializeOptionValueSortable(containerElement) {
        new Sortable(containerElement, {
            animation: 150,
            handle: '.value-drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: () => {
                setOptionsChanged();
                updateVariants(false)
            }
        });
    }

    $('#input_sku').on('input', () => updateVariants(true));

    function toggleValueFieldsBasedOnOptionType(optionGroupEl, optionType) {
        const $optionGroup = $(optionGroupEl);
        const $valuesContainer = $optionGroup.find('.option-values-container');
        const $addValueBtn = $optionGroup.find('.add-option-value-btn');
        if (optionType === 'text' || optionType === 'number') {
            $valuesContainer.hide().empty();
            $addValueBtn.hide();
        } else {
            $valuesContainer.show();
            $addValueBtn.show();
        }
    }

    function addOptionGroup(optionData = null, isButtonClick = false) {
        optionIdCounter++;
        const clientOptionId = `option_${optionIdCounter}`;

        const defaultOptionType = optionData?.type || 'dropdown';
        const initialTranslations = optionData?.name_translations || {
            [currentAdminLanguage]: ''
        };
        const isEnabled = optionData ? optionData.is_enabled : true;
        const serverId = optionData?.option_server_id || '';
        // ADDED: Read the option_key from the data sent by the backend.
        const optionKey = optionData?.option_key || '';
        const collapseId = `optionCollapse-${clientOptionId}`;
        var isInitiallyExpanded = false;

        if (isButtonClick) {
            isInitiallyExpanded = true;
            setOptionsChanged();
        }

        const optionHtml = `
            <div class="option-group" id="${clientOptionId}"
                 data-option-client-id="${clientOptionId}"
                 ${serverId ? `data-option-server-id="${serverId}"` : ''}
                 ${optionKey ? `data-option-key="${optionKey}"` : ''}
                 data-option-type="${defaultOptionType}"
                 data-translations='${JSON.stringify(initialTranslations)}'
                 data-enabled="${isEnabled}">
                <div class="option-header">
                  <div class="flex-item">
                    <span class="drag-handle option-drag-handle" title="${configOptions.dragReorderOptionTitle}"><i class="fa fa-grip-vertical"></i></span>
                     <button type="button" class="btn btn-sm btn-default collapse-option-btn"
                             data-toggle="collapse" data-target="#${collapseId}"
                             aria-expanded="${isInitiallyExpanded}" aria-controls="${collapseId}"
                             title="${isInitiallyExpanded ? configOptions.collapseOptionTooltip : configOptions.expandOptionTooltip}">
                         <i class="fas ${isInitiallyExpanded ? 'fa-chevron-up' : 'fa-chevron-down'}"></i> </button>
                    </div>
                    <div class="form-group">
                        <label for="option-name-${clientOptionId}">${configOptions.optionName}</label>
                        <div class="input-group">
                            <input type="text" class="form-control option-name" id="option-name-${clientOptionId}"
                                   placeholder="${configOptions.optionNamePlaceholder}" value="${initialTranslations[currentAdminLanguage] || ''}">
                            <span class="input-group-btn">
                                <button class="btn btn-default btn-translate" type="button">
                                    <i class="fas fa-globe"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="option-type-${clientOptionId}">${configOptions.optionTypeLabel}</label>
                        <select class="form-control custom-select option-type-selector" id="option-type-${clientOptionId}">
                          <optgroup label="${configOptions.optGroupNameCreateVariants}">
                            <option value="dropdown" ${defaultOptionType === 'dropdown' ? 'selected' : ''}>${configOptions.optTypeNameDropdown}</option>
                            <option value="radio" ${defaultOptionType === 'radio' ? 'selected' : ''}>${configOptions.optTypeNameRadioButtons}</option>
                            <option value="swatch-color" ${defaultOptionType === 'swatch-color' ? 'selected' : ''}>${configOptions.optTypeNameSwatchColor}</option>
                            <option value="swatch-image" ${defaultOptionType === 'swatch-image' ? 'selected' : ''}>${configOptions.optTypeNameSwatchImage}</option>
                          </optgroup>
                          <optgroup label="${configOptions.optGroupNameCreateExtraOptions}">
                            <option value="checkbox" ${defaultOptionType === 'checkbox' ? 'selected' : ''}>${configOptions.optTypeNameCheckbox}</option>
                            <option value="text" ${defaultOptionType === 'text' ? 'selected' : ''}>${configOptions.optTypeNameTextInput}</option>
                            <option value="number" ${defaultOptionType === 'number' ? 'selected' : ''}>${configOptions.optTypeNameNumberInput}</option>
                          </optgroup>
                        </select>
                    </div>
                   <div class="flex-item">
                        <div class="form-check form-switch option-enable-switch-container">
                            <input class="form-check-input option-enable-switch" type="checkbox" id="option-enable-${clientOptionId}" ${isEnabled ? 'checked' : ''}>
                            <label class="form-check-label" for="option-enable-${clientOptionId}"></label>
                        </div>
                        <button type="button" class="btn btn-sm btn-default remove-option-btn"><i class="fas fa-trash-alt"></i></button>
                   </div>
                </div>
                <div class="collapse collapsible-option-content ${isInitiallyExpanded ? 'in' : ''}" id="${collapseId}">
                    <div class="option-values-container"></div>
                    <button type="button" class="btn btn-sm btn-default add-option-value-btn"> <i class="fas fa-plus"></i> ${configOptions.addValueButton}</button>
                </div>
            </div>`;
        const $newOptionGroup = $(optionHtml);
        $('#options-container').append($newOptionGroup);

        initializeOptionValueSortable($newOptionGroup.find('.option-values-container')[0]);
        toggleValueFieldsBasedOnOptionType($newOptionGroup, defaultOptionType);

        $newOptionGroup.find('.option-enable-switch').trigger('change');

        const $collapseTarget = $newOptionGroup.find(`#${collapseId}`);
        const $collapseButton = $newOptionGroup.find('.collapse-option-btn');
        const $collapseButtonIcon = $collapseButton.find('i');

        $collapseTarget.on('show.bs.collapse', function () {
            $collapseButtonIcon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $collapseButton.attr('title', configOptions.collapseOptionTooltip);
        });
        $collapseTarget.on('hide.bs.collapse', function () {
            $collapseButtonIcon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $collapseButton.attr('title', configOptions.expandOptionTooltip);
        });

        if (optionData && optionData.values && Array.isArray(optionData.values)) {
            optionData.values.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
            optionData.values.forEach(valueData => {
                addOptionValue($newOptionGroup, valueData);
            });
        }

        if (!optionData) {
            updateVariants(true);
        }
        return $newOptionGroup;
    }

    $('#add-option-btn').on('click', function () {
        addOptionGroup(null, true);
    });

    function addOptionValue($optionGroup, valueData = null) {
        if (!valueData) {
            setOptionsChanged();
        }

        const optionType = $optionGroup.attr('data-option-type');
        const $valuesContainer = $optionGroup.find('.option-values-container');
        const optionClientId = $optionGroup.attr('data-option-client-id');
        const valueClientIdSuffix = Date.now() + Math.random().toString(36).substring(2, 7);
        const valueClientId = `val-${optionClientId}-${valueClientIdSuffix}`;

        const initialTranslations = valueData?.name_translations || {
            [currentAdminLanguage]: ''
        };
        const color = valueData?.color || '#ffffff';
        const serverId = valueData?.value_server_id || '';
        const valueKey = valueData?.value_key || '';

        let imageObjects = [];
        let primarySwatchObject = null;

        if (valueData) {
            const allImages = configOptions.imageUrls || [];
            let imageIdsFromServer = valueData.image_ids;
            if (typeof imageIdsFromServer === 'string' && imageIdsFromServer.startsWith('[')) {
                try {
                    imageIdsFromServer = JSON.parse(imageIdsFromServer);
                } catch (e) {
                    imageIdsFromServer = [];
                }
            }
            if (Array.isArray(imageIdsFromServer)) {
                imageObjects = imageIdsFromServer.map(id => allImages.find(img => String(img.id) === String(id))).filter(img => img);
            }
            if (valueData.primary_swatch_id) {
                primarySwatchObject = allImages.find(img => String(img.id) === String(valueData.primary_swatch_id)) || null;
            }
        }

        const $newEntry = $(createOptionValueEntryHtml(optionType, valueClientId, serverId, valueKey, initialTranslations, color, imageObjects, primarySwatchObject));
        $valuesContainer.append($newEntry);

        if (optionType === 'swatch-color' && valueData) {
            $newEntry.find('.value-color-picker.coloris-input').val(color).trigger('input');
        }

        updateValueEntryVisuals($newEntry);

        if (!valueData) {
            updateVariants(true);
        }
        return $newEntry;
    }


    $('#options-container').on('click', '.add-option-value-btn', function () {
        const $optionGroup = $(this).closest('.option-group');
        addOptionValue($optionGroup);
    });

    function createOptionValueEntryHtml(currentOptionType, valueClientId, valueServerId, valueKey, nameTranslations = {}, colorValue = '#ffffff', imageObjects = [], primarySwatchObject = null) {
        let specificFieldsHtml = '';
        let imageManagementHtml = '';
        let mainInputPlaceholder = configOptions.valuePlaceholder;

        if (typeof nameTranslations !== 'object' || nameTranslations === null) nameTranslations = {};
        const currentLangValueName = nameTranslations[currentAdminLanguage] || '';

        if (!Array.isArray(imageObjects)) imageObjects = [];

        imageManagementHtml = `<div class="option-value-image-preview-area"><img src="${configOptions.noImagePlaceholder}" alt="Preview" onerror="this.src='${configOptions.errorImagePlaceholder}'"/></div>`;

        if (currentOptionType === 'swatch-color') {
            specificFieldsHtml = `<span class="value-visual-cue" style="background-color: ${colorValue};" data-color="${colorValue}"></span><input type="text" class="form-control input-sm value-color-picker coloris-input" value="${colorValue}" data-coloris autocomplete="off">`;
            mainInputPlaceholder = configOptions.valuePlaceholder;
        } else if (currentOptionType === 'swatch-image') {
            mainInputPlaceholder = configOptions.valuePlaceholder;
            specificFieldsHtml = `<div class="swatch-image-container"><img src="${PLACEHOLDER_SWATCH_IMG}" alt="Swatch" class="swatch-image-display" onerror="this.src='${PLACEHOLDER_SWATCH_IMG}'"><i class="fas fa-edit edit-icon"></i></div>`;
        }

        const valueNameInputGroup = `
                <div class="input-group" style="flex-grow:1;">
                    <input type="text" class="form-control input-sm option-value-name"
                           placeholder="${mainInputPlaceholder}" value="${currentLangValueName}">
                    <span class="input-group-btn">
                        <button class="btn btn-xs btn-default btn-translate" type="button"><i class="fas fa-globe"></i></button>
                    </span>
                </div>`;

        const serverIdAttribute = valueServerId ? `data-value-server-id="${valueServerId}"` : '';
        const valueKeyAttribute = valueKey ? `data-value-key="${valueKey}"` : '';

        // Assemble all attributes for the main div.
        return `
            <div class="option-value-entry" data-value-client-id="${valueClientId}" ${serverIdAttribute} ${valueKeyAttribute}
                 data-image-objects='${JSON.stringify(imageObjects)}'
                 data-primary-swatch='${JSON.stringify(primarySwatchObject)}'
                 data-translations='${JSON.stringify(nameTranslations)}'>
                <span class="drag-handle value-drag-handle" title="${configOptions.dragReorderValueTitle}"><i class="fa fa-grip-vertical"></i></span>
                ${specificFieldsHtml}
                ${imageManagementHtml}
                ${valueNameInputGroup}
                <button type="button" class="btn btn-xs btn-danger remove-option-value-btn"><i class="fas fa-times"></i></button>
            </div>`;
    }

    function loadInitialProductData() {
        if (!initialProductData) return;
        if (initialProductData.options && Array.isArray(initialProductData.options)) {
            initialProductData.options.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
            initialProductData.options.forEach(optionData => {
                if (optionData.values && Array.isArray(optionData.values)) {
                    optionData.values.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
                }
                addOptionGroup(optionData);
            });
        }
        if (initialProductData.variants && Array.isArray(initialProductData.variants)) {
            initialProductData.variants.forEach(serverVariant => {
                const variantKey = serverVariant.stable_key;
                if (variantKey) {
                    previousVariantData[variantKey] = {
                        ...serverVariant,
                        isActive: serverVariant.is_active === true || serverVariant.is_active === 1 || String(serverVariant.is_active) === "1",
                        is_default: serverVariant.is_default === true || serverVariant.is_default === 1 || String(serverVariant.is_default) === "1",
                    };
                }
            });
        }
        updateVariants(false);
    }

    $('#options-container').on('click', '.remove-option-btn', function () {
        setOptionsChanged();
        $(this).closest('.option-group').remove();
        updateVariants(true);
    });

    $('#options-container').on('focus', '.option-type-selector', function () {
        $(this).data('previous-type-val', $(this).val());
    });

    $('#options-container').on('change', '.option-type-selector', function () {
        const $selector = $(this);
        const $optionGroup = $selector.closest('.option-group');
        const newType = $selector.val();
        const previousTypeVal = $selector.data('previous-type-val') || newType;

        const proceedWithChange = (confirmedNewType) => {
            setOptionsChanged();
            $optionGroup.attr('data-option-type', confirmedNewType);
            toggleValueFieldsBasedOnOptionType($optionGroup, confirmedNewType);

            const $valueEntriesToReformat = $optionGroup.find('.option-values-container .option-value-entry').get();
            $($valueEntriesToReformat).each(function () {
                const $oldValueEntry = $(this);
                let nameTranslations = {};
                try {
                    nameTranslations = JSON.parse($oldValueEntry.attr('data-translations') || '{}');
                } catch (e) {
                    console.error("Error parsing value entry translations", e);
                    nameTranslations = {};
                }

                const uniqueClientId = $oldValueEntry.attr('data-value-client-id');
                const serverId = $oldValueEntry.attr('data-value-server-id') || '';

                let existingColor = $oldValueEntry.find('.value-color-picker').val() ||
                    ($oldValueEntry.find('.value-visual-cue').length ? $oldValueEntry.find('.value-visual-cue').attr('data-color') : '#ffffff');

                let existingImageObjects = [];
                try {
                    existingImageObjects = JSON.parse($oldValueEntry.attr('data-image-objects') || '[]');
                } catch (e) {
                    existingImageObjects = [];
                    console.error('Error parsing image objects', e);
                }
                if (!Array.isArray(existingImageObjects)) existingImageObjects = [];

                let existingPrimarySwatch = null;
                try {
                    existingPrimarySwatch = JSON.parse($oldValueEntry.attr('data-primary-swatch') || 'null');
                } catch (e) {
                    existingPrimarySwatch = null;
                }


                const valueKey = $oldValueEntry.attr('data-value-key') || '';

                const newEntryHtml = createOptionValueEntryHtml(confirmedNewType, uniqueClientId, serverId, valueKey, nameTranslations, existingColor, existingImageObjects, existingPrimarySwatch);
                const $newEntry = $(newEntryHtml);
                $oldValueEntry.replaceWith($newEntry);
                updateValueEntryVisuals($newEntry);
            });
            updateVariants(false);
            $selector.data('previous-type-val', confirmedNewType);
        };

        if ((newType === 'text' || newType === 'number' || newType === 'checkbox') && previousTypeVal !== 'text' && previousTypeVal !== 'number' && previousTypeVal !== 'checkbox' && $optionGroup.find('.option-values-container .option-value-entry').length > 0) {
            let confirmedAction = false;
            $('#confirmationModalConfirmBtn').off('click').one('click', function () {
                confirmedAction = true;
                proceedWithChange(newType);
                $('#confirmationModal').modal('hide');
            });
            $('#confirmationModal').off('hidden.bs.modal').one('hidden.bs.modal', function () {
                if (!confirmedAction) {
                    $selector.val(previousTypeVal);
                }
                $('#confirmationModalConfirmBtn').off('click');
            });
            $('#confirmationModal').modal('show');
        } else {
            proceedWithChange(newType);
        }
    });

    function handleDirectNameInput() {
        setOptionsChanged();
        const $inputField = $(this);
        const value = $inputField.val();
        const $targetElement = $inputField.hasClass('option-name') ?
            $inputField.closest('.option-group') :
            $inputField.closest('.option-value-entry');

        let translations = {};
        try {
            translations = JSON.parse($targetElement.attr('data-translations') || '{}');
        } catch (e) {
            console.error("Error parsing translations JSON on input:", e);
            translations = {};
        }

        translations[currentAdminLanguage] = value;
        $targetElement.attr('data-translations', JSON.stringify(translations));
    }

    $('#options-container').on('input', '.option-name', handleDirectNameInput);
    $('#options-container').on('input', '.option-value-name', handleDirectNameInput);

    // Attach specific updateVariants triggers
    $('#options-container').on('input', '.option-name', () => updateVariants(false)); // Option name change is not a SKU-dirtying event
    $('#options-container').on('input', '.option-value-name', () => updateVariants(true)); // Value name change IS a SKU-dirtying event


    $('#options-container').on('click', '.btn-translate', function () {
        const $button = $(this);
        const $inputField = $button.closest('.input-group').find('.form-control');
        currentTranslationTargetElement = $inputField.hasClass('option-name') ?
            $inputField.closest('.option-group') :
            $inputField.closest('.option-value-entry');

        let currentTranslations = {};
        try {
            currentTranslations = JSON.parse(currentTranslationTargetElement.attr('data-translations') || '{}');
        } catch (e) {
            console.error("Error parsing translations JSON for modal: ", e);
            currentTranslations = {};
        }

        const $modalBody = $('#translationModalBody');
        $modalBody.empty();
        configOptions.supportedLanguages.forEach(lang => {
            const value = currentTranslations[lang.code] || '';
            $modalBody.append(`
                    <div class="form-group">
                        <label for="translation-input-${lang.code}">${lang.name} (${lang.code.toUpperCase()})</label>
                        <input type="text" class="form-control translation-input"
                               id="translation-input-${lang.code}" data-lang-code="${lang.code}" value="${value}">
                    </div>`);
        });
        $('#translationModal').modal('show');
    });

    $('#saveTranslationsBtn').on('click', function () {
        if (!currentTranslationTargetElement) return;
        setOptionsChanged();
        const newTranslations = {};
        $('#translationModalBody .translation-input').each(function () {
            const langCode = $(this).attr('data-lang-code');
            const value = $(this).val();
            if (value.trim() !== '') {
                newTranslations[langCode] = value;
            }
        });
        currentTranslationTargetElement.attr('data-translations', JSON.stringify(newTranslations));

        const isValue = currentTranslationTargetElement.is('.option-value-entry');
        const mainInputSelector = isValue ? '.option-value-name' : '.option-name';
        const currentLangName = newTranslations[currentAdminLanguage] || Object.values(newTranslations)[0] || '';
        currentTranslationTargetElement.find(mainInputSelector).val(currentLangName);

        updateVariants(isValue);
        $('#translationModal').modal('hide');
        currentTranslationTargetElement = null;
    });

    // Update all visual elements of a value entry.
    function updateValueEntryVisuals(optionValueEntry) {
        const $entry = $(optionValueEntry);
        if (!$entry || $entry.length === 0) return;

        // Get the latest data from the data attributes
        let imageObjects = [];
        let primarySwatchObject = null;
        try {
            imageObjects = JSON.parse($entry.attr('data-image-objects') || '[]');
            if (!Array.isArray(imageObjects)) imageObjects = [];
        } catch (e) {
            imageObjects = [];
            console.error("Error parsing data-image-objects", e);
        }
        try {
            primarySwatchObject = JSON.parse($entry.attr('data-primary-swatch') || 'null');
        } catch (e) {
            primarySwatchObject = null;
        }

        // Update the multi-image preview area
        const $previewArea = $entry.find('.option-value-image-preview-area');
        if ($previewArea.length) {
            const firstImageUrl = imageObjects.length > 0 ? imageObjects[0].url : configOptions.noImagePlaceholder;
            const badgeHtml = imageObjects.length > 1 ? `<span class="badge badge-info">${imageObjects.length}</span>` : '';
            $previewArea.html(`<img src="${firstImageUrl}" alt="Preview" onerror="this.src='${configOptions.errorImagePlaceholder}'"/> <i class="fas fa-edit edit-icon"></i> ${badgeHtml}`);
        }

        // Update the single swatch-image display
        const $swatchDisplay = $entry.find('.swatch-image-display');
        if ($swatchDisplay.length) {
            const swatchUrl = primarySwatchObject ? primarySwatchObject.url : PLACEHOLDER_SWATCH_IMG;
            $swatchDisplay.attr('src', swatchUrl);
        }
    }

    $('#options-container').on('click', '.remove-option-value-btn', function () {
        setOptionsChanged();
        $(this).closest('.option-value-entry').remove();
        updateVariants(true);
    });

    $('#options-container').on('click', '.value-visual-cue', function () {
        $(this).closest('.option-value-entry').find('.value-color-picker.coloris-input').trigger('click');
    });

    $('#options-container').on('clr-picker-change input change', '.value-color-picker.coloris-input', function (event) {
        setOptionsChanged();
        const color = $(this).val();
        const $valueEntry = $(this).closest('.option-value-entry');
        $valueEntry.find('.value-visual-cue').css('background-color', color).attr('data-color', color);

        if (event.type === 'clr-picker-change' || event.type === 'change') {
            updateVariants(false);
        }
    });

    function openManageImagesModal(managingEntry, mode) {
        currentManagingOptionValueEntry = managingEntry;
        $('#manageOptionImagesModal').data('management-mode', mode);

        let optionValueNameForDisplay = managingEntry.find('.option-value-name').val() || "this value";
        $('#manageOptionImagesModalLabel').text(`${configOptions.manageImagesModalTitle}: ${optionValueNameForDisplay}`);

        tempOptionImages = [];

        if (mode === 'single-swatch-image') {
            try {
                const swatchObject = JSON.parse(currentManagingOptionValueEntry.attr('data-primary-swatch') || 'null');
                if (swatchObject && swatchObject.url) {
                    tempOptionImages = [swatchObject];
                }
            } catch (e) {
                tempOptionImages = [];
            }
        } else {
            try {
                tempOptionImages = JSON.parse(currentManagingOptionValueEntry.attr('data-image-objects') || '[]');
            } catch (e) {
                tempOptionImages = [];
            }
        }
        if (!Array.isArray(tempOptionImages)) tempOptionImages = [];

        renderTempOptionImagesInModal();
        populateUploadedImagesInModal();

        $('#newOptionImageFileInput').val('').prop('multiple', mode !== 'single-swatch-image');
        $('#newOptionImagePreviewContainer').empty();
        $('#manageOptionImagesModal').modal('show');
    }

    $('#options-container').on('click', '.option-value-image-preview-area', function () {
        openManageImagesModal($(this).closest('.option-value-entry'), 'multiple');
    });
    $('#options-container').on('click', '.swatch-image-container', function () {
        openManageImagesModal($(this).closest('.option-value-entry'), 'single-swatch-image');
    });

    $('#manageOptionImagesModal').on('hidden.bs.modal', function () {
        $(this).data('management-mode', '');
        $('#newOptionImageFileInput').prop('multiple', true);
        $('#manageOptionImagesModalLabel').text(configOptions.manageImagesModalTitle);
        tempOptionImages = [];
        currentManagingOptionValueEntry = null;
    });

    function renderTempOptionImagesInModal() {
        const listElement = $('#currentOptionImagesList');
        listElement.empty();
        tempOptionImages.forEach((imgObj, index) => {
            listElement.append(`
                    <div class="option-image-list-item" data-index="${index}" data-image-id="${imgObj.id}">
                        <img src="${imgObj.url}" alt="Option Image ${index + 1}" onerror="this.src='${configOptions.errorPlaceholderSwatchImg}'">
                        <button type="button" class="delete-option-image-btn">&times;</button>
                    </div>`);
        });
    }

    $('#uploadedOptionImagesList').on('click', '.uploaded-image-item', function () {
        const imgObject = $(this).data('image-object');
        if (!imgObject) return;

        const managementMode = $('#manageOptionImagesModal').data('management-mode');
        if (managementMode === 'single-swatch-image') {
            tempOptionImages = [imgObject];
        } else {
            if (!tempOptionImages.some(img => img.id === imgObject.id)) {
                tempOptionImages.push(imgObject);
            }
        }
        renderTempOptionImagesInModal();
    });

    $('#currentOptionImagesList').on('click', '.delete-option-image-btn', function () {
        tempOptionImages.splice($(this).closest('.option-image-list-item').data('index'), 1);
        renderTempOptionImagesInModal();
    });

    $('#addNewOptionImageToListButton').on('click', function () {
        const files = document.getElementById('newOptionImageFileInput').files;
        if (!files || files.length === 0) {
            return;
        }

        const managementMode = $('#manageOptionImagesModal').data('management-mode');
        let filesProcessed = 0;
        const totalFilesToProcess = managementMode === 'single-swatch-image' ? Math.min(1, files.length) : files.length;

        for (let i = 0; i < totalFilesToProcess; i++) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const newImageObject = {
                    id: null,
                    url: e.target.result
                };

                if (managementMode === 'single-swatch-image') {
                    tempOptionImages = [newImageObject];
                } else {
                    tempOptionImages.push(newImageObject);
                }
                filesProcessed++;
                if (filesProcessed === totalFilesToProcess) {
                    renderTempOptionImagesInModal();
                    $('#newOptionImageFileInput').val('');
                    $('#newOptionImagePreviewContainer').empty();
                }
            }
            reader.readAsDataURL(files[i]);
        }
    });

    $('#newOptionImageFileInput').on('change', function (event) {
        const previewContainer = $('#newOptionImagePreviewContainer');
        previewContainer.empty();
        const files = event.target.files;
        if (files && files.length > 0) {
            const limit = $('#manageOptionImagesModal').data('management-mode') === 'single-swatch-image' ? 1 : files.length;
            for (let i = 0; i < limit; i++) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewContainer.append(`<img src="${e.target.result}" alt="Preview" style="width: 80px; height: 80px; object-fit: cover; margin: 5px; border: 1px solid #ddd; border-radius: 3px;" onerror="this.style.display='none'">`);
                }
                reader.readAsDataURL(files[i]);
            }
        }
    });

    $('#saveOptionImagesButton').on('click', function () {
        if (!currentManagingOptionValueEntry) return;

        setOptionsChanged();

        const managementMode = $('#manageOptionImagesModal').data('management-mode');
        if (managementMode === 'single-swatch-image') {
            const newSwatchObject = tempOptionImages.length > 0 ? tempOptionImages[0] : null;
            currentManagingOptionValueEntry.attr('data-primary-swatch', JSON.stringify(newSwatchObject));
        } else {
            currentManagingOptionValueEntry.attr('data-image-objects', JSON.stringify(tempOptionImages));
        }

        // Refresh the visual display to show the newly saved images.
        updateValueEntryVisuals(currentManagingOptionValueEntry);

        updateVariants(false);
        $('#manageOptionImagesModal').modal('hide');
    });

    function getSafeTranslation(translations, langCode, fallbackLangOrder = ['en']) {
        if (translations && typeof translations === 'object') {
            if (translations[langCode] && String(translations[langCode]).trim() !== '') {
                return String(translations[langCode]);
            }
            for (const fallback of fallbackLangOrder) {
                if (translations[fallback] && String(translations[fallback]).trim() !== '') {
                    return String(translations[fallback]);
                }
            }
            const availableLangs = Object.keys(translations).filter(k => translations[k] && String(translations[k]).trim() !== '');
            if (availableLangs.length > 0) {
                return String(translations[availableLangs[0]]);
            }
        }
        return '';
    }

    function getOptionsData() {
        const options = [];
        $('#options-container .option-group').each(function (index) {
            const $optionGroup = $(this);
            if ($optionGroup.attr('data-enabled') === 'false') {
                return true;
            }

            let optionNameTranslations = {};
            try {
                optionNameTranslations = JSON.parse($optionGroup.attr('data-translations') || '{}');
            } catch (e) {
                console.error("Error parsing option name translations", e);
                optionNameTranslations = {};
            }

            let optionNameForLogic = getSafeTranslation(optionNameTranslations, currentAdminLanguage) || $optionGroup.find('.option-name').val();
            if (!optionNameForLogic || optionNameForLogic.trim() === '') {
                return true;
            }

            const optionType = $optionGroup.attr('data-option-type');
            const optionClientId = $optionGroup.attr('data-option-client-id');
            const optionServerId = $optionGroup.attr('data-option-server-id') || null;
            const values = [];

            if (optionType !== 'text' && optionType !== 'number' && optionType !== 'checkbox') {
                $optionGroup.find('.option-values-container .option-value-entry').each(function () {
                    const $valueEntry = $(this);
                    let valueNameTranslations = {};
                    try {
                        valueNameTranslations = JSON.parse($valueEntry.attr('data-translations') || '{}');
                    } catch (e) {
                        console.error("Error parsing value entry translations", e);
                        valueNameTranslations = {};
                    }

                    let valueNameForLogic = getSafeTranslation(valueNameTranslations, currentAdminLanguage) || $valueEntry.find('.option-value-name').val();
                    if (!valueNameForLogic || valueNameForLogic.trim() === '') {
                        return true;
                    }

                    let imageObjects = [];
                    try {
                        imageObjects = JSON.parse($valueEntry.attr('data-image-objects') || '[]');
                    } catch (e) {
                        imageObjects = [];
                    }
                    if (!Array.isArray(imageObjects)) imageObjects = [];

                    let primarySwatch = null;
                    try {
                        primarySwatch = JSON.parse($valueEntry.attr('data-primary-swatch') || 'null');
                    } catch (e) {
                        primarySwatch = null;
                    }

                    let colorCode = (optionType === 'swatch-color') ? ($valueEntry.find('.value-color-picker').val() || $valueEntry.find('.value-visual-cue').attr('data-color')) : null;
                    const valueClientId = $valueEntry.attr('data-value-client-id');
                    const valueServerId = $valueEntry.attr('data-value-server-id') || null;

                    values.push({
                        clientId: valueClientId,
                        serverId: valueServerId,
                        nameTranslations: valueNameTranslations,
                        nameForDisplay: valueNameForLogic,
                        imageObjects: imageObjects,
                        color: colorCode,
                        primarySwatch: (optionType === 'swatch-image') ? primarySwatch : null
                    });
                });
            }

            if ((optionType !== 'text' && optionType !== 'number' && values.length > 0) || (optionType === 'text' || optionType === 'number')) {
                options.push({
                    clientId: optionClientId,
                    serverId: optionServerId,
                    nameTranslations: optionNameTranslations,
                    nameForDisplay: optionNameForLogic,
                    type: optionType,
                    values: values
                });
            }
        });
        return options;
    }

    function sanitizeForSku(text, partMaxLength = 20) {
        if (!text) return '';
        let sanitized = String(text);
        sanitized = sanitized.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        sanitized = sanitized.replace(/[\s\u00A0]+/g, '-');
        sanitized = sanitized.replace(/[^\p{L}\p{N}-]/gu, '');
        sanitized = sanitized.replace(/-+/g, '-');
        sanitized = sanitized.replace(/^-+|-+$/g, '');
        return sanitized.substring(0, partMaxLength);
    }

    function generateStableVariantKey(compositionValueIdentifiers) {
        if (!compositionValueIdentifiers || compositionValueIdentifiers.length === 0) return null;
        const sortedIds = compositionValueIdentifiers.slice().sort((a, b) => {
            const valA = String(a);
            const valB = String(b);
            if (valA < valB) return -1;
            if (valA > valB) return 1;
            return 0;
        });
        return sortedIds.join('_');
    }

    function generateVariants(options) {
        const activeOptions = options.filter(opt => !['text', 'number', 'checkbox'].includes(opt.type) && opt.values.length > 0);

        if (activeOptions.length === 0) return [];

        let variantsCombinations = [
            []
        ];
        for (const option of activeOptions) {
            const newCombinations = [];
            for (const existingCombination of variantsCombinations) {
                for (const value of option.values) {
                    newCombinations.push([...existingCombination, {
                        optionClientId: option.clientId,
                        optionServerId: option.serverId,
                        optionType: option.type,
                        valueClientId: value.clientId,
                        valueServerId: value.serverId,
                        valueName: value.nameForDisplay,
                        valueImageObjects: value.imageObjects || [],
                        valueColor: value.color,
                        valuePrimarySwatch: value.primarySwatch,
                        originalValueNameTranslations: value.nameTranslations
                    }]);
                }
            }
            variantsCombinations = newCombinations;
        }

        const baseSku = $('#input_sku').val().trim();
        return variantsCombinations.map((variantSet, index) => {
            if (variantSet.length === 0) return null;

            const valueIdentifiersForKey = variantSet.map(v => v.valueServerId || v.valueClientId);
            const stableKey = generateStableVariantKey(valueIdentifiersForKey);

            const nameParts = variantSet.map(v => v.valueName);
            const variantName = nameParts.join(' / ');

            const skuLangForValue = 'en';
            const skuPartMaxLength = 20;
            const skuParts = variantSet.map(v_set_item => {
                let nameForSku = getSafeTranslation(v_set_item.originalValueNameTranslations, skuLangForValue, configOptions.supportedLanguages.map(l => l.code));
                if (!nameForSku || !nameForSku.trim()) {
                    nameForSku = v_set_item.valueName;
                }
                return sanitizeForSku(nameForSku, skuPartMaxLength);
            });
            let generatedSku = baseSku ? `${baseSku}-${skuParts.join('-')}` : skuParts.join('-');
            const finalSku = generatedSku.substring(0, 250);

            let imageToDisplayUrl = null;
            let colorForVariantDisplay = null;

            const swatchImageValueChoice = variantSet.find(v => v.optionType === 'swatch-image' && v.valuePrimarySwatch);
            if (swatchImageValueChoice) {
                imageToDisplayUrl = swatchImageValueChoice.valuePrimarySwatch.url;
            } else {
                for (const v of variantSet) {
                    if (v.valueImageObjects && v.valueImageObjects.length > 0) {
                        imageToDisplayUrl = v.valueImageObjects[0].url;
                        break;
                    }
                }
            }

            if (!imageToDisplayUrl) {
                const colorValueChoice = variantSet.find(v => v.optionType === 'swatch-color' && v.valueColor);
                if (colorValueChoice) {
                    colorForVariantDisplay = colorValueChoice.valueColor;
                }
            }

            return {
                stable_key: stableKey,
                name: variantName,
                sku: finalSku,
                imageToDisplay: imageToDisplayUrl,
                colorToDisplay: colorForVariantDisplay,
                composition: variantSet.map(v => ({
                    option_client_id: v.optionClientId,
                    option_server_id: v.optionServerId,
                    value_client_id: v.valueClientId,
                    value_server_id: v.valueServerId,
                })),
                price: '',
                quantity: '',
                weight: '',
                isActive: true,
                price_discounted: '',
                is_default: false,
            };
        }).filter(v => v !== null);
    }

    // Update the variants table and preview based on the current product options.
    function updateVariants(forceSkuRegeneration = false) {
        setOptionsChanged();

        const variantsTableBody = $('#variants-table-body');
        const variantsPreviewContainer = $('#variants-preview-container');

        if ($('#options-container .option-group').length === 0) {
            variantsPreviewContainer.hide();
            variantsTableBody.empty().html(`<tr><td colspan="10" class="text-center">${configOptions.noVariantsAddOptions}</td></tr>`);
            $('#bulk-actions-toolbar').hide();
            $('#select-all-variants').prop('checked', false);
            previousVariantData = {};
            return;
        }
        variantsPreviewContainer.show();

        const options = getOptionsData();
        const newVariants = generateVariants(options);
        const newPreviousVariantDataState = {};
        variantsTableBody.empty();

        if (newVariants.length === 0) {
            let message = configOptions.noVariantsGeneratedBase;
            variantsTableBody.html(`<tr><td colspan="10" class="text-center">${message}</td></tr>`);
            $('#bulk-actions-toolbar').hide();
            $('#select-all-variants').prop('checked', false);
            previousVariantData = {};
            return;
        }

        if(newVariants.length > 0){
            $("#alertDefaultVariant").show();
        }

        let isAnyVariantDefault = newVariants.some(v => previousVariantData[v.stable_key]?.is_default);

        if (!isAnyVariantDefault && newVariants.length > 0) {
            const firstVariantKey = newVariants[0].stable_key;
            if (previousVariantData[firstVariantKey]) {
                previousVariantData[firstVariantKey].is_default = true;
            } else {
                newVariants[0].is_default = true;
            }
        }

        const defaultPrice = $('#default-price').val();
        const defaultQuantity = $('#default-quantity').val();
        const defaultWeight = $('#default-weight').val();

        newVariants.forEach(variant => {
            const oldData = previousVariantData[variant.stable_key] || {};
            const finalVariantData = {...variant};

            finalVariantData.price = (oldData.price !== undefined && oldData.price !== '') ? oldData.price : (defaultPrice || '');
            finalVariantData.quantity = (oldData.quantity !== undefined && oldData.quantity !== '') ? oldData.quantity : (defaultQuantity || '');
            finalVariantData.weight = (oldData.weight !== undefined && oldData.weight !== '') ? oldData.weight : (defaultWeight || '');
            finalVariantData.price_discounted = oldData.price_discounted || '';
            finalVariantData.isActive = oldData.isActive !== undefined ? oldData.isActive : true;
            finalVariantData.is_default = oldData.is_default !== undefined ? oldData.is_default : finalVariantData.is_default;

            if (!forceSkuRegeneration && oldData.sku !== undefined) {
                finalVariantData.sku = oldData.sku;
            }

            const isDefault = finalVariantData.is_default;
            const priceInputsDisabled = isDefault ? 'disabled' : '';
            const priceValue = isDefault ? '' : finalVariantData.price;
            const discountedPriceValue = isDefault ? '' : finalVariantData.price_discounted;
            const weightValue = isDefault ? '' : finalVariantData.weight;
            const quantityValue = isDefault ? '' : finalVariantData.quantity;

            let imageHtml = '';
            if (finalVariantData.imageToDisplay) {
                imageHtml = `<img src="${finalVariantData.imageToDisplay}" alt="" class="variant-image-preview" onerror="this.src='${configOptions.errorImagePlaceholder}'">`;
            } else if (finalVariantData.colorToDisplay) {
                imageHtml = `<span class="variant-color-swatch" style="background-color: ${finalVariantData.colorToDisplay};"></span>`;
            } else {
                imageHtml = configOptions.variantNoImagePlaceholder;
            }

            const activeToggleHtml = `<button type="button" class="btn btn-xs toggle-variant-status" data-stable-key="${finalVariantData.stable_key}"><i class="fas ${finalVariantData.isActive ? 'fa-toggle-on' : 'fa-toggle-off'}"></i></button>`;
            const rowClass = finalVariantData.isActive ? '' : 'variant-inactive';

            // HTML for the default variant switch button cell
            const defaultVariantHtml = `<button type="button" class="btn btn-xs toggle-variant-default" data-stable-key="${finalVariantData.stable_key}">
                    <i class="fas ${isDefault ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>
                </button>`;

            const weightColumnHtml = (configOptions.isPhysicalProduct) ?
                `<td style="width: 8%;"><input type="number" class="form-control input-sm variant-weight" placeholder="${configOptions.weightPlaceholder}" value="${weightValue}" inputmode="decimal" min="0" max="9999999.999" step="0.001" ${priceInputsDisabled}></td>` :
                '';

            const variantRowHtml = `
            <tr data-stable-key="${finalVariantData.stable_key}" class="${rowClass}">
                <td class="variant-select-cell" style="width: 3%;"><input type="checkbox" class="variant-select-checkbox"></td>
                <td class="variant-visual-cell" style="width: 5%;"><div class="variant-image-wrapper">${imageHtml}</div></td>
                <td style="width: 40%">${finalVariantData.name}</td>
                <td style="width: 17%;"><input type="text" class="form-control input-sm variant-sku" placeholder="${configOptions.skuPlaceholder}" value="${finalVariantData.sku}" maxlength="255"></td>
                <td style="width: 8%;"><input type="text" class="form-control input-sm variant-price input-price" value="${priceValue}" maxlength="13" placeholder="${configOptions.priceInputPlaceholder}" inputmode="decimal" ${priceInputsDisabled}></td>
                <td style="width: 8%;"><input type="text" class="form-control input-sm variant-discounted-price input-price" value="${discountedPriceValue}" maxlength="13" placeholder="${configOptions.priceInputPlaceholder}" inputmode="decimal" ${priceInputsDisabled}></td>
                <td style="width: 8%;"><input type="number" class="form-control input-sm variant-quantity" placeholder="${configOptions.qtyPlaceholder}" value="${quantityValue}" min="0" max="9999999" step="1" ${priceInputsDisabled}></td>
                ${weightColumnHtml}
                <td style="width: 3%;" class="default-variant-cell">${defaultVariantHtml}</td>
                <td style="width: 3%;" class="variant-status-cell">${activeToggleHtml}</td>
            </tr>`;

            variantsTableBody.append(variantRowHtml);
            variantsTableBody.find(`tr[data-stable-key="${finalVariantData.stable_key}"] .variant-price, tr[data-stable-key="${finalVariantData.stable_key}"] .variant-discounted-price, tr[data-stable-key="${finalVariantData.stable_key}"] .variant-quantity, tr[data-stable-key="${finalVariantData.stable_key}"] .variant-weight`).prop('disabled', !finalVariantData.isActive || isDefault);
            variantsTableBody.find(`tr[data-stable-key="${finalVariantData.stable_key}"] .variant-sku`).prop('disabled', !finalVariantData.isActive);

            newPreviousVariantDataState[finalVariantData.stable_key] = finalVariantData;
        });

        previousVariantData = newPreviousVariantDataState;
        updateBulkActionsToolbar();
        $('#select-all-variants').prop('checked', false);
    }

    // Event listener to handle changing the default variant with a switch.
    $('#variants-table-body').on('click', '.toggle-variant-default', function() {
        const $clickedButton = $(this);
        const newDefaultKey = $clickedButton.data('stable-key');

        // Prevent action if it's already the default
        if (previousVariantData[newDefaultKey] && previousVariantData[newDefaultKey].is_default) {
            return;
        }

        setOptionsChanged();

        let oldDefaultKey = null;
        // Find the old default and update the data model
        for (const key in previousVariantData) {
            if (previousVariantData[key].is_default) {
                oldDefaultKey = key;
                previousVariantData[key].is_default = false;
            }
        }
        previousVariantData[newDefaultKey].is_default = true;

        // Update the UI for the old default variant (if one existed)
        if (oldDefaultKey) {
            const $oldDefaultRow = $(`tr[data-stable-key="${oldDefaultKey}"]`);
            $oldDefaultRow.find('.toggle-variant-default i').removeClass('fa-toggle-on').addClass('fa-toggle-off');
            const $oldPriceInputs = $oldDefaultRow.find('.variant-price, .variant-discounted-price, .variant-quantity, .variant-weight');
            $oldPriceInputs.prop('disabled', false);
            // Restore values
            const oldVariantData = previousVariantData[oldDefaultKey];
            if (oldVariantData) {
                $oldDefaultRow.find('.variant-price').val(oldVariantData.price);
                $oldDefaultRow.find('.variant-discounted-price').val(oldVariantData.price_discounted);
                $oldDefaultRow.find('.variant-quantity').val(oldVariantData.quantity);
                $oldDefaultRow.find('.variant-weight').val(oldVariantData.weight);
            }
        }

        // Update the UI for the new default variant
        const $newDefaultRow = $(`tr[data-stable-key="${newDefaultKey}"]`);
        $newDefaultRow.find('.toggle-variant-default i').removeClass('fa-toggle-off').addClass('fa-toggle-on');
        const $newPriceInputs = $newDefaultRow.find('.variant-price, .variant-discounted-price, .variant-quantity, .variant-weight');
        $newPriceInputs.prop('disabled', true).val('');
    });


    $('#variants-table-body').on('input change', '.variant-price, .variant-sku, .variant-quantity, .variant-weight, .variant-discounted-price', function () {
        setOptionsChanged();
        const $row = $(this).closest('tr');
        const variantKey = $row.attr('data-stable-key');
        if (variantKey && previousVariantData[variantKey]) {
            if (!previousVariantData[variantKey].is_default) {
                previousVariantData[variantKey].price = $row.find('.variant-price').val();
                previousVariantData[variantKey].quantity = $row.find('.variant-quantity').val();
                previousVariantData[variantKey].weight = $row.find('.variant-weight').val();
                previousVariantData[variantKey].price_discounted = $row.find('.variant-discounted-price').val();
            }
            previousVariantData[variantKey].sku = $row.find('.variant-sku').val();
        }
    });

    $('#variants-table-body').on('click', '.toggle-variant-status', function () {
        setOptionsChanged();
        const variantKey = $(this).attr('data-stable-key');
        const $row = $(this).closest('tr');
        if (previousVariantData[variantKey]) {
            const newIsActive = !previousVariantData[variantKey].isActive;
            previousVariantData[variantKey].isActive = newIsActive;

            $(this).html(`<i class="fas ${newIsActive ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>`);
            $row.toggleClass('variant-inactive', !newIsActive);

            const isDefault = previousVariantData[variantKey].is_default;
            $row.find('.variant-price, .variant-discounted-price, .variant-quantity, .variant-weight').prop('disabled', !newIsActive || isDefault);
            $row.find('.variant-sku').prop('disabled', !newIsActive);
        }
    });

    $('#apply-all-price').on('click', function () {
        applyToAllActive('.variant-price', $('#default-price').val(), 'price');
    });
    $('#apply-all-discounted-price').on('click', function () {
        applyToAllActive('.variant-discounted-price', $('#default-discounted-price').val(), 'price_discounted');
    });
    $('#apply-all-quantity').on('click', function () {
        applyToAllActive('.variant-quantity', $('#default-quantity').val(), 'quantity');
    });
    $('#apply-all-weight').on('click', function () {
        applyToAllActive('.variant-weight', $('#default-weight').val(), 'weight');
    });

    function applyToAllActive(selector, value, dataKey) {
        setOptionsChanged();
        let warningMessage = '';
        if (dataKey === 'price') warningMessage = "invalid";
        else if (dataKey === 'quantity') warningMessage = "invalid";
        else if (dataKey === 'weight') warningMessage = "invalid";

        if (value === '' || (dataKey !== 'dimensions' && isNaN(parseFloat(value)))) {
            if (warningMessage && value !== '') console.warn(warningMessage);
            return;
        }

        $('#variants-table-body tr:not(.variant-inactive)').each(function () {
            const $row = $(this);
            const variantKey = $row.attr('data-stable-key');
            const $inputField = $row.find(selector);
            if (!$inputField.prop('disabled')) {
                $inputField.val(value);
                if (variantKey && previousVariantData[variantKey]) {
                    previousVariantData[variantKey][dataKey] = value;
                }
            }
        });
    }

    $('#select-all-variants').on('change', function () {
        $('.variant-select-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActionsToolbar();
    });
    $('#variants-table-body').on('change', '.variant-select-checkbox', function () {
        updateBulkActionsToolbar();
        if (!$(this).prop('checked')) {
            $('#select-all-variants').prop('checked', false);
        } else {
            if ($('.variant-select-checkbox:checked').length === $('.variant-select-checkbox').length) {
                if ($('.variant-select-checkbox').length > 0) $('#select-all-variants').prop('checked', true);
            }
        }
    });

    function updateBulkActionsToolbar() {
        $('#bulk-actions-toolbar').toggle($('.variant-select-checkbox:checked').length > 0);
    }

    function performBulkAction(makeActive) {
        setOptionsChanged();
        $('.variant-select-checkbox:checked').each(function () {
            const $row = $(this).closest('tr');
            const variantKey = $row.attr('data-stable-key');
            const $toggleButton = $row.find('.toggle-variant-status');
            if (previousVariantData[variantKey] && previousVariantData[variantKey].isActive !== makeActive) {
                previousVariantData[variantKey].isActive = makeActive;
                $toggleButton.html(`<i class="fas ${makeActive ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>`);
                $row.toggleClass('variant-inactive', !makeActive);

                const isDefault = previousVariantData[variantKey].is_default;
                $row.find('.variant-price, .variant-discounted-price, .variant-quantity, .variant-weight').prop('disabled', !makeActive || isDefault);
                $row.find('.variant-sku').prop('disabled', !makeActive);
            }
        });
        $('#select-all-variants').prop('checked', false);
        $('.variant-select-checkbox').prop('checked', false);
        updateBulkActionsToolbar();
    }

    $('#bulk-deactivate-selected').on('click', function () {
        performBulkAction(false);
    });
    $('#bulk-activate-variants').on('click', function () {
        performBulkAction(true);
    });

    $('#options-container').on('change', '.option-enable-switch', function () {
        const $checkbox = $(this);
        const $optionGroup = $checkbox.closest('.option-group');
        const isEnabled = $checkbox.is(':checked');

        setOptionsChanged();

        $optionGroup.attr('data-enabled', isEnabled.toString());

        $optionGroup.find('input:not(.option-enable-switch), select, textarea, button:not(.option-enable-switch):not(.remove-option-btn):not(.collapse-option-btn)')
            .prop('disabled', !isEnabled);

        if (!isEnabled) {
            $optionGroup.find('.value-color-picker.coloris-input').each(function () {
                if (this.clrInstance && typeof this.clrInstance.close === 'function') {
                    this.clrInstance.close(true);
                }
            });
        }
        updateVariants(true);
    });

    function prepareDataForSubmission() {
        $('#variants-table-body').find('tr[data-stable-key]').each(function () {
            const $row = $(this);
            const variantKey = $row.attr('data-stable-key');
            if (variantKey && previousVariantData[variantKey]) {
                previousVariantData[variantKey].sku = $row.find('.variant-sku').val();
                if (!previousVariantData[variantKey].is_default) {
                    previousVariantData[variantKey].price = $row.find('.variant-price').val();
                    previousVariantData[variantKey].quantity = $row.find('.variant-quantity').val();
                    previousVariantData[variantKey].weight = $row.find('.variant-weight').val();
                    previousVariantData[variantKey].price_discounted = $row.find('.variant-discounted-price').val();
                }
            }
        });

        $('#submitted_base_sku').val($('#input_sku').val());

        const optionsToSubmit = [];
        $('#options-container .option-group').each(function (optionIndex) {
            const $optionGroup = $(this);
            const optionData = {
                client_id: $optionGroup.attr('data-option-client-id'),
                server_id: $optionGroup.attr('data-option-server-id') || null,
                option_key: $optionGroup.attr('data-option-key') || null,
                name_translations: JSON.parse($optionGroup.attr('data-translations') || '{}'),
                type: $optionGroup.attr('data-option-type'),
                is_enabled: $optionGroup.attr('data-enabled') === 'true',
                sort_order: optionIndex,
                values: []
            };
            $optionGroup.find('.option-values-container .option-value-entry').each(function (valueIndex) {
                const $valueEntry = $(this);
                let imageObjects = [];
                try {
                    imageObjects = JSON.parse($valueEntry.attr('data-image-objects') || '[]');
                } catch (e) {
                }
                let primarySwatch = null;
                try {
                    primarySwatch = JSON.parse($valueEntry.attr('data-primary-swatch') || 'null');
                } catch (e) {
                }

                const imageIds = imageObjects.map(img => img.id).filter(id => id);
                const primarySwatchId = (primarySwatch && primarySwatch.id) ? primarySwatch.id : null;

                optionData.values.push({
                    client_id: $valueEntry.attr('data-value-client-id'),
                    server_id: $valueEntry.attr('data-value-server-id') || null,
                    value_key: $valueEntry.attr('data-value-key') || null,
                    name_translations: JSON.parse($valueEntry.attr('data-translations') || '{}'),
                    color: (optionData.type === 'swatch-color') ? ($valueEntry.find('.value-color-picker').val() || $valueEntry.find('.value-visual-cue').attr('data-color')) : null,
                    image_ids: imageIds,
                    primary_swatch_id: primarySwatchId,
                    sort_order: valueIndex
                });
            });
            optionsToSubmit.push(optionData);
        });
        $('#submitted_options_data').val(JSON.stringify(optionsToSubmit));

        const variantsToSubmit = [];
        Object.keys(previousVariantData).forEach(variantKey => {
            const variant = previousVariantData[variantKey];
            variantsToSubmit.push({
                sku: variant.sku,
                price: variant.price,
                quantity: variant.quantity,
                weight: variant.weight,
                is_active: variant.isActive ? 1 : 0,
                is_default: variant.is_default ? 1 : 0,
                composition: variant.composition,
                price_discounted: variant.price_discounted
            });
        });
        $('#submitted_variants_data').val(JSON.stringify(variantsToSubmit));

        return true;
    }

    $('#form_product_details').on('submit', function (e) {

        const sku = $('#input_sku').val().trim();
        if (sku === '') {
            const generatedSku = generateUniqueString();
            $('#input_sku').val(generatedSku);
            $('#input_sku').trigger('input');
        }

        if (!prepareDataForSubmission()) {
            e.preventDefault();
            console.error("Error preparing data for submission.");
        }
    });

    // Initialization
    loadInitialProductData();

    if (!initialProductData) {
        updateVariants(false);
    }

    isInitialLoadComplete = true;
});

function loadUploadedOptionImages(id) {
    $.ajax({
        type: 'POST',
        url: configOptions.loadUploadedOptionImagesUrl,
        data: {'product_id': configOptions.optionProductId},
        success: function (response) {
            if (response.result == 1) {
                $("#uploaderFile" + id).remove();
                populateUploadedImagesInModal(response.images);
            }
        }
    });
}

function deleteOptionImage(event, id) {
    event.stopPropagation();
    Swal.fire(swalOptions(configOptions.confirmDeleteMessage)).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: configOptions.optionImageDeleteUrl,
                data: {
                    'image_id': id,
                    'product_id': configOptions.optionProductId
                },
                success: function (response) {
                    if (response.result == 1) {
                        $('#currentOptionImagesList div[data-image-id="' + id + '"]').remove();
                        populateUploadedImagesInModal(response.images);
                    }
                }
            });
        }
    });
}

$('#manageOptionImagesModal').on('shown.bs.modal', function () {
    loadUploadedOptionImages("none");
});

function populateUploadedImagesInModal(images = null) {
    const uploadedImages = images != null ? images : configOptions.imageUrls;
    const listElement = $('#uploadedOptionImagesList');
    listElement.empty();
    (uploadedImages || []).forEach(imgObj => {
        let deleteButtonHtml = '';
        if (imgObj.is_option_image == 1) {
            deleteButtonHtml = `
            <button type="button" class="delete-option-image-btn" onclick="deleteOptionImage(event, ${imgObj.id});">
                <i class="fa fa-trash-can"></i>
            </button>`;
        }
        const imgObjHtml = `
        <div class="option-image-list-item uploaded-image-item" style="cursor:pointer;">
            ${deleteButtonHtml}
            <img src="${imgObj.url}" alt="Sample Image" onerror="this.src='${configOptions.errorPlaceholderSwatchImg}'">
        </div>`;
        const $imgObjHtml = $(imgObjHtml);
        $imgObjHtml.data('image-object', imgObj);
        listElement.append($imgObjHtml);
    });
}