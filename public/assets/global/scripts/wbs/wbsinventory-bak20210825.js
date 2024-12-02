$( function() {
    //viewState();
	checkAllCheckboxesInTable('.check_all','.check_item');
    inventoryTable(inventoryListURL)
    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd'
    });

	$('#btn_add').on('click', function() {
		$('#form_inventory_modal').modal('show');
	});

	$('#tbl_inventory').on('click', '.btn_edit', function() {
		$('#form_inventory_modal').modal('show');
	});

	$("#btn_delete").on('click', removeByID);

	$("#frm_inventory").on('submit', function(e){
		var a = $(this).serialize();
		e.preventDefault();
		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
		}).done(function(data, textStatus, xhr) {
			msg("Modified Successful","success"); 
			inventoryTable(inventoryListURL)

		}).fail(function(xhr, textStatus, errorThrown) {
			var errors = xhr.responseJSON.errors;
			// showErrors(errors);
		});
	});

    $('#btn_search').on('click', function(){
        $('#searchmodal').modal('show');
    });

    $('#gobtn').on('click', function() {
        searchItemInspection();
    });

	$('#tbl_inventory').on('click', '.btn_edit', function(e) {
		e.preventDefault();
		$('#id').val($(this).attr('data-id'));
		$('#item').val($(this).attr('data-item'));
		$('#item_desc').val($(this).attr('data-item_desc'));
		$('#lot_no').val($(this).attr('data-lot_no'));
		$('#qty').val($(this).attr('data-qty'));
		$('#location').val($(this).attr('data-location'));
		$('#supplier').val($(this).attr('data-supplier'));
		$('#received_date').val($(this).attr('data-received_date'));
		$('#status').val($(this).attr('data-iqc_status'));
	});

    $('#btn_refresh').on('click', function() {
        $('#loading').modal('show');
        $.ajax({
            url: refreshInventoryURL,
            type: 'POST',
            dataType: 'json',
        }).done(function(data, textStatus, xhr) {
            msg(data.msg,data.status);
            inventoryTable(inventoryListURL);
        }).fail(function(xhr, textStatus, errorThrown) {
            console.log("error");
        }).always(function() {
            $('#loading').modal('hide');
        });
        
    });

});


function searchItemInspection() {
    var data = $('#frm_search').serialize();

    $.ajax({
        url: inventorySearchURL,
        type: "GET",
        data: data
    }).done( function(data,textStatus,jqXHR) {
        $('#tbl_inventory').html('');
        inventoryTableSearch(data);
        // console.log(data);
        
    }).fail( function(xhr,textStatus,errorThrown) {
        msg("There's some error while processing.",'failed');
    }).always(function() {
        $('#SearchModal').modal('hide');
    });
}

function inventoryTableSearch(data) {
    $('#tbl_inventory').dataTable().fnClearTable();
    $('#tbl_inventory').dataTable().fnDestroy();
    $('#tbl_inventory').DataTable({
        data:data,
        columns: [
            { data: function(data) {
		        return '<input type="checkbox" class="check_item" value="'+data.id+'">';
		    }, name: 'id', orderable: false, searchable: false },
			{ data: 'wbs_mr_id', name: 'wbs_mr_id' },
			{ data: 'invoice_no', name: 'invoice_no' },
			{ data: 'item', name: 'item' },
			{ data: 'item_desc', name: 'item_desc' },
			{ data: 'qty', name: 'qty' },
			{ data: 'lot_no', name: 'lot_no' },
			{ data: 'location', name: 'location' },
			{ data: 'supplier', name: 'supplier' },
			{ data: function(data) {
				if (data.iqc_status == 1) {
                    return 'Accept';
                }

                if (data.iqc_status == 2) {
                    return 'Reject';
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
			}, name: 'iqc_status' },
			{ data: 'create_user', name: 'create_user' },
			{ data: 'received_date', name: 'received_date' },
			{ data: 'update_user', name: 'update_user' },
			{ data: 'updated_at', name: 'updated_at' },
			{ data: function(x){
                return '<button class="btn btn-sm btn-primary btn_edit" data-id="' + x.id +'" '+
                            'data-wbs_mr_id="' + x.wbs_mr_id +'" '+
                            'data-invoice_no="' + x.invoice_no +'" '+
                            'data-item="' + x.item +'" '+
                            'data-item_desc="' + x.item_desc +'" '+
                            'data-qty="' + x.qty +'" '+
                            'data-lot_no="' + x.lot_no +'" '+
                            'data-location="' + x.location +'" '+
                            'data-received_date="' + x.received_date +'" '+
                            'data-supplier="' + x.supplier + '" '+
                            'data-not_for_iqc="' + x.not_for_iqc +'" '+ 
                            'data-iqc_status="' + x.iqc_status +'" '+ 
                            'data-judgement="' + x.judgement +'" '+ 
                            'data-mat_batch_id="' + x.mat_batch_id +'" '+ 
                            'data-loc_batch_id="' + x.loc_batch_id +'">'+ 
                            '<i class="fa fa-edit"></i>'+
                        '</button>';
            }, name: 'action',orderable: false, searchable: false },
        ],
        order: [[0,'desc']],
        createdRow: function (row, data, dataIndex) {
            if (data.iqc_status == 1) {
                $(row).css('background-color', '#c49f47');
                $(row).css('color', '#fff');
            }

            if (data.iqc_status == 2) {
                $(row).css('background-color', '#cb5a5e');
                $(row).css('color', '#fff');
            }

            if (data.iqc_status == 3) {
                $(row).css('background-color', '#3598dc');
                $(row).css('color', '#fff');
            }

            if (data.iqc_status == 4) {
                $(row).css('background-color', '#ff844c');
                $(row).css('color', '#fff');
            }
        }
        
    });
}

function inventoryTable(url,data) {
	$('#tbl_inventory').dataTable().fnClearTable();
    $('#tbl_inventory').dataTable().fnDestroy();
    $('#tbl_inventory').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax:url,
        columns: [
            { data: function(data) {
		        return '<input type="checkbox" class="check_item" value="'+data.id+'">';
		    }, name: 'id', orderable: false, searchable: false },
			{ data: 'wbs_mr_id', name: 'wbs_mr_id' },
			{ data: 'invoice_no', name: 'invoice_no' },
			{ data: 'item', name: 'item' },
			{ data: 'item_desc', name: 'item_desc' },
			{ data: 'qty', name: 'qty' },
			{ data: 'lot_no', name: 'lot_no' },
			{ data: 'location', name: 'location' },
			{ data: 'supplier', name: 'supplier' },
			{ data: function(data) {
				if (data.iqc_status == 1) {
                    return 'Accept';
                }

                if (data.iqc_status == 2) {
                    return 'Reject';
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
			}, name: 'iqc_status' },
			{ data: 'create_user', name: 'create_user' },
			{ data: 'received_date', name: 'received_date' },
			{ data: 'update_user', name: 'update_user' },
			{ data: 'updated_at', name: 'updated_at' },
			{ data: 'action', name: 'action',orderable: false, searchable: false },
        ],
        order: [[0,'desc']],
        createdRow: function (row, data, dataIndex) {
            if (data.iqc_status == 1) {
                $(row).css('background-color', '#c49f47');
                $(row).css('color', '#fff');
            }

            if (data.iqc_status == 2) {
                $(row).css('background-color', '#cb5a5e');
                $(row).css('color', '#fff');
            }

            if (data.iqc_status == 3) {
                $(row).css('background-color', '#3598dc');
                $(row).css('color', '#fff');
            }

            if (data.iqc_status == 4) {
                $(row).css('background-color', '#ff844c');
                $(row).css('color', '#fff');
            }
        }
        // aoColumnDefs: [
        //     {
        //         aTargets:[9], // You actual column with the string 'America'
        //         fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
        //             $(nTd).css('font-weight', '700');
        //             if(sData == "Accept") {
        //                 $(nTd).css('background-color', '#c49f47');
        //                 $(nTd).css('color', '#fff');
        //             }
        //             if(sData == "Reject") {
        //                 $(nTd).css('background-color', '#cb5a5e');
        //                 $(nTd).css('color', '#fff');
        //             }
        //             if(sData == "On-going") {
        //                 $(nTd).css('background-color', '#3598dc');
        //                 $(nTd).css('color', '#fff');
        //             }

        //             if(sData == "Special Accept") {
        //                 $(nTd).css('background-color', '#f58561');
        //                 $(nTd).css('color', '#fff');
        //             }
        //         },
        //         defaultContent: '',
        //         targets: '_all'
        //     }
        // ],
        
    });
}

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
     	inventoryTable(inventoryListURL)
    }).fail(function(xhr,textStatus) {
     	console.log("error");
    });
}

