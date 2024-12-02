$( function() {
	dropdowns();
	instance();
	detailsTable([]);

	checkAllCheckboxesInTable('.check_all_details','.check_detail');

	$('#btn_load_po').on('click', function() {
		if ($('#po').val() == '') {
			msg('Please enter a P.O. Number.','failed');
		} else {
			$.ajax({
				url: getPODetailsURL,
				type: 'GET',
				dataType: 'JSON',
				data: {
					_token: token,
					po: $('#po').val()
				},
			}).done(function(data, textStatus, xhr) {
				if (data.status == 'success') {
					var yp = data.yield_performance;

					$('#po').val(yp.po);
					$('#device').val(yp.device);
					$('#po_qty').val(yp.po_qty);
					$('#family').val(yp.family);
					$('#series').val(yp.series);
					$('#prod_type').val(yp.prod_type);

					$('#family').prop('disabled',false);
					$('#series').prop('disabled',false);
					$('#prod_type').prop('disabled',false);

					$('#yielding_station').prop('disabled',false);
					$('#classification').prop('disabled',false);
					$('#mode_of_defect').prop('disabled',false);

					$('#production_date').prop('readonly',false);
					$('#accumulated_output').prop('readonly',false);
					$('#defect_qty').prop('readonly',false);
					$('#remarks').prop('readonly',false);

					detailsTable(data.yield_performance_details);
				} else {
					msg(data.msg,data.status);
				}
				
			}).fail(function(xhr, textStatus, errorThrown) {
				msg('Search P.O.: '+errorThrown,textStatus)
			});
		}
	});
});

function instance() {
	$('#btn_load_po').prop('disabled', true);
	$('#btn_add_details').prop('disabled', true);
	$('#btn_discard').prop('disabled', true);
	$('#btn_save').prop('disabled', true);

	$('#btn_add_new').prop('disabled', false);

	$('#po').prop('readonly',true);
	$('#po_qty').prop('readonly',true);
	$('#device').prop('readonly',true);
	$('#production_date').prop('readonly',true);
	$('#accumulated_output').prop('readonly',true);
	$('#defect_qty').prop('readonly',true);
	$('#remarks').prop('readonly',true);
	$('#total_input').prop('readonly',true);
	$('#total_output').prop('readonly',true);
	$('#total_reject').prop('readonly',true);
	$('#total_mng').prop('readonly',true);
	$('#total_png').prop('readonly',true);
	$('#yield_wo_mng').prop('readonly',true);
	$('#total_yield').prop('readonly',true);
	$('#dppm').prop('readonly',true);

	$('#family').prop('disabled',true);
	$('#series').prop('disabled',true);
	$('#prod_type').prop('disabled',true);
	$('#yielding_station').prop('disabled',true);
	$('#classification').prop('disabled',true);
	$('#mode_of_defect').prop('disabled',true);
}

function new_transaction() {
	$('#btn_load_po').prop('disabled', false);
	$('#btn_discard').prop('disabled', false);
	$('#btn_save').prop('disabled', false);

	$('#po').prop('readonly',false);

	$('#btn_add_new').prop('disabled', true);
}

function discard() {
	instance();
	$('.clear').val('');
	detailsTable([]);
}

function dropdowns() {
	$.ajax({
		url: DropdownsURL,
		type: 'GET',
		dataType: 'JSON',
		data: {_token:token},
	}).done(function(data, textStatus, xhr) {
		var family = '<option value=""></option>';
		$('#family').html(family);

		$.each(data.family, function(i, x) {
			family = '<option value="'+x.description+'">'+x.description+'</option>';
			$('#family').append(family);
		});

		var series = '<option value=""></option>';
		$('#series').html(series);

		$.each(data.series, function(i, x) {
			series = '<option value="'+x.description+'">'+x.description+'</option>';
			$('#series').append(series);
		});

		var mode_of_defect = '<option value=""></option>';
		$('#mode_of_defect').html(mode_of_defect);

		$.each(data.mode_of_defect, function(i, x) {
			mode_of_defect = '<option value="'+x.description+'">'+x.description+'</option>';
			$('#mode_of_defect').append(mode_of_defect);
		});

		var yielding_station = '<option value=""></option>';
		$('#yielding_station').html(yielding_station);

		$.each(data.yielding_station, function(i, x) {
			yielding_station = '<option value="'+x.description+'">'+x.description+'</option>';
			$('#yielding_station').append(yielding_station);
		});
	}).fail(function(xhr, textStatus, errorThrown) {
		console.log("error");
	});
}

function detailsTable(arr) {
	$('#tbl_details').dataTable().fnClearTable();
    $('#tbl_details').dataTable().fnDestroy();
    $('#tbl_details').dataTable({
        data: arr,
        bLengthChange : false,
        scrollY: "200px",
        paging: false,
        searching: false,
        columns: [
            { data: function(x) {
                return "<input type='checkbox' class='check_detail' data-id='"+x.id+"' value='"+x.id+"'>";
            }, searchable: false, orderable: false },

            { data: function(x) {
               return "<button class='btn btn-sm bg-blue btn_edit_pya' data-id='"+x.id+"' data-defect_qty='"+x.defect_qty+"'data-production_date='"+x.production_date+"'data-yieldingstation='"+x.yieldingstation+"'data-accumulatedoutput='"+x.accumulatedoutput+"'data-classification='"+x.classification+"' data-mod='"+x.mod+"'><i class='fa fa-edit'></i></button>";
            }},

            { data: function(x) {
                return x.production_date+"<input type='hidden' name='production_date[]' value='"+x.production_date+"'>";
            }},

            { data: function(x) {
                return x.yielding_station+"<input type='hidden' name='yielding_station[]' value='"+x.yielding_station+"'>";
            }},

            { data: function(x) {
                return x.accumulated_output+"<input type='hidden' name='accumulated_output[]' value='"+x.accumulated_output+"'>";
            }},

            { data: function(x) {
                return x.classification+"<input type='hidden' name='classification[]' value='"+x.classification+"'>";
            }},

            { data: function(x) {
                return x.mode_of_defect+"<input type='hidden' name='mode_of_defect[]' value='"+x.mode_of_defect+"'>";
            }},

            { data: function(x) {
                return x.defect_qty+"<input type='hidden' name='defect_qty[]' value='"+x.defect_qty+"'>";
            }},

            { data: function(x) {
                return x.remarks+"<input type='hidden' name='remarks[]' value='"+x.remarks+"'>";
            }},
        ]
    });
}