$( function() {
    init();

    /* events */
    $('#btn_add_status').on('click', function() {
        view_state('add', 'STATUS');
    });

    $('#btn_add_disposition').on('click', function () {
        view_state('add', 'DISPOSITION');
    });

    $('#btn_cancel_status').on('click', function () {
        view_state('cancel', 'STATUS');
    });

    $('#btn_cancel_disposition').on('click', function () {
        view_state('cancel', 'DISPOSITION');
    });

    $('#ngr_status_link').on('click', function () {
        $('.clear').val('');
        view_state('', 'STATUS');
    });

    $('#ngr_disposition_link').on('click', function () {
        $('.clear').val('');
        view_state('', 'DISPOSITION');
    });

    $('#btn_save_status').on('click', function () {
        var frm = document.getElementById('frm_ngr_status');
        save_description(frm);
    });

    $('#btn_save_disposition').on('click', function () {
        var frm = document.getElementById('frm_ngr_disposition');
        save_description(frm);
    });

    $('#tbl_ngr_status tbody').on('click', '.btn_edit_status', function() {
        var table = $('#tbl_ngr_status').DataTable();
        var data = table.row($(this).parents('tr')).data();

        show_data_to_form('status', data);
    });

    $('#tbl_ngr_disposition tbody').on('click', '.btn_edit_disposition', function () {
        var table = $('#tbl_ngr_disposition').DataTable();
        var data = table.row($(this).parents('tr')).data();

        show_data_to_form('disposition', data);
    });

    $('#btn_delete_status').on('click', function () {
        delete_description('status');
    });

    $('#btn_delete_disposition').on('click', function () {
        delete_description('disposition');
    });
});

function init() {
    view_state('','');

    get_list('#tbl_ngr_status','STATUS');
    get_list('#tbl_ngr_disposition', 'DISPOSITION');

    check_checkboxes('status');
    check_checkboxes('disposition');
}

function view_state(state, control) {
    switch (state) {
        case 'add':
            var category = 'disposition';

            $('.clear').val('');

            if (control == 'STATUS') {
                category = 'status';
            }

            add(category);
            break;
        case 'cancel':
            var category = 'disposition';

            $('.clear').val('');

            if (control == 'STATUS') {
                category = 'status';
            }

            cancel(category);
            break;
    
        default:
            var category = 'disposition';

            $('.clear').val('');

            if (control == 'STATUS') {
                category = 'status';
            }

            $('#ngr_' + category + '_description').prop('disabled', true);

            $('#btn_add_' + category).prop('disabled', false);
            $('#btn_save_' + category).prop('disabled', true);
            $('#btn_cancel_' + category).prop('disabled', true);
            $('#btn_delete_' + category).prop('disabled', true);
            $('#tbl_ngr_' + category + ' tbody .btn_edit_' + category).prop('disabled', true);

            $('#tbl_ngr_' + category + ' tbody .check_' + category).prop({
                'checked': false,
                'disabled': false
            });

            $('#check_all_' + category).prop({
                'checked': false,
                'disabled': false
            });
            break;
    }
}

function save_description(frm) {
    if (frm.description.value == "") {
        msg("Please fill out Description Input.", "failed");
    } else {
        $('#loading').modal('show');

        var table_id = '#tbl_ngr_disposition';
        if (frm.category.value == 'STATUS') {
            table_id = '#tbl_ngr_status';
        }

        $.ajax({
            url: './ngr-master-save',
            dataType: 'JSON',
            method: 'POST',
            data: {
                _token: token,
                id: frm.id.value,
                description: frm.description.value,
                category: frm.category.value
            }
        }).done( function(data, textStatus, xhr) {
            view_state('', '');
            $(table_id).DataTable().ajax.reload();
            msg(data.msg, data.status);
        }).fail( function(xhr, textStatus, errorThrown) {
            console.log(xhr);
        }).always( function() {
            $('#loading').modal('hide');
        });
    }
}

function get_list(table_id, category) {
    var cat = 'disposition';
    if (category == 'STATUS') {
        cat = 'status';
    }

    $(table_id).DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: './ngr-master/get-list',
            type: 'GET',
            data: function (d) {
                d.category = category;
            }
        },
        order: [4,'desc'],
        columns: [
            { 
                data: function (x) {
                    return "<input type='checkbox' class='check_" + cat + " checkbox' value='" + x.id + "'/>";
                }, orderable: false, searchable: false
            },
            {
                data: function (x) {
                    return "<button type='button' class='btn btn-sm btn-primary btn_edit_" + cat + "'>\
                                <i class='fa fa-edit'></i>\
                            </button>";
                }, orderable: false, searchable: false
            },
            { data: 'description' },
            { data: 'update_user' },
            { data: 'updated_at' },
        ],
        createdRow: function (row, data, dataIndex) {
            var dataRow = $(row);
            btn = dataRow[0].cells[0].firstChild;
            $(btn).css({
                'margin-left': '5.5px',
                'height': '14px',
                'border-color': '#c6c6c6',
            });
        }
    });
}

function show_data_to_form(category,data) {
    $('#ngr_' + category + '_category').val(data.category);
    $('#ngr_' + category + '_description').val(data.description);
    $('#ngr_' + category + '_id').val(data.id);

    $('#ngr_' + category + '_description').prop('disabled', false);
    $('#btn_save_' + category).prop('disabled', false);
    $('#btn_cancel_' + category).prop('disabled', false);
    $('#btn_delete_' + category).prop('disabled', true);
    $('#btn_add_' + category).prop('disabled', true);
}

function add(category) {
    $('#ngr_' + category + '_description').prop('disabled', false);
    $('#btn_save_' + category).prop('disabled', false);
    $('#btn_cancel_' + category).prop('disabled', false);
    $('#btn_add_' + category).prop('disabled', true);
    $('#btn_delete_' + category).prop('disabled', true);
    $('#tbl_ngr_' + category + ' tbody .btn_edit_' + category).prop('disabled', true);

    $('#tbl_ngr_' + category + ' tbody .check_' + category).prop({
        'checked': false,
        'disabled': true
    });

    $('#check_all_' + category).prop({
        'checked': false,
        'disabled': true
    });
}

function cancel(category) {
    $('#ngr_' + category + '_description').prop('disabled', true);
    $('#btn_save_' + category).prop('disabled', true);
    $('#btn_cancel_' + category).prop('disabled', true);
    $('#btn_add_' + category).prop('disabled', false);
    $('#btn_delete_' + category).prop('disabled', true);
    $('#tbl_ngr_' + category + ' tbody .btn_edit_' + category).prop('disabled', false);

    $('#tbl_ngr_' + category + ' tbody .check_' + category).prop({
        'checked': false,
        'disabled': false
    });

    $('#check_all_' + category).prop({
        'checked': false,
        'disabled': false
    });
}

function checked_checkbox(category, disabled) {
    $('#btn_save_' + category).prop('disabled', disabled);
    $('#btn_cancel_' + category).prop('disabled', disabled);
    $('#tbl_ngr_' + category + ' tbody .btn_edit_' + category).prop('disabled', disabled);

    $('#btn_add_' + category).prop('disabled', disabled);
    if (!disabled) {
        $('#btn_add_' + category).prop('disabled', false);
        $('#btn_save_' + category).prop('disabled', true);
        $('#btn_cancel_' + category).prop('disabled', true);
    }
}

function check_checkboxes(category) {
    $('#check_all_' + category).on('change', function (e) {

        $('#tbl_ngr_' + category + ' tbody .check_' + category).not('[disabled]').not(this).prop('checked', this.checked);

        var checked = 0;
        var table = $('#tbl_ngr_' + category).DataTable();

        for (var x = 0; x < table.context[0].aoData.length; x++) {
            var aoData = table.context[0].aoData[x];
            if (aoData.anCells !== null && aoData.anCells[0].firstChild.checked == true) {
                checked++;
            }
        }

        if (checked > 0) {
            $('#btn_delete_' + category).prop('disabled', false);
            checked_checkbox(category, true);
        } else {
            $('#btn_delete_' + category).prop('disabled', true);
            checked_checkbox(category, false);
        }
    });

    $('#tbl_ngr_' + category + ' tbody').on('change', '.check_' + category,function() {
        if ($(this).is(':checked')) {
            $('#btn_delete_' + category).prop('disabled', false);
            checked_checkbox(category, true);
        } else {
            $('#btn_delete_' + category).prop('disabled', true);
            checked_checkbox(category, false);
        }
    });
}

function delete_description(category) {
    var IDs = [];
    var table = $('#tbl_ngr_' + category).DataTable();

    for (var x = 0; x < table.context[0].aoData.length; x++) {
        var aoData = table.context[0].aoData[x];
        if (aoData.anCells !== null && aoData.anCells[0].firstChild.checked == true) {
            IDs.push(aoData.anCells[0].firstChild.value);
        }
    }

    if (IDs.length > 0) {
        var msgs = "Do you want to delete this " + jsUcfirst(category) + "?";

        if (IDs.length > 1) {
            msgs = "Do you want to delete these Dispositions?";

            if (category == 'status') {
                msgs = "Do you want to delete these Statuses?";
            }
        }

        bootbox.confirm({
            message: msgs,
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if (result) {
                    $('#loading').modal('show');

                    $.ajax({
                        url: './ngr-master-delete',
                        dataType: 'JSON',
                        method: 'POST',
                        data: {
                            _token: token,
                            IDs: IDs,
                        }
                    }).done(function (data, textStatus, xhr) {
                        view_state('', '');
                        table.ajax.reload();
                        msg(data.msg, data.status);
                    }).fail(function (xhr, textStatus, errorThrown) {
                        console.log(xhr);
                    }).always(function () {
                        $('#loading').modal('hide');
                    });
                }
                
            }
        });
        
    }
}