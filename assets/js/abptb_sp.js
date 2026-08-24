let abp_ticket_type = abp_sp_config.seat_type ? JSON.parse(abp_sp_config.seat_type) : null;
let abp_decor_item = abp_sp_config.decor_item ? JSON.parse(abp_sp_config.decor_item) : null;
(function ($) {
    'use strict';
    let seat_groups = [
        {id: 1, label: 'Default', color: '', prefix: '', icon: '🎟️', type: 'seat'},
        {id: 2, label: 'VIP', color: '#A78BFA', prefix: 'VIP-', icon: '👑', type: 'seat'},
        {id: 3, label: 'Business Class', color: '#0EA5E9', prefix: 'B-', icon: '🛋️', type: 'seat'},
        {id: 4, label: 'Special', color: '#6366F1', prefix: 'S-', icon: '⭐', type: 'seat'},
        {id: 5, label: 'Couple', color: '#C026D3', prefix: 'C-', icon: '💑', type: 'seat'},
        {id: 6, label: 'Female', color: '#F472B6', prefix: 'F-', icon: '👩', type: 'seat'},
        {id: 7, label: 'Adult', color: '#78350F', prefix: 'AD-', icon: '🧑', type: 'seat'},
        {id: 8, label: 'Child', color: '#F59E0B', prefix: 'CH-', icon: '👦', type: 'seat'},
        {id: 9, label: 'Economy', color: '#84CC16', prefix: 'E-', icon: '💺', type: 'seat'},
    ];
    abp_ticket_type = (abp_ticket_type && abp_ticket_type.length) ? abp_ticket_type : seat_groups;
    let decor_items = [
        {id: 1, label: 'Blank Space', color: 'transparent', icon: '', type: 'other'},
        {id: 2, label: 'Driver Seat', color: '#1E293B', icon: '👨‍✈️', type: 'other'},
        {id: 3, label: 'Door Entry', color: '#EAB308', icon: '🚪', type: 'other'},
        {id: 4, label: 'Stairs', color: '#64748B', icon: '🪜', type: 'other'},
        {id: 5, label: 'Aisle/Walkway', color: '#94A3B8', icon: '↔', type: 'other'},
        {id: 6, label: 'Window', color: '#38BDF8', icon: '🪟', type: 'other'},
        {id: 7, label: 'Engine Box', color: '#475569', icon: '⚙️', type: 'other'},
        {id: 8, label: 'Toilet', color: '#06B6D4', icon: '🚽', type: 'other'},
        {id: 9, label: 'Luggage Rack', color: '#F97316', icon: '🧳', type: 'other'},
        {id: 10, label: 'Food/Snacks', color: '#10B981', icon: '🍔', type: 'other'},
        {id: 11, label: 'Emergency Exit', color: '#EF4444', icon: '🚨', type: 'other'},
    ];
    abp_decor_item = (abp_decor_item && abp_decor_item.length) ? abp_decor_item : decor_items;
    let activeGroup = '';
    let lastClickedIndex = null;
    let isDraggingSelection = false;
    let dragSourceCell = null;
    let isCloningMode = false;
    let activeCellForSpan = null;
    /**********************************/
    let sp_parent = abp_parent.find('#abp_sp_builder');
    let initialLayout = sp_parent.find('#sp_saved_data').data('layout') || [];
    window.abp_sp_add = function (id = '', clone = '') {
        id = id !== '' ? parseInt(id) : '';
        let target = sp_parent.find('.sp_builder_area');
        $.ajax({
            type: 'POST', url: abp_sp_config.ajax_url, data: {
                action: 'abp_add_sp', id: id, clone: clone, 'nonce': abp_sp_config.nonce
            }, beforeSend: function () {
                abp_spinner(target);
                abp_toast_msg(abp_sp_config.msg.sp_loading);
            }, success: function (response) {
                abp_spinner_remove(target);
                if (response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html).promise().done(function () {
                        abp_init(target);
                        abp_sp_init();
                        setTimeout(function () {
                            abp_color_picker_init(target);
                        }, 50);
                        abp_toast_msg(response.data.msg, response.data.type);
                    });
                }
            }, error: function (xhr) {
                abp_spinner_remove(target);
                if (xhr.response && xhr.response.data) {
                    abp_toast_msg(xhr.response.data.msg, xhr.response.data.type);
                }
            }
        })
    };
    window.abp_sp_clear = function () {
        if (confirm(abp_sp_config.msg.sp_clear_confirm)) {
            initialLayout = [];
            let rows = parseInt(sp_parent.find('.sp_rows').val()) || 10;
            let cols = parseInt(sp_parent.find('.sp_cols').val()) || 10;
            let totalCells = rows * cols;
            for (let i = 0; i < totalCells; i++) {
                initialLayout.push({index: i, type: 'other', id: 1});
            }
            generateGrid();
            abp_toast_msg(abp_sp_config.msg.sp_clear)
        }
    };
    window.abp_sp_delete = function (id) {
        if (confirm(abp_sp_config.msg.sp_delete_confirm)) {
            id = id !== '' ? parseInt(id) : '';
            let parent = sp_parent.find('.sp_list');
            $.ajax({
                type: 'POST', url: abp_sp_config.ajax_url, data: {
                    "action": "abp_delete_sp", 'id': id, 'nonce': abp_sp_config.nonce
                }, beforeSend: function () {
                    abp_spinner(parent);
                    abp_toast_msg(abp_sp_config.msg.sp_deleting, 'error');
                }, success: function (response) {
                    abp_spinner_remove(parent);
                    if (parent && parent.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                        parent.html(response.data.html);
                        abp_toast_msg(response.data.msg, response.data.type);
                    }
                }
            });
        }
    };
    window.abp_sp_save = function () {
        syncLayoutFromDOM();
        let typeCounts = {};
        let duplicateCheckArray = [];
        let hasDuplicate = false;
        initialLayout.forEach(cell => {
            if (cell.type === 'seat' && cell.name) {
                if (duplicateCheckArray.includes(cell.name)) {
                    hasDuplicate = true;
                }
                duplicateCheckArray.push(cell.name);
                typeCounts[cell.id] = (typeCounts[cell.id] || 0) + 1;
            }
        });
        if (hasDuplicate) {
            alert("Cannot save! Duplicate seat names detected.");
            return;
        }
        let payload = {
            action: 'abp_save_sp',
            id: sp_parent.find('#sp_saved_data').data('id') || '',
            name: sp_parent.find('.sp_name').val() || '',
            bg_image: sp_parent.find('.image_selection input').val(),
            bg_color: sp_parent.find('input[name="bg_color"]').val(),
            rows: sp_parent.find('.sp_rows').val(),
            cols: sp_parent.find('.sp_cols').val(),
            width: sp_parent.find('.sp_width').val(),
            height: sp_parent.find('.sp_height').val(),
            gap: sp_parent.find('.sp_gap').val(),
            radius: sp_parent.find('.sp_radius').val(),
            layout_data: JSON.stringify(initialLayout),
            seat_info: JSON.stringify(typeCounts),
            nonce: abp_sp_config.nonce
        };
        $.ajax({
            type: 'POST', url: abp_sp_config.ajax_url, data: payload,
            beforeSend: function () {
                abp_spinner(sp_parent);
                abp_toast_msg(abp_sp_config.msg.sp_saving, 'info');
            }, success: function (response) {
                abp_spinner_remove(sp_parent);
                abp_toast_msg(response.data.msg, response.data.type);
                window.location.reload();
            }
        });
    };
    window.abp_sp_init = function () {
        if (sp_parent.find('.sp_builder').length === 0) return;
        initialLayout = sp_parent.find('#sp_saved_data').data('layout') || [];
        activeGroup = '';
        lastClickedIndex = null;
        isCloningMode = false;
        renderSidebarGroups();
        generateGrid();
    };
    window.abp_sp_row_column = function () {
        syncLayoutFromDOM();
        generateGrid();
    };
    window.abp_sp_row_last_remove = function () {
        let rows = parseInt(sp_parent.find('.sp_rows').val()) || 10;
        if (rows <= 1) return;
        syncLayoutFromDOM();
        sp_parent.find('.sp_rows').val(rows - 1);
        generateGrid();
    };
    window.abp_sp_col_last_remove = function () {
        let cols = parseInt(sp_parent.find('.sp_cols').val()) || 10;
        if (cols <= 1) return;
        syncLayoutFromDOM();
        sp_parent.find('.sp_cols').val(cols - 1);
        generateGrid();
    };
    window.abp_sp_cell_wh = function () {
        let w = parseInt(sp_parent.find('.sp_width').val()) || 50;
        let h = parseInt(sp_parent.find('.sp_height').val()) || 50;
        let gap = parseInt(sp_parent.find('.sp_gap').val()) || 0;
        let radius = parseInt(sp_parent.find('.sp_radius').val()) || 0;
        sp_parent.find('.sp_canvas').css({'gap': gap + 'px'});
        sp_parent.find('.sp_cell').each(function () {
            let c = parseInt($(this).data('c-span')) || 1;
            let r = parseInt($(this).data('r-span')) || 1;
            let w_gap = c > 1 ? gap * c : 0;
            let h_gap = r > 1 ? gap * r : 0;
            $(this).css({'width': (w * c + w_gap) + 'px', 'height': (h * r + h_gap) + 'px', 'border-radius': radius + 'px'});
        });
    };
    window.abp_sp_cell_design = function () {
        if (!activeCellForSpan) return;
        let cols = parseInt(sp_parent.find('.sp_cols').val()) || 5;
        let baseIndex = activeCellForSpan.data('index');
        let newCSpan = parseInt(sp_parent.find('.col_span').val()) || 1;
        let newRSpan = parseInt(sp_parent.find('.row_span').val()) || 1;
        let font_size = parseInt(sp_parent.find('.custom_font_size').val()) || 12;
        syncLayoutFromDOM();
        for (let r = 0; r < newRSpan; r++) {
            for (let c = 0; c < newCSpan; c++) {
                if (r === 0 && c === 0) continue;
                let neighborIndex = baseIndex + (r * cols) + c;
                if (initialLayout[neighborIndex]) {
                    initialLayout[neighborIndex] = {index: neighborIndex, type: 'other', id: 1};
                }
            }
        }
        if (initialLayout[baseIndex]) {
            let name = sp_parent.find('.custom_label').val() || '';
            if (newCSpan > 0) {
                initialLayout[baseIndex].width_ratio = newCSpan;
            }
            if (newRSpan > 0) {
                initialLayout[baseIndex].height_ratio = newRSpan;
            }
            initialLayout[baseIndex].name = name;
            if (font_size && font_size >= 8) {
                initialLayout[baseIndex].fs = font_size;
            }
        }
        sp_parent.find('.span_control').slideUp();
        generateGrid();
    };
    /**********************************/
    sp_parent.on('abp_trigger', 'input[name="bg_color"]', function (e) {
        e.preventDefault();
        let color = $(this).val();
        sp_parent.find('.sp_canvas').css('background-color', color);
    });
    sp_parent.on('click', '.group_item', function (e) {
        e.preventDefault();
        let id = $(this).data('id');
        let type = $(this).data('type');
        activeGroup = (type === 'seat') ? abp_ticket_type.find(g => String(g.id) === String(id)) : abp_decor_item.find(g => String(g.id) === String(id));
        // console.log(activeGroup);
        highlightActiveGroup();
    });
    sp_parent.on('click', '.sp_builder', function (e) {
        if ($(e.target).closest('.sp_canvas').length) {
            return;
        }
        e.preventDefault();
        sp_parent.find('.sp_tab_content .selected').removeClass('selected');
        activeGroup = '';
        lastClickedIndex = null;
        isCloningMode = false;
    });
    sp_parent.on('click', '.sp_tab', function () {
        sp_parent.find('.sp_tab, .sp_tab_content').removeClass('abp_active');
        $(this).addClass('abp_active');
        let targetTab = $(this).data('tab');
        sp_parent.find(`#${targetTab}`).addClass('abp_active');
        activeGroup = '';
        lastClickedIndex = null;
        highlightActiveGroup();
    });
    sp_parent.on('click', '.sp_rotation', function (e) {
        e.stopPropagation();
        let cell = $(this).closest('.sp_cell');
        let currentRotation = parseInt(cell.attr('data-rotate')) || 0;
        let nextRotation = 0;
        if (currentRotation === 0) nextRotation = 90;
        else if (currentRotation === 90) nextRotation = 180;
        else if (currentRotation === 180) nextRotation = 270;
        cell.attr('data-rotate', nextRotation);
        cell.removeClass('rotate-90 rotate-180 rotate-270');
        if (nextRotation !== 0) cell.addClass(`rotate-${nextRotation}`);
        syncLayoutFromDOM();
    });
    /**********************************/
    sp_parent.on('mousedown', '.sp_cell', function (e) {
        if ($(e.target).closest('.sp_rotation').length) return;
        let targetCell = $(this).closest('.sp_cell');
        let type = targetCell.attr('data-cell-type');
        let id = targetCell.attr('data-group-id');
        let target_tab = 'sp_tab_' + type;
        if (activeGroup && String(activeGroup.id) === String(id) && activeGroup.type === type) {
            return;
        }
        if (e.altKey) {
            dragSourceCell = targetCell;
            isCloningMode = true;
            return;
        }
        isDraggingSelection = true;
        let currentIndex = targetCell.data('index');
        if (e.ctrlKey) {
            applyGroupToCell(targetCell);
        } else if (lastClickedIndex === null && !activeGroup) {
            activeGroup = (type === 'seat') ? abp_ticket_type.find(g => String(g.id) === String(id)) : abp_decor_item.find(g => String(g.id) === String(id));
            console.log(activeGroup);
            lastClickedIndex = currentIndex;
            sp_parent.find('.sp_tab, .sp_tab_content').removeClass('abp_active');
            sp_parent.find('[data-tab="' + target_tab + '"]').addClass('abp_active');
            sp_parent.find(`#${target_tab}`).addClass('abp_active');
            highlightActiveGroup();
            //alert('yes');
        } else if (e.shiftKey && lastClickedIndex !== null) {
            let start = Math.min(lastClickedIndex, currentIndex);
            let end = Math.max(lastClickedIndex, currentIndex);
            sp_parent.find('.sp_cell').each(function () {
                let idx = $(this).data('index');
                if (idx >= start && idx <= end) {
                    applyGroupToCell(this);
                }
            });
        } else {
            applyGroupToCell(targetCell);
        }
        lastClickedIndex = currentIndex;
    });
    sp_parent.on('mouseenter', '.sp_cell', function () {
        if (isDraggingSelection && !isCloningMode) {
            applyGroupToCell(this);
        }
    });
    sp_parent.on('dragstart', '.sp_cell', function (e) {
        dragSourceCell = $(this);
        if (dragSourceCell.attr('data-cell-type') === 'other' && dragSourceCell.attr('data-group-id') === 0) {
            e.preventDefault();
            return;
        }
        isCloningMode = true;
    });
    sp_parent.on('dragover', '.sp_cell', function (e) {
        e.preventDefault();
    });
    sp_parent.on('drop', '.sp_cell', function (e) {
        e.preventDefault();
        if (!isCloningMode || !dragSourceCell) return;
        let targetCell = $(this).closest('.sp_cell');
        let targetIndex = targetCell.data('index');
        let type = dragSourceCell.attr('data-cell-type');
        let groupId = dragSourceCell.attr('data-group-id');
        let cSpan = dragSourceCell.attr('data-c-span') || 1;
        let rSpan = dragSourceCell.attr('data-r-span') || 1;
        let fs = dragSourceCell.attr('data-fs') || 12;
        let rotate = dragSourceCell.attr('data-rotate') || 0;
        let finalName = dragSourceCell.find('.cell_label').text();
        if (type === 'seat') {
            let activeSeatGroup = abp_ticket_type.find(g => String(g.id) === String(groupId));
            let prefix = activeSeatGroup ? activeSeatGroup.prefix : 'S-';
            finalName = prefix + (targetIndex + 1);
        }
        syncLayoutFromDOM();
        initialLayout[targetIndex] = {
            index: targetIndex, type: type, id: groupId, name: finalName, fs: parseInt(fs),
            width_ratio: parseInt(cSpan), height_ratio: parseInt(rSpan), rotate: parseInt(rotate)
        };
        isCloningMode = false;
        dragSourceCell = null;
        generateGrid();
    });
    sp_parent.on('mouseup', function () {
        if (isDraggingSelection) {
            syncLayoutFromDOM();
        }
        isDraggingSelection = false;
        isCloningMode = false;
        dragSourceCell = null;
    });
    sp_parent.on('dblclick', '.sp_cell', function (e) {
        e.stopPropagation();
        activeCellForSpan = $(this);
        let currentIndex = activeCellForSpan.data('index');
        let cols = parseInt(sp_parent.find('.sp_cols').val()) || 10;
        let rows = parseInt(sp_parent.find('.sp_rows').val()) || 10;
        let maxCSpan = cols - (currentIndex % cols);
        let maxRSpan = rows - Math.floor(currentIndex / cols);
        sp_parent.find('.col_span').attr('max', maxCSpan).val(activeCellForSpan.attr('data-c-span') || 1);
        sp_parent.find('.row_span').attr('max', maxRSpan).val(activeCellForSpan.attr('data-r-span') || 1);
        sp_parent.find('.custom_font_size').val(activeCellForSpan.attr('data-fs') || 12);
        sp_parent.find('.custom_label').val(activeCellForSpan.find('.cell_label').text().trim() || '');
        sp_parent.find('.span_control').slideDown();
        isCloningMode = false;
    });
    /**********************************/
    function cell_data(type, id, key = 'icon') {
        let list = (type === 'seat') ? abp_ticket_type : abp_decor_item;
        let matchedItem = list.find(g => String(g.id) === String(id));
        return matchedItem?.[key] ?? '';
    }
    function highlightActiveGroup() {
        sp_parent.find('.group_item').removeClass('selected');
        sp_parent.find(`.group_item[data-id="${activeGroup.id}"][data-type="${activeGroup.type}"]`).addClass('selected');
    }
    function renderSidebarGroups() {
        let seatHtml = '', otherHtml = '';
        abp_ticket_type.forEach(g => {
            let icon = img_icon_emoji(g);
            seatHtml += `<div class="group_item _fj_between" style="color:${g.color}"  data-id="${g.id}" data-type="seat">
                                            <div class="_fa_center_gap_xs_padding_xs"><span class="color_badge" style="background:${g.color}"></span>${icon}<span>${g.label}</span><strong class="group_count _color_theme">(0)</strong></div>
                                            <label><input type="text" class="_form_control" value="${g.prefix}" placeholder="Seat Prefix"></label></div>`;
        });
        abp_decor_item.forEach(g => {
            let icon = img_icon_emoji(g);
            otherHtml += `<div class="group_item _padding_xs" style="color:${g.color}" data-id="${g.id}" data-type="other"> <span class="color_badge" style="background:${g.color}"></span>${icon}<span>${g.label}</span><strong class="group_count _color_theme">(0)</strong></div>`;
        });
        sp_parent.find('.sp_group_seats').html(seatHtml);
        sp_parent.find('.sp_group_others').html(otherHtml);
        highlightActiveGroup();
    }
    function applyGroupToCell(element) {
        let cell = $(element).closest('.sp_cell');
        if (cell.attr('data-cell-type') === activeGroup.type && String(cell.attr('data-group-id')) === String(activeGroup.id)) {
            return;
        }
        let index = cell.data('index');
        if (index === undefined) return;
        let finalName = cell.find('.cell_label').text();
        if (activeGroup.type === 'seat') {
            let currentPrefix = sp_parent.find('.group_item.selected input').val() || '';
            finalName = currentPrefix + get_seat_name(activeGroup.id);
        }
        //console.log(activeGroup);
        let color = cell_data(activeGroup.type, activeGroup.id, 'color');
        let img = cell_data(activeGroup.type, activeGroup.id, 'img');
        let icon = cell_data(activeGroup.type, activeGroup.id);
        cell.attr('data-cell-type', activeGroup.type);
        cell.attr('data-group-id', activeGroup.id);
        cell.find('.cell_icon').text('').attr('class', 'cell_icon');
        cell.css('color', color);
        if (activeGroup.type === 'other' && activeGroup.id === 1) {
            cell.find('.cell_content').css('background', 'transparent');
        } else {
            if (img) {
                cell.find('.cell_content').css('background', ' url("' + img + '")');
            } else {
                cell.find('.cell_content').css('background', 'transparent');
                if (abp_emoji_check(icon)) {
                    cell.find('.cell_icon').text(icon);
                } else {
                    cell.find('.cell_icon').addClass(icon);
                }
            }
        }
        cell.find('.cell_label').text(finalName);
        updateLiveCounters();
    }
    function syncLayoutFromDOM() {
        initialLayout = [];
        sp_parent.find('.sp_cell').each(function () {
            let cell = $(this);
            let width_ratio = parseInt(cell.attr('data-c-span')) || 1;
            let height_ratio = parseInt(cell.attr('data-r-span')) || 1;
            let rotate = parseInt(cell.attr('data-rotate')) || 0;
            let fs = parseInt(cell.attr('data-fs'));
            let cellData = {
                index: cell.data('index'),
                type: cell.attr('data-cell-type') || 'other',
                id: cell.attr('data-group-id') || 1,
                name: cell.find('.cell_label').text(),
            };
            if (width_ratio > 1) {
                cellData.width_ratio = width_ratio;
            }
            if (height_ratio > 1) {
                cellData.height_ratio = height_ratio;
            }
            if (rotate > 0) {
                cellData.rotate = rotate;
            }
            if (fs !== 12) {
                cellData.fs = fs;
            }
            initialLayout.push(cellData);
        });
    }
    function generateGrid() {
        let rows = parseInt(sp_parent.find('.sp_rows').val()) || 10;
        let cols = parseInt(sp_parent.find('.sp_cols').val()) || 10;
        let w = parseInt(sp_parent.find('.sp_width').val()) || 50;
        let h = parseInt(sp_parent.find('.sp_height').val()) || 50;
        let gap = parseInt(sp_parent.find('.sp_gap').val()) || 0;
        let radius = parseInt(sp_parent.find('.sp_radius').val()) || 0;
        sp_parent.find('.sp_canvas').css({'grid-template-columns': `repeat(${cols}, minmax(max-content, 1fr))`});
        let canvas = sp_parent.find('.sp_canvas').empty();
        let totalCells = rows * cols;
        let hiddenMap = new Array(totalCells).fill(false);
        for (let i = 0; i < totalCells; i++) {
            let cellData = initialLayout[i];
            if (cellData) {
                let cSpan = parseInt(cellData.width_ratio) || 1;
                let rSpan = parseInt(cellData.height_ratio) || 1;
                if (cSpan > 1 || rSpan > 1) {
                    for (let r = 0; r < rSpan; r++) {
                        for (let c = 0; c < cSpan; c++) {
                            if (r === 0 && c === 0) continue;
                            let targetIdx = i + (r * cols) + c;
                            if (targetIdx < totalCells) hiddenMap[targetIdx] = true;
                        }
                    }
                }
            }
        }
        for (let i = 0; i < totalCells; i++) {
            let cellData = initialLayout[i] || {type: 'other', id: 1};
            let cSpan = cellData.width_ratio || 1;
            let rSpan = cellData.height_ratio || 1;
            let fs = cellData.fs || 12;
            let img = cell_data(cellData.type, cellData.id, 'img');
            let icon = cell_data(cellData.type, cellData.id);
            let color = cell_data(cellData.type, cellData.id, 'color');
            let rotationDegree = cellData.rotate || 0;
            let gap_width = cSpan > 1 && gap > 0 ? (cSpan - 1) * gap : 0;
            let gap_height = rSpan > 1 && gap > 0 ? (rSpan - 1) * gap : 0;
            let cellStyle = `width:${w * cSpan + gap_width}px; height:${h * rSpan + gap_height}px; color:${color}; grid-column: span ${cSpan}; grid-row: span ${rSpan};font-size:${fs}px;border-radius:${radius}px`;
            let classes = `sp_cell ${hiddenMap[i] ? 'cell_hidden' : ''} ${rotationDegree ? 'rotate-' + rotationDegree : ''}`;
            let cellHtml = `
                <div class="${classes}" data-index="${i}" style="${cellStyle}" data-c-span="${cSpan}"  data-fs="${fs}" data-r-span="${rSpan}" data-rotate="${rotationDegree}" data-cell-type="${cellData.type || 'other'}" data-group-id="${cellData.id || 0}" draggable="true">
                    <span class="fa-solid fa-rotate-right sp_rotation"></span>
                    <div class="cell_content" style="background: url('${img}')">
                        ${icon_emoji(icon, 'cell_icon')}
                        <span class="cell_label">${cellData.name || ''}</span>
                    </div>
                </div>`;
            canvas.append(cellHtml);
        }
        updateLiveCounters();
    }
    function updateLiveCounters() {
        sp_parent.find('.span_control').slideUp();
        let totalSeats = 0;
        let totalOthers = 0;
        let groupCounts = {};
        let otherCounts = {};
        abp_ticket_type.forEach(g => groupCounts[g.id] = 0);
        abp_decor_item.forEach(g => otherCounts[g.id] = 0);
        sp_parent.find('.sp_cell').each(function () {
            let type = $(this).attr('data-cell-type');
            let groupId = $(this).attr('data-group-id');
            let name = $(this).find('.cell_label').text();
            if (type === 'seat' && name) {
                totalSeats++;
                if (groupCounts[groupId] !== undefined) groupCounts[groupId]++;
            } else if (type === 'other' && groupId !== '') {
                totalOthers++;
                if (otherCounts[groupId] !== undefined) otherCounts[groupId]++;
            }
        });
        sp_parent.find('.total_seat').text(totalSeats);
        sp_parent.find('.total_others').text(totalOthers);
        abp_ticket_type.forEach(g => {
            sp_parent.find(`.group_item[data-id="${g.id}"][data-type="seat"]`).find('.group_count').text('(' + groupCounts[g.id] + ')');
        });
        abp_decor_item.forEach(g => {
            sp_parent.find(`.group_item[data-id="${g.id}"][data-type="other"]`).find('.group_count').text('(' + otherCounts[g.id] + ')');
        });
    }
    function get_seat_name(id) {
        let currentPrefix = sp_parent.find('.group_item.selected input').val() || '';
        let existing_numbers = [];
        let next_number = 1;
        let target = sp_parent.find('.sp_cell[data-group-id="' + id + '"]');
        if (target.length === 0) {
            return 1;
        } else {
            target.each(function () {
                let full_name = $(this).find('.cell_label').text().trim();
                let number_str = full_name;
                if (currentPrefix && full_name.startsWith(currentPrefix)) {
                    number_str = full_name.substring(currentPrefix.length);
                }
                let seat_num = parseInt(number_str, 10);
                if (!isNaN(seat_num)) {
                    existing_numbers.push(seat_num);
                }
            });
            while (existing_numbers.includes(next_number)) {
                next_number++;
            }
            return next_number;
        }
    }
    function icon_emoji($value, $class = '') {
        if ($.isNumeric($value)) {
            return '';
        } else {
            if (abp_emoji_check($value)) {
                return '<span class="' + $class + '">' + $value + '</span>';
            } else {
                return '<span class="' + $class + '  ' + $value + '"></span>';
            }
        }
    }
    function img_icon_emoji(data) {
        let img = data.img ?? '';
        let icon = data.icon ?? '';
        if (img && $.isNumeric(icon) && icon > 0) {
            return '<div class="abp_image"><img class="_img_control"  src="' + img + '" alt="#"></div>';
        } else {
            return icon_emoji(icon);
        }
    }
}(jQuery));