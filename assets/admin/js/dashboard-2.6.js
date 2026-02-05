//duplicate product
function duplicateProduct(productId, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: generateUrl('Dashboard/duplicateProductPost'),
                data: {'product_id': productId},
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//set main image session
$(document).on('click', '.btn-set-image-main-session', function () {
    var fileId = $(this).attr('data-file-id');
    $('.btn-is-image-main').removeClass('btn-success');
    $('.btn-is-image-main').addClass('btn-secondary');
    $(this).removeClass('btn-secondary');
    $(this).addClass('btn-success');
    $.ajax({
        type: 'POST',
        url: generateUrl('File/setImageMainSession'),
        data: {'file_id': fileId},
        success: function (response) {
        }
    });
});

//set main image
$(document).on('click', '.btn-set-image-main', function () {
    var imageId = $(this).attr('data-image-id');
    var productId = $(this).attr('data-product-id');
    var data = {
        'image_id': imageId,
        'product_id': productId
    };
    $('.btn-is-image-main').removeClass('btn-success');
    $('.btn-is-image-main').addClass('btn-secondary');
    $(this).removeClass('btn-secondary');
    $(this).addClass('btn-success');
    $.ajax({
        type: 'POST',
        url: generateUrl('File/setImageMain'),
        data: data,
        success: function (response) {
        }
    });
});

//delete product image session
$(document).on('click', '.btn-delete-product-img-session', function () {
    var fileId = $(this).attr('data-file-id');
    $.ajax({
        type: 'POST',
        url: generateUrl('File/deleteImageSession'),
        data: {'file_id': fileId},
        success: function () {
            imageUploadCount = imageUploadCount - 1;
            if (imageUploadCount < 0) {
                imageUploadCount = 0;
            }
            $('#uploaderFile' + fileId).remove();
        }
    });
});

//delete product image
$(document).on('click', '.btn-delete-product-img', function () {
    var fileId = $(this).attr('data-file-id');
    var data = {
        'file_id': fileId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('File/deleteImage'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
});

//delete product video preview
function deleteProductVideoPreview(productId, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: generateUrl('File/deleteVideo'),
                data: {'product_id': productId},
                success: function (response) {
                    if(response.content){
                        document.getElementById("video_upload_result").innerHTML = response.content;
                    }
                }
            });
        }
    });
};

//delete product audio preview
function deleteProductAudioPreview(productId, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: generateUrl('File/deleteAudio'),
                data: {'product_id': productId},
                success: function (response) {
                    if(response.content){
                        document.getElementById("audio_upload_result").innerHTML = response.content;
                    }
                }
            });
        }
    });
};

function generateUniqueString() {
    var time = String(new Date().getTime()),
        i = 0,
        output = '';
    for (i = 0; i < time.length; i += 2) {
        output += Number(time.substr(i, 2)).toString(36);
    }
    return (output.toUpperCase());
}

$('input[type=radio][name=product_type]').change(function () {
    $('input[name=listing_type]').prop('checked', false);
    if (this.value == 'digital') {
        $('.listing_ordinary_listing').hide();
        $('.listing_bidding').hide();
        $('.listing_license_keys').show();
    } else {
        $('.listing_ordinary_listing').show();
        $('.listing_bidding').show();
        $('.listing_license_keys').hide();
    }
});

//delete product digital file
function deleteProductDigitalFile(fileId, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: generateUrl('File/deleteDigitalFile'),
                data: {'file_id': fileId},
                success: function (response) {
                    if (response.result == 1) {
                        document.getElementById("digital_files_upload_result").innerHTML = response.htmlContent;
                    }
                }
            });
        }
    });
};

/*
 * --------------------------------------------------------------------
 * License Key Functions
 * --------------------------------------------------------------------
 */

//add license key
function addLicenseKeys(productId) {
    var licenseKeys = $('#textarea_license_keys').val();
    if (licenseKeys.trim() != "") {
        $(".btn-add-license-keys").prop('disabled', true);
        $(".loader-license-keys").show();
        var data = {
            'product_id': productId,
            'license_keys': licenseKeys,
            'allow_dublicate': $("input[name='allow_dublicate_license_keys']:checked").val()
        };
        $.ajax({
            type: 'POST',
            url: generateUrl('Dashboard/addLicenseKeys'),
            data: data,
            success: function (response) {
                if (response.result == 1) {
                    setTimeout(function () {
                        $(".btn-add-license-keys").prop('disabled', false);
                        $(".loader-license-keys").hide();
                        document.getElementById("result-add-license-keys").innerHTML = response.message;
                        $('#textarea_license_keys').val('');
                    }, 500);
                }
            }
        });
    }
}

//delete license key
function deleteLicenseKey(id, productId) {
    var data = {
        'id': id,
        'product_id': productId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Dashboard/deleteLicenseKey'),
        data: data,
        success: function (response) {
            $('#tr_license_key_' + id).remove();
        }
    });
}

//update license code list on modal open
$("#viewLicenseKeysModal").on('show.bs.modal', function () {
    var productId = $('#license_key_list_product_id').val();
    var data = {
        'product_id': productId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Dashboard/loadLicenseKeysList'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                document.getElementById("response_license_key").innerHTML = response.htmlContent;
            }
        }
    });
});

//get filter subcategories
function getFilterSubCategoriesDashboard(val) {
    var data = {
        'parent_id': val
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Dashboard/getSubCategories'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                $('#subcategories').children('option:not(:first)').remove();
                $("#subcategories").append(response.options);
            }
        }
    });
}

function getSubCategoriesDashboard(parentId, level, langId, showIds = false) {
    level = parseInt(level);
    var newLevel = level + 1;
    var data = {
        'parent_id': parentId,
        'lang_id': langId,
        'show_ids': showIds == false ? 0 : 1
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/getSubCategories'),
        data: data,
        dataType: 'json',
        success: function (response) {
            $('.subcategory-select-container').each(function () {
                if (parseInt($(this).attr('data-level')) > level) {
                    $(this).remove();
                }
            });
            if (response.result == 1 && response.htmlContent != '') {
                var selectTag = '<div class="subcategory-select-container m-t-5" data-level="' + newLevel + '"><select class="select2 form-control subcategory-select" data-level="' + newLevel + '" name="category_id[]" onchange="getSubCategoriesDashboard(this.value,' + newLevel + ',' + langId + ');">' +
                    '<option value="">' + MdsConfig.text.selectCategory + '</option>' + response.htmlContent + '</select></div>';
                $('#category_select_container').append(selectTag);
            }
        }
    });
}
