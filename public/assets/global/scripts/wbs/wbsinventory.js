//var InventoryDataTable = $('#tbl_inventory').DataTable();

$( function() {
	ViewState();
	
	DatatableCheckBoxesEvent('#tbl_inventory','#check_all','.check_item','#btn_delete');
	inventoryDataTable(inventoryListURL);

	$('#btn_add').on('click', function() {
		$('#form_inventory_modal').modal('show');
	});

	$('#tbl_inventory').on('click', '.btn_edit', function() {
		$('#form_inventory_modal').modal('show');
	});

	$("#btn_delete").on('click', removeByID);

	$("#frm_inventory").on('submit', function(e){
		$('#loading').modal('show');
		var a = $(this).serialize();
		e.preventDefault();
		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
		}).done(function(data, textStatus, xhr) {
			msg("Modified Successful","success"); 
			inventoryDataTable(inventoryListURL);

		}).fail(function(xhr, textStatus, errorThrown) {
			var errors = xhr.responseJSON.errors;
			// showErrors(errors);
		}).always( function() {
			$('#loading').modal('hide');
		});
	});


	$('#tbl_inventory tbody').on('click', '.btn_edit', function() {
		var data = $('#tbl_inventory').DataTable().row($(this).parents('tr')).data();
		console.log(data);

		$('#id').val(data.id);
		$('#item').val(data.item);
		$('#item_desc').val(data.item_desc);
		$('#lot_no').val(data.lot_no);
		$('#qty').val(data.qty);
		$('#location').val(data.location);
		$('#supplier').val(data.supplier);
		$('#status').val(data.iqc_status);

		if (data.iqc_status == 1) {
			$('#nr').attr('checked',true);
		} else {
			$('#nr').attr('checked',false);
		}

		console.log($('#nr'));
		
	});

	$('#btn_open_search_modal').on('click', function() {
		$('#srch_from').val('');
		$('#srch_to').val('');
		$('#srch_invoice').val('');
		$('#srch_item').val('');
		$('#srch_lot_no').val('');
		
		$('#search_modal').modal('show');
	});

	$('#btnSearch').on('click', function() {
		$('#tbl_inventory').DataTable().ajax.reload(null, false);
	});
});

function ViewState() {
	if (parseInt(access_state) !== 2) {
		$('#check_all').prop('disabled', false);
		$('#btn_delete').show();

	} else {
		$('#check_all').prop('disabled', true);
		$('#btn_delete').hide();
	}
}


function removeByID(){
    var id = [];
    $(".check_item:checked").each(function () {
         id.push($(this).val());
    });

    var data = {
    	_token: token,
    	id: id
    };

    $.ajax({
    	url: deleteselected,
     	type: 'POST',
     	dataType: 'JSON',
     	data: data
    }).done(function(data, textStatus,xhr) {
     	msg(data.msg,data.status);
		inventoryDataTable(inventoryListURL);
    }).fail(function(xhr,textStatus) {
     	console.log("error");
    });
}

function inventoryDataTable(url) {
	//$('#loading').modal('show');
	
	if (!$.fn.DataTable.isDataTable('#tbl_inventory')) {
		// InventoryDataTable.clear();
		// InventoryDataTable.destroy();
		$('#tbl_inventory').DataTable({
			processing: true,
			serverSide: true,
			ajax: {
				url: url,
				dataType: "JSON",
				type: "GET",
				data: function (d) {
					d._token = $("meta[name=csrf-token]").attr("content"),
					d.srch_from = $('#srch_from').val(),
					d.srch_to = $('#srch_to').val(),
					d.srch_invoice = $('#srch_invoice').val(),
					d.srch_item = $('#srch_item').val(),
					d.srch_lot_no = $('#srch_lot_no').val(),
					d.srch_judgment = $('#srch_judgment').val()
				},
				error: function (response) {
					console.log(response);
				}
			},
			deferRender: true,
			processing: true,
			pageLength: 10,
			pagingType: "bootstrap_full_number",
			columnDefs: [{
				orderable: false,
				targets: 0
			}, {
				searchable: false,
				targets: 0
			}],
			order: [
				[18, "desc"]
			],
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
						return '<input type="checkbox" class="checkboxes check_item" value="' + data.id + '">';
					}, name: 'id', orderable: false, searchable: false
				},
                {
					data: function (data) {
						return '<button type="button" class="btn btn-sm btn-primary btn_edit"> \
                                    <i class="fa fa-edit"></i> \
                            	</button>';
					}, name: 'id', orderable: false, searchable: false
				},
				{ data: 'wbs_mr_id', name: 'wbs_mr_id' },
				{ data: 'invoice_no', name: 'invoice_no' },
				{ data: 'item', name: 'item' },
				{ data: 'item_desc', name: 'item_desc' },
				{ data: 'qty', name: 'qty' },
				{ data: 'lot_no', name: 'lot_no' },
				{ data: 'location', name: 'location' },
				{ data: 'supplier', name: 'supplier' },
				{ 
					data: function(data) {
						if (data.iqc_status == 1) {
                            switch (data.judgement) {
                                case 'Rejected':
                                    return 'Rejected';
                                    break;
                                case 'Special Accept':
                                    return 'Special Accept';
                                    break;
                                case 'Sorted':
                                    return 'Sorted';
                                    break;
                                case 'Reworked':
                                    return 'Reworked';
                                    break;
                                default:
                                    return 'Accepted';
                                    break;
                            }
						}

						if (data.iqc_status == 2) {
                            switch (data.judgement) {
                                case 'RTV':
                                    return 'RTV';
                                    break;
                                default:
                                    return 'Rejected';
                                    break;
                            }
						}

						if (data.iqc_status == 3) {
							return 'On-going';
						}

						if (data.iqc_status == 4) {
							return 'Special Accept';
						}

						if (data.iqc_status == 0) {
							return 'Pending';
						}
						
					}, name: 'iqc_status' 
				},
                { data: 'ngr_status', name: 'ngr_status' },
                { data: 'ngr_disposition', name: 'ngr_disposition' },
                { data: 'ngr_control_no', name: 'ngr_control_no' },
				{ data: 'create_user', name: 'create_user' },
				{ data: 'received_date', name: 'received_date' },
				{ data: 'kit_disabled', name: 'kit_disabled' },
				{ data: 'update_user', name: 'update_user' },
				{ data: 'updated_at', name: 'updated_at' },
				
			],
			createdRow: function (row, data, dataIndex) {
				var dataRow = $(row);
				var iqc_judgment = $(dataRow[0].cells[10]);

				if (data.kit_disabled == "Disabled") {
					dataRow.css('background-color', '#ff0000');
					dataRow.css('color', '#fff');
				}

				if (data.iqc_status == 1) {
                    switch (data.judgement) {
                        case 'Rejected':
                            iqc_judgment.css('background-color', '#ff0000');
						    iqc_judgment.css('color', '#fff');
                            break;
                        case 'Special Accept':
                            iqc_judgment.css('background-color', '#00ff00');
						    iqc_judgment.css('color', '#000');
                            break;
                        case 'Sorted':
                            iqc_judgment.css('background-color', '#ff9933');
						    iqc_judgment.css('color', '#fff');
                            break;
                        case 'Reworked':
                            iqc_judgment.css('background-color', '#ff33cc');
						    iqc_judgment.css('color', '#fff');
                            break;
                        default:
                            iqc_judgment.css('background-color', '#0000ff');
						    iqc_judgment.css('color', '#fff');
                            break;
                    }
				}

				if (data.iqc_status == 2) {
					iqc_judgment.css('background-color', '#ff0000');
					iqc_judgment.css('color', '#fff');
				}

				if (data.iqc_status == 4) {
					iqc_judgment.css('background-color', '#00ff00');
					iqc_judgment.css('color', '#000');
				}

				if (data.status == 5) {
					iqc_judgment.css('background-color', 'rgb(139 241 191)');
					iqc_judgment.css('color', '#000000');
				}

				if (data.iqc_status == 3) {
					iqc_judgment.css('background-color', '#3598dc');
					iqc_judgment.css('color', '#000000');
				}
			},
			initComplete: function () {
				$('#loading').modal('hide');
				if (parseInt(access_state) !== 2) {
					$('.check_item').prop('disabled', false);
					$('.btn_edit').prop('disabled', false);
				} else {
					$('.check_item').prop('disabled', true);
					$('.btn_edit').prop('disabled', true);
				}
			},
		});
	}

	
}

function DatatableCheckBoxesEvent(tbl_id, check_all_id, check_item_class, delete_btn_id) {
    $(check_all_id).on('change', function (e) {

        $('input:checkbox' + check_item_class).not('[disabled]').not(this).prop('checked', this.checked);

        var checked = 0;
        var table = $(tbl_id).DataTable();

        for (var x = 0; x < table.context[0].aoData.length; x++) {
            var aoData = table.context[0].aoData[x];
            if (aoData.anCells !== null && aoData.anCells[0].firstChild.checked == true) {
                checked++;
            }
        }

        // $(tblID + '_paginate').on('click', '.paginate_button', function() {
        // 	$('input:checkbox' + checkAllClass).prop('checked', false);
        // });

        if (checked > 0) {
            $(delete_btn_id).prop('disabled', false);
        } else {
            $(delete_btn_id).prop('disabled', true);
        }
    });

    $(tbl_id + ' tbody ').on('change', check_item_class, function () {
        if ($(this).is(':checked')) {
            var checked = 0;
            var table = $(tbl_id).DataTable();

            for (var x = 0; x < table.context[0].aoData.length; x++) {
                var aoData = table.context[0].aoData[x];
                if (aoData.anCells !== null && aoData.anCells[0].firstChild.checked == true) {
                    checked++;
                }
            }

            // $(tblID + '_paginate').on('click', '.paginate_button', function() {
            // 	$('input:checkbox' + checkAllClass).prop('checked', false);
            // });

            if (checked > 0) {
                $(delete_btn_id).prop('disabled', false);
            } else {
                $(delete_btn_id).prop('disabled', true);
            }
        }
    })
}
