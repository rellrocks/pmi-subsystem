var defects_arr = [];
var serial_arr = [];
var probe_lot_arr = [];

var dataColumn = [];

$( function() {
	initialize();
	$(".date-picker").datepicker().datepicker("setDate", new Date());
	checkAllCheckboxesInTable('.group-checkable','.checkboxes');
	checkAllCheckboxesInTable('.group-checkable-mod','.checkboxes-mod');

	//getDatatable('tbl_oqc',oqcDataTableURL,dataColumn,[],0);
	OQCDataTable(oqcDataTableURL);
	SerialNoDataTable(serial_arr);
	DefectsDataTable(defects_arr);
	ProbeLotsDataTable(probe_lot_arr);

	$('#serial_save_status').val('ADD');
	$('#mode_save_status').val('ADD');
	$('#probe_lot_save_status').val('ADD');

	$('#btn_probe_lot').prop('disabled', false);

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

	$('body').on('keydown', '.enter', function(e) {
		var self = $(this)
			, form = self.parents('form:eq(0)')
			, focusable
			, next
			;
		if (e.keyCode == 13) {
			focusable = form.find('.enter').filter(':visible');
			next = focusable.eq(focusable.index(this)+1);

			if (next.length) {
				next.focus();
			} else {
				form.submit();
			}
			return false;
		}
	});

	$('#frm_inspection').on('submit', function(e) {
		e.preventDefault();
		$('#loading').modal('show');
		var dataInputs = $(this).serializeArray();

		dataInputs.push({
			'name': 'serial_no',
			'value': JSON.stringify(serial_arr)
		}, {
			'name': 'defects',
			'value': JSON.stringify(defects_arr)
		}, {
			'name': 'probe_lots',
			'value': JSON.stringify(probe_lot_arr)
		});

		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			dataType: 'JSON',
			data: dataInputs,
		}).done(function(data, textStatus, xhr) {
			msg(data.msg,data.status);
			//getDatatable('tbl_oqc',oqcDataTableURL,dataColumn,[],0);
			serial_arr = [];
			defects_arr = [];
			$('#tbl_oqc').DataTable().ajax.reload();
			clearControls();
		//	$('#btn_savemodal').prop('disabled', true);
		}).fail(function(data, textStatus, xhr) {
			var errors = data.responseJSON;
			InspectionErrors(errors);
		}).always( function() {
			$('#loading').modal('hide');
		});
	});

	$('input#is_probe').on('change', function(e) {
		if ($(this).is(':checked')) {
			$(this).val(1);
			$('#po_no').focus();
			$('#po_no').attr('maxlength',16);
			$('#series_name').prop('readonly', false);
			$('#customer').prop('readonly', false);

			$('#series_name').val('');
			$('#series_code').val('');
			$('#customer').val('');
			$('#po_qty').val('');

			$('#btn_probe_lot').prop('disabled', false);
		} else {
			$(this).val(0);
			$('#po_no').focus();
			$('#po_no').attr('maxlength',15);
			$('#series_name').prop('readonly', true);
			$('#customer').prop('readonly', true);
			$('#btn_probe_lot').prop('disabled', false);
		}
	});

	$('#frm_mode_of_defects').on('submit', function(e) {
		e.preventDefault();
		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			dataType: 'JSON',
			data: $(this).serialize(),
		}).done(function(data, textStatus, xhr) {
            getNumOfDefectives($('#po_no').val(),$('#lot_no').val(),$('#submission').val());
			var modUrl = modDataTableURL+'?_token='+token+
					'&&pono='+$('#po_no').val()+
					// '&&device='+$('#series_name').val()+
					'&&lotno='+$('#lot_no').val()+
					'&&submission='+$('#submission').val();
			getDatatable('tbl_mode_of_defects',modUrl,modColumn,[],0);
			clearMOD();
		}).fail(function(data, textStatus, xhr) {
			var errors = data.responseJSON;
			ModeOfDefectsErrors(errors);
		});
	});

	$('#tbl_oqc tbody').on('click', '.btn_edit_inspection', function() {
		var data = $('#tbl_oqc').DataTable().row($(this).parents('tr')).data();
		console.log(data);

		$('#inspection_id').val(data.id);
		$('#assembly_line').val(data.assembly_line);
		$('#lot_no').val(data.lot_no);
		$('#app_date').val(data.app_date);
		
		var app_time = convertTo24HrsFormat(data.app_time);
		$('#app_time').val(app_time);
		
		$('#prod_category').val(data.prod_category);
		$('#po_no').val(data.po_no);
		$('#workweek').val(data.workweek);
		$('#series_name').val(data.device_name);
		$('#customer').val(data.customer);
		$('#po_qty').val(data.po_qty);
		$('#family').val(data.family);
		$('#type_of_inspection').val(data.type_of_inspection);
		$('#severity_of_inspection').val(data.severity_of_inspection);
		$('#inspection_lvl').val(data.inspection_lvl);
		$('#aql').val(data.aql);
		$('#accept').val(data.accept);
		$('#reject').val(data.reject);
		$('#date_inspected').val(data.date_inspected);
		$('#ww').val(data.ww);
		$('#fy').val(data.fy);
		$('#time_ins_from').val(data.time_ins_from);
		$('#time_ins_to').val(data.time_ins_to);

		// var from = time_ins(data.time_ins_from);
		// $('#time_ins_hour_from').val(from.hr);
		// $('#time_ins_mins_from').val(from.mn);

		// var to = time_ins(data.time_ins_to);
		// $('#time_ins_hour_to').val(to.hr);
		// $('#time_ins_mins_to').val(to.mn);

		$('#shift').val(data.shift);
		$('#inspector').val(data.inspector);
		$('#submission').val(data.submission);
		$('#coc_req').val(data.coc_req);
		$('#judgement').val(data.judgement);
		$('#lot_qty').val(data.lot_qty);
		$('#sample_size').val(data.sample_size);
		$('#lot_inspected').val(data.lot_inspected);
		$('#lot_accepted').val(data.lot_accepted);
		$('#no_of_defects').val(data.num_of_defects);
		$('#remarks').val(data.remarks);
		$('#inspection_save_status').val('EDIT');

        //getNumOfDefectives(data.po_no,data.lot_no,data.submission);

		if (data.type == 'PROBE PIN') {
			$('#is_probe').prop('checked', true);
			$('#btn_probe_lot').prop('disabled', false);
		} else {
			$('#is_probe').prop('checked', false);
			$('#btn_probe_lot').prop('disabled', false);
		}

		var gauge = data.gauge;
		var accessory = data.accessory;
		var yd_label_req = data.yd_label_req;
		var chs_coating = data.chs_coating;

		$('#gauge').val(gauge);
		$('#accessory').val(accessory);
		$('#yd_label_req').val(yd_label_req);
		$('#chs_coating').val(chs_coating);

		//checkAuhtor(data.inspector);

		if (data.lot_accepted > 0) {
			$('#no_of_defects_div').hide();
			$('#mode_of_defects_div').hide();
		} else {
			$('#no_of_defects_div').show();
			$('#mode_of_defects_div').show();
		}

		$('#no_of_defects').val(data.num_of_defects);

		GetSerialNo(data.id);
		GetDefects(data.id);
		GetProbeLots(data.id);

		if($('#btn_delete').is('disabled') == true){
			$('#btn_savemodal').prop('disabled', true);
		}else{
			$('#btn_savemodal').prop('disabled', false);
		}

		$('#inspection_modal').modal('show');
	});

	$('#btn_serial_no').on('click', function () {
		$('#serial_no').focus();
		$('#serial_no_modal').modal('show');
	});

	$('#btn_remove_serial_no').on('click', function () {
		bootbox.confirm({
			message: "Do you want to remove this Serial No.?",
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
					$('.sn_checkboxes:checked').each(function () {
						var serial_no = $(this).attr('data-serial_no');
						$.each(serial_arr, function (i, x) {
							if (x.serial_no == serial_no) {
								serial_arr[i].deleted = 1;
								//serial_arr.splice(i, 1);
							}
						});
					});

					SerialNoDataTable(serial_arr);
				}
				
			}
		});
	});

	$('#frmSerialUpload').on('submit', function (e) {
        var formObj = $(this);
        var formURL = formObj.attr("action");
        var formData = new FormData(this);
        var fileName = $("#serial_nos").val();
        var ext = fileName.split('.').pop();
        var pros = $('#serial_nos').val().replace("C:\fakepath", "");
        var fileN = pros.substring(12, pros.length);
        e.preventDefault();
        if ($("#serial_nos").val() == '') {
            msg("No File was selected. Please select a file.","failed");
        } else {
            if (fileName != '') {
                if (ext == 'xls' || ext == 'xlsx' || ext == 'XLS' || ext == 'XLSX' || ext == 'Xls') {

					$('#file_checking_msg').html("Checking Serial numbers...");
                    $('#file_checking_modal').modal('show');

                    $.ajax({
                        url: formURL,
                        type: 'POST',
                        data: formData,
                        mimeType: "multipart/form-data",
                        contentType: false,
                        cache: false,
                        processData: false,
                    }).done(function (returns, textStatus, xhr) {
                        $('#file_checking_msg').html("Processing Serial numbers...");

						var response = JSON.parse(returns);
						console.log(response);



						if (response.status == "success") {
							serial_arr = response.serial_nos;
							SerialNoDataTable(serial_arr);
						}

						msg(response.msg,response.status);

                        // $.ajax({
                        //     url: processPPSDeliveryFileURL,
                        //     type: 'GET',
                        //     dataType: 'JSON',
                        //     data: {
                        //         _token: token
                        //     }
                        // }).done(function (data, textStatus, xhr) {
                        //     msg(data.msg, data.status);

                        //     $("#serial_nos").empty();
                        // }).fail(function (xhr, textStatus, errorThrown) {
                        //     console.log(xhr);
                        // }).always(function () {
                        //     $('#file_checking_modal').modal('hide');
                        // });

                    }).fail(function (xhr, textStatus, errorThrown) {
                        console.log(xhr);
                        $('#file_checking_modal').modal('hide');
                    }).always(function () {
                        $('#file_checking_modal').modal('hide');
                    });
                } else {
                    $('#file_checking_modal').modal('hide');
                    msg("File Format not supported.","failed");
                }
            }
        }
    });

	$('#btn_add_serial_no').on('click', function() {
		AddSerialNo();
	});

	$('#serial_no').on('change', function() {
		AddSerialNo();
	});

	$('#tbl_serial_no tbody').on('click','.btn_edit_serial_no', function() {
		var data = $('#tbl_serial_no').DataTable().row($(this).parents('tr')).data();

		$('#serial_save_status').val('EDIT');

		$('#serial_id').val(data.id);
		$('#serial_no').val(data.serial_no);
	});

	$('#btn_mode_of_defects').on('click', function () {
		$('#mode_of_defects_modal').modal('show');
	});

	$('#btn_remove_mod').on('click', function () {
		bootbox.confirm({
			message: "Do you want to remove this Defect?",
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
					$('.defects_checkboxes:checked').each(function () {
						var defects = $(this).attr('data-defects');
						$.each(defects_arr, function (i, x) {
							if (x.mod1 == defects) {
								defects_arr[i].deleted = 1;
								//defects_arr.splice(i, 1);
							}
						});
					});

					DefectsDataTable(defects_arr);
				}
				
			}
		});
	});

	$('#btn_add_mod').on('click', function () {
		var mode_of_defects_name = $('#mode_of_defects_name').val();
		var mod_qty = $('#mod_qty').val();
		var mod_id = $('#mod_id').val();
		var save_status = $('#mode_save_status').val();

		var error = 0;
		var same_but_deleted = 0;

		if (mode_of_defects_name == "" || mod_qty == "") {
			msg("Fill out all input fields.", "failed");
		} else {
			if (defects_arr.length > 0) {
				$.each(defects_arr, function (i, x) {
					if (x.mod1 == mode_of_defects_name && (save_status != 'EDIT')) {
						if (x.deleted == 0) {
							error++;
						}

						if (x.deleted == 1) {
							same_but_deleted++;
						}
					}
				});
			}

			if (error < 1) {
				if (!checkIfExistInArray(defects_arr, mod_id)) {
					if (same_but_deleted > 0) {
						// same defect but deleted
						$.each(defects_arr, function (i, x) {
							if (x.mod1 == mode_of_defects_name && x.deleted == 1) {
								defects_arr[i].qty = mod_qty;
								defects_arr[i].deleted = 0;
							}
						});
					} else {
						defects_arr.push({
							'id': -1,
							'mod1': mode_of_defects_name,
							'qty': mod_qty,
							'deleted': 0
						});
					}
					
				} else {
					$.each(defects_arr, function (i, x) {
						if (x.id == mod_id) {
							defects_arr[i].id = mod_id;
							defects_arr[i].mod1 = mode_of_defects_name;
							defects_arr[i].qty = mod_qty;
							defects_arr[i].deleted = 0;
						}
					});
				}

			} else {
				msg("Defect " + mode_of_defects_name + " already added.", "failed");
			}
		}

		console.log(defects_arr);

		DefectsDataTable(defects_arr);
		$('.clear_mod').val('');
		$('#mode_save_status').val('ADD');

	});

	$('#tbl_mode_of_defects tbody').on('click', '.btn_edit_defects', function () {
		var data = $('#tbl_mode_of_defects').DataTable().row($(this).parents('tr')).data();
		console.log(data);
		$('#mode_save_status').val('EDIT');
		$('#mode_of_defects_name').val(data.mod1);
		$('#mod_id').val(data.id);
		$('#mod_qty').val(data.qty);
	});

	$('#btn_probe_lot').on('click', function() {
		$('#probe_lot_modal').modal('show');
	});
	
	$('#btn_remove_probe_lot').on('click', function () {
		bootbox.confirm({
			message: "Do you want to remove this Serial No.?",
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
					$('.ppl_checkboxes:checked').each(function () {
						var probe_lot = $(this).attr('data-probe_lot');
						$.each(probe_lot_arr, function (i, x) {
							if (x.probe_lot == probe_lot) {
								probe_lot_arr[i].deleted = 1;
								//probe_lot_arr.splice(i, 1);
							}
						});
					});

					ProbeLotsDataTable(probe_lot_arr);
				}
				
			}
		});
	});

	$('#btn_add_probe_lot').on('click', function() {
		var probe_lot = $('#probe_lot').val();
		var qty = $('#probe_qty').val();
		var probe_lot_id = $('#probe_lot_id').val();
		var save_status = $('#probe_lot_save_status').val();

		var error = 0;
		var same_but_deleted = 0;

		if (probe_lot == "" || qty == "") {
			msg("Fill out all input fields.", "failed");
		} else {
			console.log(probe_lot_arr);
			if (probe_lot_arr.length > 0) {
				$.each(probe_lot_arr, function (i, x) {
					if (x.probe_lot == probe_lot && (save_status != 'EDIT')) {
						if(x.deleted == 0){
							error++;
						}
						if(x.deleted == 1){
							same_but_deleted++;
						}
					}
				});
			}

			if (error < 1) {
				if (!checkIfExistInArray(probe_lot_arr, probe_lot_id)) {
					if(same_but_deleted > 0){
						$.each(probe_lot_arr, function (i,x){
							if(x.probe_lot == probe_lot && x.deleted == 1){
								probe_lot_arr[i].qty = qty;
								probe_lot_arr[i].deleted = 0;
							}
						});
					}else{
						probe_lot_arr.push({
							'id': -1,
							'probe_lot': probe_lot,
							'qty': qty,
							'deleted': 0
						});
					}
					
				} else {
					$.each(probe_lot_arr, function (i, x) {
						if (x.id == probe_lot_id) {
							probe_lot_arr[i].id = probe_lot_id;
							probe_lot_arr[i].probe_lot = probe_lot;
							probe_lot_arr[i].qty = qty;
							probe_lot_arr[i].deleted = 0;
						}
					});
				}

			} else {
				msg("Probe Pin Lot No. " + probe_lot + " already added.", "failed");
			}
		}
		

		console.log(probe_lot_arr);

		ProbeLotsDataTable(probe_lot_arr);
		$('.clear_probe_lot').val('');
		$('.clear_qty').val('');
	});

	$('#tbl_probe_lot tbody').on('click','.btn_edit_probe_lot', function() {
		var data = $('#tbl_probe_lot').DataTable().row($(this).parents('tr')).data();

		$('#probe_lot_save_status').val('EDIT');
		$('#probe_lot_id').val(data.id);
		$('#probe_lot').val(data);
	});


	$('#btn_confirm_delete').on('click', function() {
		if ($('#delete_id').val() == 'INSPECTION') {
			delete_items('.checkboxes',DeleteInspectionURL,oqcDataTableURL,'tbl_oqc',dataColumn);
		}

		if ($('#delete_id').val() == 'MOD') {
			delete_items('.checkboxes-mod',DeleteModeOfDefectsURL,modDataTableURL,'tbl_mode_of_defects',modColumn);
		}
	});

	$('.validate').on('keyup', function(e) {
		var no_error = $(this).attr('id');
		InspectionNoErrors(no_error)
	});

	$('.validateModeOfDefects').on('keyup', function(e) {
		var no_error = $(this).attr('id');
		ModeOfDefectsNoErrors(no_error)
	});



	$('#time_ins_to').on('change', function() {
		// var time = setTime($(this).val());
		// if (time.includes('::')) {
		// 	$(this).val(time.replace('::',':'));
		// } else {
		// 	$(this).val(time);
		// }
		getShift();
	});

	$('#lot_accepted').on('change', function() {
		checkLotAccepted($(this).val())
	});

	$('#po_no').on('change', function() {
		if (!$('input#is_probe').is(":checked")) {
			getpodetails();
		}
	});

	$('#btn_getpodetails').on('click', function() {
		//if (!$('input#is_probe').is(":checked")) {
			getpodetails();
		//}
	});

	$('#tbl_mode_of_defects_body').on('click', '.btn_edit_mod', function() {
		$('#mode_save_status').val('EDIT');
		$('#mod_po').val($(this).attr('data-pono'));
		$('#mod_device').val($(this).attr('data-device'));
		$('#mod_lotno').val($(this).attr('data-lotno'));
		$('#mod_submission').val($(this).attr('data-submission'));
        $('#ins_id').val($(this).attr('data-ins-id'));
		$('#mode_of_defects_name').val($(this).attr('data-mod1'));
		$('#mod_id').val($(this).attr('data-id'));
		$('#mod_qty').val($(this).attr('data-qty'));
	});

    $('#severity_of_inspection').on('change', function() {
    	samplingPLan();
    });

    $('#inspection_lvl').on('change', function() {
    	samplingPLan();
    });

    $('#aql').on('change', function() {
    	samplingPLan();
    });

    $('#lot_qty').on('keyup', function() {
    	samplingPLan();
    });

    $('#tbl_probe').on('click', 'tbody .btn_select_probe', function (e) {	
                var probeTable = $('#tbl_probe').DataTable();	
                	
                var data = probeTable.row($(this).closest('tr')).data();		
                $('#series_name').val(data.device_name);	
                $('#series_code').val(data.device_code);	
                $('#customer').val(data.customer_name);	
                $('#customer_code').val(data.customer_code);
                $('#po_qty').val(data.po_qty);	
                $('#probe_item_modal').modal('hide');
    });

});

function initialize() {
	loadSelectInput();
	getFiscalYear();
	getWorkWeek();

	$('#accept').val(0);
	$('#reject').val(1);
	$('#lot_inspected').val('1');

	$('#no_of_defects_div').hide();
	$('#mode_of_defects_div').hide();


	// open modal
	var oqc_url = window.location.href;
	var arr = oqc_url.split('?');
	if (arr.length > 1 && arr[1] !== '') {
		var oqcurl = new URL(oqc_url);
		var ppts_po_no = (oqcurl.searchParams.get('po_no') == null)? "": oqcurl.searchParams.get('po_no');
		var ppts_po_qty = (oqcurl.searchParams.get('po_qty') == null)? "": oqcurl.searchParams.get('po_qty');
		var ppts_lot_no = (oqcurl.searchParams.get('lot_no') == null)? "": oqcurl.searchParams.get('lot_no');
		var ppts_ww = (oqcurl.searchParams.get('ww') == null)? "": oqcurl.searchParams.get('ww');
		var ppts_app_date_time = (oqcurl.searchParams.get('app_date_time') == null)? "": oqcurl.searchParams.get('app_date_time');

		var ppts_app_date = (ppts_app_date_time == "" )? "" : ppts_app_date_time.substring(0, 10);
		var ppts_app_time = (ppts_app_date_time == "" )? "" : ppts_app_date_time.slice(-8);
		ppts_app_time = (ppts_app_time == "" )? "" : ppts_app_time.replace("-",":");

		ppts_app_time = (ppts_app_time == "" )? "" : convertTo24HrsFormat(ppts_app_time);

		$('#po_no').val(ppts_po_no);

		getpodetails();

		$('#po_qty').val(ppts_po_qty);
		$('#lot_no').val(ppts_lot_no);
		$('#workweek').val(ppts_ww);
		$('#app_date').val(ppts_app_date);
		$('#app_time').val(ppts_app_time);
		$('#inspection_save_status').val('ADD');
		$('#inspection_modal').modal('show');
	}

}

function convertTo24HrsFormat(time) {
	// write your solution here
	let [hours, min] = time.split(":");
	let [minutes, mode] = min.split(" ");

	if (time.endsWith("AM")) {
		if (hours == 12) {
			hours = 12-hours;
			hours="0"+hours;
			//console.log(hours)
		} else if (hours < 10) {
			hours="0"+hours;
			//console.log(hours)
		} else {
			//console.log(hours)
		}

		if (minutes < 10) {
			minutes=parseInt(minutes)
			minutes="0"+minutes;
			//console.log(minutes)
		} else {
			//console.log(minutes)
		}
		//sMinutes = "0" + sMinutes;
	} else if (time.endsWith("PM")) {
		if(hours<12){
		hours = parseInt(hours)+12
		//console.log(hours)
		}
		if (minutes < 10) {
			minutes = parseInt(minutes)
			minutes="0"+minutes;
			//console.log(minutes)
		} else {
			//console.log(minutes)
		}
	}

	return hours +':'+ minutes;
}

function clearControls() {
	$('.clear').val('');
	$('#accept').val(0);
	$('#reject').val(1);
	$('#lot_inspected').val(1);
	$('#inspector').val(author);
	$('#shift').val('Shift A');
}

function clearMOD() {
	$('.clear_mod').val('');
}

function getNumOfDefectives(po_no,lot_no,submission) {
    $.ajax({
        url: getNumOfDefectivesURL,
        type: 'GET',
        dataType: 'JSON',
        data: {
        	_token: token,
        	po_no: po_no,
        	lot_no: lot_no,
        	submission: submission
        }
    }).done(function(data,xhr,textStatus) {
        $('#no_of_defects').val(data);
        if (data > 0) {
            $('#lot_accepted').val(0);
        }
        checkLotAccepted($(this).attr('data-lot_accepted'),data);
    }).fail(function(data,xhr,textStatus) {
        msg("There was an error while calculating",'error');
    });
}

/*function getpodetails() {
	if ($('#po_no').val() == '') {

	} else {
		openloading();
		var is_probe = 0;
        if ($('#is_probe').is(':checked')) {
            is_probe = 1;
        }

		var data = {
			_token: token,
			is_probe: is_probe,
			po: $('#po_no').val(),
		}

		$.ajax({
	        url: getPOdetailsURL,
			type: 'POST',
			dataType: 'JSON',
			data: data,
	    }).done(function(data,xhr,textStatus) {
	        if (data.length > 0) {
					console.log(data);
					if (is_probe > 0) {
						getProbeProduct(data[0]['device_code']);
					} else {
						$('#series_name').val(data[0]['device_name']);
						$('#series_code').val(data[0]['device_code']);
						$('#customer').val(data[0]['customer_name']);
						$('#customer_code').val(data[0]['customer_code']);
						$('#po_qty').val(data[0]['po_qty']);
					}
				} else {
					msg("P.O. does not exist.","failed");
				}
	    }).fail(function(data,xhr,textStatus) {
	        msg("There was an error while searching P.O.",'error');
	    }).always( function() {
	    	closeloading();
	    });

		// $.ajax({
		// 	url: getPOdetailsURL,
		// 	type: 'POST',
		// 	dataType: 'JSON',
		// 	data: data,
		// 	complete: function(xhr, textStatus) {
		// 		closeloading();
		// 	},
		// 	success: function(data, textStatus, xhr) {
		// 		if (data.length > 0) {
		// 			console.log(data);
		// 			if (is_probe > 0) {
		// 				getProbeProduct(data[0]['device_code']);
		// 			} else {
		// 				$('#series_name').val(data[0]['device_name']);
		// 				$('#series_code').val(data[0]['device_code']);
		// 				$('#customer').val(data[0]['customer_name']);
		// 				$('#customer_code').val(data[0]['customer_code']);
		// 				$('#po_qty').val(data[0]['po_qty']);
		// 			}
		// 		} else {
		// 			msg("P.O. does not exist.","failed");
		// 		}
		// 	},
		// 	error: function(xhr, textStatus, errorThrown) {
		// 		//called when there is an error
		// 	}
		// });
	}
}
*/

function getpodetails() {
	if ($('#po_no').val() !== '') {
		openloading();
		var is_probe = 0;
        if ($('#is_probe').is(':checked')) {
            is_probe = 1;
        }else{
			is_probe = 0;
		}

		var data = {
			_token: token,
			is_probe: is_probe,
			po: $('#po_no').val(),
		}

		$.ajax({
	        url: getPOdetailsURL,
			type: 'POST',
			dataType: 'JSON',
			data: data,
	    }).done(function(data,xhr,textStatus) {
	        if (data.length > 0) {
					if (is_probe > 0) {
						probeDataTable(data);
						$('#probe_item_modal').modal('show');
					} else {
						$('#series_name').val(data[0]['device_name']);
						$('#series_code').val(data[0]['device_code']);
						$('#customer').val(data[0]['customer_name']);
						$('#customer_code').val(data[0]['customer_code']);
						$('#po_qty').val(data[0]['po_qty']);
					}
			}else{
				msg("P.O. does not exist.","failed");
			}
	    }).fail(function(data,xhr,textStatus) {
	        msg("There was an error while searching P.O.",'error');
	    }).always( function() {
	    	closeloading();
	    });

		// $.ajax({
		// 	url: getPOdetailsURL,
		// 	type: 'POST',
		// 	dataType: 'JSON',
		// 	data: data,
		// 	complete: function(xhr, textStatus) {
		// 		closeloading();
		// 	},
		// 	success: function(data, textStatus, xhr) {
		// 		if (data.length > 0) {
		// 			console.log(data);
		// 			if (is_probe > 0) {
		// 				getProbeProduct(data[0]['device_code']);
		// 			} else {
		// 				$('#series_name').val(data[0]['device_name']);
		// 				$('#series_code').val(data[0]['device_code']);
		// 				$('#customer').val(data[0]['customer_name']);
		// 				$('#customer_code').val(data[0]['customer_code']);
		// 				$('#po_qty').val(data[0]['po_qty']);
		// 			}
		// 		} else {
		// 			msg("P.O. does not exist.","failed");
		// 		}
		// 	},
		// 	error: function(xhr, textStatus, errorThrown) {
		// 		//called when there is an error
		// 	}
		// });
	}else{
		msg("Input P.O Number!");
	}
}

function probeDataTable(probe_items) {
	var probeTable = $('#tbl_probe').DataTable();

	probeTable.clear();
	probeTable.destroy();

	$('#tbl_probe').DataTable({
		data: probe_items,
		searching: false,
		select: true,
		columns: [
			{
				data: function(x) {
					return '<button type="button" class="btn btn-sm btn-primary btn_select_probe"><i class="fa fa-edit"></i></button>';
				}, orderable: false, searchable: false, width: '5%'
			},
			{
				data: 'device_code', width: '20%'
			},
			{
				data: 'device_name', width: '20%'
			},
			{
				data: 'customer_code', width: '20%'
			},
			{
				data: 'customer_name', width: '20%'
			},
			{
				data: 'BUNR', width: '15%'
			}
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
		order: [
			[1, "desc"]
		],
		fnDrawCallback: function(x) {
			$("#tbl_probe").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
		}
	});
}

function InspectionErrors(errors) {
	$.each(errors, function(i, x) {
		switch(i) {
			case 'assembly_line':
				$('#assembly_line_div').addClass('has-error');
				$('#assembly_line_msg').html(x);
			break;
			case 'lot_no':
				$('#lot_no_div').addClass('has-error');
				$('#lot_no_msg').html(x);
			break;
			case 'app_date':
				$('#app_date_div').addClass('has-error');
				$('#app_date_msg').html(x);
			break;
			case 'app_time':
				$('#app_time_div').addClass('has-error');
				$('#app_time_msg').html(x);
			break;
			case 'prod_category':
				$('#prod_category_div').addClass('has-error');
				$('#prod_category_msg').html(x);
			break;
			case 'po_no':
				$('#po_no_div').addClass('has-error');
				$('#po_no_msg').html(x);
			break;
			case 'series_name':
				$('#series_name_div').addClass('has-error');
				$('#series_name_msg').html(x);
			break;
			case 'customer':
				$('#customer_div').addClass('has-error');
				$('#customer_msg').html(x);
			break;
			case 'po_qty':
				$('#po_qty_div').addClass('has-error');
				$('#po_qty_msg').html(x);
			break;
			case 'family':
				$('#family_div').addClass('has-error');
				$('#family_msg').html(x);
			break;
			case 'type_of_inspection':
				$('#type_of_inspection_div').addClass('has-error');
				$('#type_of_inspection_msg').html(x);
			break;
			case 'severity_of_inspection':
				$('#severity_of_inspection_div').addClass('has-error');
				$('#severity_of_inspection_msg').html(x);
			break;
			case 'inspection_lvl':
				$('#inspection_lvl_div').addClass('has-error');
				$('#inspection_lvl_msg').html(x);
			break;
			case 'aql':
				$('#aql_div').addClass('has-error');
				$('#aql_msg').html(x);
			break;
			case 'date_inspected':
				$('#date_inspected_div').addClass('has-error');
				$('#date_inspected_msg').html(x);
			break;
			case 'shift':
				$('#shift_div').addClass('has-error');
				$('#shift_msg').html(x);
			break;
			case 'time_ins_from':
				$('#time_ins_div').addClass('has-error');
				$('#time_ins_msg').html(x);
			break;
			case 'time_ins_to':
				$('#time_ins_div').addClass('has-error');
				$('#time_ins_msg').html(x);
			break;
			case 'submission':
				$('#submission_div').addClass('has-error');
				$('#submission_msg').html(x);
			break;
			case 'coc_req':
				$('#coc_req_div').addClass('has-error');
				$('#coc_req_msg').html(x);
			break;
			case 'judgement':
				$('#judgement_div').addClass('has-error');
				$('#judgement_msg').html(x);
			break;
			case 'lot_qty':
				$('#lot_qty_div').addClass('has-error');
				$('#lot_qty_msg').html(x);
			break;
			case 'sample_size':
				$('#sample_size_div').addClass('has-error');
				$('#sample_size_msg').html(x);
			break;
			case 'lot_inspected':
				$('#lot_inspected_div').addClass('has-error');
				$('#lot_inspected_msg').html(x);
			break;
			case 'lot_accepted':
				$('#lot_accepted_div').addClass('has-error');
				$('#lot_accepted_msg').html(x);
			break;
		}
	});
}

function InspectionNoErrors(error) {
	switch(error) {
		case 'assembly_line':
			$('#assembly_line_div').removeClass('has-error');
			$('#assembly_line_msg').html('');
		break;
		case 'lot_no':
			$('#lot_no_div').removeClass('has-error');
			$('#lot_no_msg').html('');
		break;
		case 'app_date':
			$('#app_date_div').removeClass('has-error');
			$('#app_date_msg').html('');
		break;
		case 'app_time':
			$('#app_time_div').removeClass('has-error');
			$('#app_time_msg').html('');
		break;
		case 'prod_category':
			$('#prod_category_div').removeClass('has-error');
			$('#prod_category_msg').html('');
		break;
		case 'po_no':
			$('#po_no_div').removeClass('has-error');
			$('#po_no_msg').html('');
		break;
		case 'series_name':
			$('#series_name_div').removeClass('has-error');
			$('#series_name_msg').html('');
		break;
		case 'customer':
			$('#customer_div').removeClass('has-error');
			$('#customer_msg').html('');
		break;
		case 'po_qty':
			$('#po_qty_div').removeClass('has-error');
			$('#po_qty_msg').html('');
		break;
		case 'family':
			$('#family_div').removeClass('has-error');
			$('#family_msg').html('');
		break;
		case 'type_of_inspection':
			$('#type_of_inspection_div').removeClass('has-error');
			$('#type_of_inspection_msg').html('');
		break;
		case 'severity_of_inspection':
			$('#severity_of_inspection_div').removeClass('has-error');
			$('#severity_of_inspection_msg').html('');
		break;
		case 'inspection_lvl':
			$('#inspection_lvl_div').removeClass('has-error');
			$('#inspection_lvl_msg').html('');
		break;
		case 'aql':
			$('#aql_div').removeClass('has-error');
			$('#aql_msg').html('');
		break;
		case 'date_inspected':
			$('#date_inspected_div').removeClass('has-error');
			$('#date_inspected_msg').html('');
		break;
		case 'shift':
			$('#shift_div').removeClass('has-error');
			$('#shift_msg').html('');
		break;
		case 'time_ins_from':
			$('#time_ins_div').removeClass('has-error');
			$('#time_ins_msg').html('');
		break;
		case 'time_ins_to':
			$('#time_ins_div').removeClass('has-error');
			$('#time_ins_msg').html('');
		break;
		case 'submission':
			$('#submission_div').removeClass('has-error');
			$('#submission_msg').html('');
		break;
		case 'coc_req':
			$('#coc_req_div').removeClass('has-error');
			$('#coc_req_msg').html('');
		break;
		case 'judgement':
			$('#judgement_div').removeClass('has-error');
			$('#judgement_msg').html('');
		break;
		case 'lot_qty':
			$('#lot_qty_div').removeClass('has-error');
			$('#lot_qty_msg').html('');
		break;
		case 'sample_size':
			$('#sample_size_div').removeClass('has-error');
			$('#sample_size_msg').html('');
		break;
		case 'lot_inspected':
			$('#lot_inspected_div').removeClass('has-error');
			$('#lot_inspected_msg').html('');
		break;
		case 'lot_accepted':
			$('#lot_accepted_div').removeClass('has-error');
			$('#lot_accepted_msg').html('');
		break;
	}
}

function ModeOfDefectsErrors(errors) {
	$.each(errors, function(i, x) {
		switch(i) {
			case 'mode_of_defects_name':
				$('#mode_of_defects_name_div').addClass('has-error');
				$('#mode_of_defects_name_msg').html(x);
			break;
			case 'mod_qty':
				$('#mod_qty_div').addClass('has-error');
				$('#mod_qty_msg').html(x);
			break;
		}
	});
}

function ModeOfDefectsNoErrors(error) {
	switch(error) {
		case 'mode_of_defects_name':
			$('#mode_of_defects_name_div').removeClass('has-error');
			$('#mode_of_defects_name_msg').html('');
		break;
		case 'mod_qty':
			$('#mod_qty_div').removeClass('has-error');
			$('#mod_qty_msg').html('');
		break;
	}
}

function NewInspection() {
	clearControls();
	$('#btn_savemodal').prop('disabled', false);
	$('#inspection_save_status').val('ADD');
	getWorkWeek();
	getFiscalYear();
	$('#inspection_modal').modal('show');
}

function ModeOfDefects() {
	clearMOD();
	if ($('#po_no').val() == '') {
		msg('Please select a P.O. Number.');
	} else {
		$('#mode_save_status').val('ADD');
		$('#mod_po').val($('#po_no').val());
		$('#mod_device').val($('#series_name').val());
		$('#mod_lotno').val($('#lot_no').val());
		$('#mod_submission').val($('#submission').val());
        $('#ins_id').val($('#inspection_id').val());

		var modUrl = modDataTableURL+'?_token='+token+
					'&&pono='+$('#po_no').val()+
					'&&submission='+$('#submission').val();
		getDatatable('tbl_mode_of_defects',modUrl,modColumn,[],0);
		$('#mode_of_defects_modal').modal('show');
	}
}

function loadSelectInput() {
	$.ajax({
		url: loadSelectInputURL,
		type: 'GET',
		dataType: 'JSON',
		data: {_token: token},
		complete: function(xhr, textStatus) {
			//called when complete
		},
		success: function(data, textStatus, xhr) {
			$.each(data.families, function(i, x) {
                $('#family').append('<option value="'+x.description+'">'+x.description+'</option>');
            });

            $.each(data.assemblyline, function(i, x) {
                $('#assembly_line').append('<option value="'+x.description+'">'+x.description+'</option>');
            });

            $.each(data.tofinspections, function(i, x) {
                $('#type_of_inspection').append('<option value="'+x.description+'">'+x.description+'</option>');
            });

            $.each(data.sofinspections, function(i, x) {
                $('#severity_of_inspection').append('<option value="'+x.description+'">'+x.description+'</option>');
            });

            $.each(data.inspectionlvls, function(i, x) {
                $('#inspection_lvl').append('<option value="'+x.description+'">'+x.description+'</option>');
            });

            $.each(data.aqls, function(i, x) {
                $('#aql').append('<option value="'+x.description+'">'+x.description+'</option>');
            });

            // $.each(data.shifts, function(i, x) {
            //     $('#shift').append('<option value="'+x.description+'">'+x.description+'</option>');
            // });

            $.each(data.submissions, function(i, x) {
                $('#submission').append('<option value="'+x.description+'">'+x.description+'</option>');
                $('#rpt_sub').append('<option value="'+x.description+'">'+x.description+'</option>');
            });

            $.each(data.mods, function(i, x) {
                $('#mode_of_defects_name').append('<option value="'+x.description+'">'+x.description+'</option>');
            });

            //$('#shift').val('Shift A');
            $('#submission').val('1st');
		},
		error: function(xhr, textStatus, errorThrown) {
			console.log(xhr);
			msg("There was an error while generating Select input options.");
		}
	});
}

function delete_items(checkboxClass,deleteUrl,datatableURL,table,dataColumn) {
	var chkArray = [];
	$(checkboxClass+":checked").each(function() {
		chkArray.push($(this).val());
	});

	if (chkArray.length > 0) {
		var data = {
			_token: token,
			ids: chkArray,
		}
		delete_now(deleteUrl,datatableURL,data,table,dataColumn);
	} else {
		$('#delete_modal').modal('hide');
		msg("Please select at least 1 item.", 'failed');
	}
}

function delete_now(deleteUrl,datatableURL,data,table,dataColumn) {
	$.ajax({
		url: deleteUrl,
		type: 'POST',
		dataType: 'JSON',
		data: data,
	}).done(function(data,textStatus,jqXHR) {
		$('#delete_modal').modal('hide');
		msg(data.msg,data.status);
		getDatatable(table,datatableURL,dataColumn,[],0);
	}).fail(function(data,textStatus,jqXHR) {
		msg("There's an error occurred while processing.",'error');
	});
}

function setTime(time_input) {
	var time = time_input.replace('::',':');
	var h = time.substring(0,2);
	var m = time.substring(2,5);
	var a = time.substring(6,8);

	if (m == undefined || m == '' || m == null) {
		m = '00';
	}

	if (h < 12 && h > 0) {
		if (a == undefined || a == '' || a == null || a == 'A') {
			a = 'AM';
		}
		return h+":"+m+" "+a;
	} else if (h == 00 || h == 0) {
		if (a == undefined || a == '' || a == null || a == 'A') {
			a = 'AM';
		}
		return "12"+":"+m+" "+a;
	}  else if (h == 12) {
		if (a == undefined || a == '' || a == null || a == 'P') {
			a = 'PM';
		}
		return h+":"+m+" "+a;
	} else {
		if (a == undefined || a == '' || a == null || a == 'P') {
			a = 'PM';
		}
		switch(h) {
			case '13':
				return "01"+":"+m+" "+a;
				break;
			case '14':
				return "02"+":"+m+" "+a;
				break;
			case '15':
				return "03"+":"+m+" "+a;
				break;
			case '16':
				return "04"+":"+m+" "+a;
				break;
			case '17':
				return "05"+":"+m+" "+a;
				break;
			case '18':
				return "06"+":"+m+" "+a;
				break;
			case '19':
				return "07"+":"+m+" "+a;
				break;
			case '20':
				return "08"+":"+m+" "+a;
				break;
			case '21':
				return "09"+":"+m+" "+a;
				break;
			case '22':
				return "10"+":"+m+" "+a;
				break;
			case '23':
				return "11"+":"+m+" "+a;
				break;
			default:
				return time;
				break;
		}
	}
}

function getFiscalYear() {
    var date = new Date();
    var month = date.getMonth();
    var year = date.getFullYear();

    $('#fy').val(year);
}

function getWorkWeek() {
	$.ajax({
		url: getWorkWeekURL,
		type: 'GET',
		dataType: 'JSON',
		data: {_token: token},
		complete: function(xhr, textStatus) {
			//called when complete
		},
		success: function(data, textStatus, xhr) {
			$('#ww').val(data);
		},
		error: function(xhr, textStatus, errorThrown) {
			//called when there is an error
		}
	});
}

function DeleteInspection() {
	if ($('.checkboxes:checkbox:checked').length > 0) {
		$('#delete_id').val('INSPECTION');
		$('#delete_modal').modal('show');
	} else {
		msg('Select at least 1 inspection.','failed');
	}
}

function DeleteModeOfDefects() {
	if ($('.checkboxes-mod:checkbox:checked').length > 0) {
		$('#delete_id').val('MOD');
		$('#delete_modal').modal('show');
	} else {
		msg('Select at least 1 mode of defect.','failed');
	}
}

function checkLotAccepted(lot_accepted,no_of_defects) {
	if (lot_accepted == 0) {
		$('#judgement').val('Reject');
		$('#no_of_defects_div').show();
		$('#mode_of_defects_div').show();
	}

	if (lot_accepted == 1) {
		$('#judgement').val('Accept');
		$('#no_of_defects').val(0);
		$('#no_of_defects_div').hide();
		$('#mode_of_defects_div').hide();
	}

    if (no_of_defects > 0) {
        $('#judgement').val('Reject');
		$('#no_of_defects_div').show();
		$('#mode_of_defects_div').show();
    }
}

function Search() {
	$('#search_modal').modal('show');
}

function Report() {
	$('#rpt_submission').val('1st');
	$('#report_modal').modal('show');
}

function searchInspection() {
	var oqcSearchURL = oqcDataTableURL+'?type=search&search_po='+$('#search_po').val()+
						'&search_from='+$('#search_from').val()+
						'&search_to='+$('#search_to').val();
						$('#tbl_oqc').DataTable().ajax.reload();
	//getDatatable('tbl_oqc',oqcSearchURL,dataColumn,[],0);
}

// function checkAuhtor(inspector) {
// 	if (author != inspector || author != 'kurt' || author != 'Administrator') {
// 		$('#btn_savemodal').prop('disabled', true);
// 	} else {
// 		$('#btn_savemodal').prop('disabled', false);
// 	}
// }

function PDFReport() {
	var po = $('#search_po').val();
	var from = $('#search_from').val();
	var to = $('#search_to').val();
	var chosen = $('#chosen').val();
	console.log(chosen);

	var url = ReportDataCheckURL;

	if(po === null || po === ''){
		msg('Must input PO Number!','failed');
	}else{
		if(chosen === null || chosen === ''){
			msg('Parameter is required!','failed');
		}else{
			if(from !== null && from !== ''){
				if(to === null || to === ''){
					to = from;
				}
				$.ajax({
					url: url,
					type: "GET",
					dataType: "JSON",
					data: {
						//_token: _token,
						po: po,
						from: from,
						to: to,
						chosen: chosen
					}
				}).done(function (data, textStatus, jqXHR) {
					$('#loading').modal('hide');
		
					if (data.return_status == 0) {
						msg("No Data found.", 'failed');
					}
					else{
						_read_type = "search";
						$('#tbl_oqc').DataTable().ajax.reload();
						var link = PDFReportURL + "?po=" + po + "&&chosen=" + chosen + "&&from=" + from + "&&to=" + to;
						window.location.href = link;
					}
				}).fail(function (jqXHR, textStatus, errorThrown) {
					console.log(jqXHR);
					msg("There's some error while processing.", 'failed');
				}).always( function() {
					$('#loading').modal('hide');
				});	
				
			}else{
				if(to !== null && to !== ''){
					msg('Must input date inspected from!','failed');
				}else{
					$.ajax({
						url: url,
						type: "GET",
						dataType: "JSON",
						data: {
							//_token: _token,
							po: po,
							chosen: chosen
						}
					}).done(function (data, textStatus, jqXHR) {
						$('#loading').modal('hide');
			
						if (data.return_status == 0) {
							msg("No Data found.", 'failed');
						}
						else{						
							_read_type = "search";
							$('#tbl_oqc').DataTable().ajax.reload();
							var link = PDFReportURL + "?po=" + po + "&&chosen=" + chosen + "&&from=" + from + "&&to=" + to;
							window.location.href = link;																
						}
					}).fail(function (jqXHR, textStatus, errorThrown) {
						console.log(jqXHR);
						msg("There's some error while processing.", 'failed');
					}).always( function() {
						$('#loading').modal('hide');
					});				
				}
			}		
		}
		
	}
	// if(to === '' || to === null){
	// 	to = from;
	// }

	// var param = {
    //     _token: token,
    //     report_type: 'pdf',
    //     po: po,
    //     from: from,
    //     to: to
    // }

    // ReportDataCheck(param, function(output) {
	// 	if (output > 0) {
	// 		var oqcSearchURL = oqcDataTableURL+'?type=search&search_po='+po+
	// 									'&search_from='+from+
	// 									'&search_to='+to;
	// 				$('#tbl_oqc').DataTable().ajax.reload();

	// 				var link = PDFReportURL+"?po="+po+"&&from="+from+
	// 				"&&to="+to;

	// 				window.open(link,'_tab');
	// 	} else {
	// 		msg('No data was recorded.','failed');
	// 	}
	// });
	
}

function ExcelReport() {
	
	var po = $('#search_po').val();
	var from = $('#search_from').val();
	var to = $('#search_to').val();
	var chosen = $('#chosen').val();
	console.log(chosen);
	
	var url = ReportDataCheckURL;
	console.log(url);

	if(chosen === null || chosen === ''){
		msg('Parameter is required!','failed');
	}else{
		if(from !== null && from !== ''){
			if(to === null || to === ''){
				to = from;
			}
			$.ajax({
				url: url,
				type: "GET",
				dataType: "JSON",
				data: {
					//_token: _token,
					po: po,
					from: from,
					to: to,
					chosen: chosen
				}
			}).done(function (data, textStatus, jqXHR) {
				$('#loading').modal('hide');
	
				if (data.return_status == 0) {
					msg("No Data found.", 'failed');
				}
				else{
					_read_type = "search";
					$('#tbl_oqc').DataTable().ajax.reload();
					var link = ExcelReportURL + "?po=" + po + "&&chosen=" + chosen + "&&from=" + from + "&&to=" + to;
					window.location.href = link;
				}
			}).fail(function (jqXHR, textStatus, errorThrown) {
				console.log(jqXHR);
				msg("There's some error while processing.", 'failed');
			}).always( function() {
				$('#loading').modal('hide');
			});	
		}
		else{
			if(to !== null && to !== ''){
				msg('Must input date inspected from!','failed');
			}else{
				if(po === null || po === ''){
					msg('Must input PO number or date inspected from!','failed');
				}else{
					$.ajax({
						url: url,
						type: "GET",
						dataType: "JSON",
						data: {
							//_token: _token,
							po: po,
							chosen: chosen
						}
					}).done(function (data, textStatus, jqXHR) {
						$('#loading').modal('hide');
			
						if (data.return_status == 0) {
							msg("No Data found.", 'failed');
						}
						else{						
							_read_type = "search";
							$('#tbl_oqc').DataTable().ajax.reload();
							var link = ExcelReportURL + "?po=" + po + "&&chosen=" + chosen + "&&from=" + from + "&&to=" + to;
							window.location.href = link;																
						}
					}).fail(function (jqXHR, textStatus, errorThrown) {
						console.log(jqXHR);
						msg("There's some error while processing.", 'failed');
					}).always( function() {
						$('#loading').modal('hide');
					});				
				}
			}
		}		
	}

	// if(to === '' || to === null){
	// 	to = from;
	// }

	// var param = {
    //     _token: token,
    //     report_type: 'excel',
    //     po: po,
    //     from: from,
    //     to: to
    // }

    // ReportDataCheck(param, function(output) {
	// 	if (output > 0) {
	// 		var oqcSearchURL = oqcDataTableURL+'?type=search&search_po='+$('#search_po').val()+
	// 									'&search_from='+$('#search_from').val()+
	// 									'&search_to='+$('#search_to').val();
	// 				$('#tbl_oqc').DataTable().ajax.reload();

	// 				window.location.href = ExcelReportURL+"?po="+$('#search_po').val()+"&&from="+$('#search_from').val()+
	// 				"&&to="+$('#search_to').val();
	// 	} else {
	// 		msg('No data was recorded.','failed');
	// 	}
	// });
}

// function ReportDataCheck(param,handleData) {
//     $.ajax({
//         url: ReportDataCheckURL,
//         type: 'GET',
//         dataType: 'JSON',
//         data: param,
//     }).done(function(data,textStatus,jqXHR) {
//         handleData(data.DataCount);
//     }).fail(function(jqXHR,textStatus,errorThrown) {
//         msg(errorThrown,'error');
//     });
// }

function getProbeProduct(code) {
	$('#item_probe').html('');
    var data = {
        _token: token,
        code: code
    }
    $.ajax({
        url: GetProbeProductURL,
        type: 'GET',
        dataType: 'JSON',
        data: data,
    }).done(function(data,textStatus,jqXHR) {
        $('#item_probe').html('<option value=""></option>');
        $.each(data, function(i, x) {
            $('#item_probe').append('<option value="'+x.devicecode+'|'+x.DEVNAME+'">'+x.DEVNAME+'</option>');
            $('#probe_item_modal').modal('show');
        });
    }).fail(function(data,textStatus,jqXHR) {
        console.log("error");
    });
}

function samplingPLan() {
	var data = {
		_token: token,
		soi: $('#severity_of_inspection').val(),
		il: $('#inspection_lvl').val(),
		lot_qty: $('#lot_qty').val(),
		aql: $('#aql').val()
	};

	$.ajax({
		url: SamplingPlanURL,
		type: 'GET',
		dataType: 'JSON',
		data: data,
	}).done(function(data, textStatus, jqXHR) {
		console.log(data);

		if (data.ins_lvl != undefined) {
			$('#inspection_lvl').val(data.ins_lvl);
		}

		$('#accept').val(data.accept);
		$('#reject').val(data.reject);
		$('#sample_size').val(data.size);
	}).fail(function(data, textStatus, jqXHR) {
		console.log("error");
	});
}

function getShift() {
	var from = $('#time_ins_from').val();
	var to = $('#time_ins_to').val();

	var data = {
		_token: token,
		from: from,
		to: to
	};

	$.ajax({
		url: getShiftURL,
		type: 'GET',
		dataType: 'JSON',
		data: data
	}).done( function(data, textStatus,jqXHR) {
		$("#shift").val(data.shift);
		console.log(data);
	}).fail( function(data, textStatus,jqXHR) {
		console.log(data);
	});
}

function time_ins(timeVal) {
	momentVal = moment(timeVal, ["h:mm A"])
	var fTime = momentVal.format("HH:mm");
	var hr = fTime.substring(0, 2);
	var mn = fTime.substring(3, 5);

	if (fTime == "Invalid date") {
		hr = "";
		mn = "";
	}

	return {
		'hr': hr,
		'mn': mn
	}
}

function OQCDataTable(url) {
	$('#tbl_oqc').DataTable().clear();
	$('#tbl_oqc').DataTable().destroy();
	$('#tbl_oqc').DataTable({
		processing: true,
		serverSide: true,
		ajax: {
			url: url,
			dataType: "JSON",
			type: "GET",
			data: function (d) {
				d._token = $("meta[name=csrf-token]").attr("content");
				d.search_po = $('#search_po').val();
				d.search_trace_code = $('#search_trace_code').val();
				d.search_from = $('#search_from').val();
				d.search_to = $('#search_to').val();
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
			{ data: 'fyww', name: 'fyww', orderable: false, searchable: false },
			{ data: 'date_inspected', name: 'date_inspected' },
			{ data: 'po_no', name: 'po_no' },
			{ data: 'device_name', name: 'device_name' },
			{ data: 'time_ins_from', name: 'time_ins_from' },
			{ data: 'time_ins_to', name: 'time_ins_to' },
			{ data: 'submission', name: 'submission' },
			{ data: 'lot_qty', name: 'lot_qty' },
			{ data: 'sample_size', name: 'sample_size' },
			{ data: 'num_of_defects', name: 'num_of_defects' },
			{ data: 'lot_no', name: 'lot_no' },
			{ data: 'mod', name: 'mod', orderable: false, searchable: false },
			{ data: 'po_qty', name: 'po_qty' },
			{ data: 'judgement', name: 'judgement' },
			{ data: 'inspector', name: 'inspector' },
			{ data: 'remarks', name: 'remarks' },
			{ data: 'type', name: 'type' }
		],
		order: [[3, 'desc']]
	});
}

function SerialNoDataTable(dataArr) {
	$('#tbl_serial_no').DataTable().clear();
	$('#tbl_serial_no').DataTable().destroy();
	$('#tbl_serial_no').DataTable({
		processing: true,
		data: dataArr,
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
					return '<input type="checkbox" class="sn_checkboxes" value="' + data.id + '" data-serial_no="'+data.serial_no+'">';
				}, orderable: false, searchable: false, width: '5%'
			},
			{ data: function(data) {
				var disabled = '';
				if (data.id == 0 || data.id == "0") {
					disabled = 'disabled="disabled"';
				}
				return '<button type="button" class="btn btn-sm blue btn_edit_serial_no" data-id="' + data.id + '" ' + disabled + '><i class="fa fa-edit"></i></button>'
			}, orderable: false, searchable: false, width: '5%' },
			{ data: 'serial_no', width: '75%' },
		],
		order: [[2, 'desc']],
		rowCallback: function (row, data, index) {
			if (data['deleted'] == 1) {
				$(row).hide();
			}
		},
	});
}

function GetSerialNo(inspection_id) {
	
	$.ajax({
		url: GetSerialNoURL,
		type: 'GET',
		dataType: 'JSON',
		data: {
			_token: token,
			inspection_id: inspection_id
		},
	}).done(function (data, textStatus, jqXHR) {
		if (data.status == 'success') {
			serial_arr = data.serial;
			SerialNoDataTable(serial_arr);
		} else {
			msg(data.msg,data.status);
		}

		console.log(data);
	}).fail(function (jqXHR, textStatus, errorThrown) {
		msg(errorThrown,'error');
	});
}

function AddSerialNo() {
	var serial_no = $('#serial_no').val();
	var serial_id = $('#serial_id').val();
	var save_status = $('#serial_save_status').val();

	var error = 0;

	if (serial_no == "") {
		msg("Fill out all input fields.", "failed");
	} else {
		console.log(serial_arr);
		if (serial_arr.length > 0) {
			$.each(serial_arr, function (i, x) {
				if (x.serial_no == serial_no && (save_status != 'EDIT')) {
					error++;
				}
			});
		}

		if (error < 1) {
			if (!checkIfExistInArray(serial_arr, serial_id)) {
				serial_arr.push({
					'id': -1,
					'serial_no': serial_no,
					'deleted': 0
				});
			} else {
				$.each(serial_arr, function (i, x) {
					if (x.id == serial_id) {
						serial_arr[i].id = serial_id;
						serial_arr[i].serial_no = serial_no;
						serial_arr[i].deleted = 0;
					}
				});
			}

		} else {
			msg("Serial No. " + serial_no + " already added.", "failed");
		}
	}
	

	console.log(serial_arr);

	SerialNoDataTable(serial_arr);
	$('.clear_serial_no').val('');
}

function checkIfExistInArray(arrData, id) {
	var exist = 0;
	$.each(arrData, function (i, x) {
		if (x.id.toString() == id) {
			exist++;
		}
	});

	return exist;
}

function DefectsDataTable(dataArr) {
	$('#tbl_mode_of_defects').DataTable().clear();
	$('#tbl_mode_of_defects').DataTable().destroy();
	$('#tbl_mode_of_defects').DataTable({
		processing: true,
		data: dataArr,
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
					return '<input type="checkbox" class="defects_checkboxes" value="' + data.id + '" data-defects="' + data.mod1 + '">';
				}, orderable: false, searchable: false, width: '5%'
			},
			{
				data: function (data) {
					var disabled = '';
					if (data.id == 0 || data.id == "0") {
						disabled = 'disabled="disabled"';
					}
					return '<button type="button" class="btn btn-sm blue btn_edit_defects" data-id="' + data.id + '" ' + disabled + '><i class="fa fa-edit"></i></button>'
				}, orderable: false, searchable: false, width: '5%'
			},
			{ data: 'mod1', width: '75%' },
			{ data: 'qty', width: '20%' }
		],
		order: [[2, 'desc']],
		rowCallback: function (row, data, index) {
			if (data['deleted'] == 1) {
				$(row).hide();
			}
		},
	});

	var qty = 0;
	$.each(dataArr, function(i,x) {
		if (x.deleted == 0) {
			qty += parseFloat(x.qty);
		}
	});

	$('#no_of_defects').val(qty);

	if (qty > 0) {
		$('#lot_accepted').val(0);
	}
	checkLotAccepted($('#lot_accepted').val(), qty);
}

function GetDefects(inspection_id) {
	var pono = $('#po_no').val();
	var submission = $('#submission').val();
	var lotno = $('#lot_no').val();

	$.ajax({
		url: GetDefectsURL,
		type: 'GET',
		dataType: 'JSON',
		data: {
			_token: token,
			inspection_id: inspection_id,
			pono: pono,
			submission: submission,
			lotno: lotno
		},
	}).done(function (data, textStatus, jqXHR) {
		if (data.status == 'success') {
			defects_arr = data.defects;

			DefectsDataTable(defects_arr);
		} else {
			msg(data.msg, data.status);
		}

		console.log(data);
	}).fail(function (jqXHR, textStatus, errorThrown) {
		msg(errorThrown, 'error');
	});
}

function ProbeLotsDataTable(dataArr) {
	$('#tbl_probe_lot').DataTable().clear();
	$('#tbl_probe_lot').DataTable().destroy();
	$('#tbl_probe_lot').DataTable({
		processing: true,
		data: dataArr,
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
					return '<input type="checkbox" class="ppl_checkboxes" value="' + data.id + '" data-probe_lot="'+data.probe_lot+'">';
				}, orderable: false, searchable: false, width: '5%'
			},
			{ data: function(data) {
				var disabled = '';
				if (data.id == 0 || data.id == "0") {
					disabled = 'disabled="disabled"';
				}
				return '<button type="button" class="btn btn-sm blue btn_edit_probe_lot" data-id="' + data.id + '" ' + disabled + '><i class="fa fa-edit"></i></button>'
			}, orderable: false, searchable: false, width: '5%' },
			{ data: 'probe_lot', width: '65%' },
			{ data: 'qty', width: '10%' },
		],
		order: [[2, 'desc']],
		rowCallback: function (row, data, index) {
			if (data['deleted'] == 1) {
				$(row).hide();
			}
		},
	});
}

function GetProbeLots(inspection_id) {
	
	$.ajax({
		url: GetProbeLotsURL,
		type: 'GET',
		dataType: 'JSON',
		data: {
			_token: token,
			inspection_id: inspection_id
		},
	}).done(function (data, textStatus, jqXHR) {
		if (data.status == 'success') {
			probe_lot_arr = data.probe_lots;
			ProbeLotsDataTable(probe_lot_arr);
		} else {
			msg(data.msg,data.status);
		}

		console.log(data);
	}).fail(function (jqXHR, textStatus, errorThrown) {
		msg(errorThrown,'error');
	});
}