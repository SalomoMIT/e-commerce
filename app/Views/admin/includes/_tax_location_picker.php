<div id="divLocationSelect" class="form-group" <?= !empty($tax) && $tax->is_all_countries == 1 ? 'style="display: none;"' : ''; ?>>
    <div class="row">
        <div id="selected_locations" class="col-sm-12">
            <?php if (!empty($tax)):
                $countryIds = array();
                $stateIds = array();
                if (!empty($tax->country_ids)) {
                    $countryIds = unserializeData($tax->country_ids);
                }
                if (!empty($tax->state_ids)) {
                    $stateIds = unserializeData($tax->state_ids);
                }
                if (!empty($countryIds)):
                    foreach ($countryIds as $id):
                        $country = getCountry($id);
                        if (!empty($country)): ?>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-default btn-location-name"><?= esc($country->name); ?></button>
                                <button type="button" class="btn btn-sm btn-default btn-location-delete"><i class="fa fa-times"></i></button>
                                <input type="hidden" name="countries[]" value="<?= $country->id; ?>">
                            </div>
                        <?php endif;
                    endforeach;
                endif;
                if (!empty($stateIds)):
                    foreach ($stateIds as $id):
                        $state = getState($id);
                        if (!empty($state)):
                            $country = getCountry($state->country_id);
                            if (!empty($country)):?>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-default btn-location-name"><?= esc($country->name . '/' . $state->name); ?></button>
                                    <button type="button" class="btn btn-sm btn-default btn-location-delete"><i class="fa fa-times"></i></button>
                                    <input type="hidden" name="states[]" value="<?= $state->id; ?>">
                                </div>
                            <?php endif;
                        endif;
                    endforeach;
                endif;
            endif; ?>
        </div>
        <?php if ($generalSettings->single_country_mode != 1): ?>
            <div class="col-md-12 m-b-5">
                <select id="select_countries" name="country_id" class="select2 form-control" onchange="getStates(this.value);" data-placeholder="<?= trans("country"); ?>">
                    <option value=""></option>
                    <?php if (!empty($countries)):
                        foreach ($countries as $item):
                            if ($item->status == 1):?>
                                <option value="<?= $item->id; ?>"><?= esc($item->name); ?></option>
                            <?php endif;
                        endforeach;
                    endif; ?>
                </select>
            </div>
        <?php else: ?>
            <?php $countryId = $generalSettings->single_country_id;
            $singleCountry = getCountry($countryId);
            $states = getStatesByCountry($countryId);
        endif; ?>
        <div id="get_states_container" class="col-md-12 m-b-5 <?= !empty($countryId) ? '' : 'display-none'; ?>">
            <select id="select_states" name="state_id" class="select2 form-control" data-placeholder="<?= trans("state"); ?>">
                <option value=""></option>
                <?php if (!empty($states)):
                    foreach ($states as $item): ?>
                        <option value="<?= $item->id; ?>"><?= esc($item->name); ?></option>
                    <?php endforeach;
                endif; ?>
            </select>
        </div>
        <div class="col-sm-12">
            <button type="button" id="btnSelectLocation" class="btn btn-sm btn-info"><i class="fa fa-check"></i>&nbsp;<?= trans("select_location") ?></button>
        </div>
    </div>
</div>

<?php if (!empty($singleCountry)): ?>
    <script>
        var singleCountrytext = "<?= clrDoubleQuotes($singleCountry->name); ?>";
    </script>
<?php endif; ?>


<style>
    #btnSelectLocation {
        display: none;
    }

    .btn-location-name {
        cursor: default;
    }

    .btn-location-name:hover, .btn-location-name:focus, .btn-location-name:active {
        background-color: #f4f4f4 !important;
        color: #444 !important;
        border-color: #ddd !important;
    }

    .btn-group {
        margin-bottom: 5px;
        margin-right: 5px;
    }

    .select2 {
        width: 100% !important;
    }
</style>

<script>
    $(document).on("change", "#checkboxAllCountries", function () {
        if ($(this).is(":checked")) {
            $('#divLocationSelect').hide();
        } else {
            $('#divLocationSelect').show();
        }
    });

    $(document).on("change", "select", function () {
        $('#btnSelectLocation').show();
    });

    // jQuery
    $(document).ready(function () {
        $('#btnSelectLocation').click(function () {
            var countryId = $('#select_countries').val();
            var countryText = $('#select_countries option:selected').text();
            var stateId = $('#select_states').val();
            var stateText = $('#select_states option:selected').text();

            if (typeof singleCountrytext !== 'undefined') {
                countryText = singleCountrytext;
            }

            // Construct the label text
            var locationText = stateId ? countryText + ' / ' + stateText : countryText;

            // Check if the location is already added
            var alreadyAdded = false;
            $('#selected_locations .btn-group').each(function () {
                if ($(this).find('button.btn-default').text() === locationText) {
                    alreadyAdded = true;
                    return false; // Break out of the loop
                }
            });

            if (!alreadyAdded) {
                var buttonHtml = '<div class="btn-group" role="group">' +
                    '<button type="button" class="btn btn-sm btn-default btn-location-name">' + locationText + '</button>' +
                    '<button type="button" class="btn btn-sm btn-default btn-location-delete"><i class="fa fa-times"></i></button>';

                // Add hidden input only for state if both are selected
                if (stateId) {
                    buttonHtml += '<input type="hidden" name="states[]" value="' + stateId + '">';
                } else {
                    buttonHtml += '<input type="hidden" name="countries[]" value="' + countryId + '">';
                }

                buttonHtml += '</div>';
                $('#selected_locations').append(buttonHtml);
            }
            $('#select_countries').val(null).trigger('change');
            $('#select_states').val(null).trigger('change');
        });

        // Event delegation for dynamically added elements
        $('#selected_locations').on('click', '.btn-location-delete', function () {
            var locationButtonGroup = $(this).closest('.btn-group');

            // Remove the entire button group, including hidden inputs
            locationButtonGroup.remove();
        });
    });
</script>