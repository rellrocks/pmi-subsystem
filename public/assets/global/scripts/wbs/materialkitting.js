//UPDATE 2024-04-15 : 01:20pm
id = "";
issuance_no = "";
tbl_check_details_modal = "";
var fifoTable = "";
var issuance_details_arr = [];
$( function() {
    checkDetails();
    // DetailModalTable();
	getMaterialKittingData();
	checkAllCheckboxesInTable('.tbl_kitdetails_group_checkable','.kit_checkboxes');
	checkAllCheckboxesInTable('.tbl_issuance_group_checkable','.iss_checkboxes');
	checkAllCheckboxesInTable('.check_all_items','.check_lot');
	$('#kitdetailsli').on('click', function(e) {
		$('#btn_received_by').hide();
	});
	$('#issuancedetailsli').on('click', function(e) {
		$('#btn_received_by').show();
	});
	$('#searchpoForm').on('submit', function(event) {
		event.preventDefault();
		var data = $(this).serialize();
		$('#loading').modal('show');
		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			dataType: 'JSON',
			data: data,
		}).done(function(data,textStatus,jqXHR) {
			$('#loading').modal('hide');
			if (data.hasOwnProperty('msg')) {
				msg(data.msg,data.status);
			} else {
				console.log(data);
				tempTblKitDetailsData(data);
				$('#btn_addPO').attr('disabled','true');
				$('#btn_cancel').removeAttr('disabled');
				$('#kitqtyModal').modal('show');
			}
		}).fail(function(data,textStatus,jqXHR) {
			$('#loading').modal('hide');
			msg("There was an error occurred while searching.",'error');
		});
	});

	$('#updateKityQty').on('click', function(event) {
		var kitqty = parseFloat($('#getkitQty').val());
		var poqty = parseFloat($('#poqty').val());

		// if (kitqty > poqty) {
		// 	msg('Kit Qty must not be greater than the PO Qty.','failed');
		// } else {
			$('#kitqty').val(kitqty);
			$('#kitqtyModal').modal('hide');

			var usages = $('input[name="kit_usage[]"]').map(function(){return $(this).val();}).get();
			var qty = [];
			
			$.each(usages, function(i, x) {
				newkitqty = x * kitqty;
				qty.push(newkitqty);
			});

			$('input[name="kit_qty[]"]').each(function(i, x) {
				$(this).val(qty[i]);
			});

			$('.kit_qty').each(function(i, x) {
				$(this).html(qty[i]+'<input type="hidden" name="kit_qty[]" value="'+qty[i]+'">');
			});
		// }
	});

	$('#btn_check_details').on('click',function(){
		  $('#loading').modal('show');
		  checkDetails();  
		  setTimeout(function(){
       $('#check_issuance_details').modal('show');
   	}, 1000);
	});
	$('#iss_delete_detail_id').on('click',function(){
		var arr_id = [];
		$('.check_lot:checked').each(function(index, el) {
			arr_id.push($(this).val());
			// console.log(arr_id);			
		});

		if(arr_id.length > 1)

		{
			 bootbox.confirm("Are you sure you want to delete this selected items?", function(result){
                if(result){
                    bootbox.alert({
                    message: "Deleted successfully!",
                    backdrop: true,
                    size: 'small'
                    });  
                   Delete(arr_id);
                  	// $('#check_issuance_details').modal('hide');
                  	// cleartable();
                }else{
                }              
            });	   
		}else if(arr_id.length == 1){

				 bootbox.confirm("Are you sure you want to delete this selected item?", function(result){
                if(result){
                    bootbox.alert({
                    message: "Deleted successfully!",
                    backdrop: true,
                    size: 'small'
                    });  
                 Delete(arr_id);
                  	// $('#check_issuance_details').modal('hide');
                  	// cleartable();
                }else{


                }              
            });	   

		
		}  	 
	});
	// $('#example-select-all').on('click',function(){
	// 	var rows = tbl_check_details_modal.rows({'seach':'applied'}).nodes();
	//     $('input[type="checkbox"]', rows).prop('checked', this.checked);	
	// });

	$('#iss_edit_close_modal').on('click',function(){
		 $('#check_issuance_details').modal('hide');
		 cleartable();
	});

// 	$('#tbl_check_details_modal').on('click',function(){

//     var tbl_check_details_modal = $('#tbl_check_details_modal').DataTable();

//     if ( ! tbl_check_details_modal.data().any() ) {

//       // $('#btn_edit').prop('disabled',true);

//       $('#iss_delete_detail_id').prop('disabled',true);

//     }else{
//     }

// });

	// tbl_check_details_modal = $('#tbl_check_details_modal').DataTable();
 //    $('#tbl_check_details_modal tbody').on( 'click', 'tr', function () {
 //        if  ($(this).hasClass('selected')) {
 //            $(this).removeClass('selected');
 //            // $('#btn_edit').prop('disabled',true);
 //            $('#iss_delete_detail_id').prop('disabled',true);
 //            // tbl_check_details_modal.fixedHeader.disable();
 //        }
 //        else {
 //            tbl_check_details_modal.$('tr.selected').removeClass('selected');
 //            $(this).addClass('selected');           
 //            // $('#btn_edit').prop('disabled',false);
 //            $('#iss_delete_detail_id').prop('disabled',false);
 //            // tbl_check_details_modal.fixedHeader.disable();
 //        }
 //    });   

	//SAVE ISSUANCE
	$('#btn_save').on('click', function(e) {
		var params = null;
		var material_kitting = null;
		var material_kitting_details = [];
		var issuance_details = [];
		if ($('input[name=kitno]').val() == '' || $('input[name=preparedby]').val() == '') {
			msg("All fields are required.",'failed');
		} else {
			if ($('#save_type').val() == 'KIT') {
				//ADD NEW Kit Details
				material_kitting = {
					id: $('#kitinfo_id').val(),
					issuanceno: $('input[name=issuanceno]').val(),
					po: $('input[name=po]').val(),
					devicecode: $('input[name=devicecode]').val(),
					devicename: $('input[name=devicename]').val(),
					poqty: $('input[name=poqty]').val(),
					kitqty: $('input[name=kitqty]').val(),
					kitno: $('input[name=kitno]').val(),
					preparedby: $('input[name=preparedby]').val(),
					status: $('input[name=status]').val()
				};
				for(let i = 0; i < $('input[name="kit_detail_id[]"]').map(function(){return $(this).val();}).get().length;i++){
					material_kitting_details.push({
						kit_detail_id: $('input[name="kit_detail_id[]"]').map(function(){return $(this).val();}).get()[i],
						kit_itemcode: $('input[name="kit_itemcode[]"]').map(function(){return $(this).val();}).get()[i],
						kit_itemname: $('input[name="kit_itemname[]"]').map(function(){return $(this).val();}).get()[i],
						kit_usage: $('input[name="kit_usage[]"]').map(function(){return $(this).val();}).get()[i],
						kit_rqdqty: $('input[name="kit_rqdqty[]"]').map(function(){return $(this).val();}).get()[i],
						kit_qty: $('input[name="kit_qty[]"]').map(function(){return $(this).val();}).get()[i],
						kit_issuedqty: $('input[name="kit_issuedqty[]"]').map(function(){return $(this).val();}).get()[i],
						kit_loaction: $('input[name="kit_loaction[]"]').map(function(){return $(this).val();}).get()[i],
						kit_drawno: $('input[name="kit_drawno[]"]').map(function(){return $(this).val();}).get()[i],
						kit_supplier: $('input[name="kit_supplier[]"]').map(function(){return $(this).val();}).get()[i],
						kit_whs100: $('input[name="kit_whs100[]"]').map(function(){return $(this).val();}).get()[i],
						kit_whs102: $('input[name="kit_whs102[]"]').map(function(){return $(this).val();}).get()[i],
					});
				}
				params = {
					save_type: $('#save_type').val(),
					material_kitting : material_kitting,
					material_kitting_details : material_kitting_details
				};
			}else if ($('#save_type').val() == 'ISSUANCE'){
				//ADD / UPDATE Issuance Details
				material_kitting = {
					id: $('#kitinfo_id').val(),
					issuanceno: $('input[name=issuanceno]').val(),
					po: $('input[name=po]').val(),
					devicecode: $('input[name=devicecode]').val(),
					devicename: $('input[name=devicename]').val(),
					poqty: $('input[name=poqty]').val(),
					kitqty: $('input[name=kitqty]').val(),
					kitno: $('input[name=kitno]').val(),
					preparedby: $('input[name=preparedby]').val(),
					status: $('input[name=status]').val()
				};

				for(let i = 0; i < $('input[name="kit_detail_id[]"]').map(function(){return $(this).val();}).get().length;i++){
					material_kitting_details.push({
						kitting_details_id: $('input[name="kitting_details_id[]"]').map(function(){return $(this).val();}).get()[i],
						kit_detail_id: $('input[name="kit_detail_id[]"]').map(function(){return $(this).val();}).get()[i],
						kit_itemcode: $('input[name="kit_itemcode[]"]').map(function(){return $(this).val();}).get()[i],
						kit_itemname: $('input[name="kit_itemname[]"]').map(function(){return $(this).val();}).get()[i],
						kit_usage: $('input[name="kit_usage[]"]').map(function(){return $(this).val();}).get()[i],
						kit_rqdqty: $('input[name="kit_rqdqty[]"]').map(function(){return $(this).val();}).get()[i],
						kit_qty: $('input[name="kit_qty[]"]').map(function(){return $(this).val();}).get()[i],
						kit_issuedqty: $('input[name="kit_issuedqty[]"]').map(function(){return $(this).val();}).get()[i],
						kit_loaction: $('input[name="kit_loaction[]"]').map(function(){return $(this).val();}).get()[i],
						kit_drawno: $('input[name="kit_drawno[]"]').map(function(){return $(this).val();}).get()[i],
						kit_supplier: $('input[name="kit_supplier[]"]').map(function(){return $(this).val();}).get()[i],
						kit_whs100: $('input[name="kit_whs100[]"]').map(function(){return $(this).val();}).get()[i],
						kit_whs102: $('input[name="kit_whs102[]"]').map(function(){return $(this).val();}).get()[i],
					});
				}

				for(let i = 0;i < issuance_details_arr.length;i++) {
					var info = issuance_details_arr[i];
					issuance_details.push({
						issdetailid: info.issdetailid,
						issitem: info.item,
						issitemdesc: info.item_desc,
						isskit_qty: info.kit_qty,
						ississued_qty: info.issued_qty,
						isslot_no: info.lot_no,
						isslocation: info.location,
						issremarks: info.remarks,
						fifoid: info.fifoid,
						iss_db_id: info.iss_db_id,
					});
				}


				params = {
					save_type: $('#save_type').val(),
					material_kitting : material_kitting,
					material_kitting_details : material_kitting_details,
					issuance_details : issuance_details,
					isHaveIssuance : issuance_details.length
				};
				
			}
			$('#loading').modal('show');
			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				dataType: 'JSON',
				data: {
					_token: token,
					data : params
				},
			}).done(function(data,textStatus,jqXHR) {
				$('#loading').modal('hide');
				getMaterialKittingData('',$('#kitinfo_id').val());
				msg(data.msg,data.status);
			}).fail(function(data,textStatus,jqXHR) {
				$('#loading').modal('hide');
				msg("There was an error occurred while processing.",'error');
			});
		}
	});

	$('#btn_update_issuance_details').on('click', function(e) {
		if ($('input[name=kitno]').val() == '' || $('input[name=preparedby]').val() == '') {
			msg("All fields are required.",'failed');
		} else {
			var params = null;
			var material_kitting = null;
			var material_kitting_details = [];
			var issuance_details = [];

			if ($('#save_type').val() == 'ISSUANCE') {
				//ADD / UPDATE Issuance Details
				material_kitting = {
					id: $('#kitinfo_id').val(),
					issuanceno: $('input[name=issuanceno]').val(),
					po: $('input[name=po]').val(),
					devicecode: $('input[name=devicecode]').val(),
					devicename: $('input[name=devicename]').val(),
					poqty: $('input[name=poqty]').val(),
					kitqty: $('input[name=kitqty]').val(),
					kitno: $('input[name=kitno]').val(),
					preparedby: $('input[name=preparedby]').val(),
					status: $('input[name=status]').val()
				};

				for(let i = 0; i < $('input[name="kit_detail_id[]"]').map(function(){return $(this).val();}).get().length;i++){
					material_kitting_details.push({
						kitting_details_id: $('input[name="kitting_details_id[]"]').map(function(){return $(this).val();}).get()[i],
						kit_detail_id: $('input[name="kit_detail_id[]"]').map(function(){return $(this).val();}).get()[i],
						kit_itemcode: $('input[name="kit_itemcode[]"]').map(function(){return $(this).val();}).get()[i],
						kit_itemname: $('input[name="kit_itemname[]"]').map(function(){return $(this).val();}).get()[i],
						kit_usage: $('input[name="kit_usage[]"]').map(function(){return $(this).val();}).get()[i],
						kit_rqdqty: $('input[name="kit_rqdqty[]"]').map(function(){return $(this).val();}).get()[i],
						kit_qty: $('input[name="kit_qty[]"]').map(function(){return $(this).val();}).get()[i],
						kit_issuedqty: $('input[name="kit_issuedqty[]"]').map(function(){return $(this).val();}).get()[i],
						kit_loaction: $('input[name="kit_loaction[]"]').map(function(){return $(this).val();}).get()[i],
						kit_drawno: $('input[name="kit_drawno[]"]').map(function(){return $(this).val();}).get()[i],
						kit_supplier: $('input[name="kit_supplier[]"]').map(function(){return $(this).val();}).get()[i],
						kit_whs100: $('input[name="kit_whs100[]"]').map(function(){return $(this).val();}).get()[i],
						kit_whs102: $('input[name="kit_whs102[]"]').map(function(){return $(this).val();}).get()[i],
					});
				}
				for(let i = 0;i < issuance_details_arr.length;i++) {
					var info = issuance_details_arr[i];
					issuance_details.push({
						issdetailid: info.issdetailid,
						issitem: info.item,
						issitemdesc: info.item_desc,
						isskit_qty: info.kit_qty,
						ississued_qty: info.issued_qty,
						isslot_no: info.lot_no,
						isslocation: info.location,
						issremarks: info.remarks,
						fifoid: info.fifoid,
						iss_db_id: info.iss_db_id,
					});
				}
				
				params = {
					save_type: $('#save_type').val(),
					material_kitting : material_kitting,
					material_kitting_details : material_kitting_details,
					issuance_details : issuance_details,
					isHaveIssuance : issuance_details.length
				};
				$('#loading').modal('show');
				$.ajax({
					url: $(this).attr('action'),
					type: 'POST',
					dataType: 'JSON',
					data: {
						_token: token,
						data : params
					},
				}).done(function(data,textStatus,jqXHR) {
					$('#loading').modal('hide');
					getMaterialKittingData('',$('#kitinfo_id').val());
					msg(data.msg,data.status);
				}).fail(function(data,textStatus,jqXHR) {
					$('#loading').modal('hide');
					msg("There was an error occurred while processing.",'error');
				});
			}
			// $.ajax({
			// 	url: $(this).attr('action'),
			// 	type: 'POST',
			// 	dataType: 'JSON',
			// 	data: {
			// 		_token: token,
			// 		params : params
			// 	},
			// }).done(function(data,textStatus,jqXHR) {
			// }).fail(function(data,textStatus,jqXHR) {
			// 	$('#loading').modal('hide');
			// 	msg("There was an error occurred while processing.",'error');
			// });
		}
	});

	$('#issuanceno').on('change', function(event) {
		event.preventDefault();
		getMaterialKittingData('',$(this).val());
	});

	$('#btn_kitqty').on('click', function(event) {
		$('#kitqtyModal').modal('show');
	});

	$('#btn_delete_kit_details').on('click', function() {
		$('#delete_id').val('kit_details');
		delete_modal();
	});

	$('#btn_delete_issuance_details').on('click', function() {
		$('#delete_id').val('issuance_details');
		delete_modal();
	});

	$('#btn_confirm_delete').on('click', function() {
		if ($('#delete_id').val() == 'kit_details') {
			delete_items('.kit_checkboxes',DeleteKitDetailsURL);
		}
		
		if ($('#delete_id').val() == 'issuance_details') {
			delete_items('.iss_checkboxes',DeleteIssDetailsURL);
		}
	});

	$('#btn_confirm').on('click', function() {
		var data = {
			_token: token,
			issuanceno: $('#issuanceno').val()
		};
		$('#confirm_modal').modal('hide');
		save(CancelPoURL,data);
		getMaterialKittingData();
	});

	$('#btn_add_issuance_details').on('click', function() {
		$('.iss_clear').val('');
		$('#iss_item').prop('readonly', false);
		//$('#iss_lotno').prop('readonly', true);
		$('#iss_qty').prop('readonly', false);
		$('#iss_location').prop('readonly', true);
		$('#iss_remarks').prop('readonly', true);
		$('#tbl_fifo_body').html('');
		$('#iss_save_status').val('ADD');
		$('#fifoid').val("0");
		$('#addIssuanceDetailsModal').modal('show');
		$('#iss_item').focus();
	});

	$('#iss_item').on('change', function(e) {
		clear_addIssuanceTable();
		getItemAndLotnumFifo();
	});
	
	$('#iss_lotno').on('change', function(e) {
		getItemAndLotnumFifo();
	});


	$('#btn_print').on('click',function(e){

		transferSlip();

	});

	$('#btn_search_kitting').on('click',function(e){
			kittingList();
	});

	
	$('#tbl_fifo_body').on('click', '.showfifoitem', function(event) {
		if ($(this).attr('data-rowcount') != 1) {
			$('#fr_id').val($(this).attr('data-id'));
			$('#fr_item').val($(this).attr('data-item'));
			$('#fr_item_desc').val($(this).attr('data-item_desc'));
			$('#fr_qty').val($(this).attr('data-qty'));
			$('#fr_lot_no').val($(this).attr('data-lot_no'));
			$('#fr_location').val($(this).attr('data-location'));
			$('#fr_kit_qty').val($(this).attr('data-kit_qty'));
			$('#fifoReasonModal').modal('show');
		} else {
			$('#fifoid').val($(this).attr('data-id'));
			$('#iss_item').val($(this).attr('data-item'));
			$('#iss_item_desc').val($(this).attr('data-item_desc'));
			$('#iss_lotno').val($(this).attr('data-lot_no'));
			$('#iss_kitqty').val($(this).attr('data-kit_qty'));
			$('#iss_qty').val($(this).attr('data-qty'));
			$('#iss_location').val($(this).attr('data-location'));

			//$('#iss_lotno').prop('readonly', true);
			$('#iss_remarks').prop('readonly', false);

			$('#iss_selected_qty').val($(this).attr('data-qty'));
		}
	});

	$('#btn_add_issuance').on('click', function() {

		if($("#iss_lotno").val() != ""){
			var iss_qty = parseFloat($('#iss_qty').val());
			var kit_qty = parseFloat($('#iss_kitqty').val());
			var selected_qty = parseFloat($('#iss_selected_qty').val());
			if (selected_qty < iss_qty) {
				msg("Issued quantity is larger than selected quantity based on lot number.",'failed');
			} else {
				if (iss_qty > kit_qty) {
					msg("Issued quantity is larger than kit quantity.",'failed');
				} else {
					var inventory_id = $('#fifoid').val();
					var iss_qty = $('#iss_qty').val();
					var iss_lotno = $('#iss_lotno').val();
					var iss_location = $('#iss_location').val();
					var iss_remarks = $('#iss_remarks').val();
					if($('#iss_save_status').val() == "EDIT") {
						var issuance_id = $('#iss_id').val();
						var f = issuance_details_arr.find(x => x.iss_db_id == issuance_id);
						if (f != "" && f !== undefined) {
							f.issued_qty = iss_qty;
							f.lot_no = iss_lotno;
							f.location = iss_location;
							f.remarks = iss_remarks;
							f.fifoid = inventory_id;
							$('#issued_qty_'+issuance_id).text(iss_qty);
							$('#lot_no_'+issuance_id).text(iss_lotno);
							$('#location_'+issuance_id).text(iss_location);
							$('#remarks_'+issuance_id).text(iss_remarks);
							$('#fifoid').val('');
						}
						
					}else {
						issuance_details_arr.push({
							iss_db_id : 0,//if zero, means is new row from Add Details
							item : $('#iss_item').val(),
							item_desc : $('#iss_item_desc').val(),
							kit_qty : $('#iss_kitqty').val(),
							issued_qty : iss_qty,
							lot_no : iss_lotno,
							location : iss_location,
							remarks : iss_remarks,
							fifoid : $('#fifoid').val(),
							issdetailid : (issuance_details_arr.length + 1)
						});
						console.log(issuance_details_arr);
						kitIssuanceTable(issuance_details_arr);
					}
					$('#addIssuanceDetailsModal').modal('hide');
				}
				
			}
		}else{
			$('#addIssuanceDetailsModal').modal('hide');
		}
		
	});

	$('#btn_search').on('click', function() {
		searchReset();
		$('#searchModal').modal('show');
	});
	$('#btn_receivedByConfirm').on('click', function() {
		var self = $(this);
		var params = {
			_token: token,
			kit_id : $('#kitinfo_id').val()
		};
		$.ajax({
            url: saveReceivedByURL,
            type: "POST",
            data: params,
        }).done(function (r, textStatus, jqXHR) {
            $('#loading').modal('hide');
            if(r.status == 'success') {
				msg(r.msg,r.status);
				$('#receivedBy_modal').modal('hide');
				self.prop('disabled',true);
				getMaterialKittingData('',$('#kitinfo_id').val());
			}else {
				msg(r.msg,r.status);
			}
        }).fail(function (r, textStatus, jqXHR) {
            $('#loading').modal('hide');
            failedMsg("There's some error while processing.");
        });
	});
	$('#btn_received_by').on('click', function (){
		$('#receivedBy_modal').modal('show');
	});

	$('#tbl_search_body').on('click', '.btn_get_search', function() {
		getMaterialKittingData('',$(this).attr('data-kitinfo_id'));
		$('#searchModal').modal('hide');
	});

	$('#tbl_issuance_body').on('click', '.barcodebtn', function() {
		var self = $(this);
		var issuance_id = self.attr("data-ids");
		if (isOnMobile() == true) {
			printBRcode(issuance_id);
		} else {
			printBRcode(issuance_id);
			msg("Please use mobile device.",'failed');
		}
	});

	$('#tbl_issuance_body').on('click', '.btn_edit_issuance', function() {
		var self = $(this);
		clear_addIssuanceTable();
		var f = issuance_details_arr.find(x => x.iss_db_id == self.attr('data-ids'));
		if (f != "" && f !== undefined) {
			$('#iss_save_status').val('EDIT');
			$('#fifoid').val(0);
			$('#iss_item').val(f.item);
			$('#iss_item_desc').val(f.item_desc);
			$('#iss_lotno').val(f.lot_no);
			$('#iss_kitqty').val(f.kit_qty);
			$('#iss_qty').val(f.issued_qty);
			$('#iss_location').val(f.location);
			$('#iss_remarks').val(f.remarks);

			$('#iss_item').prop('readonly', true);
			
			$('#iss_id').val(f.iss_db_id);
			$('#iss_detail_id').val(f.issdetailid);
			getItemAndLotnumFifo();
			$('#addIssuanceDetailsModal').modal('show');
		}
	});

	$('#btn_fiforeason').on('click', function() {
		var data = {
			_token: token,
			id: $('#fr_id').val(),
			prev_id: $('#fr_prev_id').val(),
			item: $('#fr_item').val(),
			item_desc: $('#fr_item_desc').val(),
			qty: $('#fr_qty').val(),
			lotno: $('#fr_lot_no').val(),
			location: $('#fr_location').val(),
			kitqty: $('#fr_kit_qty').val(),
			reason_id: $('#reason_id').val(),
			reason: $('#fiforeason').val(),
			issuanceno: $('#issuanceno').val(),
			user_id: $('#user_id').val(),
			password: $('#password').val()

		};

		if ($('#reason_id').val() == '') {
			msg('Please specify your reason for using this Lot Number.','failed');
		}  else {
			saveFifoReason(data);
		}
	});

	$('#btn_reasonlogs').on('click', function() {
		window.location.href = reasonLogsURL + "?issuanceno="+$('#issuanceno').val();
	});

	$('#btn_kittinglist').on('click', function() {
		$('#kittingListModal').modal('show');
	});

	$("#tbl_fifo").on( 'column-sizing.dt', function ( e, settings ) {
		$(".dataTables_scrollHeadInner").css( "width", "100%" );
	});

	$('#btn_enable').on('click', function () {
		var data = {
			_token: token,
			inv_id: $('#enable_id').val(),
			row_id: $('#enable_row_id').val(),
			prev_row_id: $('#enable_prev_row_id').val(),
			reason: $('#enable_reason').val(),
			issuanceno: $('#issuanceno').val(),
			user_id: $('#enable_user_id').val(),
			password: $('#enable_password').val()
		};

		if (data.user_id == "" || data.password == "" || data.reason == "") {
			msg("Please fill out all input fields.","failed");
		} else {
			enableItem(data);
		}
	});
});

function enableItem(param) {
	$('#loading').modal('show');
	$.ajax({
		url: enableItemURL,
		type: 'POST',
		dataType: 'JSON',
		data: param
	}).done(function (data, textStatus, jqXHR) {
		if (data.status == 'success') {
			var prev_id = data.prev_row_id;
			var row_id = data.row_id;

			fifoTable.row(prev_id).deselect();

			var col = fifoTable.row(row_id).context[0].aoData[row_id].anCells[0];

			$(col).css('width', '56.78125px');
			$(col).html('');

			fifoTable.row(row_id).select();

			var selected = fifoTable.row(row_id).data();

			populateIssuanceDetails(selected);

			$('#enableModal').modal('hide');

		} else {
			msg(data.msg, data.status);
		}

	}).fail(function (data, textStatus, jqXHR) {
		msg("There was an error while processing.", 'error');
	}).always(function () {
		$('#loading').modal('hide');
	});
}

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
		getMaterialKittingData('',$('#kitinfo_id').val());
	}
}

function viewState() {
	//buttons
	$('#btn_received_by').hide();
	if (parseInt(access_state) !== 2) {
		$('#btn_addPO').show();
		$('#btn_save').hide();

		if ($('#status').val() === 'Cancelled' || $('#status').val() === 'C') {
			$('#btn_edit').hide();
			$('#btn_cancel').hide();
			// $('#btn_kittinglist').hide();
			$('#btn_print').hide();
		} else {
			$('#btn_edit').show();
			$('#btn_cancel').show();
			// $('#btn_kittinglist').show();
			$('#btn_print').show();
		}

		$('.btn_edit_issuance').prop('disabled', true);


		
		$('#btn_discard').hide();
		$('#btn_search').show();
		$('#btn_delete_kit_details').hide();
		$('#btn_add_issuance_details').hide();
		$('#btn_update_issuance_details').hide();
		$('#btn_delete_issuance_details').hide();

		// input control buttons
		$('#btn_min').removeAttr('disabled');
		$('#btn_prv').removeAttr('disabled');
		$('#btn_nxt').removeAttr('disabled');
		$('#btn_max').removeAttr('disabled');
		$('#btn_searchpo').prop('disabled',true);
		$('#btn_kitqty').prop('disabled',true);

		//inputs
		$('#issuanceno').prop('readonly',false);
		$('#searchpono').prop('readonly',true);
		$('#devicecode').prop('readonly',true);
		$('#devicename').prop('readonly',true);
		$('#poqty').prop('readonly',true);
		$('#kitqty').prop('readonly',true);
		$('#kitno').prop('readonly',true);
		$('#assessment').prop('readonly',true);
		$('#preparedby').prop('readonly',true);
		$('#btn_addPO').prop('disabled', false);
	} else {
		$('#btn_addPO').hide();
		$('#btn_save').hide();

		$('#btn_edit').hide();
		$('#btn_cancel').hide();
		$('#btn_print').show();

		$('.btn_edit_issuance').prop('disabled', true);

		$('#btn_discard').hide();
		$('#btn_search').show();
		$('#btn_delete_kit_details').hide();
		$('#btn_add_issuance_details').hide();
		$('#btn_update_issuance_details').hide();
		$('#btn_delete_issuance_details').hide();
		$('#btn_check_details').hide();

		// input control buttons
		$('#btn_min').removeAttr('disabled');
		$('#btn_prv').removeAttr('disabled');
		$('#btn_nxt').removeAttr('disabled');
		$('#btn_max').removeAttr('disabled');
		$('#btn_searchpo').prop('disabled', true);
		$('#btn_kitqty').prop('disabled', true);

		//inputs
		$('#issuanceno').prop('readonly', false);
		$('#searchpono').prop('readonly', true);
		$('#devicecode').prop('readonly', true);
		$('#devicename').prop('readonly', true);
		$('#poqty').prop('readonly', true);
		$('#kitqty').prop('readonly', true);
		$('#kitno').prop('readonly', true);
		$('#assessment').prop('readonly', true);
		$('#preparedby').prop('readonly', true);
		$('#btn_addPO').prop('disabled', false);
	}
}

function editState() {
	$('#save_type').val('ISSUANCE');
	//buttons
	$('#btn_addPO').prop('disabled', false);
	$('#btn_addPO').hide();
	$('#btn_save').show();
	$('#btn_edit').hide();
	$('#btn_cancel').hide();
	$('#btn_discard').show();
	$('#btn_search').hide();
	$('#btn_delete_kit_details').show();
	$('#btn_add_issuance_details').show();
	$('#btn_update_issuance_details').show();
	$('#btn_delete_issuance_details').show();
	$('.btn_edit_issuance').prop('disabled', false);

	// input control buttons
	$('#btn_min').attr('disabled', true);
	$('#btn_prv').attr('disabled', true);
	$('#btn_nxt').attr('disabled', true);
	$('#btn_max').attr('disabled', true);
	$('#btn_searchpo').prop('disabled',false);
	$('#btn_kitqty').prop('disabled',false);

	//inputs
	$('#issuanceno').prop('readonly',true);
	$('#searchpono').prop('readonly',true);
	$('#devicecode').prop('readonly',true);
	$('#devicename').prop('readonly',true);
	$('#poqty').prop('readonly',true);
	$('#kitqty').prop('readonly',true);
	$('#kitno').prop('readonly',false);
	$('#preparedby').prop('readonly',false);
}

function addState() {
	$('#save_type').val('KIT');
	//buttons
	$('#btn_addPO').prop('disabled', false);
	$('#btn_addPO').hide();
	$('#btn_save').show();
	$('#btn_edit').hide();
	$('#btn_cancel').hide();
	$('#btn_discard').show();
	$('#btn_search').hide();
	$('#btn_delete_kit_details').hide();
	$('#btn_add_issuance_details').hide();
	$('#btn_update_issuance_details').hide();
	$('#btn_delete_issuance_details').hide();
	$('.btn_edit_issuance').prop('disabled', false);

	// input control buttons
	$('#btn_min').attr('disabled', true);
	$('#btn_prv').attr('disabled', true);
	$('#btn_nxt').attr('disabled', true);
	$('#btn_max').attr('disabled', true);
	$('#btn_searchpo').prop('disabled',false);
	$('#btn_kitqty').prop('disabled',false);

	//inputs
	$('#kitinfo_id').val("");
	$('#issuanceno').prop('readonly',true);
	$('#searchpono').prop('readonly',false);
	$('#devicecode').prop('readonly',true);
	$('#devicename').prop('readonly',true);
	$('#poqty').prop('readonly',true);
	$('#kitqty').prop('readonly',true);
	$('#kitno').prop('readonly',false);
	$('#preparedby').prop('readonly',false);

	var cd = getDateTime();
	$('.add').val('');
	$('#tbl_kitdetails_body').html('');
	$('#tbl_issuance_body').html('');

	$('#createdby').val(currentUser);
	$('#updatedby').val(currentUser);
	$('#createddate').val(cd);
	$('#updateddate').val(cd);
}

function getDateTime() {
    var d = new Date();

    var month = d.getMonth()+1;
    var day = d.getDate();

    var date = d.getFullYear() + '/' +
        ((''+month).length<2 ? '0' : '') + month + '/' +
        ((''+day).length<2 ? '0' : '') + day;

    var hours = d.getHours();
	var minutes = d.getMinutes();
	var ampm = hours >= 12 ? 'PM' : 'AM';
	hours = hours % 12;
	hours = hours ? hours : 12; // the hour '0' should be '12'
	minutes = minutes < 10 ? '0'+minutes : minutes;
	var time = hours + ':' + minutes + ' ' + ampm;

	output = date+' '+time;

    return output;
}

function tempTblKitDetailsData(data) {
	$('#tbl_kitdetails_body').html('');
	var tbl_kitdetails_body = '';
	var cnt = 1;
	var code = '';
	var name = '';
	var poqty = '';

	$.each(data, function(i, x) {
		code = x.code;
		name = x.prodname;
		poqty = x.POqty;
		var kit_qty = '';
		var location = x.location;
		var whs100 = x.whs100;
		var whs102 = x.whs102;

		if (x.kit_qty !== undefined) {
			kit_qty = x.kit_qty;
		}
		if (x.location == null) {
			location = '';
		}
		if (x.whs100 == null) {
			whs100 = '0.0000';
		}
		if (x.whs102 == null) {
			whs102 = '0.0000';
		}

		tbl_kitdetails_body = '<tr>'+
							'<td width="3.6%">'+
							 	'<input type="checkbox" class="checkboxes" data-id="'+cnt+'"/>'+
								'<input type="hidden" name="kitting_details_id[]" value="0"/>'+
							'</td>'+
							'<td width="5.6%">'+cnt+
								'<input type="hidden" name="kit_detail_id[]" value="'+cnt+'">'+
							'</td>'+
							'<td width="7.6%">'+x.item+
								'<input type="hidden" name="kit_itemcode[]" value="'+x.item+'">'+
							'</td>'+
							'<td width="13.6%">'+x.item_desc+
								'<input type="hidden" name="kit_itemname[]" value="'+x.item_desc+'">'+
							'</td>'+
							'<td width="4.6%">'+x.usage+
								'<input type="hidden" name="kit_usage[]" value="'+x.usage+'">'+
							'</td>'+
							'<td width="5.6%">'+x.rqd_qty+
								'<input type="hidden" name="kit_rqdqty[]" value="'+x.rqd_qty+'">'+
							'</td>'+
							'<td width="4.6%" class="kit_qty">'+x.kit_qty+
								'<input type="hidden" name="kit_qty[]" value="'+x.kit_qty+'">'+
							'</td>'+
							'<td width="6.6%">'+x.issued_qty+
								'<input type="hidden" name="kit_issuedqty[]" value="'+x.issued_qty+'">'+
							'</td>'+
							'<td width="13.6%">'+location+
								'<input type="hidden" name="kit_loaction[]" value="'+location+'">'+
							'</td>'+
							'<td width="10.6%">'+x.drawing_no+
								'<input type="hidden" name="kit_drawno[]" value="'+x.drawing_no+'">'+
							'</td>'+
							'<td width="7.6%">'+x.supplier+
								'<input type="hidden" name="kit_supplier[]" value="'+x.supplier+'">'+
							'</td>'+
							'<td width="7.6%">'+whs100+
								'<input type="hidden" name="kit_whs100[]" value="'+whs100+'">'+
							'</td>'+
							'<td width="7.6%">'+whs102+
								'<input type="hidden" name="kit_whs102[]" value="'+whs102+'">'+
							'</td>'+
						'</tr>';
		$('#tbl_kitdetails_body').append(tbl_kitdetails_body);
		cnt++;
	});

	$('#devicecode').val(code);
	$('#devicename').val(name);
	$('#poqty').val(poqty);
}

function navigate(to) {
	getMaterialKittingData(to,$('#kitinfo_id').val());
}

function getMaterialKittingData(to,id) {
	var data = {
		_token: token, 
		id: id,
		to: to
	};
	$.ajax({
		url: materialKittingDataURL,
		type: 'GET',
		dataType: 'JSON',
		data: data,
	}).done(function(data,textStatus,jqXHR) {
		if (data.status == 'failed') {
			msg(data.msg,data.status);
		} else {
			kitInfo(data.kitinfo);
			kitDetails(data.kitdetails);
			kitIssuance(data.kitissuance);
		}
		viewState();
	}).fail(function(data,textStatus,jqXHR) {
		msg("There was an error occurred while processing.",'error');
	});
}

function getKitData(issuanceno) {
	var data = {
		_token: token, 
		issuanceno: issuanceno,
	};
	$.ajax({
		url: KitDetailsURL,
		type: 'GET',
		dataType: 'JSON',
		data: data,
	}).done(function(data,textStatus,jqXHR) {
		kitDetails(data);
	}).fail(function(data,textStatus,jqXHR) {
		msg("There was an error occurred while processing.",'error');
	});
}

function kitInfo(data) {
	var status = data.status;
	if (data.status == 'O') {
		status = 'Open';
	}

	if (data.status == 'C') {
		status = 'Cancelled';
	}

	if (data.status == 'X') {
		status = 'Closed';
	}

	$('#kitinfo_id').val(data.id);
	$('#issuanceno').val(data.issuance_no);
	$('#searchpono').val(data.po_no);
	$('#devicecode').val(data.device_code);
	$('#devicename').val(data.device_name);
	$('#poqty').val(data.po_qty);
	$('#kitqty').val(data.kit_qty);
	$('#kitno').val(data.kit_no);
	$('#preparedby').val(data.prepared_by);
	$('#status').val(status);
	$('#createdby').val(data.create_user);
	$('#createddate').val(data.created_at);
	$('#updatedby').val(data.update_user);
	$('#updateddate').val(data.updated_at);
	$('#btn_received_by').prop('disabled',(data.received_by != ''));
}

function kitDetails(data) {
	$('#tbl_kitdetails_body').html('');
	var tbl_kitdetails_body = '';
	
	$.each(data, function(i, x) {
		var item = x.item == null ? '' : x.item;
		var item_desc = x.item_desc == null ? '' : x.item_desc;
		var usage = x.usage == null ? '' : x.usage;
		var rqd_qty = x.rqd_qty == null ? '' : x.rqd_qty;
		var kit_qty = x.kit_qty == null ? '' : x.kit_qty;
		var issued_qty = x.issued_qty == null ? '' : x.issued_qty;
		var location = x.location == null ? '' : x.location;
		var drawing_no = x.drawing_no == null ? '' : x.drawing_no;
		var supplier = x.supplier == null ? '' : x.supplier;
		var whs100 = x.whs100 == null ? '' : x.whs100;
		var whs102 = x.whs102 == null ? '' : x.whs102;
		var po = $('#searchpono').val();
		var issue_no = $('#issuanceno').val();
		tbl_kitdetails_body = '<tr>'+
							'<td width="3.6%">'+
							'<input type="checkbox" class="kit_checkboxes" data-id="'+x.id+'" value="'+x.id+'" data-item="'+item+'" data-issue_no="'+issue_no+'" data-po="'+po+'"/>'+
							 	'<input type="hidden" name="kitting_details_id[]" value="'+x.id+'"/>'+
							'</td>'+
							'<td width="5.6%">'+x.detailid+
								'<input type="hidden" name="kit_detail_id[]" value="'+x.detailid+'">'+
							'</td>'+
							'<td width="7.6%">'+item+
								'<input type="hidden" name="kit_itemcode[]" value="'+item+'">'+
							'</td>'+
							'<td width="13.6%">'+item_desc+
								'<input type="hidden" name="kit_itemname[]" value="'+item_desc+'">'+
							'</td>'+
							'<td width="4.6%">'+usage+
								'<input type="hidden" name="kit_usage[]" value="'+usage+'">'+
							'</td>'+
							'<td width="5.6%">'+rqd_qty+
								'<input type="hidden" name="kit_rqdqty[]" value="'+rqd_qty+'">'+
							'</td>'+
							'<td width="4.6%" class="kit_qty">'+kit_qty+
								'<input type="hidden" name="kit_qty[]" value="'+kit_qty+'">'+
							'</td>'+
							'<td width="6.6%">'+issued_qty+
								'<input type="hidden" name="kit_issuedqty[]" value="'+issued_qty+'">'+
							'</td>'+
							'<td width="13.6%">'+location+
								'<input type="hidden" name="kit_loaction[]" value="'+location+'">'+
							'</td>'+
							'<td width="10.6%">'+drawing_no+
								'<input type="hidden" name="kit_drawno[]" value="'+drawing_no+'">'+
							'</td>'+
							'<td width="7.6%">'+supplier+
								'<input type="hidden" name="kit_supplier[]" value="'+supplier+'">'+
							'</td>'+
							'<td width="7.6%">'+whs100+
								'<input type="hidden" name="kit_whs100[]" value="'+whs100+'">'+
							'</td>'+
							'<td width="7.6%">'+whs102+
								'<input type="hidden" name="kit_whs102[]" value="'+whs102+'">'+
							'</td>'+
						'</tr>';
		$('#tbl_kitdetails_body').append(tbl_kitdetails_body);
	});

	// $('#tbl_kitdetails').DataTable().clear();
	// $('#tbl_kitdetails').DataTable().destroy();
	// $('#tbl_kitdetails').DataTable({
	// 	data: data,
	// 	pageLength: 100,
	// 	pagingType: "bootstrap_full_number",
	// 	order: [[1,'asc']],
	// 	searching: false,
	// 	lengthChange: false,
	// 	// paging: false,
	// 	// scrollY: '400px',
	// 	// scrollX: true,
    //     // scrollCollapse: true,
	// 	// fixedColumns: true,
	// 	columnDefs: [{
	// 		orderable: false,
	// 		targets: [0,1,2,3,4,5,6,7,8,9,10,11,12]
	// 	}, {
	// 		searchable: false,
	// 		targets: [0,1,2,3,4,5,6,7,8,9,10,11,12]
	// 	}],
	// 	language: {
	// 		aria: {
	// 			sortAscending: ": activate to sort column ascending",
	// 			sortDescending: ": activate to sort column descending"
	// 		},
	// 		emptyTable: "No data available in table",
	// 		info: "Showing _START_ to _END_ of _TOTAL_ records",
	// 		infoEmpty: "No records found",
	// 		infoFiltered: "(filtered1 from _MAX_ total records)",
	// 		lengthMenu: "Show _MENU_",
	// 		search: "Search:",
	// 		zeroRecords: "No matching records found",
	// 		paginate: {
	// 			"previous": "Prev",
	// 			"next": "Next",
	// 			"last": "Last",
	// 			"first": "First"
	// 		}
	// 	},
	// 	columns: [
	// 		{
	// 			data: function (x) {
	// 				return '<input type="checkbox" class="kit_checkboxes" data-id="'+x.id+'" value="'+x.id+'"/>'+
	// 						 	'<input type="hidden" name="kitting_details_id[]" value="'+x.id+'"/>';
	// 			}, orderable: false, searchable: false
	// 		},
	// 		{ data: function (x) {
	// 			return x.detailid+ '<input type="hidden" name="kit_detail_id[]" value="'+x.detailid+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.item+ '<input type="hidden" name="kit_itemcode[]" value="'+x.item+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.item_desc+ '<input type="hidden" name="kit_itemname[]" value="'+x.item_desc+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.usage+ '<input type="hidden" name="kit_usage[]" value="'+x.usage+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.rqd_qty+ '<input type="hidden" name="kit_rqdqty[]" value="'+x.rqd_qty+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.kit_qty+ '<input type="hidden" name="kit_qty[]" value="'+x.kit_qty+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.issued_qty+ '<input type="hidden" name="kit_issuedqty[]" value="'+x.issued_qty+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.location+ '<input type="hidden" name="kit_loaction[]" value="'+x.location+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.drawing_no+ '<input type="hidden" name="kit_drawno[]" value="'+x.drawing_no+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.supplier+ '<input type="hidden" name="kit_supplier[]" value="'+x.supplier+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.whs100+ '<input type="hidden" name="kit_whs100[]" value="'+x.whs100+'">';
	// 		} },
	// 		{ data: function (x) {
	// 			return x.whs102+ '<input type="hidden" name="kit_whs102[]" value="'+x.whs102+'">';
	// 		} }
			
	// 	],
	// 	createdRow: function (row, data, dataIndex) {
	// 		var dataRow = $(row);
	// 		var kit_qty = $(dataRow[0].cells[6]);
	// 		kit_qty.addClass('kit_qty');
	// 	},
	// });
}

function kitIssuance(data) {
	issuance_details_arr.length = 0;
	var detailid = 1;
	$.each(data, function(i, x) {
		var item = x.item == null ? '' : x.item;
		var item_desc = x.item_desc == null ? '' : x.item_desc;
		var kit_qty = x.kit_qty == null ? '' : x.kit_qty;
		var issued_qty = x.issued_qty == null ? '' : x.issued_qty;
		var lot_no = x.lot_no == null ? '' : x.lot_no;
		var location = x.location == null ? '' : x.location;
		var remarks = x.remarks == null ? '' : x.remarks;
		var id = x.id;
		issuance_details_arr.push({
			iss_db_id : id,//if zero, means is new row from Add Details
			item : item,
			item_desc : item_desc,
			kit_qty : kit_qty,
			issued_qty : issued_qty,
			lot_no : lot_no,
			location : location,
			remarks : remarks,
			fifoid : 0,//inventory id : will update inventory id from zero to inv id after Add data from Add Details
			issdetailid : detailid
		});
		detailid++;
	});
	kitIssuanceTable(issuance_details_arr);
}
/*
	Make Datatable from Issuance Details Table
*/
function kitIssuanceTable(arr){
	$('#tbl_issuance_body').html('');
	var row = '';
	for(let i = 0;i < arr.length;i++) {
		var d = arr[i];
		row += '<tr id="3">'+
		'    <td width="3.09%"><input type="checkbox" id="chkIssDetail'+d.iss_db_id+'" data-inpt="issDetail'+d.iss_db_id+'" data-tr="'+d.iss_db_id+'" class="iss_checkboxes" value="'+d.iss_db_id+'"/></td>'+
		'    <td width="6.09%">'+
		'        <a href="javascript:;" class="btn btn-success btn-sm btn_edit_issuance" data-ids="'+d.iss_db_id+'" '+(d.iss_db_id == 0 ? 'disabled' : '')+'><i class="fa fa-edit"></i></a>'+
		'    </td>'+
		'    <td width="6.09%">'+d.issdetailid+'</td>'+
		'    <td width="12.09%">'+d.item+'</td>'+
		'    <td width="18.09%">'+d.item_desc+'</td>'+
		'    <td width="6.09%">'+d.kit_qty+'</td>'+
		'    <td width="6.09%" id="issued_qty_'+d.iss_db_id+'">'+d.issued_qty+'</td>'+
		'    <td width="12.09%" id="lot_no_'+d.iss_db_id+'">'+d.lot_no+'</td>'+
		'    <td width="9.09%" id="location_'+d.iss_db_id+'">'+d.location+'</td>'+
		'    <td width="15.09%" id="remarks_'+d.iss_db_id+'">'+d.remarks+'</td>'+
		'    <td width="6.09%">'+
		'        <a href="javascript:;" class="btn input-sm grey-gallery barcodebtn" data-ids="'+d.iss_db_id+'" '+(d.iss_db_id == 0 ? 'disabled' : '')+'>'+
		'            <i class="fa fa-barcode"></i>'+
		'        </a>'+
		'    </td>'+
		'</tr>';
	}
	$('#tbl_issuance_body').append(row);
}

function delete_items(checkboxClass,url) {
	var chkArray = [];
	$(checkboxClass+":checked").each(function() {
		if (checkboxClass == '.iss_checkboxes') {
			chkArray.push({
				id: $(this).val(),
			});
		} else if(checkboxClass == '.kit_checkboxes') {
			chkArray.push({
				id: $(this).val(),
				issue_no: $(this).attr('data-issue_no'),
				po: $(this).attr('data-po'),
				item: $(this).attr('data-item')
			});
		}
	});
	if (chkArray.length > 0) {
		var data = {
			_token: token,
			ids: chkArray,
		}
		delete_now(url,data);
	} else {
		$('#delete_modal').modal('hide');
		msg("Please select at least 1 item.", 'failed');
	}
}

function delete_now(url,data) {
	$.ajax({
		url: url,
		type: 'POST',
		dataType: 'JSON',
		data: data,
	}).done(function(data,textStatus,jqXHR) {
		$('#delete_modal').modal('hide');
		msg(data.msg,data.status);
		getMaterialKittingData('',$('#kitinfo_id').val());
	}).fail(function(data,textStatus,jqXHR) {
		msg("There's an error occurred while processing.",'error');
	});
}

function getItemAndLotnumFifo() {
	$('#loading').modal('show');
	console.log($('#iss_lotno').val().replace(/^\s+|\s+$/gm,''))
	var data = {
			_token : token,
			item: $('#iss_item').val(),
			lotno: $('#iss_lotno').val(),
			location: $('#iss_location').val(),
			issuanceno: $('#issuanceno').val()
		};
	$.ajax({
		url: getItemAndLotnumFifoURL,
		type: 'POST',
		dataType: 'JSON',
		data: data,
	}).done(function(data,textStatus,jqXHR) {
		
		if(data.fifo_data.length < 1){
			$('#iss_lotno').val("");
			msg(data.msg, data.status);
		}else{
			if(data.status == "failed"){
				$('#iss_lotno').val("");
				msg(data.msg, data.status);
			}else{
				getFifoTable(data.fifo_data);
			}
		}
	}).fail(function(data,textStatus,jqXHR) {
		msg("There was an error occurred while searching.",'error');
	}).always(function() {
		$('#loading').modal('hide');
	});
}

function getFifoTable(dataArr) {
	var tbl_fifo_body = '';
	$('#tbl_fifo_body').html('');
	var cnt = 1;
	var rows = [];

	$('#tbl_fifo').DataTable().clear();
	$('#tbl_fifo').DataTable().destroy();
	fifoTable = $('#tbl_fifo').DataTable({
		data: dataArr,
		pageLength: 10,
		pagingType: "bootstrap_full_number",
		//order: [[1, 'asc'],[7, 'asc'], [5, 'asc'] ],
		searching: false,
		lengthChange: false,
		paging: false,
		scrollY: '400px',
		scrollX: true,
        scrollCollapse: true,
		fixedColumns: true,
		select: {
			selector: 'td:not(.disable_me)',
			style: 'os'
		},
		columnDefs: [{
			orderable: false,
			targets: [0,1,2,3,4,5,6,7,8,9,10,11]
		}, {
			searchable: false,
			targets: [0,1,2,3,4,5,6,7,8,9,10,11]
		}],
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
					return '<button class="btn green btn-sm showfifoitem" data-rowcount="'+cnt+'" data-id="'+data.id+'" data-item="'+data.item+'" '+
								'data-item_desc="'+data.item_desc+'" data-qty="'+data.qty+'" data-lot_no="'+data.lot_no+'" '+
								'data-location="'+data.location+'" data-receive_date="'+data.receive_date+'" data-kit_qty="'+data.kit_qty+'" style="display:none">'+
								'<i class="fa fa-edit"></i>'+
							'</button>';

					
				}, orderable: false, searchable: false, width: '56.78125px'
			},
			{ data: 'judgement' },
			{ data: 'item' },
			{ data: 'item_desc' },
			{ data: 'qty' },
			{ data: 'lot_no' },
			{ data: 'location' },
			{ data: 'receive_date' },
			{ data: 'ngr_status' },
			{ data: 'ngr_disposition' },
			{ data: 'invoice_no' },
			{ data: 'receive_module' }
			
		],
		createdRow: function (row, data, dataIndex) {
			var dataRow = $(row);
			var iqc_judgment = $(dataRow[0].cells[1]);
			var btn = $(dataRow[0].cells[0])[0].firstChild;

			if (data.kit_disabled == 1) {
				dataRow.css('background-color','#FBF46D');
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

			rows.push(dataRow[0]);

			for (let index = 0; index < 12; index++) {
				$(dataRow[0].cells[index]).addClass('disable_me');
			}

			if (data.kit_disabled == 1) {
				// for (let index = 0; index < 12; index++) {
				// 	$(dataRow[0].cells[index]).addClass('disable_me');
				// }

				$(dataRow[0].cells[0]).css('width', '56.78125px');
				$(dataRow[0].cells[0]).html('<button class="btn green btn-sm btn_enable_fifo_item">' + //data-rowcount="' + cnt + '"
					'<i class="fa fa-unlock"></i>' +
					'</button>');
			} 
			// else {
			// 	$(dataRow[0].cells[0]).addClass('disable_me');
			// }
			cnt++;
		},
		"fnInitComplete": function () {
			var click_count = 0;
			var api = this.api();
			var dtData = api.rows().data();

			console.log(api.row(3).data());

			$.each(dtData, function(i,x) {
				if ((x.kit_disabled == 0 || x.kit_disabled == null) && (x.iqc_status !== "2" && x.judgement !== "Rejected")) {
					api.row(i).select();
					populateIssuanceDetails(x);
					return false;
				}
			});
			
			
			$('#btn_nxt_item').on('click', function() {
				var records = fifoTable.rows().data();
				var total_records = records.length;

				$('#reason_id').val('');
				$('#user_id').val('');
				$('#password').val('');
				$('#fiforeason').val('');

				if (total_records > 0) {
					// get data
					var tbl_data = fifoTable.row({ selected: true }).data();
					var indx = fifoTable.row({ selected: true }).index();
					var next_row_indx = indx + 1;

					if (tbl_data !== undefined && tbl_data == null) {
						msg("No selected Item.", "failed");
						return false;
					}

					if (tbl_data.kit_disabled == 1) {
						click_count++;
						//return false;
					}

					if (!$.isEmptyObject(tbl_data)) {

						var nxt_data = fifoTable.row(next_row_indx).data();

						if (nxt_data != undefined) {
							$('#fr_prev_id').val(tbl_data.id);
							if ((tbl_data.iqc_status !== "2" && tbl_data.judgement !== "Rejected") && (nxt_data.kit_disabled == 0 || nxt_data.kit_disabled == null)) {

								if (!$.isEmptyObject(tbl_data)) {

									$('#fr_id').val(tbl_data.id);
									$('#fr_item').val(tbl_data.item);
									$('#fr_item_desc').val(tbl_data.item_desc);
									$('#fr_qty').val(tbl_data.qty);
									$('#fr_lot_no').val(tbl_data.lot_no);
									$('#fr_location').val(tbl_data.location);
									$('#fr_kit_qty').val(tbl_data.kit_qty);

									$('#next_row_indx').val(next_row_indx);

									$('#fifoReasonModal').modal('show');
								} else {
									msg("No Items available to select.", "failed");
								}
							} else {
								msg("Next Item was Rejected or disabled.", "failed");
							}
						}
					}
				} else {
					msg("No data was found in the table.", "failed");
				}
			});

			$('#tbl_fifo tbody').on('click','.btn_enable_fifo_item', function() {
				var data = fifoTable.row( $(this).parents('tr') ).data();
				var prev_id = fifoTable.row({ selected: true }).index();
				var indx = fifoTable.row($(this).parents('tr')).index();
				console.log(data);
				$('#enable_id').val(data.id);
				$('#enable_row_id').val(indx);
				$('#enable_prev_row_id').val(prev_id);
				$('#enable_item').val(data.item);
				$('#enable_itemdesc').val(data.item_desc);
				$('#enable_qty').val(data.qty);
				$('#enable_lotno').val(data.lot_no);
				$('#enable_location').val(data.location);
				$('#enable_kitqty').val(data.kit_qty);

				$('#enable_user_id').val('');
				$('#enable_password').val('');
				$('#enable_reason').val('');

				$('#enableModal').modal('show');
			});

			// $('#tbl_fifo tbody').on('click', 'tr', function () {
			// 	if (fifoTable.rows('.selected').any()) {
			// 		fifoTable.row($(this)).deselect();
			// 	} else {
			// 		fifoTable.row($(this)).select();
			// 	}
			// });
		},
	});
	
	if($('#iss_lotno').val() == ""){
		$("#iss_lotno").focus();
	}else{
		$("#iss_qty").focus();
	}
}

function disableFifoItem(tbl_data) {
	$.ajax({
		url: postUpdateKitDisabled,
		type: 'POST',
		dataType: 'JSON',
		data: {
			_token: token,
			id: tbl_data.id,
			issuanceno: $('#issuanceno').val(),
			item: tbl_data.item
		}
	}).done( function(data, textStatus, jqXHR) {
		populateIssuanceDetails(tbl_data);
		//getFifoTable(data);
	}).fail( function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR);
	});
}

function populateIssuanceDetails(data) {
	var lot_no ="";
	$('#fifoid').val(data.id);
	$('#iss_item').val(data.item);
	$('#iss_item_desc').val(data.item_desc);

	if($('#iss_lotno').val() == ""){
		lot_no = "";
	} else{
		lot_no = $('#iss_lotno').val();
	}

	// if((data.lot_no.substring(data.lot_no.length-4)[0]) == " " && (data.lot_no.substring(data.lot_no.length-4)[2]) == "/"){
	// 	lot_no = data.lot_no.substring(0, data.lot_no.length-4);
	// }else{
	// 	lot_no = data.lot_no;
	// }
	
	$('#iss_lotno').val(lot_no);
	$('#iss_kitqty').val(data.kit_qty);
	$('#iss_qty').val(data.qty);
	$('#iss_location').val(data.location);

	//$('#iss_lotno').prop('readonly', true);
	$('#iss_remarks').prop('readonly', false);

	$('#iss_selected_qty').val(data.qty);
}

function filterSearch() {
	var chkArray = [];

	$(".srch_status:checked").each(function() {
		chkArray.push($(this).val());
	});

	var data = {
		_token: token,
		pono: $('input[name=srch_pono]').val(),
		kitno: $('input[name=srch_kitno]').val(),
		preparedby: $('input[name=srch_preparedby]').val(),
		slipno: $('input[name=srch_slipno]').val(),
		status: chkArray
	};

	$.ajax({
		url: searchFilterURL,
		type: 'GET',
		dataType: 'JSON',
		data: data,
	}).done(function(data,textStatus,jqXHR) {
		var tbl_search_body = '';
		$('#tbl_search_body').html(tbl_search_body);
		$.each(data, function(i, x) {
			var status = x.status;
			if (x.status == 'O') {
				status = 'Open';
			}

			if (x.status == 'C') {
				status = 'Cancelled';
			}

			if (x.status == 'X') {
				status = 'Closed';
			}
			 tbl_search_body = '<tr>'+
									'<td width="8.3%">'+
										'<button type="button" class="btn green btn_get_search btn-sm" data-kitinfo_id="'+x.id+'">'+
											'<i class="fa fa-edit"></i>'+
										'</button>'+
									'</td>'+
									'<td width="8.3%">'+x.issuance_no+'</td>'+
									'<td width="8.3%">'+x.po_no+'</td>'+
									'<td width="8.3%">'+x.device_code+'</td>'+
									'<td width="8.3%">'+x.device_name+'</td>'+
									'<td width="8.3%">'+x.kit_no+'</td>'+
									'<td width="8.3%">'+x.prepared_by+'</td>'+
									'<td width="8.3%">'+status+'</td>'+
									'<td width="8.3%">'+x.create_user+'</td>'+
									'<td width="8.3%">'+x.created_at+'</td>'+
									'<td width="8.3%">'+x.update_user+'</td>'+
									'<td width="8.3%">'+x.updated_at+'</td>'+
								'</tr>';
			 $('#tbl_search_body').append(tbl_search_body);
		});
	}).fail(function(data,textStatus,jqXHR) {
		msg("There was an error while searching.",'error');
	});
}

function searchReset() {
	$('.search_reset').val('');
	$('#tbl_search_body').html('');
}

function saveFifoReason(params) {
	$('#loading').modal('show');
	$.ajax({
		url: fifoReasonURL,
		type: 'POST',
		dataType: 'JSON',
		data: params
	}).done( function(data, textStatus, jqXHR) {
		if (data.status == 'success') {
			var next_row_indx = $('#next_row_indx').val();
			var prev_row_indx = next_row_indx - 1;

			fifoTable.row(prev_row_indx).deselect();

			// disable prev row
			for (let index = 0; index < 12; index++) {
				var column = fifoTable.row(prev_row_indx).context[0].aoData[prev_row_indx].anCells[index];
				$(column).addClass('disable_me');
			}

			// add button to previous row
			var col = fifoTable.row(prev_row_indx).context[0].aoData[prev_row_indx].anCells[0];

			$(col).css('width', '56.78125px');
			$(col).html('<button class="btn green btn-sm btn_enable_fifo_item">' + //data-rowcount="' + cnt + '"
				'<i class="fa fa-unlock"></i>' +
				'</button>');

			var nxt_data = fifoTable.row(next_row_indx).data();

			if ((nxt_data.kit_disabled == 0 || nxt_data.kit_disabled == null) && (nxt_data.iqc_status !== "2" && nxt_data.judgement !== "Rejected")) {
				$('#fifoid').val(nxt_data.id);
				$('#iss_item').val(nxt_data.item);
				$('#iss_item_desc').val(nxt_data.item_desc);
				$('#iss_lotno').val(nxt_data.lot_no);
				$('#iss_kitqty').val(nxt_data.kit_qty);
				$('#iss_qty').val(nxt_data.qty);
				$('#iss_location').val(nxt_data.location);
				$('#iss_remarks').val(params.reason);

				$('#iss_lotno').prop('readonly', false);
				$('#iss_qty').prop('readonly', false);
				$('#iss_location').prop('readonly', false);
				$('#iss_remarks').prop('readonly', false);

				fifoTable.row(next_row_indx).select();
			} else {
				$('#fifoid').val('');
				$('#iss_item').val('');
				$('#iss_item_desc').val('');
				$('#iss_lotno').val('');
				$('#iss_kitqty').val('');
				$('#iss_qty').val('');
				$('#iss_location').val('');
				$('#iss_remarks').val('');

				msg("Next Item is either Rejected or Disabled.","failed");
			}

			$('#fifoReasonModal').modal('hide');
		} else {
			msg(data.msg,data.status);
		}

	}).fail( function(data, textStatus, jqXHR) {
		msg("There was an error while processing.",'error');
	}).always( function() {
		$('#loading').modal('hide');
	});
}

function kittingList() {
	if ($('#kittinglist_po').val() == '' || $('#kittinglist_po').val() == '') {
		msg("All fields are required.","failed");
	} else {
		// window.location.href = kittingListURL +  '?po=' + $("#kittinglist_po").val() + '&&kitqty='+$("#kittinglist_kitqty").val();
		window.open(kittingListURL +  '?po=' + $("#kittinglist_po").val() + '&&kitqty='+$("#kittinglist_kitqty").val()+ '&&porder=' +$("#kittinglist_porder").val(),'_blank');
	}
}

function transferSlip() {
	// window.location.href = transferSlipURL + '?id=' + $("#kitinfo_id").val();
	window.open(transferSlipURL + '?id=' + $("#kitinfo_id").val(),'_blank');
}

function printBRcode(id,item,item_desc,kit_qty,issued_qty,lot_no,location,po,issuanceno) {
	window.location.href= printBarCodeURL +"?id=" +id;
}

function isOnMobile() {
	var isMobile = false; //initiate as false
	// device detection
	if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;

	return isMobile;
}

function cleartable(){
	 tbl_check_details_modal.clear();
	 tbl_check_details_modal.draw();
	 // tbl_check_details_modal.dataTable().fnDestroy();
}

//2024-01-27 armando
function clear_addIssuanceTable(){
	$('#tbl_fifo_body').html('');
	$('#tbl_fifo').DataTable().clear();
	$('#tbl_fifo').DataTable().destroy();
}

function checkDetails(){
	var data = {
	 issue_no : $('#issuanceno').val(),
	 po : $('#searchpono').val(),
	}
	$.ajax({
		url: CheckDetailsURL,
		type: 'GET',
		dataType: 'JSON',
		data:
		 {
		 	_token: token,
		 	data:data
		 },
	})
	.done(function(data, textStatus, jqXHR) {
		 $('#loading').modal('hide');
		 DetailModalTable(data);
	})
	.fail(function(data, textStatus, jqXHR) {
		console.log("error");
	});	
}

function DetailModalTable(arr){
	tbl_check_details_modal = $('#tbl_check_details_modal').DataTable({
		destroy: true,
	   data:arr,
		 columns:[  
   
			 {
				 data:function(x){
					 return '<input type="checkbox" class="check_lot" value="' + x.id + '"' + 'data-detailid="'+x.detailid+'"' + 'data-issue_no="' + x.issue_no + '"' + 'data-item="' + x.item + '"' + 'data-item_desc="' + x.item_desc + '"' + 'data-lot_no="'+ x.lot_no +'"' + 'data-location="' + x.location + '"' + '>';
				 }
   
			 },
   
			{ 
				title: "ID",
				data:"id",
				"visible":false 
			},
			 { title: "Detail ID", data:"detailid"},
   
		   { title: "Issue No", data:"issue_no"},
   
			{ title: "Item", data:"item"},
   
			{ title: "Item Description", data:"item_desc"},
   
			{ title: "Location",data:"location"},
   
			{ title: "Lot No",data:"lot_no"},
	  ],
   
	});
   }

function Delete(arr_id){	
	$.ajax({
		url: DeleteWrongitemURL,
		type: 'POST',
		dataType: 'JSON',
		data: 
		{
			_token: token,
			ids:arr_id,
		},
	})
	.done(function(data, textStatus, jqXHR) {
		checkDetails();
	})
	.fail(function(data, textStatus, jqXHR) {
		console.log("error");
	})
}