/*CSRF Protection*/
$.ajaxSetup({
    beforeSend: function (xhr, settings) {
        const csrfHash = $('meta[name="X-CSRF-TOKEN"]').attr('content');
        if (settings.type.toUpperCase() === 'POST') {
            if (typeof settings.data === 'string') {
                settings.data += '&' + MdsConfig.csrfTokenName + '=' + csrfHash;
                settings.data += '&sysLangId=' + MdsConfig.sysLangId;
            } else if (typeof settings.data === 'object') {
                settings.data = settings.data || {};
                settings.data[MdsConfig.csrfTokenName] = csrfHash;
            }
        }
    }
});

//set serialized form token
function setSerializedData(serializedData) {
    serializedData.push({name: 'sysLangId', value: MdsConfig.sysLangId});
    serializedData.push({name: MdsConfig.csrfTokenName, value: $('meta[name="X-CSRF-TOKEN"]').attr('content')});
    return serializedData;
}

function generateUrl(path) {
    return MdsConfig.baseUrl + path;
}

//run email queue
$(document).ready(function () {
    $.ajax({
        type: 'GET',
        url: generateUrl('ajax/run-queue-worker'),
        data: {}
    });
});

function swalOptions(message) {
    return {
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: MdsConfig.text.ok,
        cancelButtonText: MdsConfig.text.cancel,
        reverseButtons: true
    };
}

//validate price input
$(document).ready(function () {
    const decimalSeparator = MdsConfig.decimalSeparator || '.';
    const thousandSeparator = decimalSeparator === '.' ? ',' : '.';

    /**
     * Validate the input of a price field in real-time.
     */
    function validatePriceInput($input) {
        let currentValue = $input.val();
        let lastValidValue = $input.data('lastValidValue') || '';

        // Immediately reject input if it contains the thousand separator.
        // This is the main fix to prevent inputs like "1.000" or "1,000".
        if (currentValue.includes(thousandSeparator)) {
            $input.val(lastValidValue);
            return;
        }

        // Build a regular expression to validate the decimal format.
        const escapedSeparator = decimalSeparator === '.' ? '\\.' : decimalSeparator;
        const validationRegex = new RegExp(`^\\d*(${escapedSeparator}?\\d{0,2})$`);

        // Test the current value against the regex.
        if (validationRegex.test(currentValue)) {
            // If the value is valid, update the 'lastValidValue' to this new state.
            $input.data('lastValidValue', currentValue);
        } else {
            // If the value is invalid, revert the input to its last known valid state.
            $input.val(lastValidValue);
        }
    }

    // Initialize the 'lastValidValue' for all price inputs on page load.
    $('.input-price').each(function () {
        const $this = $(this);
        $this.data('lastValidValue', $this.val());
    });

    // Attach the validation function to the 'input' event
    $(document).on('input', '.input-price', function () {
        validatePriceInput($(this));
    });
});
