viewState();

var dataColumn = [
		{ data: 'action', name: 'action', orderable: false, searchable: false },
		{ data: 'item', name: 'item' },
		{ data: 'item_desc', name: 'item_desc' },
		{ data: 'received_qty', name: 'received_qty' },
		{ data: 'qty', name: 'qty' },
		{ data: 'lot_no', name: 'lot_no' },
		{ data: 'location', name: 'location' },
		{ data: 'received_date', name: 'received_date' }
	];

$(document).on('click', function(e) {
	var isFocused = $('#hd_barcode').is(':focus');
	var isModalOpen = $('#itemModal').hasClass('in');
	if (isModalOpen == true) {
		$('#hd_barcode').focus();
	} else {
		// if ($('#brsense').val() == 'edit') {
		// 	$('#hd_barcode').focus();
		// }
	}
});

$(document).ready(function(e) {
	getLatest();
	

	$("#issueqty").focusout(function ()
	{
		var iqty = 0
		var rqqty = 0;

		if(parseInt($("#issueqty").val()))
		{
			iqty = parseInt($("#issueqty").val());

			if(parseInt($("#reqqty").val()))
			{
				rqqty = parseInt($("#reqqty").val());
			    $("#retqty").val(iqty - rqqty);
			}
			else
			{
				msg('Please input valid required quantity.','failed');
			}
		}
		else
		{
			msg('Please input valid issued quantity.','failed');
		}
	});


	$("#reqqty").focusout(function ()
	{
		var iqty = 0
		var rqqty = 0;

		if(parseInt($("#issueqty").val()))
		{
			iqty = parseInt($("#issueqty").val());
		}
		else
		{
			msg('Please input valid issued quantity.','failed');
		}

		if(parseInt($("#reqqty").val()))
		{
			rqqty = parseInt($("#reqqty").val());
		}
		else
		{
			msg('Please input valid required quantity.','failed');
		}

	    if(rqqty >= iqty)
	    {
	    	$("#retqty").val(iqty - rqqty);
	    }
	    else
	    {
	    	$("#retqty").val(iqty - rqqty);
	    }
	});

	var queryString = new Array;
	var query = location.search.substr(1);
	var result = {};


	query.split("&").forEach(function(part)
	{
		var item = part.split("=");

		if(decodeURIComponent(item[0]) == 'action'&&(decodeURIComponent(item[1]) == 'ADD'||decodeURIComponent(item[1]) == 'EDIT'))
		{
			var rowCount = $('#sample_3 tr').length;
			if(rowCount >= 2)
			{
				if(rowCount == 2)
				{
					$("#validShipModal").modal("show");
				}
				else
				{
					$("#itemModal").modal("show");
				}
			}
			else
			{
				msg('Please input valid and existing PO No. or Item Code','failed');
			}
		}
	});

	$('#receivingdate').datepicker({
		"autoclose":true
	});

	//selecting PO
	$('#btn_ponosaki').on('click', function(e) {
		$('#searchPOform').submit(function(e) {
			e.preventDefault();
			$('#item_tbl_body').html('');
			var url = $(this).attr('action');
			var data = {
				_token: token,
				po: $('#ponosaki').val(),
			}
			searchPO(url,data);
		});
	});

	//cancel PO
	$('#btn_confirm').on('click', function(){
		var data = {
			_token: token,
			po: $('#ponosaki').val()
		};
		$('#confirm_modal').modal('hide');
		save(cancelPOURL,data);
		geSakidashiData();
	})


	//selecting item inside PO
	$('#item_tbl_body').on('click', '.select_item', function() {
		var item = $(this).attr('data-item');
		var item_desc = $(this).attr('data-item_desc');
		var req_qty = $(this).attr('data-req_qty');
		var schedretdate = $(this).attr('data-schedretdate');
		$('#partcode').val(item);
		$('#hdnpartcode').val(item);
		$('#partname').val(item_desc);
		$('#reqqty').val(req_qty);
		$('#schedretdate').val(schedretdate);
		$('#itemModal').modal('hide');
		$('#hd_barcode').focus();
		loadfifo(item,$('#tblfifoAdd'));
		$('#fifoModal').modal('show');
		$('#lotno').focus();
		itemHistory();
	});

	//Barcoding item using barcode scanner
	$('#hd_barcode').on('change', function(e){
		if (checkIfInPO($('#hd_barcode').val(),$('#ponosaki').val())) {
			var item = $('#hd_barcode').val();
			var item_desc = $('.select_item').attr('data-item_desc');
			var req_qty = $('.select_item').attr('data-req_qty');
			var schedretdate = $('.select_item').attr('data-schedretdate');
			$('#partcode').val(item);
			$('#hdnpartcode').val(item);
			$('#partname').val(item_desc);
			$('#reqqty').val(req_qty);
			$('#schedretdate').val(schedretdate);
			$('#itemModal').modal('hide');
			loadfifo(item,$('#tblfifoAdd'));
			$('#fifoModal').modal('show');
			$('#lotno').focus();
			itemHistory();
		} else {
			msg('This Item code is not available in this P.O.','failed');
			$('#hd_barcode').val('');
			$('#hd_barcode').focus();
		}

	});

	// $('#lotno').on('change', function() {
	// 	if (checkInFIFO($('#partcode').val(),$(this).val()) == true) {
	// 		$('#fifoModal').modal('hide');
	// 	} else {
	// 		msg('This Lot No is not available in this Item code','failed');
	// 		$(this).val('');
	// 	}

	// });

	$('#btn_partcode').on('click', function() {
		$('#item_tbl_body').html('');
		var url = $('#searchPOform').attr('action');
		var data = {
			_token: token,
			po: $('#ponosaki').val(),
		}
		searchPO(url,data);
	});

	$('#btn_save').on('click', function() {
		var info = {
			issuanceno: $('#issuancenosaki').val(),
			po: $('#ponosaki').val(),
			code: $('#devicecodesaki').val(),
			devname: $('#devicenamesaki').val(),
			poqty: $('#poqtysaki').val(),
			incharge: $('#incharge').val(),
			remarks: $('#remarks').val(),
		};
		var details = {
			partcode: $('#partcode').val(),
			partname: $('#partname').val(),
			lotno: $('#lotno').val(),
			fifoid: $('#fifoid').val(),
			pairno: $('#pairno').val(),
			issueqty: $('#issueqty').val(),
			reqqty: $('#reqqty').val(),
			retqty: $('#retqty').val(),
			schedretdate: $('#schedretdate').val(),
		}
		var data = {
			_token: token,
			info: info,
			details: details,
		};
		saveRecord(saveIssuanceURL,data);
	});

	$('#issuancenosaki').focusout(function() {
		getTransCode($(this).val());
	});

	$('#btn_search').on('click', function() {
		$('#searchModal').modal('show');
	});

	$('#btn_receivedByConfirm').on('click', function() {
		$('#receivedBy_modal').modal('hide');
		$('#loading').modal('show');
		var self = $(this);
		var params = {
			_token: token,
			issuance_no : $('#issuancenosaki').val()
		};
		$.ajax({
			url: saveReceivedByURL,
			type: "POST",
			data: params,
		}).done(function (r, textStatus, jqXHR) {
			$('#loading').modal('hide');
			if(r.status == 'success') {
				msg(r.msg,r.status);
			}else {
				msg(r.msg,r.status);
			}
			geSakidashiData('', $('#issuancenosaki').val());
		}).fail(function (r, textStatus, jqXHR) {
			$('#loading').modal('hide');
			failedMsg("There's some error while processing.");
		});
	});

	$('#btn_received_by').on('click', function() {
		$('#receivedBy_modal').modal('show');
	});

	$('#srch_tbl_body').on('click', '.select_item_search', function() {
		var transcode = $(this).attr('data-transcode');
		getTransCode(transcode);
		$('#searchModal').modal('hide');
	});

	$('#btn_fiforeason').on('click', function() {
		var item = $('#fritem').val();
		var lotno = $('#frlotno').val();
		var qty = $('#frqty').val();
		var id = $('#frid').val();
		var issuanceno = $('#issuancenosaki').val();
		var reason = $('#fiforeason').val();

		if (reason == '') {
			msg('Please specify your reason for using this Lot Number.','failed');
		} else {
			saveFifoReason(item,lotno,qty,id,issuanceno,reason);
		}
	});

	$('#tblfifoAdd').on('click', '.btn_select_lot', function() {
		if ($(this).attr('data-rowcount') != 1) {
			$('#frlotno').val($(this).attr('data-lotno'));
			$('#frqty').val($(this).attr('data-qty'));
			$('#fritem_desc').val($(this).attr('data-item_desc'));
			$('#fritem').val($(this).attr('data-item'));
			$('#frfifo').val($(this).attr('data-id'));

            $('#fifoReasonModal').modal('show');

            $('#partcode').val($(this).attr('data-item'));
			$('#partname').val($(this).attr('data-item_desc'));
			$('#fifoid').val($(this).attr('data-id'));
			$('#issueqty').val($(this).attr('data-qty'));
			$('#lotno').val($(this).attr('data-lotno'));
		} else {
			$('#partcode').val($(this).attr('data-item'));
			$('#partname').val($(this).attr('data-item_desc'));
			$('#fifoid').val($(this).attr('data-id'));
			$('#issueqty').val($(this).attr('data-qty'));
			$('#lotno').val($(this).attr('data-lotno'));
			
			// if ($('#lotno').val() != '') {
			// 	$('#fifoModal').modal('hide');
			// }
		}

		var returnqty = parseFloat($('#issueqty').val()) - parseFloat($('#reqqty').val());
		$('#retqty').val(returnqty);
	});

	$('#btn_reasonlogs').on('click', function() {
		window.location.href = fifoReasonURL + "?issuanceno="+$('#issuancenosaki').val();
	});

	$('#btn_print_excel').on('click',function(){
		var issuanceno = $('#issuancenosaki').val();
		var partcode = $('#partcode').val();
		window.location.href = ExportToExcelURL +"?issuanceno=" + issuanceno + "&partcode=" + partcode;
	});
});

function setControl(ctrl) {
	if (ctrl == 'ADD') {
		$('#brsense').val('');
		addState();
	}

	if (ctrl == 'EDIT') {
		editState();
		$('#brsense').val('edit');
		$('#hd_barcode').focus();
	}

	if (ctrl == 'DISCARD') {
		$('#brsense').val('');
		viewState();
		getLatest();
	}
}

function saveFifoReason(item,lotno,qty,id,issuanceno,reason) {
	var data = {
		_token: token,
		item: item,
		lotno: lotno,
		issuanceno: issuanceno,
		reason: reason,
	};

	$.ajax({
		url: fifoReasonURL,
		type: 'POST',
		dataType: 'JSON',
		data: data
	}).done( function(data, textStatus, jqXHR) {
		if (data.status == 'success') {
			console.log(data);
			$('#lotnoiss').val(lotno);
			$('#qtyiss').val(qty);

			$('#editlotnoiss').val(lotno);
			$('#editqtyiss').val(qty);
			$('#fifoidedit').val(id);

			$('#fifoReasonModal').modal('hide');
			$('#fifoModal').modal('hide');
		} else {
			$('#fifoReasonModal').modal('hide');
			msg('Requesting Failed.','failed');
		}

	}).fail( function(data, textStatus, jqXHR) {
		msg("There was an error while processing.",'failed');
	});
}

function searchPO(url,data) {
	$('#loading').modal('show');
	$('#hd_barcode').focus();

	$.ajax({
		url: url,
		type: 'POST',
		data:  data,
	}).done(function(data, textStatus, jqXHR){
		var saki = JSON.parse(data);
		var info = saki.info;
		var details = saki.details;
		var item_tbl_body = '';

		console.log(data);

		if (saki.return_status == 'success') {
			$('#devicecodesaki').val(info['code']);
			$('#devicenamesaki').val(info['prodname']);
			$('#poqtysaki').val(info['POqty']);

			$.each(details, function(i, x) {
				item_tbl_body = '<tr>'+
									'<td style="width:14%">'+
										'<a href="javascript:;" class="btn btn-sm green select_item" data-schedretdate="'+saki.return_date+'"'+
											' data-item="'+x.kcode+'" data-item_desc="'+x.partname+'" data-req_qty="'+x.rqdqty+'">'+
											'<i class="fa fa-thumbs-up"></i>'+
										'</a>'+
									'</td>'+
									'<td style="width:20%">'+x.kcode+'</td>'+
									'<td style="width:30%">'+x.partname+'</td>'+
									'<td style="width:18%">'+x.rqdqty+'</td>'+
									'<td style="width:18%">'+x.actualqty+'</td>'+
								'</tr>';
				$('#item_tbl_body').append(item_tbl_body);
			});
			$('#loading').modal('hide');
			$('#itemModal').modal('show');

			openDetails();
		} else {
			$('#loading').modal('hide');
			msg(saki.msg,'failed');
		}
	}).fail(function(data, textStatus, jqXHR){
		$('#loading').modal('hide');
		msg("There's a problem occurred.",'error');
	});
}

function saveRecord(url,data) {
	$('#loading').modal('show');
	$.ajax({
		url: url,
		type: 'POST',
		data: data,
		dataType: 'JSON',
	}).done( function(data,textStatus,jqXHR) {
		console.log(data);
		if (data.return_status == 'success') {
			geSakidashiData(data.issuanceno);
			$('#loading').modal('hide');
			msg(data.msg,'success');
			viewState();
		} else {
			$('#loading').modal('hide');
			msg(data.msg,'failed');
		}
	}).fail( function(data,textStatus,jqXHR) {
		$('#loading').modal('hide');
		msg("There's a problem occurred.",'failed');
	});
}

function getLatest() {
	var data = {
		_token: token,
	}

	$.ajax({
		url: getLatestURL,
		type: 'GET',
		data: data,
		dataType: 'JSON',
	}).done( function(data,textStatus,jqXHR) {
		if (data != null) {
			valuesOfInfo(data);
			valuesOfDetails(data);
			itemHistory();
		} else {
			// msg("No data available.",'failed');
			console.log("Null");
		}
	}).fail( function(data,textStatus,jqXHR) {
		console.log(data);
		msg("There's a problem occurred.",'error');
	});
}

function geSakidashiData(issuanceno) {
	var data = {
		_token: token,
		issuanceno: issuanceno
	}

	$.ajax({
		url: getSakidashiDataURL,
		type: 'GET',
		data: data,
		dataType: 'JSON',
	}).done( function(data,textStatus,jqXHR) {
		console.log(data);
		if (data != null) {
			valuesOfInfo(data);
			valuesOfDetails(data);
			itemHistory();
		} else {
			getLatest();
			console.log("Null");
		}
	}).fail( function(data,textStatus,jqXHR) {
		console.log(data);
		msg("There's a problem occurred.",'error');
	});
}

function getTransCode(issuanceno) {
	$('#loading').modal('show');
	var data = {
		_token: token,
		issuanceno: issuanceno
	}

	$.ajax({
		url: getTransCodeURL,
		type: 'GET',
		data: data,
		dataType: 'JSON',
	}).done( function(data,textStatus,jqXHR) {
		$('#loading').modal('hide');
		valuesOfInfo(data);
		valuesOfDetails(data);
		itemHistory();
	}).fail( function(data,textStatus,jqXHR) {
		$('#loading').modal('hide');
		msg("There's a problem occurred.",'error');
	});
}

function valuesOfInfo(data) {
	console.log(data)
	$('#btn_received_by').prop('disabled',(data.received_taken == 1));
	$('#recid').val(data.id);
	$('#issuancenosaki').val(data.issuance_no);
	$('#ponosaki').val(data.po_no);
	$('#devicecodesaki').val(data.device_code);
	$('#devicenamesaki').val(data.device_name);
	$('#poqtysaki').val(data.po_qty);
	$('#incharge').val(data.incharge);
	$('#remarks').val(data.remarks);
	$('#statussaki').val(data.status);
	$('#createdbysaki').val(data.create_user);
	$('#createddatesaki').val(data.created_at);
	$('#updatedbysaki').val(data.update_user);
	$('#updateddatesaki').val(data.updated_at);
}

function valuesOfDetails(data) {
	$('#partcode').val(data.item);
	$('#hdnpartcode').val(data.item);
	$('#partname').val(data.item_desc);
	$('#lotno').val(data.lot_no);
	$('#pairno').val(data.pair_no);
	$('#issueqty').val(data.issued_qty);
	$('#reqqty').val(data.req_qty);
	$('#retqty').val(data.return_qty);
	$('#schedretdate').val(data.return_date);
}

function dataOfItemHistory(data) {
	var tbl_history = '';
	$.each(data, function(i, x) {
		tbl_history = '<tr>'+
							'<td style="width: 12.5%">'+x.issuance_no+'</td>'+
							'<td style="width: 12.5%">'+x.issued_qty+'</td>'+
							'<td style="width: 12.5%">'+x.req_qty+'</td>'+
							'<td style="width: 12.5%">'+x.return_qty+'</td>'+
							'<td style="width: 12.5%">'+x.lot_no+'</td>'+
							'<td style="width: 12.5%">'+x.pair_no+'</td>'+
							'<td style="width: 12.5%">'+x.remarks+'</td>'+
							'<td style="width: 12.5%">'+
								'<a href="javascript:;" class="btn btn-sm grey-gallery brcodebtn" data-id="'+x.id+'">'+
									'<i class="fa fa-barcode"></i></a>'+
							'</td>'
						'</tr>';

		$('#tbl_history').append(tbl_history);
	});
}

function itemHistory() {
	$('#tbl_history').html('');
	var data = {
		_token: token,
		po: $('#ponosaki').val(),
		item: $('#partcode').val()
	}

	$.ajax({
		url: historyURL,
		type: 'GET',
		data: data,
		dataType: 'JSON',
	}).done( function(data,textStatus,jqXHR) {
		if (data != null) {
			dataOfItemHistory(data);
		}
	}).fail( function(data,textStatus,jqXHR) {
		msg("There's a problem occurred.",'error');
	});
}

function viewState() {
	if (parseInt(access_state) !== 2) {
		$('#issuancenosaki').prop('readonly', false);
		$('#ponosaki').prop('readonly', true);
		$('#incharge').prop('readonly', true);
		$('#remarks').prop('readonly', true);
		$('#assessment').prop('readonly', true);

		$('#lotno').prop('readonly', true);
		$('#pairno').prop('readonly', true);
		$('#issueqty').prop('readonly', true);
		$('#reqqty').prop('readonly', true);
		$('#schedretdate').prop('disabled', true);

		$('#btn_ponosaki').prop('disabled', true);
		$('#btn_partcode').hide();

		$('#btn_min').prop('disabled', false);
		$('#btn_prv').prop('disabled', false);
		$('#btn_nxt').prop('disabled', false);
		$('#btn_max').prop('disabled', false);

		$('#btn_save').hide();
		$('#btn_cancel').hide();
		$('#btn_discard').hide();

		$('#btn_add').show();
		$('#btn_edit').show();
		$('#btn_search').show();
		$('#btn_print').show();
	} else {
		$('#issuancenosaki').prop('readonly', false);
		$('#ponosaki').prop('readonly', true);
		$('#incharge').prop('readonly', true);
		$('#remarks').prop('readonly', true);
		$('#assessment').prop('readonly', true);

		$('#lotno').prop('readonly', true);
		$('#pairno').prop('readonly', true);
		$('#issueqty').prop('readonly', true);
		$('#reqqty').prop('readonly', true);
		$('#schedretdate').prop('disabled', true);

		$('#btn_ponosaki').prop('disabled', true);
		$('#btn_partcode').hide();

		$('#btn_min').prop('disabled', false);
		$('#btn_prv').prop('disabled', false);
		$('#btn_nxt').prop('disabled', false);
		$('#btn_max').prop('disabled', false);

		$('#btn_save').hide();
		$('#btn_cancel').hide();
		$('#btn_discard').hide();

		$('#btn_add').hide();
		$('#btn_edit').hide();
		$('#btn_search').show();
		$('#btn_print').show();
	}
}

function addState() {
	$('.clear').val('');
	$('#issuancenosaki').prop('readonly', true);
	$('#ponosaki').prop('readonly', false);
	$('#incharge').prop('readonly', false);
	$('#remarks').prop('readonly', false);
	$('#btn_ponosaki').prop('disabled', false);

	$('#btn_min').prop('disabled', true);
	$('#btn_prv').prop('disabled', true);
	$('#btn_nxt').prop('disabled', true);
	$('#btn_max').prop('disabled', true);

	$('#btn_save').show();
	$('#btn_cancel').hide();
	$('#btn_discard').show();

	$('#btn_add').hide();
	$('#btn_edit').hide();
	$('#btn_search').hide();
	$('#btn_print').hide();
}

function openDetails() {
	$('#btn_partcode').show();
	$('#lotno').prop('readonly', false);
	$('#pairno').prop('readonly', false);
	$('#issueqty').prop('readonly', false);
	$('#reqqty').prop('readonly', false);
	$('#schedretdate').prop('readonly', false);
}

function editState() {
	$('#btn_partcode').show();
	$('#lotno').prop('readonly', true);
	$('#pairno').prop('readonly', false);
	$('#issueqty').prop('readonly', false);
	$('#reqqty').prop('readonly', false);
	$('#schedretdate').prop('readonly', false);

	$('#btn_min').prop('disabled', true);
	$('#btn_prv').prop('disabled', true);
	$('#btn_nxt').prop('disabled', true);
	$('#btn_max').prop('disabled', true);

	$('#btn_save').show();
	$('#btn_cancel').show();
	$('#btn_discard').show();

	$('#btn_add').hide();
	$('#btn_edit').hide();
	$('#btn_search').hide();
	$('#btn_print').hide();

	$('#brsense').val('edit');

	$('#hd_barcode').focus();
}

function search() {
	$('#srch_tbl_body').html('');
	$('#loading').modal('show');
	var condition_arr = {
		srch_pono: $('#srch_pono').val(),
		srch_devicecode: $('#srch_devicecode').val(),
		srch_itemcode: $('#srch_itemcode').val(),
		srch_incharge: $('#srch_incharge').val(),
		srch_open: (($('#srch_open').is(':checked')) ? 1 : 0),
		srch_close: (($('#srch_close').is(':checked')) ? 1 : 0),
		srch_cancelled: (($('#srch_cancelled').is(':checked')) ? 1 : 0),
	}
	// var data = {
	// 	_token: token,
	// 	condition_arr: condition_arr
	// }

	$('#srch_tbl').DataTable().clear();
	$('#srch_tbl').DataTable().destroy();
	$('#srch_tbl').DataTable({
		processing: true,
		deferRender: true,
		ajax: {
			url: searchURL,
			dataType: "JSON",
			type: "POST",
			data: function (d) {
				d._token = token;
				d.condition_arr = condition_arr;
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
			[12, "desc"]
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
				data: function (x) {
					return '<a href="javascript:;" class="btn btn-sm blue select_item_search" data-transcode="' + x.issuance_no + '">' +
						'<i class="fa fa-edit"></i>' +
						'</a>';
				}, orderable: false, searchable: false, name: "issuance_no"
			},
			{ data: 'issuance_no', name: 'issuance_no' },
			{ data: 'po_no', name: 'po_no' },
			{ data: 'device_code', name: 'device_code' },
			{ data: 'device_name', name: 'device_name' },
			{ data: 'incharge', name: 'incharge' },
			{ data: 'item', name: 'item' },
			{ data: 'item_desc', name: 'item_desc' },
			{ data: 'status', name: 'status' },
			{ data: 'create_user', name: 'create_user' },
			{ data: 'created_at', name: 'created_at' },
			{ data: 'update_user', name: 'update_user' },
			{ data: 'updated_at', name: 'updated_at' },
		],
		createdRow: function (row, data, dataIndex) {
			// var dataRow = $(row);
			// var status_td = $(dataRow[0].cells[2]);

		},
		initComplete: function () {
			$('#loading').modal('hide');
		}
	});

	// $.ajax({
	// 	url: searchURL,
	// 	type: 'POST',
	// 	data: data,
	// 	dataType: 'JSON',
	// }).done( function(data,textStatus,jqXHR) {
	// 	$('#loading').modal('hide');
	// 	$.each(data, function(i, x) {
	// 		srch_tbl_body = '<tr>'+
	// 							'<td style="width: 4.69%">'+
	// 								'<a href="javascript:;" class="btn btn-sm blue select_item_search" data-transcode="'+x.issuance_no+'">'+
	// 									'<i class="fa fa-edit"></i>'+
	// 								'</a>'+
	// 							'</td>'+
	// 							'<td style="width: 7.69%">'+x.issuance_no+'</td>'+
	// 							'<td style="width: 7.69%">'+x.po_no+'</td>'+
	// 							'<td style="width: 7.69%">'+x.device_code+'</td>'+
	// 							'<td style="width: 10.69%">'+x.device_name+'</td>'+
	// 							'<td style="width: 7.69%">'+x.incharge+'</td>'+
	// 							'<td style="width: 7.69%">'+x.item+'</td>'+
	// 							'<td style="width: 11.69%">'+x.item_desc+'</td>'+
	// 							'<td style="width: 5.69%">'+x.status+'</td>'+
	// 							'<td style="width: 6.69%">'+x.create_user+'</td>'+
	// 							'<td style="width: 7.69%">'+x.created_at+'</td>'+
	// 							'<td style="width: 6.69%">'+x.update_user+'</td>'+
	// 							'<td style="width: 7.69%">'+x.updated_at+'</td>'+
	// 						'</tr>';
	// 		$('#srch_tbl_body').append(srch_tbl_body);
	// 	});
	// }).fail( function(data,textStatus,jqXHR) {
	// 	$('#loading').modal('hide');
	// 	msg("There's a problem occurred.",'failed');
	// });
}

function reset() {
	$('.clear_search').val('');
	$('#srch_tbl_body tr').remove();
}

function nav(to) {
	//$('#loading').modal('show');
	var data = {
		_token: token,
		issuanceno: $('#issuancenosaki').val(),
		to: to
	}

	$.ajax({
		url: navigationURL,
		type: 'GET',
		data: data,
		dataType: 'JSON',
	}).done( function(data,textStatus,jqXHR) {
		//$('#loading').modal('hide');
		console.log(data);
		if (data == null) {
			//msg("There's no data found.",'failed');
		} else {
			valuesOfInfo(data);
			valuesOfDetails(data);
			itemHistory();
		}
	}).fail( function(data,textStatus,jqXHR) {
		//$('#loading').modal('hide');
		msg("There's a problem occurred.",'error');
	});
}

function cancelPO() {
	var data = {
		_token: token,
		po: $('#ponosaki').val()
	}

	$.ajax({
		url: cancelPOURL,
		type: 'POST',
		data: data,
		dataType: 'JSON',
	}).done( function(data,textStatus,jqXHR) {
		getTransCode($('#issuancenosaki').val());
	}).fail( function(data,textStatus,jqXHR) {
		$('#loading').modal('hide');
		msg("There's a problem occurred.",'error');
	});
}

function generateSiReport()
{
	window.open( issuanceSheetURL + '?id=' + $("#recid").val(), '_blank');
}


function loadfifo(code,table) {
	var fifoURLs = fifoURL+'?_token='+token+'&code='+code;

	getDatatable('tblfifo',fifoURLs,dataColumn,[],0);
	// table.html('');
 // 	var tblfifoAdd = '';
 // 	var data = {
 // 		_token: token,
 // 		code  : code
 // 	};

 // 	$.ajax({
 // 		url: fifoURL,
 //        type: "GET",
 //        data: data,
 // 	}).done(function(data) {
 // 		var cnt = 1;
 // 		$.each(data, function(i, x) {
 // 			tblfifoAdd = '<tr>'+
 // 							'<td width="8.5%">'+
 // 								'<a href="javascript:;" class="btn btn-primary btn_select_lot btn-sm" data-id="'+x.id+'" '+
 // 								'data-rowcount="'+cnt+'" data-item="'+x.item+'" data-item_desc="'+x.item_desc+'" data-lotno="'+x.lot_no+'" data-qty="'+x.qty+'">'+
 // 									'<i class="fa fa-pencil"></i>'+
 // 								'</a>'+
 // 							'</td>'+
 //                            '<td width="12.5%">'+x.item+'</td>'+
 //                            '<td width="20.5%">'+x.item_desc+'</td>'+
 //                            '<td width="8.5%">'+x.received_qty+'</td>'+
 //                            '<td width="8.5%">'+x.qty+'</td>'+
 //                            '<td width="12.5%">'+x.lot_no+'</td>'+
 //                            '<td width="14.5%">'+x.location+'</td>'+
 //                            '<td width="14.5%">'+x.received_date+'</td>'+
 //                        '</tr>';

 //    		table.append(tblfifoAdd);
 //    		cnt++;
 // 		});
 // 	}).fail(function(data) {
 // 		msg("There's a problem occurred.",'error');
 // 	});
}

function checkIfInPO(item,po) {
	var data = {
		_token:token,
		item:item,
		po:po,
	};

	$.ajax({
		url:checkInPOURL,
		type:'get',
		dataType:'JSON',
		data:data,
	}).done( function(data,txtStatus) {
		if (data > 0) {
			return true;
		} else {
			return false;
		}
	}).fail( function(data,txtStatus) {
		msg("There's a problem occurred.",'error');
	});
}

function checkInFIFO(item,lot) {
	var data = {
		_token:token,
		item:item,
		lot:lot,
	};

	$.ajax({
		url: checkInFifioURL,
		type:'get',
		dataType:'JSON',
		data:data,
	}).done( function(data,txtStatus) {
		if (data > 0) {
			return true;
		} else {
			return false;
		}
	}).fail( function(data,txtStatus) {
		alert('error');
	});
}

$('#tbl_history').on('click', '.brcodebtn',function() {
	var id = $(this).attr('data-id');

	if (isOnMobile() == true) {
		printBRcode(id);
	} else {
		$('#msg').modal('show');
		$('#title').html('<strong><i class="fa fa-exclamation-triangle"></i></strong> Notice!')
		$('#err_msg').html('Please use mobile device to directly print barcodes.');
		printBRcode(id);
	}
});

function printBRcode(id) {
	window.location.href= printBarcodeURL+"?id="+id;
}

function isOnMobile() {
	var isMobile = false; //initiate as false
	// device detection
	if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;

	return isMobile;
}