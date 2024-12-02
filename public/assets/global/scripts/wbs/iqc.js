$(function () {
    //loadforIQC(GetWBSIQCdata);
    loadforIQC(GetWBSIQCdata,0);

    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

    $('.timepicker').timepicker({
        showMeridian: false
    });


    $('#searchbtn').on('click', function () {
        $('#tbl_iqc').DataTable().search('');
        $('#searchmodal').modal('show');
    });

    $('#statusbtn').on('click', function () {
        var notAppliedItems = checkIfAppliedToIQC();

        if (notAppliedItems.length > 0) {
            msg('Please notify Receiving Staff to apply these items to IQC. Items : ' + notAppliedItems.join(), 'failed');
        } else {
            $('#bulkupdatemodal').modal('show');
        }
    });

    $('#tbl_iqc_body').on('click', '.updatesinglebtn', function () {
        var id = $(this).attr('data-id');
        if ($(this).attr('data-app_date') == '') {
            msg('Please notify the Receiving Staff to apply this item to IQC.', 'failed');
        } else {
            $('#selectedid').val(id);
            $('#statusModal').modal('show');
        }
    });

    $('#gobtn').on('click', function () {
        searchstatus();
    });

    $('#updateIQCstatusbtn').on('click', function () {
        $('#loading').modal('show');
        var id = $('#selectedid').val();
        var statusup = $('#statusup').val();
        var iqcresup = $('#iqcresup').val();
        var inspector = $('#inspector').val();
        var start_time = $('#start_time').val();
        var data = {
            _token: token,
            id: id,
            statusup: statusup,
            inspector: inspector,
            start_time: start_time,
            iqcresup: iqcresup
        };
        $.ajax({
            url: PostWBSIQCSingleUpdate,
            type: "POST",
            data: data,
        }).done(function (data, textStatus, jqXHR) {
            console.log(data);
            loadforIQC(GetWBSIQCdata,0);
            isCheck($('#chk_all'))
        }).fail(function (jqXHR, textStatus, errorThrown) {
            msg(errorThrown, textStatus);
        }).always( function() {
            $('#loading').modal('hide');
        });
    });

    $('.group-checkable').on('change', function (e) {
        $('input:checkbox.chk').not(this).prop('checked', this.checked);
    });

    $('#updateIQCbulkbtn').on('click', function () {
        var ids = getAllChecked();        
        var statusup = $('#statusupbulk').val();
        var iqcresup = $('#iqcresupbulk').val();
        var inspector = $('#inspectorbulk').val();
        var start_time = $('#start_timebulk').val();
        var item = getAllCheckedItemCode();

        var data = {
            _token: token,
            id: ids,
            statusup: statusup,
            inspector: inspector,
            start_time: start_time,
            iqcresup: iqcresup,
            item: item
        };

        if (ids.length > 0) {
            $('#loading').modal('show');

            $.ajax({
                url: PostWBSIQCBulkUpdate,
                type: "POST",
                data: data,
            }).done(function (data, textStatus, jqXHR) {
                console.log(data);
                loadforIQC(GetWBSIQCdata,0);
                isCheck($('#chk_all'))
            }).fail(function (jqXHR, textStatus, errorThrown) {
                msg(errorThrown, textStatus);
            }).always(function () {
                $('#loading').modal('hide');
            });
        } else {
            $('#loading').modal('hide');
            msg('Please check 2 or more checkboxes', 'failed');
        }
    });

});

function loadforIQC(url, status_val) {
    $('#tbl_iqc').DataTable().clear();
    $('#tbl_iqc').DataTable().destroy();
    $('#tbl_iqc').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: {
            url: url,
            dataType: "JSON",
            type: "GET",
            data: function (d) {
                d._token = $("meta[name=csrf-token]").attr("content");
                d.from = $('#from').val();
                d.to = $('#to').val();
                d.recno = $('#recno').val();
                d.status = (status_val !== "" && status_val !== null)? status_val : $('#status').val();
                d.itemno = $('#itemno').val();
                d.lotno = $('#lotno').val();
                d.invoice_no = $('#invoice_no').val();
            },
            error: function (response) {
                console.log(response);
            }
        },
        pageLength: 5,
        pagingType: "bootstrap_full_number",
        columnDefs: [{
            orderable: false,
            targets: 0
        }, {
            searchable: false,
            targets: 0
        }],
        order: [
            [13, "desc"]
        ],
        lengthMenu: [
            [5, 10, 20, 50, 100, 150, 200, 500, -1],
            [5, 10, 20, 50, 100, 150, 200, 500, "All"]
        ],
        language: {
            aria: {
                sortAscending: ": activate to sort column ascending",
                sortDescending: ": activate to sort column descending"
            },
            emptyTable: "No data available in table",
            info: "Showing _START_ to _END_ of _TOTAL_ records",
            infoEmpty: "No records found",
            infoFiltered: "(filtered1 from _MAX_ total records)",
            lengthMenu: "Show _MENU_",
            search: "Search:",
            zeroRecords: "No matching records found",
            paginate: {
                "previous": "Prev",
                "next": "Next",
                "last": "Last",
                "first": "First"
            }
        },
        columns: [
            {
                data: function (data) {
                    return '<input type="checkbox" class="chk" value="' + data.id + '"' +
                        ' data-id="' + data.id + '" data-code="' + data.item + '" ' +
                        ' data-app_date="' + data.app_date + '"/>';
                }, orderable: false, searchable: false, name: "id"
            },
            {
                data: function (data) {
                    return "<button type='button' class='updatesinglebtn btn btn-primary btn-sm input-sm' data-id='" + data.id + "' " +
                        " data-app_date='" + data.app_date + "' data-app_time='" + data.app_time + "' ><i class='fa fa-edit'></i></a>";
                }, name: 'action', orderable: false, searchable: false
            },
            {
                data: function (data) {
                    return data.judgement;
                }, name: "judgement"
            },
            { data: 'item', name: 'item' },
            { data: 'item_desc', name: 'item_desc' },
            { data: 'supplier', name: 'supplier' },
            { data: 'qty', name: 'qty' },
            { data: 'lot_no', name: 'lot_no' },
            { data: 'drawing_num', name: 'drawing_num' },
            { data: 'wbs_mr_id', name: 'wbs_mr_id' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'app_by', name: 'app_by' },
            { data: 'app_date', name: 'app_date' },
            { data: 'ins_by', name: 'ins_by' },
            { data: 'ins_date', name: 'ins_date' },
            { data: 'iqc_result', name: 'iqc_result' },
            { data: 'updated_at', name: 'updated_at' },
        ],
        createdRow: function (row, data, dataIndex) {
            var dataRow = $(row);
            var status_td = $(dataRow[0].cells[2]);

            switch (data.judgement) {
                case 'Accepted':
                    status_td.css('background-color', '#0d47a1');
                    status_td.css('color', '#fff');
                    break;
                case 'Rejected':
                    status_td.css('background-color', '#ff6266');
                    status_td.css('color', '#fff');
                    break;

                case 'On-going':
                    status_td.css('background-color', '#3598dc');
                    status_td.css('color', '#fff');
                    break;

                case 'Special Accept':
                    status_td.css('background-color', '#64dd17');
                    status_td.css('color', '#000');
                    break;

                case 'RTV':
                    status_td.css('background-color', '#ff6266');
                    status_td.css('color', '#fff');
                    break;

                case 'Sorted':
                    status_td.css('background-color', '#ff9933');
                    status_td.css('color', '#fff');
                    break;

                case 'Reworked':
                    status_td.css('background-color', '#ab47bc');
                    status_td.css('color', '#fff');
                    break;
            
                default:
                    if (data.iqc_status == 0) {
                        status_td.html("Pending");
                        status_td.css('font-weight', '700');
                        status_td.css('color', '#000');
                    }

                    if (data.iqc_status == 1) {
                        status_td.html("Accepted");
                        status_td.css('background-color', '#0d47a1');
                        status_td.css('color', '#fff');
                    }

                    if (data.iqc_status == 3) {
                        status_td.html("On-going");
                        status_td.css('background-color', '#3598dc');
                        status_td.css('color', '#fff');
                    }
                    break;
            }
            
        },
        order: [[16, 'desc']]
    });
}

function searchstatus() {
    $('#tbl_iqc').DataTable().search('');
    // $('#tbl_iqc').DataTable().ajax.reload();
    loadforIQC(GetWBSIQCdata,"");
    // loadforIQC(GetWBSIQCSearch + '?_token=' + token +
    //     '&&from=' + $('#from').val() +
    //     '&&to=' + $('#to').val() +
    //     '&&recno=' + $('#recno').val() +
    //     '&&status=' + $('#status').val() +
    //     '&&itemno=' + $('#itemno').val() +
    //     '&&lotno=' + $('#lotno').val() +
    //     '&&invoice_no=' + $('#invoice_no').val());
}

function getAllChecked() {
    /* declare an checkbox array */
    var chkArray = [];

    /* look for all checkboes that have a class 'chk' attached to it and check if it was checked */
    $(".chk:checked").each(function () {
        chkArray.push($(this).val());
    });

    return chkArray;
}

function getAllCheckedItemCode() {
    /* declare an checkbox array */
    var chkArray = [];

    /* look for all checkboes that have a class 'chk' attached to it and check if it was checked */
    $(".chk:checked").each(function () {
        chkArray.push($(this).attr('data-code'));
    });

    return chkArray;
}

function isCheck(element) {
    if (element.is(':checked')) {
        element.parent('tr').removeAttr('checked');
        element.prop('checked', false)
    }
}

function checkIfAppliedToIQC() {
    var notApplied = [];

    $(".chk:checked").each(function () {
        if ($(this).attr('data-app_date') == ' ') {
            notApplied.push($(this).attr('data-code'));
        }
    });

    return $.unique(notApplied);
}