var _read_type = "";

$(function () {
    OQCInventoryDataTable(OQCInventoryDataTableURL);

    $('.timepicker').timepicker({
        showMeridian: false,
        hourStep: 1,
        minStep: 1
    });
    
    $("#search_from").datepicker({
		format: 'mm-dd-yyyy',
		autoclose: true,
	}).on('changeDate', function (selected) {
		var startDate = new Date(selected.date.valueOf());
		$('#search_to').datepicker('setStartDate', startDate);
	}).on('clearDate', function (selected) {
		$('#search_to').datepicker('setStartDate', null);
	});

	$("#search_to").datepicker({
		format: 'mm-dd-yyyy',
		autoclose: true,
	}).on('changeDate', function (selected) {
		var endDate = new Date(selected.date.valueOf());
		$('#search_from').datepicker('setEndDate', endDate);
	}).on('clearDate', function (selected) {
		$('#search_from').datepicker('setEndDate', null);
	});

    $('#btn_search').on('click', function(){
        $('#search_modal').modal('show');
    });

    // $('#btn_pdf').on('click',function(){
    // 	PDFReport();
    // });
    $('#btn_excel').on('click',function(){
    	ExcelReport();
    });

    $('#btn_addnew').on('click', function() {
        clear();
        $('#inventory_modal').modal('show');
    });

    $('.validate-input').on('keyup', function(e) {
        var input = $(e.currentTarget);
        id = input.attr('id');

        if (input.val() !== "") {
            removeInputError(id);
        }
    });
    
    $('#btn_save').on('click', function() {
        saveEntry();
    });

    $('#po_no').on('change', function(e) {
        var po = $(e.currentTarget).val();
        PODetails(po);
    });

    $('#tbl_oqc_inventory tbody').on('click', '.btn_edit_inventory', function() {
        var data = $('#tbl_oqc_inventory').DataTable().row($(this).parents('tr')).data();
        console.log(data);

        clear();

        $('#inventory_id').val(data.id);
        $('#inventory_date').val(data.inventory_date);
        $('#po_no').val(data.po_no);
        $('#series_name').val(data.series_name);
        $('#quantity').val(data.quantity);
        $('#total_no_of_lots').val(data.total_no_of_lots);
        $('#lot_date').val(data.lot_date);
        $('#lot_time').val(data.lot_time);

        $('#inventory_modal').modal('show');
    });

    $('.group-checkable').on('change', function (event) {
        $('#tbl_oqc_inventory tbody .checkboxes').not(this).prop('checked', this.checked);

        var checked = 0;
        var table = $('#tbl_oqc_inventory').DataTable();
        for (var x = 0; x < table.context[0].aoData.length; x++) {
            var dtRow = table.context[0].aoData[x];
            if (dtRow.anCells !== null) {
                if (dtRow.anCells[0].firstChild.checked == true) {
                    checked++;
                }
            }
        }
    });

    $('#tbl_oqc_inventory_wrapper .checkboxes').on('change', function () {
        var index = $(this).parents('tr');
        var table = $('#tbl_oqc_inventory').DataTable();
        $(this).not(this).prop('checked', this.checked);

        var checked = 0;
        if ($(this).is(':checked')) {
            checked++;
        }

        if (checked == 0) {
            $('.group-checkable').prop('checked', false);
        }
    });

    $('#btn_delete').on('click', function() {
        var id_array = [];
        var table = $('#tbl_oqc_inventory').DataTable();
        for (var x = 0; x < table.context[0].aoData.length; x++) {
            var dtRow = table.context[0].aoData[x];
            if (dtRow.anCells !== null) {
                if (dtRow.anCells[0].firstChild.checked == true) {
                    var id = dtRow.anCells[0].firstChild.value;
                    id_array.push(id);
                }
            }
        }

        if (id_array.length > 0) {
            msgs = "Do you want to delete this data?";
            if (id_array.length > 1) {
                msgs = "Do you want to delete these data?";
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
                        DeleteInventory(id_array);
                    }
                }
            });
            
        } else {
            msg('Please select at least 1 item to delete.','failed');
        }
    });
});

function OQCInventoryDataTable(url) {
    $('#tbl_oqc_inventory').DataTable().clear();
    $('#tbl_oqc_inventory').DataTable().destroy();
    $('#tbl_oqc_inventory').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url,
            dataType: "JSON",
            type: "GET",
            data: function (d) {
                d._token = $("meta[name=csrf-token]").attr("content");
                d.search_po = $('#search_po').val();
                d.search_from = $('#search_from').val();
                d.search_to = $('#search_to').val();
                d.type = _read_type;
            },
            error: function (response) {
                console.log(response);
            }
        },
        deferRender: true,
        pageLength: 10,
        pagingType: "bootstrap_full_number",
        columnDefs: [{
            orderable: false,
            targets: 0
        }, {
            searchable: false,
            targets: 0
        }],
        lengthMenu: [
            [10, 20, 50, 100, 150, 200, 500, -1],
            [10, 20, 50, 100, 150, 200, 500, "All"]
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
                    return '<input type="checkbox" class="checkboxes" value="' + data.id + '">';
                }, name: 'id', orderable: false, searchable: false
            },
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'inventory_date', name: 'inventory_date' },
            { data: 'lot_date', name: 'lot_date' },
            { data: 'lot_time', name: 'lot_time' },
            { data: 'po_no', name: 'po_no' },
            { data: 'series_name', name: 'series_name' },
            { data: 'quantity', name: 'device_name' },
            { data: 'total_no_of_lots', name: 'total_no_of_lots' },
            { data: 'update_user', name: 'update_user' },
            { data: 'updated_at', name: 'updated_at' }
        ],
        order: [[10, 'desc']]
    });
}

function saveEntry() {
    $('#loading').modal('show');
    var inputs = {
        _token: token,
        inventory_id: $('#inventory_id').val(),
        state: $('#state').val(),
        inventory_date: $('#inventory_date').val(),
        po_no: $('#po_no').val(),
        series_name: $('#series_name').val(),
        quantity: $('#quantity').val(),
        total_no_of_lots: $('#total_no_of_lots').val(),
        lot_date: $('#lot_date').val(),
        lot_time: $('#lot_time').val(),
    };

    if (validateEntry() < 1) {
        $.ajax({
            url: SaveInventorysURL,
            type: 'POST',
            dataType: 'JSON',
            data: inputs
        }).done(function (data, textStatus, xhr) {
            msg(data.msg, data.status);
            if (data.status == "success") {
                clear();
                $('#tbl_oqc_inventory').DataTable().ajax.reload();
            }
        }).fail(function (xhr, textStatus, errorThrown) {
            console.log(xhr);
        }).always(function () {
            $('#loading').modal('hide');
        });
    }
}

function DeleteInventory(id_array) {
    $('#loading').modal('show');
    $.ajax({
        url: DeleteInventoryURL,
        type: 'POST',
        dataType: 'JSON',
        data: {
            _token: token,
            ids: id_array
        }
    }).done(function (data, textStatus, xhr) {
        msg(data.msg, data.status);
        if (data.status == "success") {
            clear();
            $('#tbl_oqc_inventory').DataTable().ajax.reload();
        }
    }).fail(function (xhr, textStatus, errorThrown) {
        console.log(xhr);
    }).always(function () {
        $('#loading').modal('hide');
    });
}

function validateEntry() {
    var error = 0;
    var inputs = $('.validate-input');

    inputs.each(function(i,x) {
        var input = $(x).val();
        if (input == "") {
            $('#div_' + i).addClass('has-error');
            $('#err_' + i).addClass('help-block');
            $('#err_' + i).html("Please Fill out this input field.");
            error++;
        }
    });

    return error;
}

function Search() {
	$('#search_modal').modal('show');
}


function PDFReport() {

    var from = $('#search_from').val();
	var to = $('#search_to').val();

    var url = checkDataReport;

    if(from !== null && from !== ''){
        if(to === null || to === ''){
            to = from;
        }
        $.ajax({
            url: url,
            type: "GET",
            dataType: "JSON",
            data: {
                from: from,
                to: to
            }
        }).done(function (data, textStatus, jqXHR) {

            $('#loading').modal('hide');

            if (data.return_status == 0) {
                msg("No Data found.", 'failed');
            }else{
                _read_type = "search";
                $('#tbl_oqc_inventory').DataTable().ajax.reload();
                var link = PDFReportURL + "?inv=&&from=" + $('#search_from').val() + "&&to="+$('#search_to').val();
                window.location.href = link;
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            msg("There's some error while processing.", 'failed');
        }).always( function() {
            $('#loading').modal('hide');
        });	
    }else{
        msg('Must input inventory date from!','failed');
    }
    // var from = $('#search_from').val();
	// var to = $('#search_to').val();

    // if(from !== null && from !== ''){
    //     if(to === null || to === ''){
    //         to = from;
    //     }
    //     _read_type = "search";
    //         $('#tbl_oqc_inventory').DataTable().ajax.reload();
    //         var link = PDFReportURL + "?inv=&&from=" + $('#search_from').val() + "&&to="+$('#search_to').val();
    //         window.location.href = link;
    // }else{
    //     msg('Must input inventory date from!','failed');
    // }
}

function ExcelReport() {

    var from = $('#search_from').val();
	var to = $('#search_to').val();

    var url = checkDataReport;

    if(from !== null && from !== ''){
        if(to === null || to === ''){
            to = from;
        }
        $.ajax({
            url: url,
            type: "GET",
            dataType: "JSON",
            data: {
                from: from,
                to: to
            }
        }).done(function (data, textStatus, jqXHR) {

            $('#loading').modal('hide');

            if (data.return_status == 0) {
                msg("No Data found.", 'failed');
            }else{
                _read_type = "search";
                $('#tbl_oqc_inventory').DataTable().ajax.reload();
                var link = ExcelReportURL + "?inv=&&from=" + $('#search_from').val() + "&&to="+$('#search_to').val();
                window.location.href = link;
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            msg("There's some error while processing.", 'failed');
        }).always( function() {
            $('#loading').modal('hide');
        });	
    }else{
        msg('Must input inventory date from!','failed');
    }

    // var from = $('#search_from').val();
	// var to = $('#search_to').val();

    // if(from !== null && from !== ''){
    //     if(to === null || to === ''){
    //         to = from;
    //     }
    //     _read_type = "search";
    //         $('#tbl_oqc_inventory').DataTable().ajax.reload();
    //         var link = ExcelReportURL + "?inv=&&from=" + $('#search_from').val() + "&&to="+$('#search_to').val();
    //         window.location.href = link;
    // }else{
    //     msg('Must input inventory date from!','failed');
    // }
}

function removeInputError(id) {
    $('#div_' + id).removeClass('has-error');
    $('#err_' + id).removeClass('help-block');
    $('#err_' + id).html("");
}

function isValidDate(date) {
    return (new Date(date) !== "Invalid Date") && !isNaN(new Date(date));
}

function PODetails(po) {
    if (po !== '') {
        $('#loading').modal('show');

        var is_probe = 0;
        // if ($('#is_probe').is(':checked')) {
        //     is_probe = 1;
        // }

        var inputs = {
            _token: token,
            is_probe: is_probe,
            po: po,
        }

        $.ajax({
            url: PODetailsURL,
            type: 'GET',
            dataType: 'JSON',
            data: inputs
        }).done(function(data, textStatus, xhr) {
            if (data.msg !== "") {
                msg(data.msg,data.status);
            } else {
                detail = data.data[0];
                $('#series_name').val(detail.device_name);
                $('#quantity').val(detail.po_qty);
            }
        }).fail(function(xhr, textStatus, errorThrown) {
            console.log(xhr);
        }).always(function() {
            $('#loading').modal('hide');
        });
    }
}

function clear() {
    $('.clear').val('');
}