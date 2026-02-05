<script>
    //select continen
    $(document).on("change", "#select_continents", function () {
        $("#btn_select_region_container").show();
        getCountriesByContinent($(this).val(), "<?= trans("all_countries"); ?>");
        if ($(this).val() != '' && $(this).val() != 0) {
            $("#form_group_countries").show();
        } else {
            $("#form_group_countries").hide();
        }
    });
    //select country
    $(document).on("change", "#select_countries", function () {
        $("#btn_select_region_container").show();
        getStatesByCountry($(this).val(), "<?= trans("all_states"); ?>");
        $("#form_group_states").show();
    });
    //select region
    $(document).on("click", "#btn_select_region", function () {
        var continent = $('#select_continents').val();
        var continent_text = $('#select_continents option:selected').text();
        var country = $('#select_countries').val();
        var country_text = $('#select_countries option:selected').text();
        var state = $('#select_states').val();
        var state_text = $('#select_states option:selected').text();
        var region_id = state;
        var region_text = continent_text + '/' + country_text + '/' + state_text;
        var input_name = 'state';
        if (region_id == '' || region_id == 0 || region_id == null) {
            region_id = country;
            region_text = continent_text + '/' + country_text;
            input_name = 'country';
        }
        if (region_id == '' || region_id == 0 || region_id == null) {
            region_id = continent;
            region_text = continent_text;
            input_name = 'continent';
        }
        region_text = region_text.replace(/^\/+|\/+$/g, '');
        if (region_id) {
            if (!$('#lc-' + input_name + '-' + region_id).length) {
                $("#selected_regions_container").append('<div id="lc-' + input_name + '-' + region_id + '" class="region">' + region_text + '<a href="javascript:void(0)"><i class="fa fa-times"></i></a><input type="hidden" value="' + region_id + '" name="' + input_name + '[]"></div>');
            }
        }
        //reset
        $('#select_continents').val(null).trigger('change');
        $('#select_countries').val(null).trigger('change');
        //$('#select_countries option').empty();
        $('#select_states option').empty();
        $('#select_countries').hide();
        $('#form_group_states').hide();
    });
    //delete location
    $(document).on("click", ".region a", function () {
        $(this).parent().remove();
    });

    //delete location database
    function deleteShippingLocation(id) {
        var data = {
            'id': id
        };
        $.ajax({
            type: 'POST',
            url: generateUrl('Dashboard/deleteShippingLocationPost'),
            data: data,
            success: function (response) {
            }
        });
    }

    //shipping methods
    $(document).on("click", "#btn_select_shipping_method", function () {
        var data = {
            'selected_option': $('#select_shipping_methods').val()
        };
        $.ajax({
            type: 'POST',
            url: generateUrl('Dashboard/selectShippingMethod'),
            data: data,
            success: function (response) {
                if (response.result == 1) {
                    $("#selected_shipping_methods").append(response.htmlContent);
                }
            }
        });
    });

    //delete shipping method
    $(document).on("click", ".btn-delete-shipping-method", function () {
        var id = $(this).attr('data-id');
        $("#row_shipping_method_" + id).remove();
    });

    //delete shipping method database
    function deleteShippingMethod(id, message) {
        Swal.fire(swalOptions(message)).then((result) => {
            if (result.isConfirmed) {
                var data = {
                    'id': id
                };
                $.ajax({
                    type: 'POST',
                    url: generateUrl('Dashboard/deleteShippingMethodPost'),
                    data: data,
                    success: function (response) {
                        location.reload();
                    }
                });
            }
        });
    }

    $(document).ready(function () {
        function toggleShippingFields() {
            const selectedType = $('#shipping_calculation_type').val();

            const weightRulesContainer = $('#weight_rules_container');
            const singleCostContainer = $('#single_cost_container');

            if (selectedType === 'total_weight') {
                weightRulesContainer.show();
                singleCostContainer.hide();
            } else {
                weightRulesContainer.hide();
                singleCostContainer.show();
            }
        }

        $('#shipping_calculation_type').on('change', toggleShippingFields);

        toggleShippingFields();
    });

    $(document).ready(function () {
        // Start index for new rows.
        let rateIndex = $('#shipping-rates-container .rate-row').length;

        // Add a new rate row
        $('#btn-add-rate').click(function () {
            let newRow = `
           <div class="rate-row">
                <div class="rate-row-inner">
                    <div class="rate-input">
                        <div class="input-group">
                            <span class="input-group-addon">${MdsConfig.text.kg}</span>
                            <input type="number" name="rates[${rateIndex}][min_weight]" class="form-control" placeholder="${MdsConfig.text.minWeight}" min="0" max="999999" step="0.01">
                        </div>
                    </div>

                    <div class="rate-input">
                        <div class="input-group">
                            <span class="input-group-addon">${MdsConfig.text.kg}</span>
                            <input type="number" name="rates[${rateIndex}][max_weight]" class="form-control" placeholder="${MdsConfig.text.maxWeight}" min="0" max="999999" step="0.01">
                        </div>
                    </div>

                    <div class="rate-input">
                        <div class="input-group">
                            <span class="input-group-addon">${MdsConfig.currencySymbol}</span>
                            <input type="text" name="rates[${rateIndex}][cost]"  class="form-control form-input input-price" maxlength="13" placeholder="${MdsConfig.text.cost}" inputmode="decimal">
                        </div>
                    </div>
                    <div style="display: flex; align-items: center">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-rate"><i class="fa fa-times"></i></button>
                    </div>
                </div>
            </div>`;
            $('#shipping-rates-container').append(newRow);
            rateIndex++;
        });

        // Remove a rate row
        $('#shipping-rates-container').on('click', '.btn-remove-rate', function () {
            $(this).closest('.rate-row').remove();
        });
    });

</script>
