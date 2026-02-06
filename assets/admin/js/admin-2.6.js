//custom scrollbar
$(function () {
    $('.custom-scrollbar').overlayScrollbars({});
    $('.sidebar-scrollbar').overlayScrollbars({});
});

//check all checkboxes
$("#checkAll").click(function () {
    $('input:checkbox').not(this).prop('checked', this.checked);
});

$('.increase-count').each(function () {
    $(this).prop('Counter', 0).animate({
        Counter: $(this).text()
    }, {
        duration: 1000,
        easing: 'swing',
        step: function (now) {
            $(this).text(Math.ceil(now));
        }
    });
});

//show hide delete button
$('.checkbox-table').click(function () {
    if ($(".checkbox-table").is(':checked')) {
        $(".btn-table-delete").show();
    } else {
        $(".btn-table-delete").hide();
    }
});

//get blog categories
function getBlogCategoriesByLang(val) {
    var data = {
        'lang_id': val
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/getBlogCategoriesByLang'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                $('#categories').children('option:not(:first)').remove();
                $("#categories").append(response.options);
            }
        }
    });
}

//approve selected edited products
function approveSelectedEditedProducts(message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var productIds = [];
            $("input[name='checkbox-table']:checked").each(function () {
                productIds.push(this.value);
            });
            var data = {
                'product_ids': productIds,
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Product/approveSelectedEditedProducts'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//delete selected products
function deleteSelectedProducts(message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var productIds = [];
            $("input[name='checkbox-table']:checked").each(function () {
                productIds.push(this.value);
            });
            var data = {
                'product_ids': productIds,
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Product/deleteSelectedProducts'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//delete selected products permanently
function deleteSelectedProductsPermanently(message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var productIds = [];
            $("input[name='checkbox-table']:checked").each(function () {
                productIds.push(this.value);
            });
            var data = {
                'product_ids': productIds,
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Product/deleteSelectedProductsPermanently'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//remove from featured
function removeFromFeatured(val) {
    var data = {
        'product_id': val,
        'is_ajax': 1
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Product/addRemoveFeaturedProduct'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
}

//add remove special offer
function addRemoveSpecialOffer(val) {
    var data = {
        'product_id': val
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Product/addRemoveSpecialOffer'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
}

//delete item
function deleteItem(url, id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'id': id,
            };
            $.ajax({
                type: 'POST',
                url: generateUrl(url),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//perform action
function performAction(url, id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'id': id,
            };
            $.ajax({
                type: 'POST',
                url: generateUrl(url),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//confirm user email
function confirmUserEmail(id) {
    var data = {
        'id': id,
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Membership/confirmUserEmail'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
};

//ban remove user ban
function banRemoveBanUser(id) {
    var data = {
        'id': id,
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Membership/banRemoveBanUser'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
};


//add delete user affiliate program
function addDeleteUserAffiliateProgram(id) {
    var data = {
        'id': id,
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Membership/addDeleteUserAffiliateProgram'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
};

//get countries by continent
function getCountriesByContinent(key, firstOption = null) {
    var data = {
        'key': key
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/getCountriesByContinent'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                $('#select_countries option').remove();
                $('#select_states option').remove();
                if (firstOption) {
                    $("#select_countries").append('<option value="0">' + firstOption + '</option>');
                }
                $("#select_countries").append(response.options);
            }
        }
    });
}

//get states by country
function getStatesByCountry(val, firstOption = null) {
    var data = {
        'country_id': val
    };
    console.log(data)
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/getStatesByCountry'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                $('#select_states option').remove();
                if (firstOption) {
                    $("#select_states").append('<option value="0">' + firstOption + '</option>');
                }
                $("#select_states").append(response.options);
            }
        }
    });
}

//activate inactivate countries
function activateInactivateCountries(action) {
    var data = {
        'action': action
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Admin/activateInactivateCountries'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
};

//get states
function getStates(val) {
    $('#select_states').children('option').remove();
    $('#select_cities').children('option').remove();
    $('#get_states_container').hide();
    $('#get_cities_container').hide();
    var data = {
        'country_id': val,
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/getStates'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                document.getElementById("select_states").innerHTML = response.content;
                $('#get_states_container').show();
            } else {
                document.getElementById("select_states").innerHTML = '';
                $('#get_states_container').hide();
            }
        }
    });
}

//get cities
// function getCities(val) {
//     var data = {
//         'state_id': val,
//     };
//     $.ajax({
//         type: 'POST',
//         url: generateUrl('Ajax/getCities'),
//         data: data,
//         success: function (response) {
//             if (response.result == 1) {
//                 document.getElementById("select_cities").innerHTML = response.content;
//                 $('#get_cities_container').show();
//             } else {
//                 document.getElementById("select_cities").innerHTML = '';
//                 $('#get_cities_container').hide();
//             }
//         }
//     });
// }
// $('.select2').on('select2:opening', function (e) {
//     const id = this.id;
//     console.log(id);

//     if (id && id.startsWith('select_provinces_')) {
//         console.log("opening", 'select_provinces_');
//     }
// });
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
                $('#select_provinces'+idSuffix).siblings('input[name="province_id"]').val(provSelect.value);
                $('#select_provinces'+idSuffix).siblings('input[name="province_name"]').val(provSelect.options[provSelect.selectedIndex].text);
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

                $('#select_cities'+idSuffix).siblings('input[name="city_id_modif"]').val(selectedCityId);
                $('#select_cities'+idSuffix).siblings('input[name="city_name_modif"]').val(selectedCityName);

                // Debugging di console
                console.log("Hidden City ID Updated:", citySelect);
                console.log("Hidden City Name Updated:", selectedCityName); 
            }
        },
        error: function () {
            alert("Gagal memuat data distrik")
        }
    });
}

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
//approve product
function approveProduct(id) {
    var data = {
        'id': id,
        'isAjax': true
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Product/approveProduct'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
};

//restore product
function restoreProduct(id) {
    var data = {
        'id': id,
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Product/restoreProduct'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
}

//delete attachment
function deleteSupportAttachment(id) {
    var data = {
        'id': id,
        'ticket_type': 'admin'
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

//change ticket status
function changeTicketStatus(id, status) {
    var data = {
        'id': id,
        'status': status
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('SupportAdmin/changeTicketStatusPost'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
}

function getSubCategories(parentId, level, divContainer = 'category_select_container') {
    level = parseInt(level);
    var newLevel = level + 1;
    var data = {
        'parent_id': parentId,
        'lang_id': MdsConfig.sysLangId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/getSubCategories'),
        data: data,
        success: function (response) {
            $('.subcategory-select-container').each(function () {
                if (parseInt($(this).attr('data-level')) > level) {
                    $(this).remove();
                }
            });
            if (response.result == 1 && response.htmlContent != '') {
                var selectTag = '<div class="subcategory-select-container m-t-5" data-level="' + newLevel + '"><select class="select2 form-control subcategory-select" data-level="' + newLevel + '" name="category_id[]" onchange="getSubCategories(this.value,' + newLevel + ',\'' + divContainer + '\');">' +
                    '<option value="">' + MdsConfig.text.none + '</option>' + response.htmlContent + '</select></div>';
                $('#' + divContainer).append(selectTag);
            }
        }
    });
}

//get filter subcategories
function getFilterSubCategories(val) {
    var data = {
        'parent_id': val
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Ajax/getSubCategories'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                $('#subcategories').children('option:not(:first)').remove();
                $("#subcategories").append(response.htmlContent);
            }
        }
    });
}

function showPreviewImage(input) {
    var name = $(input).attr('name');
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#img_preview_' + name).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

//delete selected reviews
function deleteSelectedReviews(message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var reviewIds = [];
            $("input[name='checkbox-table']:checked").each(function () {
                reviewIds.push(this.value);
            });
            var data = {
                'review_ids': reviewIds
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Product/deleteSelectedReviews'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//approve selected comments
function approveSelectedComments() {
    var commentIds = [];
    $("input[name='checkbox-table']:checked").each(function () {
        commentIds.push(this.value);
    });
    var data = {
        'comment_ids': commentIds
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Product/approveSelectedComments'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
};

//delete selected comments
function deleteSelectedComments(message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var commentIds = [];
            $("input[name='checkbox-table']:checked").each(function () {
                commentIds.push(this.value);
            });
            var data = {
                'comment_ids': commentIds
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Product/deleteSelectedComments'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//approve selected comments
function approveSelectedBlogComments() {
    var commentIds = [];
    $("input[name='checkbox-table']:checked").each(function () {
        commentIds.push(this.value);
    });
    var data = {
        'comment_ids': commentIds
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Blog/approveSelectedComments'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
};

//delete selected blog comments
function deleteSelectedBlogComments(message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var commentIds = [];
            $("input[name='checkbox-table']:checked").each(function () {
                commentIds.push(this.value);
            });
            var data = {
                'comment_ids': commentIds
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Blog/deleteSelectedComments'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//edit custom field option
$(document).on("input", ".input-custom-field-option", function () {
    var input = $(this);
    var data = {
        'option_text': input.val(),
        'option_id': input.attr('data-option-id'),
        'lang_id': input.attr("data-lang-id")
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Category/editCustomFieldOptionPost'),
        data: data,
        success: function (response) {
            console.log(response);
            input.addClass('flash-background-color');
            setTimeout(function () {
                input.removeClass('flash-background-color');
            }, 500);
        }
    });
});

//delete custom field option
function deleteCustomFieldOption(message, id) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'id': id
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Category/deleteCustomFieldOption'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//delete custom field category
function deleteCategoryFromField(message, fieldId, categoryId) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'field_id': fieldId,
                'category_id': categoryId
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Category/deleteCategoryFromField'),
                data: data,
                success: function (response) {
                    location.reload();
                }
            });
        }
    });
};

//approve bank transfer
function approveBankTransfer(id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'report_id': id,
                'option': 'approved'
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Admin/bankTransferOptionsPost'),
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

//remove by homepage manager
function removeItemHomepageManager(categoryId, submit) {
    var data = {
        'submit': submit,
        'category_id': categoryId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Admin/homepageManagerPost'),
        data: data,
        success: function (response) {
            location.reload();
        }
    });
};

//update featured category order
$(document).on("input", ".input-featured-categories-order", function () {
    var input = $(this);
    var val = input.val();
    var categoryId = $(this).attr("data-category-id");
    var data = {
        'order': val,
        'category_id': categoryId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Category/editFeaturedCategoriesOrderPost'),
        data: data,
        success: function (response) {
            input.addClass('flash-background-color');
            setTimeout(function () {
                input.removeClass('flash-background-color');
            }, 500);
        }
    });
});

//update homepage category order
$(document).on("input", ".input-index-categories-order", function () {
    var input = $(this);
    var val = input.val();
    var categoryId = $(this).attr("data-category-id");
    var data = {
        'order': val,
        'category_id': categoryId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Category/editIndexCategoriesOrderPost'),
        data: data,
        success: function (response) {
            input.addClass('flash-background-color');
            setTimeout(function () {
                input.removeClass('flash-background-color');
            }, 500);
        }
    });
});

//update exchange rate
$(document).on('input', '.input-exchange-rate', function () {
    var input = $(this);
    var val = input.val();
    var currencyId = $(this).attr('data-currency-id');
    var data = {
        'exchange_rate': val,
        'currency_id': currencyId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Admin/updateCurrencyRate'),
        data: data,
        success: function (response) {
            input.addClass('flash-background-color');
            setTimeout(function () {
                input.removeClass('flash-background-color');
            }, 500);
        }
    });
});

//get knowledge base categories by lang
function getKnowledgeBaseCategoriesByLang(val) {
    var data = {
        'lang_id': val
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('SupportAdmin/getCategoriesByLang'),
        data: data,
        success: function (response) {
            if (response.result == 1) {
                $('#categories').children('option').remove();
                $("#categories").append(response.options);
            }
        }
    });
}

$(document).on('click', '.btn-export-data', function () {
    var dateExportForm = $(this).attr("data-export-form");
    var dateExportType = $(this).attr("data-export-type");
    var dateExportFileType = $(this).attr("data-export-file-type");

    var formAction = '';
    if ($(this).attr("data-section") && $(this).attr("data-section") == 'vn') {
        formAction = generateUrl('Dashboard/exportTableDataPost');
    } else {
        formAction = generateUrl('File/exportTableDataPost');
    }

    var form = document.getElementById(dateExportForm);
    //csrf
    var inputCsrf = document.createElement('input');
    inputCsrf.type = 'hidden';
    inputCsrf.name = MdsConfig.csrfTokenName;
    inputCsrf.value = $('meta[name="X-CSRF-TOKEN"]').attr('content');
    form.appendChild(inputCsrf);
    //language
    var inputLang = document.createElement('input');
    inputLang.type = 'hidden';
    inputLang.name = 'lang_id';
    inputLang.value = MdsConfig.sysLangId;
    form.appendChild(inputLang);
    //data export type
    var inputDateExType = document.createElement('input');
    inputDateExType.type = 'hidden';
    inputDateExType.name = 'data_export_type';
    inputDateExType.value = dateExportType;
    form.appendChild(inputDateExType);
    //data export file type
    var inputDataExFileType = document.createElement('input');
    inputDataExFileType.type = 'hidden';
    inputDataExFileType.name = 'data_export_file_type';
    inputDataExFileType.value = dateExportFileType;
    form.appendChild(inputDataExFileType);
    //back url
    var inputBackUrl = document.createElement('input');
    inputBackUrl.type = 'hidden';
    inputBackUrl.name = 'back_url';
    inputBackUrl.value = MdsConfig.backUrl;
    form.appendChild(inputBackUrl);
    //submit
    var oldAction = form.action;
    var oldMethod = form.method;
    form.action = formAction;
    form.method = 'POST';
    form.submit();
    form.action = oldAction;
    form.method = oldMethod;
});

$('#selected_system_marketplace').on('ifChecked', function () {
    $('.system-currency-select').show();
});
$('#selected_system_classified_ads').on('ifChecked', function () {
    $('.system-currency-select').hide();
});

$(document).ready(function () {
    $('.magnific-image-popup').magnificPopup({type: 'image'});
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
            val = val.replace(',', '.');
            if ($.isNumeric(val) && val != 0) {
                $(this).removeClass('is-invalid');
            } else {
                e.preventDefault();
                $(this).addClass('is-invalid');
                $(this).focus();
            }
        });
    });
});

function deleteChatMessage(id, message) {
    Swal.fire(swalOptions(message)).then((result) => {
        if (result.isConfirmed) {
            var data = {
                'id': id,
            };
            $.ajax({
                type: 'POST',
                url: generateUrl('Admin/deleteChatMessagePost'),
                data: data,
                success: function (response) {
                    $('#message-row-' + id).remove();
                }
            });
        }
    });
};

//delete category image
function deleteCategoryImage(categoryId) {
    var data = {
        'category_id': categoryId
    };
    $.ajax({
        type: 'POST',
        url: generateUrl('Category/deleteCategoryImagePost'),
        data: data,
        success: function (response) {
            $(".img-category").remove();
            $(".btn-delete-category-img").hide();
        }
    });
};

if ($('#select2-users-container').length) {
    $(function () {
        $('.select2-users').select2({
            placeholder: MdsConfig.text.select,
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                type: 'POST',
                url: MdsConfig.baseUrl + '/Ajax/loadUsersDropdown',
                dataType: 'json',
                method: 'POST',
                data: function (params) {
                    return {q: params.term};
                },
                processResults: function (data) {
                    return {
                        results: data.items.map(function (item) {
                            return {
                                id: item.id,
                                text: item.id + ': ' + item.username
                            };
                        })
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            language: {
                noResults: function () {
                    return MdsConfig.text.noResultsFound;
                },
                searching: function () {
                    return MdsConfig.text.searching;
                },
                inputTooShort: function (args) {
                    return MdsConfig.text.enterTwoCharacters;
                },
                errorLoading: function () {
                    return MdsConfig.text.noResultsFound;
                }
            },
            dir: MdsConfig.directionality
        });
    });
} else {
    $(document).on('ready ajaxComplete', function () {
        $('.select2').select2({
            height: 40,
            language: {
                noResults: function () {
                    return MdsConfig.text.noResultsFound;
                },
                searching: function () {
                    return MdsConfig.text.searching;
                },
                inputTooShort: function (args) {
                    return MdsConfig.text.enterTwoCharacters;
                },
                errorLoading: function () {
                    return MdsConfig.text.noResultsFound;
                }
            },
            dir: MdsConfig.directionality
        });
    });
}

$(document).on('input keyup paste', '.number-spinner input', function () {
    var val = $(this).val();
    val = parseInt(val);
    if (val < 1) {
        val = 1;
    }
    $(this).val(val);
});

$(document).on("input", ".price-input", function () {
    const $input = $(this);
    const raw = $input.val().trim();
    const decimalSeparator = $input.attr('lang') === 'de-DE' ? ',' : '.';

    const normalized = decimalSeparator === ',' ? raw.replace(',', '.') : raw;

    const isValidFormat = /^(\d+)(\.\d{1,2})?$/.test(normalized);
    const price = parseFloat(normalized);

    if (isValidFormat && !isNaN(price) && price > 0 && price <= 999999.99) {
        $input.removeClass('is-invalid');
    } else {
        $input.addClass('is-invalid');
    }
});


$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

//sanitize url
function sanitizeUrl(url) {
    url = url.replace(/&amp;/g, '&');
    const validUrlPattern = /^[a-zA-Z0-9-._~:/?#[\]@!$&'()*+,;%=]+$/;
    if (!validUrlPattern.test(url)) {
        return '';
    }
    if (url.toLowerCase().includes("javascript:")) {
        return '';
    }
    let urlObj = new URL(url);
    let params = new URLSearchParams(urlObj.search);
    params.forEach((value, key) => {
        if (params.getAll(key).length > 1) {
            params.delete(key);
            params.append(key, value);
        }
    });
    urlObj.search = params.toString();
    return urlObj.href;
}

//add back url to the forms
$(document).ready(function () {
    $('form[method="post"]').each(function () {
        if ($(this).find('input[name="back_url"]').length === 0) {
            let backUrl = window.location.href;
            backUrl = sanitizeUrl(backUrl);
            if (backUrl) {
                $(this).append('<input type="hidden" name="back_url" value="' + backUrl + '">');
            }
        }
    });
});

document.querySelectorAll('.image-input').forEach(function (input) {
    input.addEventListener('change', function (event) {
        const file = event.target.files[0];
        const previewSelector = input.getAttribute('data-preview');
        const preview = document.querySelector(previewSelector);

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    });
});

$(document).ready(function () {
    $('.input-tagify').each(function () {
        new Tagify(this, {
            enforceWhitelist: false,
            placeholder: MdsConfig.text.typeTag
        });
    });
});

//generate text with ai
$(document).on('submit', '#formAIWriter', function (e) {
    e.preventDefault();
    $('.buttons-ai-writer button').prop('disabled', true);
    //reset
    $('#generatedContentAIWriter').html('');
    $('#generatedContentAIWriter').hide();

    var form = $(this);
    var topic = form.find("textarea[name='topic']").val();
    if (!topic || topic.trim() === '') {
        $('.buttons-ai-writer button').prop('disabled', false);
        Swal.fire({text: MdsConfig.text.topicEmpty, icon: 'warning', confirmButtonText: MdsConfig.text.ok});
        return false;
    }
    $('#spinnerAIWriter').show();
    var formData = form.serializeArray();
    formData = setSerializedData(formData);
    $.ajax({
        url: generateUrl('Ajax/generateTextAI'),
        type: 'POST',
        data: formData,
        success: function (response) {
            $('.buttons-ai-writer button').prop('disabled', false);
            $('#spinnerAIWriter').hide();
            if (response.status === 'error') {
                Swal.fire({text: response.message, icon: 'warning', confirmButtonText: MdsConfig.text.ok});
            } else if (response.status === 'success') {
                $('#generatedContentAIWriter').html(response.content);
                $('#generatedContentAIWriter').show();
                $('#btnAIGenerate').hide();
                $('#btnAIRegenerate').show();
                $('#btnAIUseText').show();
                $('#btnAIReset').show();
            } else {
                console.error("Unexpected response format.");
            }
        },
        error: function (error) {
            $('.buttons-ai-writer button').prop('disabled', false);
        }
    });
});

//add ai content to editor
$(document).on('click', '#btnAIUseText', function () {
    const content = $('#generatedContentAIWriter').html().trim();
    const editorId = 'editor_main';
    const editor = tinymce.get(editorId);

    if (content && editor) {
        editor.execCommand('mceInsertContent', false, content);
        $('#modalAiWriter').modal('hide');
        resetFormAIWriter();
    } else {
        console.log('TinyMCE editor not found or content is empty.');
    }
});

//reset ai writer form
function resetFormAIWriter() {
    $('#formAIWriter')[0].reset();
    $('#generatedContentAIWriter').html('');
    $('#generatedContentAIWriter').hide();
    $('#btnAIGenerate').show();
    $('#btnAIRegenerate').hide();
    $('#btnAIUseText').hide();
    $('#btnAIReset').hide();
}

//toggle commission mode
$(document).ready(function () {
    const $select = $('#commission_mode');
    const $input = $('#custom_commission_input');
    if ($select.length && $input.length) {
        const toggleCommissionInput = function () {
            $input.toggle($select.val() === 'custom');
        };
        toggleCommissionInput();
        $select.on('change.select2 change', toggleCommissionInput);
    }
});

