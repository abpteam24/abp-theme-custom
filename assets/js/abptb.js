let abp_route_info = JSON.parse(abp_infos.route_info);
let abp_location_info = JSON.parse(abp_infos.location_info);
(function ($) {
    "use strict";
    $(document).ready(function () {
        $(document).find('div.abp_area .abp_search_form').each(function () {
            load_bp($(this));
        })
    });
    //console.log(abp_route_info);
    function load_bp(parent) {
        let post_id = $.trim(parent.find('[name="post_id"]').val());
        if (!abp_route_info) return;
        parent.find('.abp_bp ul').html('');
        parent.find('.abp_dp ul').html('');
        parent.find('.abp_bp input').val('');
        parent.find('.abp_dp input').val('');
        let rawData;
        if (post_id) {
            if (abp_route_info[post_id]) {
                let postData = abp_route_info[post_id];
                rawData = postData['all_bp'] || rawData;
            }
        } else {
            rawData = abp_route_info['all_bp'];
        }
        if (!rawData) return;
        let routes = Array.isArray(rawData) ? rawData : Object.values(rawData).flat();
        if (!Array.isArray(routes)) return;
        let locationMap = {};
        if (Array.isArray(abp_location_info)) {
            abp_location_info.forEach(loc => {
                locationMap[loc.id.toString()] = loc.label;
            });
            let optionsHtml = routes.map(slot => {
                let label = locationMap[slot.toString()] || '';
                return `<li data-value="${slot}" data-text="${label}"><span class="fas fa-map-marker-alt _mar_r_xxs"></span>${label}</li>`;
            }).join('');
            parent.find('.abp_bp ul').html(optionsHtml);
        }
    }
    function load_dp(parent) {
        if (typeof abp_route_info === 'undefined' || !abp_route_info) return;
        let post_id = $.trim(parent.find('[name="post_id"]').val());
        let bp = $.trim(parent.find('[name="_bp"]').val());
        parent.find('.abp_dp input').val('');
        if (!bp) {
            parent.find('.abp_dp ul').html('');
            return;
        }
        let validDpList = new Set();
        let extractDpFromObject = (routeObj) => {
            if (routeObj && typeof routeObj === 'object' && routeObj[bp]) {
                let dpArray = routeObj[bp]; // BP key-er bhetorer DP array
                if (Array.isArray(dpArray)) {
                    dpArray.forEach(dpId => validDpList.add(dpId.toString()));
                }
            }
        };
        if (post_id) {
            if (abp_route_info[post_id]) {
                extractDpFromObject(abp_route_info[post_id]);
            }
        } else {
            Object.keys(abp_route_info).forEach(key => {
                if (key !== 'all_bp') {
                    if (abp_route_info[key]) {
                        extractDpFromObject(abp_route_info[key]);
                    }
                }
            });
        }
        let locationMap = {};
        if (typeof abp_location_info !== 'undefined' && Array.isArray(abp_location_info)) {
            abp_location_info.forEach(loc => {
                locationMap[loc.id.toString()] = loc.label;
            });
        }
        let optionsHtml = Array.from(validDpList).map(slot => {
            let label = locationMap[slot] || '';
            return `<li class="_gap_xxs" data-value="${slot}" data-text="${label}"><span class="fas fa-map-marker-alt"></span>${label}</li>`;
        }).join('');
        parent.find('.abp_dp ul').html(optionsHtml);
    }
    $(document).on('click', 'div.abp_booking .pagination_item .select_post', function (e) {
        e.preventDefault();
        let post_id = parseInt($(this).attr('data-post_id'));
        let target = $(this).closest('div.abp_area').find('#abp_search_area .post_selection .dropdown_list li');
        target.each(function () {
            if (parseInt($(this).attr('data-value')) === post_id) {
                $(this).trigger('click');
                return true;
            }
        });
    });
    $(document).on('abp_trigger', 'div.abp_area .abp_search_form [name="post_id"]', function () {
        let parent = $(this).closest(".abp_search_form");
        let target_return = parent.find('.return_date');
        let target_journey = parent.find('.journey_date');
        let target_bp_dp = parent.find('.abp_bp_dp');
        load_bp(parent);
        let post_id = parseInt(parent.find('[name="post_id"]').val());
        if (target_bp_dp.length > 0 && (isNaN(post_id) || post_id < 1)) {
            target_bp_dp.slideUp('fast').html('');
        } else {
            let action = target_journey.length > 0 ? 'abp_load_date' : 'abp_load_route';
            let formData = new FormData();
            formData.append('post_id', post_id);
            formData.append('action', action);
            formData.append('nonce', abp_infos.nonce);
            $.ajax({
                type: 'POST', url: abp_infos.ajax_url, contentType: false, processData: false, data: formData,
                beforeSend: function () {
                    abp_spinner(target_return);
                    abp_spinner(target_journey);
                    abp_spinner(target_bp_dp);
                    abp_toast_msg(abp_infos.msg.loading);
                },
                success: function (response) {
                    abp_spinner_remove(target_journey);
                    abp_spinner_remove(target_return);
                    if (target_journey.length > 0 && response.data && response.data.hasOwnProperty('html_journey')) {
                        target_journey.html(response.data.html_journey).promise().done(function () {
                            if (response.data.hasOwnProperty('picker_config') && response.data.picker_config) {
                                abp_init_dynamic_date_pickers(response.data.journey, response.data.picker_config);
                            } else {
                                abp_load_datepicker(target_journey);
                            }
                        });
                        if (target_return.length > 0 && response.data.hasOwnProperty('html_return') && response.data.html_return) {
                            target_return.slideDown('fast');
                            target_return.html(response.data.html_return).promise().done(function () {
                                if (response.data.hasOwnProperty('picker_config') && response.data.picker_config) {
                                    abp_init_dynamic_date_pickers(response.data.return, response.data.picker_config);
                                } else {
                                    abp_load_datepicker(target_return);
                                }
                            });
                        } else {
                            target_return.slideUp('fast');
                        }
                    }
                    if (target_bp_dp.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                        target_bp_dp.slideDown('fast').html(response.data.html)
                    }
                    abp_toast_msg(response.data.msg, response.data.type);
                }, error: function (xhr) {
                    abp_ajx_error(xhr, target_journey);
                }
            });
        }
    });
    $(document).on('abp_trigger', 'div.abp_area .abp_search_form [name="_bp"]', function () {
        load_dp($(this).closest('.abp_search_form'));
    });
    $(document).on('change', "div.abp_area .abp_search_form [name='journey_date']", function (e) {
        e.preventDefault();
        let parent = $(this).closest(".abp_search_form");
        let target = parent.find('.return_date');
        if (target.length > 0) {
            let date = parent.find('[name="journey_date"]').val();
            let post_id = parseInt(parent.find('[name="post_id"]').val());
            if (post_id && post_id > 0) {
                let formData = new FormData();
                formData.append('post_id', post_id);
                formData.append('journey_date', date);
                formData.append('action', 'abp_load_return_date');
                formData.append('nonce', abp_infos.nonce);
                $.ajax({
                    type: 'POST', url: abp_infos.ajax_url, contentType: false, processData: false, data: formData,
                    beforeSend: function () {
                        abp_spinner(target);
                        abp_toast_msg(abp_infos.msg.end_date_loading);
                    },
                    success: function (response) {
                        abp_spinner_remove(target);
                        if (response.data && response.data.hasOwnProperty('html')) {
                            target.html(response.data.html).promise().done(function () {
                                if (response.data.hasOwnProperty('picker_config') && response.data.picker_config) {
                                    abp_init_dynamic_date_pickers(response.data.selector, response.data.picker_config);
                                } else {
                                    abp_load_datepicker(target);
                                }
                            });
                            abp_toast_msg(response.data.msg, response.data.type);
                        } else {
                            abp_toast_msg(response.data.msg, response.data.type);
                        }
                    }, error: function (xhr) {
                        abp_ajx_error(xhr, target);
                    }
                });
            }
        }
    });
    $(document).on('submit', 'div.abp_area #abp_search_area form.abp_search_form', function (e) {
        e.preventDefault();
        let form_area = $(this).closest('#abp_search_area');
        if ($.trim(form_area.find('[name="_bp"]').val()).length === 0) {
            setTimeout(function () {
                abp_toast_msg(abp_infos.msg.bp_select);
                form_area.find('[name="_bp"]').siblings('input').click();
            }, 100);
            return;
        }
        if ($.trim(form_area.find('[name="_dp"]').val()).length === 0) {
            setTimeout(function () {
                abp_toast_msg(abp_infos.msg.dp_select);
                form_area.find('[name="_dp"]').siblings('input').click();
            }, 100);
            return;
        }
        let post_id = parseInt(form_area.find('[name="post_id"]').val());
        if (post_id && post_id > 0) {
            if ($.trim(form_area.find('[name="journey_date"]').val()).length === 0) {
                setTimeout(function () {
                    abp_toast_msg(abp_infos.msg.select_journey_date);
                    form_area.find('#journey_date').focus();
                }, 100);
                return;
            }
        }
        let target = $(this).closest('div.abp_area').find('.abp_booking');
        let formData = new FormData(this);
        formData.append('action', 'abp_global_booking');
        formData.append('nonce', abp_infos.nonce);
        $.ajax({
            type: 'POST', url: abp_infos.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abp_spinner(target);
                abp_spinner(form_area);
                abp_toast_msg(abp_infos.msg.loading);
            },
            success: function (response) {
                abp_spinner_remove(target);
                abp_spinner_remove(form_area);
                abp_toast_msg(response.data.msg, response.data.type);
                if (response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html).promise().done(function () {
                        abp_init(target);
                    });
                } else {
                    window.location.reload();
                }
            }, error: function (xhr) {
                abp_ajx_error(xhr, target);
            }
        });
    });
    //==============//
    $(document).on('change', "div.abp_booking [name='bp_time'] ,div.abp_booking [name='return_bp_time'] ,div.abp_booking [name='sp_id'] ,div.abp_booking [name='return_sp_id']", function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let target = $(this).closest(".booking_area");
        let parent = target.closest("form");
        let formData = abp_get_form_data(target);
        formData.append('post_id', parent.find('[name="post_id"]').val());
        formData.append('double_route', parent.find('[name="double_route"]').val());
        formData.append('action', 'abp_load_transport_data');
        formData.append('nonce', abp_infos.nonce);
        $.ajax({
            type: 'POST', url: abp_infos.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abp_spinner(target);
                abp_toast_msg(abp_infos.msg.loading);
            },
            success: function (response) {
                abp_spinner_remove(target);
                //console.log(response);
                abp_toast_msg(response.data.msg, response.data.type);
                if (response.data && response.data.hasOwnProperty('html') && target.length > 0) {
                    target.html(response.data.html).promise().done(function () {
                        abp_init(target);
                        all_management(target);
                    });
                }
            }, error: function (xhr) {
                abp_ajx_error(xhr, target);
            }
        });
    });
    $(document).on('abp_trigger', "div.abp_booking [name='item_check[]'],div.abp_booking [name='return_item_check[]']", function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let $this = $(this);
        let parent = $this.closest(".booking_area");
        let form = $this.closest("form");
        let seat_type = $.trim(form.find('[name="seat_type"]').val());
        let max_qty = parseInt(form.find('[name="max_qty"]').val());
        let r = parent.hasClass('return');
        let prefix = r ? 'return_' : '';
        let data_id = $this.attr('data-id');
        let target = parent.find('[data-collapse="' + data_id + '"]');
        let item_parent = $this.closest('.ticket_item');
        if (!item_parent.hasClass('abp_active')) {
            let min_qty = parseInt(item_parent.find(`[name="${prefix}item_qty[]"]`).attr('data-min'), 10) || 0;
            item_parent.find(`[name="${prefix}item_qty[]"]`).val(min_qty);
        }
        let qty = get_quantity(parent, seat_type, prefix);
        if (max_qty > 0 && qty > max_qty) {
            item_parent.find('[data-checked]').trigger('abp_role_back');
            abp_toast_msg(form.find('[name="max_qty"]').attr('data-msg'), 'warn');
        } else {
            if (target.length > 0) {
                target.slideToggle('fast');
                item_parent.toggleClass('abp_active');
            }
        }
        item_parent.find(`[name="${prefix}item_qty[]"]`).trigger('change');
    });
    $(document).on('change', 'div.abp_booking [name="item_qty[]"],div.abp_booking [name="return_item_qty[]"]', function (e) {
        e.preventDefault();
        let $this = $(this);
        let parent = $this.closest(".booking_area");
        let form = $this.closest("form");
        let seat_type = $.trim(form.find('[name="seat_type"]').val());
        let max_qty = parseInt(form.find('[name="max_qty"]').val());
        let r = parent.hasClass('return');
        let prefix = r ? 'return_' : '';
        let qty = get_quantity(parent, seat_type, prefix);
        if (max_qty > 0 && qty > max_qty) {
            $this.val(parseInt($this.val()) - 1);
            abp_toast_msg(form.find('[name="max_qty"]').attr('data-msg'), 'warn');
        }
        all_management($this);
    })
    $(document).on('change', 'div.abp_booking .ex_price_calculate', function (e) {
        e.preventDefault();
        all_management($(this));
    });
    $(document).on('click', 'div.abp_booking .sp_cell.available', function (e) {
        e.preventDefault();
        let current = $(this);
        current.toggleClass('selected').promise().done(function () {
            all_management(current);
        });
    });
    $(document).on('click', 'div.abp_booking .seat_remove', function (e) {
        e.preventDefault();
        const $current = $(this);
        const seatName = $current.data('name') || $current.attr('data-name');
        const $parent = $current.closest('.booking_area');
        if (!seatName || !$parent.length) return;
        $parent.find(`.sp_cell.available.selected[data-name="${seatName}"]`).trigger('click');
    });
    $(document).on('click', 'div.abp_booking .book_continue', function (e) {
        e.preventDefault();
        let current = $(this);
        let form = current.closest("form");
        let parent = form.find('.booking_area:not(.booking_area.return)');
        let return_parent = form.find('.booking_area.return');
        let seat_type = $.trim(form.find('[name="seat_type"]').val());
        if (get_quantity(parent, seat_type) > 0 || get_quantity(return_parent, seat_type, 'return_') > 0) {
            if (submit_validation(current) < 1) {
                form.find("[name='add-to-cart']").trigger('click');
                form.find("[name='add-admin-order']").trigger('click');
                form.closest('div.abp_area').find('form.abp_search_form').submit();
            }
        } else {
            abp_alert(current);
        }
    });
    function all_management($this) {
        let form = $this.closest("form");
        let parent = form.find('.booking_area:not(.booking_area.return)');
        let return_parent = form.find('.booking_area.return');
        let seat_type = $.trim(form.find('[name="seat_type"]').val());
        let total = 0;
        let qty = get_quantity(parent, seat_type);
        let return_qty = get_quantity(return_parent, seat_type, 'return_');
        let total_qty = qty + return_qty;
        let price = 0;
        let return_price = 0;
        let ex_price = 0;
        let return_ex_price = 0;
        if (total_qty > 0) {
            if (qty > 0) {
                price = get_price(parent, seat_type);
                ex_price = get_additional_price(parent);
                form.find('.total_continue_area .price_up').slideDown('fast');
                parent.find('.additional_service_area').slideDown('fast');
                parent.find('.seat_selection').slideDown('fast');
            } else {
                form.find('.total_continue_area .price_up').slideUp('fast');
                form.find('.total_continue_area .ex_price_up').slideUp('fast')
                parent.find('.additional_service_area').slideUp('fast');
                parent.find('.seat_selection').slideUp('fast').find('.insert_item').html('');
            }
            if (return_qty > 0) {
                return_price = get_price(return_parent, seat_type, 'return_');
                return_ex_price = get_additional_price(return_parent, true);
                form.find('.total_continue_area .price_down').slideDown('fast');
                return_parent.find('.additional_service_area').slideDown('fast');
                return_parent.find('.seat_selection').slideDown('fast');
            } else {
                form.find('.total_continue_area .price_down').slideUp('fast');
                form.find('.total_continue_area .ex_price_down').slideUp('fast')
                return_parent.find('.additional_service_area').slideUp('fast');
                return_parent.find('.seat_selection').slideUp('fast').find('.insert_item').html('');
            }
            form.find('.price_up .item_total').html(price > 0 ? abp_wc_price_format(price) : abp_infos.msg.free);
            form.find('.price_down .item_total').html(return_price > 0 ? abp_wc_price_format(return_price) : abp_infos.msg.free);
            total = price + ex_price + return_price + return_ex_price;
            form.find('.total_continue_area').slideDown('fast');
        } else {
            form.find('.additional_service_area').slideUp('fast');
            form.find('.total_continue_area').slideUp('fast');
            form.find('.seat_selection').slideUp('fast').find('.insert_item').html('');
        }
        attendee_management(parent, qty);
        attendee_management(return_parent, return_qty);
        total = total > 0 ? abp_wc_price_format(total) : abp_infos.msg.free;
        form.find('.abp_total').html(total);
        //abp_load_image();
    }
    function attendee_management(parent, qty) {
        let target = parent.find('.client_info_area');
        let item = target.find('.attendee_item');
        let count = item.length;
        if (count > 0) {
            let single_attendee = parent.closest("form").find('[name="same_attendee"]').val();
            if (single_attendee !== 'on') {
                if (qty > 0) {
                    let firstItem = item.first();
                    if (count < qty) {
                        let needed = qty - count;
                        for (let i = 0; i < needed; i++) {
                            let newItem = firstItem.clone();
                            newItem.find('input:not([type="checkbox"]):not([type="radio"]), textarea').val('');
                            newItem.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
                            newItem.find('select').prop('selectedIndex', 0);
                            target.append(newItem);
                        }
                        abp_load_datepicker(target);
                    } else if (count > qty) {
                        item.slice(qty).remove();
                    }
                } else {
                    item.not(':first').remove();
                }
            }
        } else {
            item.not(':first').remove();
        }
        let reqFields = target.find('[data_req], [data-req]');
        if (qty > 0) {
            reqFields.prop('required', true);
        } else {
            reqFields.prop('required', false);
        }
    }
    function get_quantity(parent, seat_type, prefix = '') {
        let qty = 0;
        if (seat_type === 'sp') {
            qty = parent.find('.sp_cell.available.selected').length;
        } else {
            parent.find('.item_select').each(function () {
                let current = $(this);
                let active_property = $.trim(current.find(`[name="${prefix}item_check[]"]`).val());
                let item_qty = parseInt($.trim(current.find(`[name="${prefix}item_qty[]"]`).val()), 10) || 0;
                if (active_property && item_qty > 0) {
                    qty += item_qty;
                }
            });
        }
        return qty;
    }
    function get_price(parent, seat_type, prefix = '') {
        let total = 0;
        if (seat_type === 'sp') {
            let seat_names = [];
            let type_ids = [];
            let selection_target = parent.find('.seat_selection');
            let hidden_target = selection_target.find('.abp_hidden .delete_area');
            let target = selection_target.find('.insert_item');
            target.html('');
            parent.find('.seat_selection .insert_item').html('');
            parent.find('.sp_cell.available.selected').each(function () {
                let $this = $(this);
                let name = $.trim($this.attr('data-name'));
                let label = $.trim($this.attr('data-label'));
                let id = $.trim($this.attr('data-id'));
                if (name && id) {
                    let price = parseFloat($.trim($this.attr('data-price'))) || 0;
                    total += price;
                    seat_names.push(name);
                    type_ids.push(id);
                    let item_clone = hidden_target.clone();
                    item_clone.find('.seat_remove').attr('data-name', name);
                    item_clone.find('.seat_name').html(name + (label ? '(' + label + ')' : ''));
                    item_clone.find('.seat_price').html(abp_wc_price_format(price));
                    target.append(item_clone);
                }
            });
            selection_target.find('.sub_total').html(abp_wc_price_format(total));
            parent.find(`[name="${prefix}sp_selected_seat"]`).val(seat_names.join(','));
            parent.find(`[name="${prefix}sp_selected_seat_id"]`).val(type_ids.join(','));
        } else {
            parent.find('.item_select').each(function () {
                let current = $(this);
                let target_qty = current.find(`[name="${prefix}item_qty[]"]`);
                let active_property = $.trim(current.find(`[name="${prefix}item_check[]"]`).val());
                let item_qty = parseInt($.trim(target_qty.val()), 10) || 0;
                if (active_property && item_qty > 0) {
                    let price = parseFloat($.trim(target_qty.attr('data-price'))) || 0;
                    if (price > 0) {
                        total += price * item_qty;
                    }
                }
            });
        }
        return total;
    }
    function get_additional_price(parent, r = false) {
        let form = parent.closest('form');
        let target = parent.find('.ex_price_calculate');
        let total = 0;
        if (target.length > 0) {
            let ex_qty = 0;
            target.each(function () {
                let qty = parseInt($(this).val());
                ex_qty += qty;
                let ex_price = $(this).attr('data-price');
                ex_price = ex_price && ex_price >= 0 ? ex_price : 0;
                total = total + parseFloat(ex_price) * qty;
            });
            let total_price = total > 0 ? abp_wc_price_format(total) : abp_infos.msg.free;
            if (r) {
                if (ex_qty > 0) {
                    form.find('.total_continue_area .ex_price_down').slideDown('fast').find('.additional_total').html(total_price);
                } else {
                    form.find('.total_continue_area .ex_price_down').slideUp('fast').find('.additional_total').html(total_price);
                }
            } else {
                if (ex_qty > 0) {
                    form.find('.total_continue_area .ex_price_up').slideDown('fast').find('.additional_total').html(total_price);
                } else {
                    form.find('.total_continue_area .ex_price_up').slideUp('fast').find('.additional_total').html(total_price);
                }
            }
        }
        return total;
    }
    function submit_validation(current) {
        let exit = 0;
        current.closest('form').find("[required]").each(function () {
            let value = $(this).val();
            if (!value || value === ' ' || value === 'undefined' || value === '') {
                $(this).trigger('focus').addClass('abp_required');
                exit++;
            }
        });
        return exit;
    }
}(jQuery));