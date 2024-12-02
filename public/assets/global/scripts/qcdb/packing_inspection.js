var inspected_arr = [];
var runcard_arr = [];
var mod_arr = [];
var _stamp = getStampCode();
var _packcodeperseries = '';
var date = new Date();
var _month = ("0" + (date.getMonth() + 1)).slice(-2);
var _carton = '';
var _packingcode = '';

$( function() {

	$('body').on('keydown', '.enter', function(e) {
		var self = $(this)
			, form = self.parents('form:eq(0)')
			, focusable
			, next
			;

		console.log(self);
		if (e.keyCode == 13) {
			focusable = form.find('.enter').filter(':visible');
			console.log(focusable);
			next = focusable.eq(focusable.index(this)+1);

			if (next.length) {
				next.focus();
			} else {
				form.submit();
			}
			return false;
		}
	});

	makeDataInspectedTable(getDataInspectedURL);

	initData();

	checkAllCheckboxesInTable('.check_all','.check_item');
	checkAllCheckboxesInTable('.check_all_runcard','.check_item_runcard');
	checkAllCheckboxesInTable('.check_all_mod','.check_item_mod');

	$('#btn_add').on('click', function() {
		$('.clear').val('');
		$('#inspector').val(current_user);
		$('#inspection_modal').modal('show');
	});

	$('#tbl_packing_inspection_body').on('click', '.btn_edit_inspection', function() {
		$('#id').val($(this).attr('data-id'));
		$('#po_num').val($(this).attr('data-po_num'));
		$('#date_inspected').val($(this).attr('data-date_inspected'));
		$('#shipment_date').val($(this).attr('data-shipment_date'));
		$('#device_name').val($(this).attr('data-device_name'));
		$('#inspector').val($(this).attr('data-inspector'));
		$('#packing_type').val($(this).attr('data-packing_type'));
		$('#unit_condition').val($(this).attr('data-unit_condition'));
		$('#packing_operator').val($(this).attr('data-packing_operator'));
		$('#remarks').val($(this).attr('data-remarks'));
		$('#packing_code_series').val($(this).attr('data-packing_code_series'));
		$('#carton_num').val($(this).attr('data-carton_num'));
		$('#packing_code').val($(this).attr('data-packing_code'));
		$('#judgement').val($(this).attr('data-judgement'));
		$('#total_qty').val($(this).attr('data-total_qty'));

		getRuncard($(this).attr('data-id'),$(this).attr('data-po_num'));
		getMOD($(this).attr('data-id'),$(this).attr('data-po_num'));

		$('#inspection_modal').modal('show');
	});

	$('#btn_save').on('click', function() {
		$('#loading').modal('show');
		inspected_arr = [];

		$.ajax({
			url: saveFormURL,
			type: 'POST',
			dataType: 'JSON',
			data: {
				_token: token,
				po_num: $('#po_num').val(),
				id: $('#id').val(),
				date_inspected: $('#date_inspected').val(),
				shipment_date: $('#shipment_date').val(),
				device_name: $('#device_name').val(),
				inspector: $('#inspector').val(),
				packing_type: $('#packing_type').val(),
				unit_condition: $('#unit_condition').val(),
				packing_operator: $('#packing_operator').val(),
				remarks: $('#remarks').val(),
				packing_code_series: $('#packing_code_series').val(),
				carton_num: $('#carton_num').val(),
				packing_code: $('#packing_code').val(),
				judgement: $('#judgement').val(),
				total_qty: $('#total_qty').val(),
				no_of_defects: $('#no_of_defects').val()
			},
		}).done(function(data, textStatus, xhr) {
			makeDataInspectedTable(getDataInspectedURL);
			if (data.status !== 'no_change') {
				msg(data.msg,data.status);
			}
		}).fail(function(xhr, textStatus, errorThrown) {
			msg(errorThrown,textStatus);
		}).always(function() {
			$('#loading').modal('hide');
		});
	});

	// $('#frm_inspection').on('submit', function(e) {
	// 	e.preventDefault();
	// 	$('#loading').modal('show');
	// 	inspected_arr = [];

	// 	$.ajax({
	// 		url: $(this).attr('action'),
	// 		type: 'POST',
	// 		dataType: 'JSON',
	// 		data: $(this).serialize(),
	// 	}).done(function(data, textStatus, xhr) {
	// 		makeDataInspectedTable(getDataInspectedURL);
	// 		if (data.status !== 'no_change') {
	// 			msg(data.msg,data.status);
	// 		}
	// 	}).fail(function(xhr, textStatus, errorThrown) {
	// 		msg(errorThrown,textStatus);
	// 	}).always(function() {
	// 		$('#loading').modal('hide');
	// 	});
	// });

	$('#frm_runcard').on('submit', function(e) {
		e.preventDefault();
		$('#loading').modal('show');
		runcard_arr = [];

		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
		}).done(function(data, textStatus, xhr) {
			$('#runcard_id').val('');
			$('#runcard_no').val('');
			$('#runcard_qty').val('');
			runcard_arr = data.runcard;

			if (data.total_qty != undefined) {
				$('#total_qty').val(data.total_qty);
			}

			makeRuncardTable(runcard_arr);
			if (data.status !== 'no_change') {
				msg(data.msg,data.status);
			}
		}).fail(function(xhr, textStatus, errorThrown) {
			ErrorMsg(xhr);
			//msg(errorThrown,textStatus);
		}).always(function() {
			$('#loading').modal('hide');
		});
	});

	$('#frm_mode_of_defects').on('submit', function(e) {
		e.preventDefault();
		$('#loading').modal('show');
		mod_arr = [];

		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
		}).done(function(data, textStatus, xhr) {
			$('#mod_id').val('');
			$('#mod').val('');
			$('#mod_qty').val('');
			$('#no_of_defects').val(data.no_of_defects);
			mod_arr = data.mod;
			makeMODTable(mod_arr);

			if (data.total_qty != undefined) {
				$('#no_of_defects').val(data.total_qty);
			}

			if (data.status !== 'no_change') {
				msg(data.msg,data.status);
			}
		}).fail(function(xhr, textStatus, errorThrown) {
			msg(errorThrown,textStatus);
		}).always(function() {
			$('#loading').modal('hide');
		});
	});

	$('#btn_runcard').on('click', function() {
		if ($('#po_num').val() == '') {
			msg('Please fill out the P.O. number.', 'failed');
		} else {
			$('#runcard_po').val($('#po_num').val());
			$('#runcard_modal').modal('show');
		}
	});

	$('#btn_mode_of_defects').on('click', function() {
		if ($('#po_num').val() == '') {
			msg('Please fill out the P.O. number.', 'failed');
		} else {
			$('#mod_po_inspection').val($('#po_num').val());
			$('#mode_of_defects_modal').modal('show');
		}
	});

	$('#tbl_runcard_body').on('click', '.btn_edit_runcard', function() {
		$('#runcard_id').val($(this).attr('data-id'));
		$('#runcard_po').val($(this).attr('data-pono'));
		$('#runcard_no').val($(this).attr('data-runcard_no'));
		$('#runcard_qty').val($(this).attr('data-runcard_qty'));
		$('#runcard_remarks').val($(this).attr('data-runcard_remarks'));
	});

	$('#tbl_mode_of_defects_body').on('click', '.btn_edit_mod', function() {
		$('#mod_id').val($(this).attr('data-id'));
		$('#mod').val($(this).attr('data-mod'));
		$('#mod_qty').val($(this).attr('data-qty'));
		$('#mod_id_inspection').val($(this).attr('data-inspection_id'));
		$('#mod_po_inspection').val($(this).attr('data-po_no'));
	});

	$('#btn_delete').on('click', function() {
		$('#delete_id').val('inspection');
		delete_modal();
	});

	$('#btn_delete_runcard').on('click', function() {
		$('#delete_id').val('runcard');
		delete_modal();
	});

	$('#btn_delete_mod').on('click', function() {
		$('#delete_id').val('mod');
		delete_modal();
	});

	$('#btn_confirm_delete').on('click', function() {
		var chkArray = [];
		switch($('#delete_id').val()) {
			case 'inspection':
				$(".check_item:checked").each(function() {
					chkArray.push($(this).attr('data-id'));
				});
				var deleteURL = deleteInspectionURL;
				delete_data(deleteURL,chkArray,'inspection');
				break;
			case 'runcard':
				$(".check_item_runcard:checked").each(function() {
					chkArray.push($(this).attr('data-id'));
				});
				var deleteURL = deleteRuncardURL;
				delete_data(deleteURL,chkArray,'runcard');
				break;
			case 'mod':
				$(".check_item_mod:checked").each(function() {
					chkArray.push($(this).attr('data-id'));
				});
				var deleteURL = deleteMODURL;
				delete_data(deleteURL,chkArray,'mod');
				break;
		}
	});

	$('#po_num').on('focusout', function() {
		getPOdetails($(this).val());
	});

	$('#packing_code_series').on('change', function() {
		_packcodeperseries = $(this).val();
		var stamp = '';
		if (_stamp !== undefined) {
			stamp = _stamp;
			$('#er_inspector').html('');
		} else {
			$('#er_inspector').html('Please register to Dropdown Master');
		}
		_packingcode = _packcodeperseries+'-'+_month+'-'+_carton+stamp;
		$('#packing_code').val(_packingcode);
	});

	$('#carton_num').on('keyup', function() {
		_carton = $(this).val();
		var stamp = '';
		if (_stamp !== undefined) {
			stamp = _stamp;
			$('#er_inspector').html('');
		} else {
			$('#er_inspector').html('Please register to Dropdown Master');
		}
		_packingcode = _packcodeperseries+'-'+_month+'-'+_carton+stamp;
		$('#packing_code').val(_packingcode);
	});

	$('#btn_search').on('click', function() {
		$('#search_modal').modal('show');
	});

	$('#btn_groupby').on('click', function() {
		$('#group_by_modal').modal('show');
	});

	$('#pdf_search').on('click', function() {
		var param = {
			search_po: $('#search_po').val(),
			search_from: $('#search_from').val(),
			search_to: $('#search_to').val(),
			_token: token
		}

		ReportDataCheck(param, function(output) {
			if (output > 0) {
				var link = searchPdfURL+'?search_po='+$('#search_po').val()+'&&'+
						'search_from='+$('#search_from').val()+'&&search_to='+$('#search_to').val()+'&&'+
						'_token='+token;


				window.open(link,'_tab');
			} else {
				msg('No data was retrieved.','failed');
			}
		});
	});

	$('#excel_search').on('click', function() {
		var param = {
			search_po: $('#search_po').val(),
			search_from: $('#search_from').val(),
			search_to: $('#search_to').val(),
			_token: token
		}
		
		ReportDataCheck(param, function(output) {
			if (output > 0) {
				window.location.href = searchExcelURL+'?search_po='+$('#search_po').val()+'&&'+
				'search_from='+$('#search_from').val()+'&&search_to='+$('#search_to').val()+'&&'+
				'_token='+token;
			} else {
				msg('No data was retrieved.','failed');
			}
		});
	});

	$('#field1').on('change', function() {
		GroupByValues($(this).val(),$('#content1'));
	});

	$('#field2').on('change', function() {
		GroupByValues($(this).val(),$('#content2'));
	});

	$('#field3').on('change', function() {
		GroupByValues($(this).val(),$('#content3'));
	});

	$('#btn_search_data').on('click', function() {
		var url = searchDataURL+'?search_po='+$('#search_po').val()+'&&'+
						'search_from='+$('#search_from').val()+'&&search_to='+$('#search_to').val()+'&&'+
						'_token='+token
		makeDataInspectedTable(url);
	});

	$('#tbl_packing_inspection_body').on('click', '.view_inspection',function(e) {
		$('#id').val($(this).attr('data-id'));
		$('#po_num').val($(this).attr('data-po_num'));
		$('#date_inspected').val($(this).attr('data-date_inspected'));
		$('#shipment_date').val($(this).attr('data-shipment_date'));
		$('#device_name').val($(this).attr('data-device_name'));
		$('#inspector').val($(this).attr('data-inspector'));
		$('#packing_type').val($(this).attr('data-packing_type'));
		$('#unit_condition').val($(this).attr('data-unit_condition'));
		$('#packing_operator').val($(this).attr('data-packing_operator'));
		$('#remarks').val($(this).attr('data-remarks'));
		$('#packing_code_series').val($(this).attr('data-packing_code_series'));
		$('#carton_num').val($(this).attr('data-carton_num'));
		$('#packing_code').val($(this).attr('data-packing_code'));
		$('#judgement').val($(this).attr('data-judgement'));
		$('#total_qty').val($(this).attr('data-total_qty'));

		getRuncard($(this).attr('data-id'),$(this).attr('data-po_num'));
		getMOD($(this).attr('data-id'),$(this).attr('data-po_num'));

		$('#inspection_modal').modal('show');
	});
});

function getDataInspected() {
	inspected_arr = [];

	$.ajax({
		url: getDataInspectedURL,
		type: 'GET',
		dataType: 'JSON',
		data: {
			_token: token,
		},
	}).done(function(data, textStatus, xhr) {
		inspected_arr = data;
		makeDataInspectedTable(inspected_arr);
	}).fail(function(xhr, textStatus, errorThrown) {
		msg(errorThrown,textStatus);
	}).always(function() {
		$('#loading').modal('hide');
	});
}

function getRuncard(inspection_id,po) {
	runcard_arr = [];

	$.ajax({
		url: getRuncardURL,
		type: 'GET',
		dataType: 'JSON',
		data: {
			_token: token,
			inspection_id: inspection_id,
			pono:po
		},
	}).done(function(data, textStatus, xhr) {
		$('#runcard_id_inspection').val($('#id').val());
		$('#runcard_po').val($('#po_num').val());
		$('#runcard_carton_no').val($('#carton_num').val());
		runcard_arr = data;
		makeRuncardTable(runcard_arr);
	}).fail(function(xhr, textStatus, errorThrown) {
		msg(errorThrown,textStatus);
	}).always(function() {
		$('#loading').modal('hide');
	});
}

function getMOD(inspection_id,po) {
	runcard_arr = [];

	$.ajax({
		url: getMODURL,
		type: 'GET',
		dataType: 'JSON',
		data: {
			_token: token,
			inspection_id: inspection_id,
			po_no:po
		},
	}).done(function(data, textStatus, xhr) {
		$('#mod_id_inspection').val($('#id').val());
		$('#mod_po_inspection').val($('#po_num').val());
		$('#no_of_defects').val(data.no_of_defects);
		mod_arr = data.mod;
		makeMODTable(mod_arr);
	}).fail(function(xhr, textStatus, errorThrown) {
		console.log(xhr);
		msg(errorThrown,textStatus);
	}).always(function() {
		$('#loading').modal('hide');
	});
}

function makeDataInspectedTable(url) {
	$('#tbl_packing_inspection').DataTable().clear();
	$('#tbl_packing_inspection').DataTable().destroy();
	$('#tbl_packing_inspection').DataTable({
		processing: true,
		serverSide: true,
		ajax: url,
		deferRender: true,
		//data: url,
		lengthMenu: [
			[5, 10, 20, 100, -1],
			[5, 10, 20, 100,"All"]
		],
		pageLength: 10, 
		columns: [
			{ data: function(x) {
				return '<input type="checkbox" class="check_item" data-id="'+x.id+'" value="'+x.id+'">';
			}, searchable: false, orderable: false, name: 'id' },

			{ data: function(x) {
				return '<button class="btn blue btn-sm btn_edit_inspection" '+
							'data-id="'+x.id+'"'+
							'data-date_inspected="'+x.date_inspected+'"'+
							'data-shipment_date="'+x.shipment_date+'"'+
							'data-device_name="'+x.device_name+'"'+
							'data-po_num="'+x.po_num+'"'+
							'data-packing_operator="'+x.packing_operator+'"'+
							'data-inspector="'+x.inspector+'"'+
							'data-packing_type="'+x.packing_type+'"'+
							'data-unit_condition="'+x.unit_condition+'"'+
							'data-packing_code_series="'+x.packing_code_series+'"'+
							'data-carton_num="'+x.carton_num+'"'+
							'data-packing_code="'+x.packing_code+'"'+
							'data-total_qty="'+x.total_qty+'"'+
							'data-judgement="'+x.judgement+'"'+
							'data-remarks="'+x.remarks+'">'+
							'<i class="fa fa-edit"></i>'+
						'</button>';
			}, searchable: false, orderable: false, name: 'action' },

			{ data: 'date_inspected', name: 'date_inspected' },
			{ data: 'shipment_date', name: 'shipment_date' },
			{ data: 'device_name', name: 'device_name' },
			{ data: 'po_num', name: 'po_num' },
			{ data: 'packing_operator', name: 'packing_operator' },
			{ data: 'inspector', name: 'inspector' },
			{ data: 'packing_type', name: 'packing_type' },
			{ data: 'unit_condition', name: 'unit_condition' },
			{ data: 'packing_code_series', name: 'packing_code_series' },
			{ data: 'carton_num', name: 'carton_num' },
			{ data: 'packing_code', name: 'packing_code' },
			{ data: 'total_qty', name: 'total_qty' },
			{ data: 'judgement', name: 'judgement' },
			{ data: 'remarks', name: 'remarks' }
		],
		fnDrawCallback: function () {
			$("#tbl_packing_inspection").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
		},
		order: [
			[2, 'desc']
		]
	});
}

function makeRuncardTable(arr) {
	$('#tbl_runcard').DataTable().clear();
	$('#tbl_runcard').DataTable().destroy();
	$('#tbl_runcard').DataTable({
		data: arr,
		// bLengthChange : false,
		// scrollY: "200px",
		searching: false,
		// paging: false,
		columns: [
			{ data: function(x) {
				return '<input type="checkbox" class="check_item_runcard" data-id="'+x.id+'" value="'+x.id+'">';
			}, searchable: false, orderable: false },

			{ data: function(x) {
				return '<button type="button" class="btn btn-sm btn-primary btn_edit_runcard" '+
								'data-id="'+x.id+'"'+
								'data-pono="'+x.pono+'"'+
								'data-runcard_no="'+x.runcard_no+'"'+
								'data-runcard_qty="'+x.runcard_qty+'"'+
								'data-runcard_remarks="'+x.runcard_remarks+'">'+
							'<i class="fa fa-edit"></i>'+
						'</button>';
			}, searchable: false, orderable: false },

			{ data: 'pono' },
			{ data: 'runcard_no' },
			{ data: 'runcard_qty' },
			{ data: 'runcard_remarks' }
		], fnDrawCallback: function () {
			$("#tbl_runcard").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
		},
		order: [
			[2, 'desc']
		]
	});
}

function makeMODTable(arr) {
	$('#tbl_mode_of_defects').DataTable().clear();
	$('#tbl_mode_of_defects').DataTable().destroy();
	$('#tbl_mode_of_defects').DataTable({
		data: arr,
		// bLengthChange : false,
		// scrollY: "200px",
		searching: false,
		// paging: false,
		columns: [
			{ data: function(x) {
				return '<input type="checkbox" class="check_item_mod" data-id="'+x.id+'" value="'+x.id+'">';
			}, searchable: false, orderable: false },

			{ data: function(x) {
				return '<button type="button" class="btn btn-sm btn-primary btn_edit_mod" '+
								'data-id="'+x.id+'"'+
								'data-mod="'+x.mod+'"'+
								'data-qty="'+x.qty+'"'+
								'data-inspection_id="'+x.inspection_id+'"'+
								'data-po_no="'+x.po_no+'">'+
							'<i class="fa fa-edit"></i>'+
						'</button>';
			}, searchable: false, orderable: false },

			{ data: 'mod' },
			{ data: 'qty' },
		], fnDrawCallback: function () {
			$("#tbl_mode_of_defects").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
		},
		order: [
			[2, 'desc']
		]
		// columnDefs: [
		// 	{ "width": "15%", "targets": 0 },
		// 	{ "width": "25%", "targets": 1 },
		// 	{ "width": "25%", "targets": 2 },
		// 	{ "width": "35%", "targets": 3 },
		// ]
	});
}

function initData() {
	$.ajax({
		url: initdataURL,
		type: 'GET',
		dataType: 'JSON',
		data: {_token: token},
	}).done(function(data,textStatus,jqXHR) {
		$.each(data.packing_type, function(i, x) {
			$('#packing_type').append('<option value="'+x.description+'">'+x.description+'</option>');
		});

		$.each(data.unit_condition, function(i, x) {
			$('#unit_condition').append('<option value="'+x.description+'">'+x.description+'</option>');
		});

		$.each(data.packing_code_series, function(i, x) {
			$('#packing_code_series').append('<option value="'+x.description+'">'+x.description+'</option>');
		});

		$.each(data.packing_operator, function(i, x) {
			$('#packing_operator').append('<option value="'+x.description+'">'+x.description+'</option>');
		});

		$.each(data.mods, function(i, x) {
			$('#mod').append('<option value="'+x.description+'">'+x.description+'</option>');
		});
	}).fail(function(data,textStatus,jqXHR) {
		console.log("error");
	});
}

function getStampCode() {
	$.ajax({
		url: getStampCodeURL,
		type: 'GET',
		dataType: 'JSON',
		data: {_token: token},
	}).done(function(data, textStatus, xhr) {
		if (data !== null) {
			var x = data[1];
			_stamp = x.replace('OQC','');
			console.log(_stamp);
			return _stamp;
		}
			
		// $('#pack_code').val(_packingcode);
	}).fail(function(xhr, textStatus, errorThrown) {
		msg(errorThrown,textStatus);
	});
}

function delete_data(deleteURL,chkArray,module) {
	$('#loading').modal('show');

	var data = {
		_token: token,
		ids: chkArray,
		po: $('#po_num').val()
	};

	$.ajax({
		url: deleteURL,
		type: 'POST',
		dataType: 'JSON',
		data: data,
	}).done(function(data, textStatus, xhr) {
		switch(module) {
			case 'inspection':
				makeDataInspectedTable(getDataInspectedURL);
				break;
			case 'runcard':
				getRuncard($('#id').val(),$('#po_num').val());

				if (data.total_qty != undefined) {
					$('#total_qty').val(data.total_qty);
				}

				break;
			case 'mod':
				getMOD($('#id').val(),$('#po_num').val());

				if (data.total_qty != undefined) {
					$('#no_of_defects').val(data.total_qty);
				}

				break;
		}
		msg(data.msg,data.status);
	}).fail(function(xhr, textStatus, errorThrown) {
		msg(errorThrown,textStatus);
	}).always(function() {
		$('#delete_modal').modal('hide');
		$('#loading').modal('hide');
	});
}

function getPOdetails(po) {
	$.ajax({
		url: getPOdetailsURL,
		type: 'GET',
		dataType: 'JSON',
		data: {
			_token: token,
			po_num: po
		},
	}).done(function(data, textStatus, xhr) {
		$('#device_name').val(data.device_name);
	}).fail(function(xhr, textStatus, errorThrown) {
		msg(errorThrown,textStatus);
	});
}

function GroupByValues(field,element) {
	element.html('<option value=""></option>');
	var data = {
		_token: token,
		field: field
	}
	$.ajax({
		url: GroupByURL,
		type: 'GET',
		dataType: 'JSON',
		data: data,
	}).done(function(data,xhr,textStatus) {
		$.each(data, function(i, x) {
			element.append('<option value="'+x.field+'">'+x.field+'</option>');
		});
	}).fail(function(data,xhr,textStatus) {
		msg("There was an error while processing the values.",'error');
	}).always(function() {
		console.log("complete");
	});
}

function show_LAR_DPPM_data(data) {
	var grp1 = '';
	var grp1_count = 2;
	var grp2 = '';
	var grp2_count = 2;
	var grp3 = '';
	var grp3_count = 2;
	var counter1 = 0;
	var node_child_count = 1;
	var node_parent_count = 1;
	var nxt_node = 1;
	var details = '';

	$('#group_by_pane').html('');
	$('#main_pane').hide();
	$('#group_by_pane').show();
	$('#group_by_pane').html('<div class="btn-group pull-right">'+
							'<button class="btn btn-danger btn-sm" id="btn_close_groupby">'+
								'<i class="fa fa-times"></i> Close'+
							'</button>'+
							'<a href="'+PDFGroupByReportURL+'" class="btn btn-info btn-sm" id="btn_pdf_groupby" target="_tab">'+
								'<i class="fa fa-file-pdf-o"></i> PDF'+
							'</a>'+
							'<button class="btn btn-success btn-sm" id="btn_excel_groupby">'+
								'<i class="fa fa-file-excel-o"></i> Excel'+
							'</button></div><br><br>');
	var details_body = '';
	var regex = /[.,\s()\//g]/g;

	$.each(data, function(i, x) {
		if (i === 'node1' && x.length > 0) {

			$.each(x, function(ii,xx) {
				var panelcolor = 'panel-info';

				var dppms = xx.DPPM;
				var dppm = dppms.split(' ');

				if (parseInt(dppm[0]) > 0) {
					panelcolor = 'panel-danger';
				}

				var groups1 = xx.group;
				var group1 = groups1.replace(regex, '');

				grp1 = '';
				grp1 += '<div class="panel-group accordion scrollable" id="grp_'+group1+'">';
				grp1 += '<div class="panel '+panelcolor+'">';
				grp1 += '<div class="panel-heading">';
				grp1 += '<h4 class="panel-title">';
				grp1 += '<a class="accordion-toggle" data-toggle="collapse" data-parent="#grp_'+group1+'" href="#grp_val_'+group1+'">';
				grp1 += jsUcfirst(xx.field)+': '+xx.group;
				grp1 += ' | LAR = '+xx.LAR;
				grp1 += ' | DPPM = '+xx.DPPM;
				grp1 += '</a>';
				grp1 += '</h4>';
				grp1 += '</div>';
				grp1 += '<div id="grp_val_'+group1+'" class="panel-collapse collapse">';
				grp1 += '<div class="panel-body table-responsive" id="child_'+group1+'">';

				if (xx.details.length > 0) {
					details = '';
					details_body = '';
					details += '<table style="font-size:10px" class="table table-condensed table-borderd">';
					details += '<thead>';
					details += '<tr>';
					details += '<td></td>';
					details += '<td></td>';
					details += '<td><strong>Date Inspected</strong></td>';
					details += '<td><strong>Shipment Date</strong></td>';
					details += '<td><strong>Series Name</strong></td>';
					details += '<td><strong>P.O. #</strong></td>';
					details += '<td><strong>Packing Operator</strong></td>';
					details += '<td><strong>Inspector</strong></td>';
					details += '<td><strong>Packing Type</strong></td>';
					details += '<td><strong>Unit Condition</strong></td>';
					details += '<td><strong>Packing Code(per Series)</strong></td>';
					details += '<td><strong>Carton #</strong></td>';
					details += '<td><strong>Packing Code</strong></td>';
					details += '<td><strong>Qty</strong></td>';
					details += '<td><strong>Judgement</strong></td>';
					details += '<td><strong>Remarks</strong></td>';
					details += '</tr>';
					details += '</thead>';
					details += '<tbody id="details_tbody">';

					var cnt = 1;

					$.each(xx.details, function(iii,xxx) {
						
						details_body += '<tr>';
						details_body += '<td>'+cnt+'</td>';
						details_body += '<td><button class="btn btn-sm view_inspection blue" data-id="'+xxx.id+'"'+ 
												'data-po_num="'+xxx.po_num+'" '+
												'data-date_inspected="'+xxx.date_inspected+'" '+
												'data-shipment_date="'+xxx.shipment_date+'" '+
												'data-device_name="'+xxx.device_name+'" '+
												'data-inspector="'+xxx.inspector+'" '+
												'data-packing_type="'+xxx.packing_type+'" '+
												'data-unit_condition="'+xxx.unit_condition+'" '+
												'data-packing_operator="'+xxx.packing_operator+'" '+
												'data-remarks="'+xxx.remarks+'" '+
												'data-packing_code_series="'+xxx.packing_code_series+'" '+
												'data-carton_num="'+xxx.carton_num+'" '+
												'data-packing_code="'+xxx.packing_code+'" '+
												'data-judgement="'+xxx.judgement+'" '+
												'data-total_qty="'+xxx.total_qty+'">'+
												'<i class="fa fa-edit"></i>'+
											'</button>'+
										'</td>';
						details_body += "<td>"+xxx.date_inspected+"</td>";
						details_body += "<td>"+xxx.shipment_date+"</td>";
						details_body += "<td>"+xxx.device_name+"</td>";
						details_body += "<td>"+xxx.po_num+"</td>";
						details_body += "<td>"+xxx.packing_operator+"</td>";
						details_body += "<td>"+xxx.inspector+"</td>";
						details_body += "<td>"+xxx.packing_type+"</td>";
						details_body += "<td>"+xxx.unit_condition+"</td>";
						details_body += "<td>"+xxx.packing_code_series+"</td>";
						details_body += "<td>"+xxx.carton_num+" </td>";
						details_body += "<td>"+xxx.packing_code+"</td>";
						details_body += "<td>"+xxx.total_qty+"</td>";
						details_body += "<td>"+xxx.judgement+"</td>";
						details_body += "<td>"+xxx.remarks+"</td>";
						details_body += '</tr>';
						cnt++;
					});
					
					details += details_body;

					details += '</tbody>';
					details += '</table>';
					//$('#child'+node_child_count.toString()).append(details);
					nxt_node++;
				}

				grp1 += details;
									
				grp1 += '</div>';
				grp1 += '</div>';
				grp1 += '</div>';
				grp1 += '</div>';


				$('#group_by_pane').append(grp1);
				node_parent_count++;
				node_child_count++;
			});
		}

		if (i === 'node2' && x.length > 0) {
			console.log(x[counter1]);
			
			$.each(x, function(ii,xx) {
				var panelcolor1 = 'panel-primary';

				var dppms = xx.DPPM;
				var dppm = dppms.split(' ');

				if (parseInt(dppm[0]) > 0) {
					panelcolor1 = 'panel-danger';
				}

				var groups1 = xx.g1;
				var group1 = groups1.replace(regex, '');

				var groups2 = xx.group;
				var group2 = groups2.replace(regex, '');

				grp2 = '';
				grp2 += '<div class="panel-group accordion scrollable" id="grp_'+group1+'_'+group2+'">';
				grp2 += '<div class="panel '+panelcolor1+'">';
				grp2 += '<div class="panel-heading">';
				grp2 += '<h4 class="panel-title">';
				grp2 += '<a class="accordion-toggle" data-toggle="collapse" data-parent="#grp_'+group1+'_'+group2+'" href="#grp_val_'+group1+'_'+group2+'">';
				grp2 += jsUcfirst(xx.field)+': '+xx.group;
				grp2 += ' | LAR = '+xx.LAR;
				grp2 += ' | DPPM = '+xx.DPPM;
				grp2 += '</a>';
				grp2 += '</h4>';
				grp2 += '</div>';
				grp2 += '<div id="grp_val_'+group1+'_'+group2+'" class="panel-collapse collapse">';
				grp2 += '<div class="panel-body table-responsive" style="height:500px" id="child_'+group1+'_'+group2+'">';

				if (xx.details.length > 0) {
					details = '';
					details_body = '';
					details += '<table style="font-size:10px" class="table table-condensed table-borderd">';
					details += '<thead>';
					details += '<tr>';
					details += '<td></td>';
					details += '<td></td>';
					details += '<td><strong>Date Inspected</strong></td>';
					details += '<td><strong>Shipment Date</strong></td>';
					details += '<td><strong>Series Name</strong></td>';
					details += '<td><strong>P.O. #</strong></td>';
					details += '<td><strong>Packing Operator</strong></td>';
					details += '<td><strong>Inspector</strong></td>';
					details += '<td><strong>Packing Type</strong></td>';
					details += '<td><strong>Unit Condition</strong></td>';
					details += '<td><strong>Packing Code(per Series)</strong></td>';
					details += '<td><strong>Carton #</strong></td>';
					details += '<td><strong>Packing Code</strong></td>';
					details += '<td><strong>Qty</strong></td>';
					details += '<td><strong>Judgement</strong></td>';
					details += '<td><strong>Remarks</strong></td>';
					details += '</tr>';
					details += '</thead>';
					details += '<tbody id="details_tbody">';

					var cnt = 1;

					$.each(xx.details, function(iii,xxx) {
						
						details_body += '<tr>';
						details_body += '<td>'+cnt+'</td>';
						details_body += '<td><button class="btn btn-sm view_inspection blue" data-id="'+xxx.id+'"'+ 
												'data-po_num="'+xxx.po_num+'" '+
												'data-date_inspected="'+xxx.date_inspected+'" '+
												'data-shipment_date="'+xxx.shipment_date+'" '+
												'data-device_name="'+xxx.device_name+'" '+
												'data-inspector="'+xxx.inspector+'" '+
												'data-packing_type="'+xxx.packing_type+'" '+
												'data-unit_condition="'+xxx.unit_condition+'" '+
												'data-packing_operator="'+xxx.packing_operator+'" '+
												'data-remarks="'+xxx.remarks+'" '+
												'data-packing_code_series="'+xxx.packing_code_series+'" '+
												'data-carton_num="'+xxx.carton_num+'" '+
												'data-packing_code="'+xxx.packing_code+'" '+
												'data-judgement="'+xxx.judgement+'" '+
												'data-total_qty="'+xxx.total_qty+'">'+
												'<i class="fa fa-edit"></i>'+
											'</button>'+
										'</td>';
						details_body += "<td>"+xxx.date_inspected+"</td>";
						details_body += "<td>"+xxx.shipment_date+"</td>";
						details_body += "<td>"+xxx.device_name+"</td>";
						details_body += "<td>"+xxx.po_num+"</td>";
						details_body += "<td>"+xxx.packing_operator+"</td>";
						details_body += "<td>"+xxx.inspector+"</td>";
						details_body += "<td>"+xxx.packing_type+"</td>";
						details_body += "<td>"+xxx.unit_condition+"</td>";
						details_body += "<td>"+xxx.packing_code_series+"</td>";
						details_body += "<td>"+xxx.carton_num+" </td>";
						details_body += "<td>"+xxx.packing_code+"</td>";
						details_body += "<td>"+xxx.total_qty+"</td>";
						details_body += "<td>"+xxx.judgement+"</td>";
						details_body += "<td>"+xxx.remarks+"</td>";
						details_body += '</tr>';
						cnt++;
					});
					
					details += details_body;

					details += '</tbody>';
					details += '</table>';
					//$('#child'+node_child_count.toString()).append(details);
					nxt_node++;
				}

				grp2 += details;
									
				grp2 += '</div>';
				grp2 += '</div>';
				grp2 += '</div>';
				grp2 += '</div>';

				var gs1 = xx.g1;
				var g1 = gs1.replace(regex, '');


				$('#child_'+g1).append(grp2);
				node_parent_count++;
				node_child_count++;
				panelcolor1 = '';
			});
			nxt_node++;
		}

		if (i === 'node3' && x.length > 0) {
			console.log(x[counter1]);
			
			$.each(x, function(ii,xx) {
				var panelcolor2 = 'panel-success';

				var dppms = xx.DPPM;
				var dppm = dppms.split(' ');

				if (parseInt(dppm[0]) > 0) {
					panelcolor2 = 'panel-danger';
				}

				var groups1 = xx.g1;
				var group1 = groups1.replace(regex, '');

				var groups2 = xx.g2;
				var group2 = groups2.replace(regex, '');

				var groups3 = xx.group;
				var group3 = groups3.replace(regex, '');

				grp3 = '';
				grp3 += '<div class="panel-group accordion scrollable" id="grp_'+group1+'_'+group2+'_'+group3+'">';
				grp3 += '<div class="panel '+panelcolor2+'">';
				grp3 += '<div class="panel-heading">';
				grp3 += '<h4 class="panel-title">';
				grp3 += '<a class="accordion-toggle" data-toggle="collapse" data-parent="#grp_'+group1+'_'+group2+'_'+group3+'" href="#grp_val_'+group1+'_'+group2+'_'+group3+'">';
				grp3 += jsUcfirst(xx.field)+': '+xx.group;
				grp3 += ' | LAR = '+xx.LAR;
				grp3 += ' | DPPM = '+xx.DPPM;
				grp3 += '</a>';
				grp3 += '</h4>';
				grp3 += '</div>';
				grp3 += '<div id="grp_val_'+group1+'_'+group2+'_'+group3+'" class="panel-collapse collapse">';
				grp3 += '<div class="panel-body table-responsive" style="height:300px" id="child_'+group1+'_'+group2+'_'+group3+'">';

				if (xx.details.length > 0) {
					details = '';
					details_body = '';
					details += '<table style="font-size:10px" class="table table-condensed table-borderd">';
					details += '<thead>';
					details += '<tr>';
					details += '<td></td>';
					details += '<td></td>';
					details += '<td><strong>Date Inspected</strong></td>';
					details += '<td><strong>Shipment Date</strong></td>';
					details += '<td><strong>Series Name</strong></td>';
					details += '<td><strong>P.O. #</strong></td>';
					details += '<td><strong>Packing Operator</strong></td>';
					details += '<td><strong>Inspector</strong></td>';
					details += '<td><strong>Packing Type</strong></td>';
					details += '<td><strong>Unit Condition</strong></td>';
					details += '<td><strong>Packing Code(per Series)</strong></td>';
					details += '<td><strong>Carton #</strong></td>';
					details += '<td><strong>Packing Code</strong></td>';
					details += '<td><strong>Qty</strong></td>';
					details += '<td><strong>Judgement</strong></td>';
					details += '<td><strong>Remarks</strong></td>';
					details += '</tr>';
					details += '</thead>';
					details += '<tbody id="details_tbody">';

					var cnt = 1;

					$.each(xx.details, function(iii,xxx) {
						
						details_body += '<tr>';
						details_body += '<td>'+cnt+'</td>';
						details_body += '<td><button class="btn btn-sm view_inspection blue" data-id="'+xxx.id+'"'+ 
												'data-po_num="'+xxx.po_num+'" '+
												'data-date_inspected="'+xxx.date_inspected+'" '+
												'data-shipment_date="'+xxx.shipment_date+'" '+
												'data-device_name="'+xxx.device_name+'" '+
												'data-inspector="'+xxx.inspector+'" '+
												'data-packing_type="'+xxx.packing_type+'" '+
												'data-unit_condition="'+xxx.unit_condition+'" '+
												'data-packing_operator="'+xxx.packing_operator+'" '+
												'data-remarks="'+xxx.remarks+'" '+
												'data-packing_code_series="'+xxx.packing_code_series+'" '+
												'data-carton_num="'+xxx.carton_num+'" '+
												'data-packing_code="'+xxx.packing_code+'" '+
												'data-judgement="'+xxx.judgement+'" '+
												'data-total_qty="'+xxx.total_qty+'">'+
												'<i class="fa fa-edit"></i>'+
											'</button>'+
										'</td>';
						details_body += "<td>"+xxx.date_inspected+"</td>";
						details_body += "<td>"+xxx.shipment_date+"</td>";
						details_body += "<td>"+xxx.device_name+"</td>";
						details_body += "<td>"+xxx.po_num+"</td>";
						details_body += "<td>"+xxx.packing_operator+"</td>";
						details_body += "<td>"+xxx.inspector+"</td>";
						details_body += "<td>"+xxx.packing_type+"</td>";
						details_body += "<td>"+xxx.unit_condition+"</td>";
						details_body += "<td>"+xxx.packing_code_series+"</td>";
						details_body += "<td>"+xxx.carton_num+" </td>";
						details_body += "<td>"+xxx.packing_code+"</td>";
						details_body += "<td>"+xxx.total_qty+"</td>";
						details_body += "<td>"+xxx.judgement+"</td>";
						details_body += "<td>"+xxx.remarks+"</td>";
						details_body += '</tr>';
						cnt++;
					});
					
					details += details_body;

					details += '</tbody>';
					details += '</table>';
					//$('#child'+node_child_count.toString()).append(details);
					nxt_node++;
				}

				node_child_count++;

				grp3 += details;
									
				grp3 += '</div>';
				grp3 += '</div>';
				grp3 += '</div>';
				grp3 += '</div>';

				var gs1 = xx.g1;
				var g1 = gs1.replace(regex, '');

				var gs2 = xx.g2;
				var g2 = gs2.replace(regex, '');

				$('#child_'+g1+'_'+g2).append(grp3);
				node_parent_count++;
			});
		}

	});

	node_parent_count++;
	node_child_count++;
}

function ReportDataCheck(param, handleData) {
	$.ajax({
		url: ReportDataCheckURL,
		type: 'get',
		dataType: 'JSON',
		data: param,
	}).done(function(data, textStatus, xhr) {
		handleData(data.DataCount);
	}).fail(function(xhr, textStatus, errorThrown) {
		msg('Report check: '+errorThrown,textStatus);
	});
}