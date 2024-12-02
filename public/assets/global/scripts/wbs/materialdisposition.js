var lot_nos = [];
$(function() {
    $('#edit_exp_date').inputmask("9999-99-99",{ "placeholder": "YYYY-MM-DD" });
    $('#from').datepicker();
    $('#to').datepicker();
    getAllData();
    ViewState();
    checkAllCheckboxesInTable('.check_all', '.check_lot');
    checkAllCheckboxesInTable('.check_all_items', '.check_item');
    $('#btn_add').on('click', function() {
        AddState();
        SelectItem();
        $('#id').val('');
        $('#item').val('');
        $('#item_desc').val('');
        $('#transaction_code').val('');
        $('#createdby').val('');
        $('#updatedby').val('');
    });

    $('#btn_disregard').on('click', function() {
        ViewState();
        getAllData();
    });

    $('#btn_search_item').on('click', function() {
        if ($('#item').val() == '') {
            msg('Please input an Item', 'failed');
        } else {
            searchItemCode($('#item').val());
        }
    });

    $('#btn_search').on('click', function() {
        $('#modal_search').modal('show');
    });

    $('#btn_export').on('click', function() {
        $('#export_data_modal').modal('show');
    });

    $("#btn_excel").on('click', function() {
        if ($('#from').val() !== '' && $('#to').val() == '') {
            msg('Both Date Required', 'failed');
        } else if ($('#from').val() == '' && $('#to').val() !== '') {
            msg('Both Date Required', 'failed');
        } else if ($('#from').val() == '' && $('#to').val() == '') {
            msg('Both Date Required', 'failed');
        } else {
            getDataExport();
        }
    });

    $('#btn_filter').on('click', function() {
        if ($('#srch_from').val() !== '' && $('#srch_to').val() == '') {
            msg('Input Transaction Date', 'failed')
        } else if ($('#srch_item').val() == '') {
            msg('Input Item Code', 'failed')
        } else {
            getSearchedTransactions();
        }
    });
 
    $('#btn_reset').on('click', function() {
        getMaterials([]);
        $('#srch_item').val('');
        $('#srch_from').val('');
        $('#srch_to').val('');
    });

    $('#btn_edit').on('click', function() {
        EditState();
        $('#item').prop('readonly', true);
        $('#btn_save').show();
    });

    $('#close_btn').on('click', function() {
        getMaterials();
        $('#srch_item').val('');
    });
  
    $('#btn_close').on('click', function() {
        $('#from').val('');
        $('#to').val('');
    });

    $('#btn_select_lot').on('click', function() {
        $('#tbl_lotno_body').find('input[type=checkbox]:checked').each(function(index, el) {
            lot_nos.push({
                id: $(this).val(),
                item: $(this).attr('data-item'),
                item_desc: $(this).attr('data-item_desc'),
                lot_no: $(this).attr('data-lot_no'),
                qty: $(this).attr('data-qty'),
                current_qty: $(this).attr('data-current_qty'),
                exp_date: '',
                disposition: '',
                remarks: '',
                inv_id: $(this).val()
            });
        });
        SelectItem(lot_nos);
        $('#lot_no_modal').modal('hide');
        $('.btn_edit_lot').prop('disabled', false);
    });

    $('#tbl_items_body').on('click', '.btn_edit_lot', function() {
        $('#lot_id').val($(this).attr('data-id'));
        $('#edit_item').val($(this).attr('data-item'));
        $('#edit_item_desc').val($(this).attr('data-item_desc'));
        // $('#qty').val($(this).attr('data-qty'));
        $('#edit_qty').val($(this).attr('data-qty'));
        $('#edit_current_qty').val($(this).attr('data-current_qty'));
        // $('#edit_qty').val($(this).attr('data-qty'));
        $('#edit_lot_no').val($(this).attr('data-lot_no'));
        $('#edit_exp_date').val($(this).attr('data-exp_date'));
        $('#edit_disposition').val($(this).attr('data-disposition'));
        $('#edit_remarks').val($(this).attr('data-remarks'));
        $('#edit_lot_no_modal').modal('show');
    });

    $('#tbl_search_body').on('click', '.btn_search_edit_lot', function() {
        $('#transaction_code').val($(this).attr('data-transaction_code'));
        $('#id').val($(this).attr('data-id'));
        $('#item').val($(this).attr('data-item'));
        $('#qty').val($(this).attr('data-qty'));
        $('#item_desc').val($(this).attr('data-item_desc'));
        $('#create_user').val($(this).attr('data-create_user'));
        $('#created_at').val($(this).attr('data-created_at'));
        $('#update_user').val($(this).attr('data-update_user'));
        $('#updated_at').val($(this).attr('data-updated_at'));
        getAllData($(this).attr('data-transaction_code'), '');
        $('#modal_search').modal('hide');
    });

    $('#btn_save_edited').on('click', function() {
        var current_qty = +$('#edit_current_qty').val();
        var edit = +$('#edit_qty').val();

        if ($('#edit_disposition').val() !== '' && edit !==0) {
            for (var i = lot_nos.length -1; i >= 0; --i) {
                if (lot_nos[i].id == $('#lot_id').val()) {
                    lot_nos[i].exp_date = $('#edit_exp_date').val();
                    lot_nos[i].qty = $('#edit_qty').val();
                    lot_nos[i].current_qty = $('#edit_current_qty').val();
                    lot_nos[i].disposition = $('#edit_disposition').val();
                    lot_nos[i].remarks = $('#edit_remarks').val();
                }
            }
           			SelectItem(lot_nos);
            	 	$('.btn_edit_lot').prop('disabled', false);
          		    $('#edit_lot_no_modal').modal('hide');
        } else {
        
            msg('Quantity and Desposition Required.', 'failed');
        }
    });

    var desc = $('#item_desc').val();
    $('#btn_save').on('click', function() {
        if ($('#item').val() !== '' && desc !== '' || lot_nos.length > 0) {
            SaveItem($('#item').val(), $('#item_desc').val(), lot_nos);
                $('.btn_edit_lot').prop('disabled', false);
        } else {
            msg('Item Code is Required.', 'failed');
        }
    });

    $('#btn_delete').on('click', function() {
    	var arr_id = [];
    	$('.check_item:checked').each(function(index, el) {
    		arr_id.push($(this).val());
    	});

    	var question = "";

    	if (arr_id.length > 1) {
    		question = "Are you sure to delete these Items?";
    	} else {
    		question = "Are you sure to delete this Item?";
    	}

    	$("#confirm_id").val(arr_id);

    	confirm_modal(question);
    });

    $('#btn_confirm').on('click', function() {
    	$.ajax({
    		url: DeleteItemURL,
    		type: 'POST',
    		dataType: 'JSON',
    		data: {
    			_token: token,
    			ids: $('#confirm_id').val()
    		},
    	}).done( function(data,textStatus,xhr) {
    		getAllData();
    		ViewState();
    		$('#confirm_modal').modal('hide');

    	}).fail( function(xhr,textStatus,errorThrown) {
    		console.log("error");
    	});
    });

});

function ViewState() {
    $('#transaction_code').prop('readonly', false);
    $('#item').prop('readonly', true);
    $('#btn_search_item').prop('readonly', true);
    $('#btn_search_item').prop('disabled', true);
    $('.btn_edit_lot').hide();
    $('#btn_add').show();
    $('#btn_save').hide();
    $('#btn_edit').show();
    $('#btn_disregard').hide();
    $('#btn_search').show();
    $('#btn_export').show();
    $('#btn_delete').hide();
}
function AddState() {
    $('#transaction_code').prop('readonly', true);
    $('#item').prop('readonly', false);
    $('#btn_search_item').prop('disabled', false);
    $('#btn_add').hide();
    $('#btn_save').show();
    $('#btn_edit').hide();
    $('#btn_delete').hide();
    $('#btn_disregard').show();
    $('#btn_search').hide();
    $('#btn_export').hide();
    lot_nos = [];
}

function EditState() {
    $('#transaction_code').prop('readonly', false);
    $('.btn_edit_lot').prop('disabled', false);
    $('#btn_search_item').prop('disabled', false);
    $('#btn_add').hide();
    $('#btn_save').hide();
    $('#btn_edit').hide();
    $('#btn_disregard').show();
    $('#btn_search').hide();
    $('#btn_export').hide();
    $('#btn_delete').show();
}

function searchItemCode(item_code) {
    $('#loading').modal('show');
    $.ajax({
        url: getItemCodeURL,
        type: 'GET',
        dataType: 'JSON',
        data: {
            _token: token,
            item: item_code
        },
    }).done(function(data, textStatus, jqXHR) {
        // console.log(data);
        $('#item_desc').val(data.item_desc);
        lotNumberTable(data.lot_no);
        $('#lot_no_modal').modal('show');
    }).fail(function(xhr, textStatus, errorThrown) {
        console.log("error");
    }).always(function() {
        $('#loading').modal('hide');
    });
}

function lotNumberTable(arr) {
    $('#tbl_lotno').dataTable().fnClearTable();
    $('#tbl_lotno').dataTable().fnDestroy();
    $('#tbl_lotno').dataTable({
        deferRender: true,
        data: arr,
        columns: [{
            data: function(x) {
                return '<input type="checkbox" class="check_lot" value="' + x.id + '"' + 'data-item="' + x.item + '"' + 'data-item_desc="' + x.item_desc + '"' + 'data-lot_no="' + x.lot_no + '"' + 'data-qty="' + x.qty + '"' + '>';
            },
	            searchable: false,
	            orderable: false
	        }, {
	            data: 'item'
	        }, {
	            data: 'item_desc'
	        }, {
	            data: 'lot_no'
	        }, {
	            data: 'qty'
	        }
	    ]
    });
}

function SelectItem(lot_nos) {
    $('#tbl_items').dataTable().fnClearTable();
    $('#tbl_items').dataTable().fnDestroy();
    $('#tbl_items').dataTable({
        deferRender: true,
        data: lot_nos,
        columns: [
            {
            	data: function(x) {
	                return '<input type="checkbox" class="check_item" value="' + x.id + '">';
	            },
                searchable: false,
                orderable: false
            },
            {
                data: function(x) {
                    return '<button type="button" disabled  class="btn btn-sm blue btn_edit_lot" data-id="' + x.id + '"' + 'data-item="' + x.item + '"' + 'data-item_desc="' + x.item_desc + '"' + 'data-lot_no="' + x.lot_no + '"' + 'data-qty="' + x.qty + '"' + 'data-current_qty="' + x.current_qty + '"' + 'data-exp_date="' + x.exp_date + '"' + 'data-disposition="' + x.disposition + '"' + 'data-remarks="' + x.remarks + '"' + 'data-inv_id="' + x.inv_id + '"' + '><i class="fa fa-edit"></i></button>';
                },
                searchable: false,
                orderable: false
            }, {
                data: 'item'
            }, {
                data: 'item_desc'
            }, {
                data: 'qty'
            }, {
                data: 'lot_no'
            }, {
                data: 'exp_date'
            }, {
                data: 'disposition'
            }, {
                data: 'remarks'
            }
        ]
    });
}

function edit_itemmCode(item_code) {
    $('#loading').modal('show');
    $.ajax({
        url: getItemCodeURL,
        type: 'GET',
        dataType: 'JSON',
        data: {
            _token: token,
            item: item_code
        },
    }).done(function(data, textStatus, jqXHR) {
        $('#item_desc').val(data.item_desc);
        lotNumberTable(data.lot_no);
        $('#edit_lot_no_modal').modal('show');
    }).fail(function(xhr, textStatus, errorThrown) {
        console.log("error");
    }).always(function() {
        $('#loading').modal('hide');
    });
}

function SaveItem(item, item_desc, lot_nos) {
    $.ajax({
        url: saveLotNosURL,
        type: 'POST',
        dataType: 'JSON',
        data: {
            _token: token,
            lot_nos: lot_nos,
            item: item,
            item_desc: item_desc,
            transaction_id: $('#id').val(),
            transaction_code: $('#transaction_code').val()
            //createdby: createdby,
            //updatedby: updatedby
        },
    }).done(function(data, textStatus, jqXHR) {
        console.log(data);
        msg(data.msg, data.status);
        getAllData();
        ViewState();
    }).fail(function(xhr, textStatus, errorThrown) {
        console.log(errorThrown);
    });
}

function navigate(to) {
    getAllData($('#transaction_code').val(), to);
}

function getAllData(transaction_code = '', to = '') {
    $.ajax({
        url: getAllDataURL,
        type: 'GET',
        dataType: 'JSON',
        data: {
            _token: token,
            transaction_code: transaction_code,
            to: to
        },
    }).done(function(data, textStatus, jqXHR) {
        lot_nos = data.details;
        $('#id').val(data.info.id);
        $('#transaction_code').val(data.info.transaction_code);
        $('#item').val(data.info.item);
        $('#item_desc').val(data.info.item_desc);
        $('#createdby').val(data.info.update_user);
        $('#updatedby').val(data.info.update_user);
        SelectItem(lot_nos);
    }).fail(function(xhr, textStatus, errorThrown) {
        console.log("error");
    }).always(function() {
        console.log("complete");
    });
}

function getSearchedTransactions() {
    openloading();
    $.ajax({
        url: getSearchedMaterialsURL,
        type: 'GET',
        dataType: 'JSON',
        data: {
            item: $('#srch_item').val(),
            srch_from: $('#srch_from').val(),
            srch_to: $('#srch_to').val()
        },
    }).done(function(data, textStatus, jqXHR) {
        getMaterials(data);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        msg('Search Transaction: ' + errorThrown, textStatus);
    }).always(function() {
        closeloading();
    });
}

function getMaterials(transactions) {
    $('#tbl_search').dataTable().fnClearTable();
    $('#tbl_search').dataTable().fnDestroy();
    $('#tbl_search').dataTable({
        deferRender: true,
        data: transactions,
        columns: [{
            data: function(x) {
                return '<button type="button" class="btn btn-sm blue btn_search_edit_lot" data-id="' + x.id + '"' + 'data-transaction_code="' + x.transaction_code + '"' + 'data-item="' + x.item + '"' + 'data-item_desc="' + x.item_desc + '"' + 'data-create_user="' + x.create_user + '"' + 'data-created_at="' + x.created_at + '"' + 'data-update_user="' + x.update_user + '"' + 'data-updated_at="' + x.updated_at + '"' + 'data-updated_at="' + x.updated_at + '"' + '><i class="fa fa-edit"></i></button>';
            },
            searchable: false,
            orderable: false
        }, {
            data: 'transaction_code'
        }, {
            data: 'item'
        }, {
            data: 'item_desc'
        }, {
            data: 'create_user'
        }, {
            data: 'created_at'
        }, {
            data: 'update_user'
        }, {
            data: 'updated_at'
        }]
    });
}

function getDataExport() {
    window.location.href = exportMaterialURL + '?from=' + $('#from').val() + '&&to=' + $('#to').val();
}

function getCurrentQty(id) {
    $.ajax({
        url: getCurrentQtyURL,
        type: 'GET',
        dataType: 'JSON',
        data: {
            id: id
        }
    }).done(function(data, textStatus, jqXHR) {
        $('#edit_current_qty').val(data.current_qty);
    }).fail(function(xhr, textStatus, errorThrown) {
        console.log(errorThrown);
    }).always(function() {
        console.log("complete");
    });
}