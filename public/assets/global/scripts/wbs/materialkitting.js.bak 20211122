id = "";
issuance_no = "";
tbl_check_details_modal = "";
$( function() {
    checkDetails();
    // DetailModalTable();
	getMaterialKittingData();
	checkAllCheckboxesInTable('.tbl_kitdetails_group_checkable','.kit_checkboxes');
	checkAllCheckboxesInTable('.tbl_issuance_group_checkable','.iss_checkboxes');
	checkAllCheckboxesInTable('.check_all_items','.check_lot');

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

	$('#btn_save').on('click', function(e) {
		if ($('input[name=kitno]').val() == '' || $('input[name=preparedby]').val() == '') {
			msg("All fields are required.",'failed');
		} else {
			if ($('#save_type').val() == 'KIT') {
				var data = {
					_token: token,
					id: $('#kitinfo_id').val(),
					issuanceno: $('input[name=issuanceno]').val(),
					po: $('input[name=po]').val(),
					devicecode: $('input[name=devicecode]').val(),
					devicename: $('input[name=devicename]').val(),
					poqty: $('input[name=poqty]').val(),
					kitqty: $('input[name=kitqty]').val(),
					kitno: $('input[name=kitno]').val(),
					preparedby: $('input[name=preparedby]').val(),
					status: $('input[name=status]').val(),
					kit_detail_id: $('input[name="kit_detail_id[]"]').map(function(){return $(this).val();}).get(),
					kit_itemcode: $('input[name="kit_itemcode[]"]').map(function(){return $(this).val();}).get(),
					kit_itemname: $('input[name="kit_itemname[]"]').map(function(){return $(this).val();}).get(),
					kit_usage: $('input[name="kit_usage[]"]').map(function(){return $(this).val();}).get(),
					kit_rqdqty: $('input[name="kit_rqdqty[]"]').map(function(){return $(this).val();}).get(),
					kit_qty: $('input[name="kit_qty[]"]').map(function(){return $(this).val();}).get(),
					kit_issuedqty: $('input[name="kit_issuedqty[]"]').map(function(){return $(this).val();}).get(),
					kit_loaction: $('input[name="kit_loaction[]"]').map(function(){return $(this).val();}).get(),
					kit_drawno: $('input[name="kit_drawno[]"]').map(function(){return $(this).val();}).get(),
					kit_supplier: $('input[name="kit_supplier[]"]').map(function(){return $(this).val();}).get(),
					kit_whs100: $('input[name="kit_whs100[]"]').map(function(){return $(this).val();}).get(),
					kit_whs102: $('input[name="kit_whs102[]"]').map(function(){return $(this).val();}).get(),
					save_type: $('#save_type').val(),
				};
			} else {
				var data = {
					_token: token,
					id: $('#kitinfo_id').val(),
					issuanceno: $('input[name=issuanceno]').val(),
					po: $('input[name=po]').val(),
					devicecode: $('input[name=devicecode]').val(),
					devicename: $('input[name=devicename]').val(),
					poqty: $('input[name=poqty]').val(),
					kitqty: $('input[name=kitqty]').val(),
					kitno: $('input[name=kitno]').val(),
					preparedby: $('input[name=preparedby]').val(),
					status: $('input[name=status]').val(),
					kitting_details_id: $('input[name="kitting_details_id[]"]').map(function(){return $(this).val();}).get(),
					kit_itemcode: $('input[name="kit_itemcode[]"]').map(function(){return $(this).val();}).get(),
					kit_itemname: $('input[name="kit_itemname[]"]').map(function(){return $(this).val();}).get(),
					kit_usage: $('input[name="kit_usage[]"]').map(function(){return $(this).val();}).get(),
					kit_rqdqty: $('input[name="kit_rqdqty[]"]').map(function(){return $(this).val();}).get(),
					kit_qty: $('input[name="kit_qty[]"]').map(function(){return $(this).val();}).get(),
					kit_issuedqty: $('input[name="kit_issuedqty[]"]').map(function(){return $(this).val();}).get(),
					kit_loaction: $('input[name="kit_loaction[]"]').map(function(){return $(this).val();}).get(),
					kit_drawno: $('input[name="kit_drawno[]"]').map(function(){return $(this).val();}).get(),
					kit_supplier: $('input[name="kit_supplier[]"]').map(function(){return $(this).val();}).get(),
					kit_whs100: $('input[name="kit_whs100[]"]').map(function(){return $(this).val();}).get(),
					kit_whs102: $('input[name="kit_whs102[]"]').map(function(){return $(this).val();}).get(),
					issdetailid: $('input[name="issdetailid[]"]').map(function(){return $(this).val();}).get(),
					issitem: $('input[name="issitem[]"]').map(function(){return $(this).val();}).get(),
					issitemdesc: $('input[name="issitemdesc[]"]').map(function(){return $(this).val();}).get(),
					isskit_qty: $('input[name="isskit_qty[]"]').map(function(){return $(this).val();}).get(),
					ississued_qty: $('input[name="ississued_qty[]"]').map(function(){return $(this).val();}).get(),
					isslot_no: $('input[name="isslot_no[]"]').map(function(){return $(this).val();}).get(),
					isslocation: $('input[name="isslocation[]"]').map(function(){return $(this).val();}).get(),
					issremarks: $('input[name="issremarks[]"]').map(function(){return $(this).val();}).get(),
					fifoid: $('input[name="issfifoid[]"]').map(function(){return $(this).val();}).get(),
					save_type: $('#save_type').val()
				};
			}

			$('#loading').modal('show');
			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				dataType: 'JSON',
				data: data,
			}).done(function(data,textStatus,jqXHR) {
				getMaterialKittingData('',$('#kitinfo_id').val());
				$('#loading').modal('hide');
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
			var data = {
				_token: token,
				id: $('#kitinfo_id').val(),
				issuanceno: $('input[name=issuanceno]').val(),
				po: $('input[name=po]').val(),
				devicecode: $('input[name=devicecode]').val(),
				devicename: $('input[name=devicename]').val(),
				poqty: $('input[name=poqty]').val(),
				kitqty: $('input[name=kitqty]').val(),
				kitno: $('input[name=kitno]').val(),
				preparedby: $('input[name=preparedby]').val(),
				status: $('input[name=status]').val(),
				kitting_details_id: $('input[name="kitting_details_id[]"]').map(function(){return $(this).val();}).get(),
				kit_itemcode: $('input[name="kit_itemcode[]"]').map(function(){return $(this).val();}).get(),
				kit_itemname: $('input[name="kit_itemname[]"]').map(function(){return $(this).val();}).get(),
				kit_usage: $('input[name="kit_usage[]"]').map(function(){return $(this).val();}).get(),
				kit_rqdqty: $('input[name="kit_rqdqty[]"]').map(function(){return $(this).val();}).get(),
				kit_qty: $('input[name="kit_qty[]"]').map(function(){return $(this).val();}).get(),
				kit_issuedqty: $('input[name="kit_issuedqty[]"]').map(function(){return $(this).val();}).get(),
				kit_loaction: $('input[name="kit_loaction[]"]').map(function(){return $(this).val();}).get(),
				kit_drawno: $('input[name="kit_drawno[]"]').map(function(){return $(this).val();}).get(),
				kit_supplier: $('input[name="kit_supplier[]"]').map(function(){return $(this).val();}).get(),
				kit_whs100: $('input[name="kit_whs100[]"]').map(function(){return $(this).val();}).get(),
				kit_whs102: $('input[name="kit_whs102[]"]').map(function(){return $(this).val();}).get(),
				issdetailid: $('input[name="issdetailid[]"]').map(function(){return $(this).val();}).get(),
				issitem: $('input[name="issitem[]"]').map(function(){return $(this).val();}).get(),
				issitemdesc: $('input[name="issitemdesc[]"]').map(function(){return $(this).val();}).get(),
				isskit_qty: $('input[name="isskit_qty[]"]').map(function(){return $(this).val();}).get(),
				ississued_qty: $('input[name="ississued_qty[]"]').map(function(){return $(this).val();}).get(),
				isslot_no: $('input[name="isslot_no[]"]').map(function(){return $(this).val();}).get(),
				isslocation: $('input[name="isslocation[]"]').map(function(){return $(this).val();}).get(),
				issremarks: $('input[name="issremarks[]"]').map(function(){return $(this).val();}).get(),
				fifoid: $('input[name="issfifoid[]"]').map(function(){return $(this).val();}).get(),
				iss_db_id: $('input[name="iss_db_id[]"]').map(function(){return $(this).val();}).get(),
				save_type: $('#save_type').val()
			};

			$('#loading').modal('show');
			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				dataType: 'JSON',
				data: data,
			}).done(function(data,textStatus,jqXHR) {
				getKitData($('#issuanceno').val());
				$('#loading').modal('hide');
				msg(data.msg,data.status);
				getMaterialKittingData('',$('#issuanceno').val());
			}).fail(function(data,textStatus,jqXHR) {
				$('#loading').modal('hide');
				msg("There was an error occurred while processing.",'error');
			});
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
		$('#iss_lotno').prop('readonly', true);
		$('#iss_qty').prop('readonly', false);
		$('#iss_location').prop('readonly', true);
		$('#iss_remarks').prop('readonly', true);
		$('#tbl_fifo_body').html('');
		$('#iss_save_status').val('ADD');
		$('#addIssuanceDetailsModal').modal('show');
		$('#iss_item').focus();
	});

	$('#iss_item').on('change', function(e) {
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
			$('#frid').val($(this).attr('data-id'));
			$('#fritem').val($(this).attr('data-item'));
			$('#fritemdesc').val($(this).attr('data-item_desc'));
			$('#frqty').val($(this).attr('data-qty'));
			$('#frlotno').val($(this).attr('data-lot_no'));
			$('#frlocation').val($(this).attr('data-location'));
			$('#frkitqty').val($(this).attr('data-kit_qty'));
			$('#fifoReasonModal').modal('show');
		} else {
			$('#fifoid').val($(this).attr('data-id'));
			$('#iss_item').val($(this).attr('data-item'));
			$('#iss_item_desc').val($(this).attr('data-item_desc'));
			$('#iss_lotno').val($(this).attr('data-lot_no'));
			$('#iss_kitqty').val($(this).attr('data-kit_qty'));
			$('#iss_qty').val($(this).attr('data-qty'));
			$('#iss_location').val($(this).attr('data-location'));

			$('#iss_lotno').prop('readonly', true);

			$('#iss_selected_qty').val($(this).attr('data-qty'));
		}
	});

	$('#btn_add_issuance').on('click', function() {
		var rowcount = $('#tbl_issuance_body tr:last').attr('id');
		if (rowcount == undefined) {
			rowcount = 0;
		}
		rowcount++;
		var iss_qty = parseFloat($('#iss_qty').val());
		var kit_qty = parseFloat($('#iss_kitqty').val());
		var selected_qty = parseFloat($('#iss_selected_qty').val());

		if (selected_qty < iss_qty) {
			msg("Issued quantity is larger than selected quantity based on lot number.",'failed');
		} else {
			if (iss_qty > kit_qty) {
				msg("Issued quantity is larger than kit quantity.",'failed');
			} else {
				var data = {
						_token: token,
						issuanceno: $('#issuanceno').val(),
						item: $('#iss_item').val(),
						qty: $('#iss_qty').val(),
						iss_save_status: $('#iss_save_status').val()
					};

				$.ajax({
					url: checkIssuedQtyURL,
					type: 'GET',
					dataType: 'JSON',
					data: data,
				}).done(function(data,textStatus,jqXHR) {
					if (data.save_status == 'ADD') {
						if ($('#iss_item').val() != '' && data.status == 'success') {
							var row = '<tr id="'+rowcount+'">'+
								 		'<td width="3.09%">'+
								 			'<input type="checkbox" id="chkIssDetail'+rowcount+'" data-inpt="issDetail'+rowcount+'" data-tr="'+rowcount+'" class="iss_checkboxes" value="'+$('#iss_qty').val()+'"/>'+
								 		'</td>'+
								 		'<td width="6.09%">'+
										'<a href="javascript:;" class="btn btn-success btn-sm edit_detail_btn" data-id="'+$('#iss_id').val()+'"'+
											'data-code="'+$('#iss_item').val()+'" data-name="'+$('#iss_item_desc').val()+'" data-kitqty="'+$('#iss_item_desc').val()+'"'+
											'data-issuedqty="'+$('#iss_qty').val()+'" data-lotno="'+$('#iss_lotno').val()+'" data-location="'+$('#iss_location').val()+'"'+
											'data-remarks="'+$('#iss_remarks').val()+'" data-detailid="'+rowcount+'"><i class="fa fa-edit"></i></a>'+

											'<input type="hidden" id="iss_db_id'+rowcount+'" name="iss_db_id[]" value="'+$('#iss_id').val()+'"/>'+
								 		'</td>'+
								 		'<td width="6.09%" id="issdetailid-'+rowcount+'">'+
											rowcount+
											'<input type="hidden" id="issdetailid'+rowcount+'" name="issdetailid[]" value="'+rowcount+'"/>'+
										'</td>'+
								 		'<td width="12.09%" id="item-'+rowcount+'">'+
											$('#iss_item').val()+
											'<input type="hidden" id="item'+rowcount+'" name="issitem[]" value="'+$('#iss_item').val()+'"/>'+
										'</td>'+
								 		'<td width="18.09%" id="item_desc-'+rowcount+'">'+
											$('#iss_item_desc').val()+
											'<input type="hidden" id="item_desc'+rowcount+'" name="issitemdesc[]" value="'+$('#iss_item_desc').val()+'"/>'+
										'</td>'+
								 		'<td width="6.09%" id="kit_qty-'+rowcount+'">'+
											$('#iss_kitqty').val()+
											'<input type="hidden" id="kit_qty'+rowcount+'" name="isskit_qty[]"value="'+$('#iss_kitqty').val()+'" />'+
										'</td>'+
								 		'<td width="6.09%" id="issued_qty-'+rowcount+'">'+
											$('#iss_qty').val()+
											'<input type="hidden" id="issued_qty'+rowcount+'" name="ississued_qty[]" value="'+$('#iss_qty').val()+'"/>'+
										'</td>'+
								 		'<td width="12.09%" id="lot_no-'+rowcount+'">'+
											$('#iss_lotno').val()+
											'<input type="hidden" id="lot_no'+rowcount+'" name="isslot_no[]" value="'+$('#iss_lotno').val()+'"/>'+
										'</td>'+
								 		'<td width="9.09%" id="location-'+rowcount+'">'+
											$('#iss_location').val()+
											'<input type="hidden" id="location'+rowcount+'" name="isslocation[]" value="'+$('#iss_location').val()+'"/>'+
										'</td>'+
								 		'<td width="15.09%" id="remarks-'+rowcount+'">'+
											$('#iss_remarks').val()+
											'<input type="hidden" id="remarks'+rowcount+'" name="issremarks[]" value="'+$('#iss_remarks').val()+'"/>'+
											'<input type="hidden" id="issfifoid'+rowcount+'" name="issfifoid[]" value="'+$('#fifoid').val()+'"/>'+
										'</td>'+
										'<td width="6.09%" id="barcode-'+rowcount+'">'+
											'<a href="javascript:;" class="btn input-sm grey-gallery barcodebtn" data-detailid="'+rowcount+'" '+
												'data-item="'+$('#iss_item').val()+'" data-item_desc="'+$('#iss_item_desc').val()+'" data-kit_qty="'+$('#iss_item_desc').val()+'" '+
												'data-issued_qty="'+$('#iss_qty').val()+'" data-lot_no="'+$('#iss_lotno').val()+'" data-location="'+$('#iss_location').val()+'" '+
												'data-id="'+$('#fifoid').val()+'" data-issueno="'+$('#issuanceno').val()+'">'+
												'<i class="fa fa-barcode"></i>'+
											'</a>'+
										'</td>'+
								'</tr>';
							$('#tbl_issuance_body').append(row);
						} else {
							msg("Total Issued quantity is larger than Required quantity.",'failed');
						}
					} else {
						$('#issued_qty-'+$('#iss_detail_id').val()).html(
							$('#iss_qty').val()+
							'<input type="hidden" id="issued_qty'+$('#iss_detail_id').val()+'" name="ississued_qty[]" value="'+$('#iss_qty').val()+'"/>'
						);
						$('#lot_no-'+$('#iss_detail_id').val()).html(
							$('#iss_lotno').val()+
							'<input type="hidden" id="lot_no'+$('#iss_detail_id').val()+'" name="isslot_no[]" value="'+$('#iss_lotno').val()+'"/>'
						);
						$('#location-'+$('#iss_detail_id').val()).html(
							$('#iss_location').val()+
							'<input type="hidden" id="location'+$('#iss_detail_id').val()+'" name="isslocation[]" value="'+$('#iss_location').val()+'"/>'
						);
						$('#remarks-'+$('#iss_detail_id').val()).html(
							$('#iss_remarks').val()+
							'<input type="hidden" id="remarks'+$('#iss_detail_id').val()+'" name="issremarks[]" value="'+$('#iss_remarks').val()+'"/>'+
							'<input type="hidden" id="issfifoid'+$('#iss_detail_id').val()+'" name="issfifoid[]" value="'+$('#fifoid').val()+'"/>'
						);
					}
				}).fail(function(data,textStatus,jqXHR) {
					msg("There was an error while check issued quantity.",'error');
				});
				
				$('#addIssuanceDetailsModal').modal('hide');
			}
		}
	});

	$('#btn_search').on('click', function() {
		searchReset();
		$('#searchModal').modal('show');
	});

	$('#tbl_search_body').on('click', '.btn_get_search', function() {
		getMaterialKittingData('',$(this).attr('data-kitinfo_id'));
		$('#searchModal').modal('hide');
	});

	$('#tbl_issuance_body').on('click', '.barcodebtn', function() {
		var id = $(this).attr('data-detailid');
		var item = $(this).attr('data-item');
		var item_desc = $(this).attr('data-item_desc');
		var kit_qty = $(this).attr('data-kit_qty');
		var issued_qty = $(this).attr('data-issued_qty');
		var lot_no = $(this).attr('data-lot_no');
		var location = $(this).attr('data-location');
		var issuanceno = $(this).attr('data-issueno');
		var po = $('#searchpono').val();

		if (isOnMobile() == true) {
			printBRcode(id,item,item_desc,kit_qty,issued_qty,lot_no,location,po,issuanceno);
		} else {
			printBRcode(id,item,item_desc,kit_qty,issued_qty,lot_no,location,po,issuanceno);
			msg("Please use mobile device.",'failed');
		}
	});

	$('#tbl_issuance_body').on('click', '.btn_edit_issuance', function() {
		$('#fifoid').val($(this).attr('data-id'));
		$('#iss_item').val($(this).attr('data-code'));
		$('#iss_item_desc').val($(this).attr('data-name'));
		$('#iss_lotno').val($(this).attr('data-lotno'));
		$('#iss_kitqty').val($(this).attr('data-kitqty'));
		$('#iss_qty').val($(this).attr('data-issuedqty'));
		$('#iss_location').val($(this).attr('data-location'));
		$('#iss_remarks').val($(this).attr('data-remarks'));

		$('#iss_item').prop('readonly', true);
		$('#iss_lotno').prop('readonly', true);

		// if ($('#iss_kitqty').val() <= $('#iss_qty').val()) {

			
		// 	$('#iss_qty').prop('readonly', true);

		// }else{
		// 	$('#iss_qty').prop('readonly', false);
		// }
		// $('#iss_item').prop('readonly', true);
		// $('#iss_lotno').prop('readonly', true);
		// $('#iss_qty').prop('readonly', false);
		$('#iss_save_status').val('EDIT');
		$('#iss_id').val($(this).attr('data-id'));
		$('#iss_detail_id').val($(this).attr('data-detailid'));
		getItemAndLotnumFifo();
		$('#addIssuanceDetailsModal').modal('show');
	});

	$('#btn_fiforeason').on('click', function() {
		var data = {
			_token: token,
			id: $('#frid').val(),
			item: $('#fritem').val(),
			item_desc: $('#fritemdesc').val(),
			qty: $('#frqty').val(),
			lotno: $('#frlotno').val(),
			location: $('#frlocation').val(),
			kitqty: $('#frkitqty').val(),
			reason: $('#fiforeason').val(),
			issuanceno: $('#issuanceno').val()
		};

		if ($('#fiforeason').val() == '') {
			msg('Please specify your reason for using this Lot Number.','failed');
		} else {
			saveFifoReason(data);
		}
	});

	$('#btn_reasonlogs').on('click', function() {
		window.location.href = reasonLogsURL + "?issuanceno="+$('#issuanceno').val();
	});

	$('#btn_kittinglist').on('click', function() {
		$('#kittingListModal').modal('show');
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
		getMaterialKittingData('',$('#kitinfo_id').val());
	}
}

function viewState() {
	//buttons
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
			console.log(data.kitinfo);
			console.log(data.kitdetails);
			console.log(data.kitissuance);
			
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
		tbl_kitdetails_body = '<tr>'+
							'<td width="3.6%">'+
							 	'<input type="checkbox" class="kit_checkboxes" data-id="'+x.id+'" value="'+x.id+'"/>'+
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
}

function kitIssuance(data) {
	$('#tbl_issuance_body').html('');
	var tbl_issuance_body = '';
	$.each(data, function(i, x) {
		var item = x.item == null ? '' : x.item;
		var item_desc = x.item_desc == null ? '' : x.item_desc;
		var kit_qty = x.kit_qty == null ? '' : x.kit_qty;
		var issued_qty = x.issued_qty == null ? '' : x.issued_qty;
		var lot_no = x.lot_no == null ? '' : x.lot_no;
		var location = x.location == null ? '' : x.location;
		var remarks = x.remarks == null ? '' : x.remarks;

		tbl_issuance_body = '<tr id="'+x.detailid+'">'+
						 		'<td width="3.09%">'+
						 			'<input type="checkbox" id="chkIssDetail'+x.detailid+'" data-inpt="issDetail'+x.detailid+'" data-tr="'+x.detailid+'" class="iss_checkboxes" value="'+x.id+'"/>'+
						 		'</td>'+
						 		'<td width="6.09%">'+
								'<a href="javascript:;" class="btn btn-success btn-sm btn_edit_issuance" data-id="'+x.id+'"'+
									'data-code="'+item+'" data-name="'+item_desc+'" data-kitqty="'+kit_qty+'"'+
									'data-issuedqty="'+issued_qty+'" data-lotno="'+lot_no+'" data-location="'+location+'"'+
									'data-remarks="'+remarks+'" data-detailid="'+x.detailid+'""><i class="fa fa-edit"></i></a>'+

									'<input type="hidden" id="iss_db_id'+x.detailid+'" name="iss_db_id[]" value="'+x.id+'"/>'+
						 		'</td>'+
						 		'<td width="6.09%" id="issdetailid-'+x.detailid+'">'+
									x.detailid+
									'<input type="hidden" id="issdetailid'+x.detailid+'" name="issdetailid[]" value="'+x.detailid+'"/>'+
								'</td>'+
						 		'<td width="12.09%" id="item-'+x.detailid+'">'+
									item+
									'<input type="hidden" id="item'+x.detailid+'" name="issitem[]" value="'+item+'"/>'+
								'</td>'+
						 		'<td width="18.09%" id="item_desc-'+x.detailid+'">'+
									item_desc+
									'<input type="hidden" id="item_desc'+x.detailid+'" name="issitemdesc[]" value="'+item_desc+'"/>'+
								'</td>'+
						 		'<td width="6.09%" id="kit_qty-'+x.detailid+'">'+
									kit_qty+
									'<input type="hidden" id="kit_qty'+x.detailid+'" name="isskit_qty[]"value="'+kit_qty+'" />'+
								'</td>'+
						 		'<td width="6.09%" id="issued_qty-'+x.detailid+'">'+
									issued_qty+
									'<input type="hidden" id="issued_qty'+x.detailid+'" name="ississued_qty[]" value="'+issued_qty+'"/>'+
								'</td>'+
						 		'<td width="12.09%" id="lot_no-'+x.detailid+'">'+
									lot_no+
									'<input type="hidden" id="lot_no'+x.detailid+'" name="isslot_no[]" value="'+lot_no+'"/>'+
								'</td>'+
						 		'<td width="9.09%" id="location-'+x.detailid+'">'+
									location+
									'<input type="hidden" id="location'+x.detailid+'" name="isslocation[]" value="'+location+'"/>'+
								'</td>'+
						 		'<td width="15.09%" id="remarks-'+x.detailid+'">'+
									remarks+
									'<input type="hidden" id="remarks'+x.detailid+'" name="issremarks[]" value="'+remarks+'"/>'+
									'<input type="hidden" id="issfifoid'+x.detailid+'" name="issfifoid[]" value=""/>'+
								'</td>'+
								'<td width="6.09%" id="barcode-'+x.detailid+'">'+
									'<a href="javascript:;" class="btn input-sm grey-gallery barcodebtn" data-detailid="'+x.detailid+'" '+
										'data-item="'+item+'" data-item_desc="'+item_desc+'" data-kit_qty="'+kit_qty+'" '+
										'data-issued_qty="'+issued_qty+'" data-lot_no="'+lot_no+'" data-location="'+location+'" '+
										'data-id="'+x.id+'" data-issueno="'+x.issue_no+'">'+
										'<i class="fa fa-barcode"></i>'+
									'</a>'+
								'</td>'+
						'</tr>';
		$('#tbl_issuance_body').append(tbl_issuance_body);
	});
}

function delete_items(checkboxClass,url) {
	var chkArray = [];
	$(checkboxClass+":checked").each(function() {
		chkArray.push($(this).val());
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
		if (data.length < 1) {
			msg("No data found. ",'failed');
		} else {
			getFifoTable(data);
		}
	}).fail(function(data,textStatus,jqXHR) {
		msg("There was an error occurred while searching.",'error');
	});
}

function getFifoTable(data) {
	var tbl_fifo_body = '';
	$('#tbl_fifo_body').html('');
	var cnt = 1;

	$.each(data, function(i, x) {
		var lot_no = x.lot_no;
		if (x.lot_no == null) {
			lot_no = '';
		}

		var blocked = '';

		if (cnt > 1) {
			blocked = 'disabled'; 
		}

		tbl_fifo_body = '<tr>'+
							'<td width="7.28%">'+
								'<button class="btn green btn-sm showfifoitem" data-rowcount="'+cnt+'" '+blocked+' data-id="'+x.id+'" data-item="'+x.item+'" '+
									'data-item_desc="'+x.item_desc+'" data-qty="'+x.qty+'" data-lot_no="'+lot_no+'" '+
									'data-location="'+x.location+'" data-receive_date="'+x.receive_date+'" data-kit_qty="'+x.kit_qty+'">'+
									'<i class="fa fa-edit"></i>'+
								'</button>'+
							'</td>'+
							'<td width="18.28%">'+x.item+'</td>'+
							'<td width="21.28%">'+x.item_desc+'</td>'+
							'<td width="7.28%">'+x.qty+'</td>'+
							'<td width="17.28%">'+lot_no+'</td>'+
							'<td width="14.28%">'+x.location+'</td>'+
							'<td width="14.28%">'+x.receive_date+'</td>'+
						'</tr>';
		$('#tbl_fifo_body').append(tbl_fifo_body);
		cnt++;
	});
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
	$.ajax({
		url: fifoReasonURL,
		type: 'POST',
		dataType: 'JSON',
		data: params
	}).done( function(data, textStatus, jqXHR) {
		if (data.status == 'success') {
			$('#fifoid').val(params.id);
			$('#iss_item').val(params.item);
			$('#iss_item_desc').val(params.item_desc);
			$('#iss_lotno').val(params.lotno);
			$('#iss_kitqty').val(params.kitqty);
			$('#iss_qty').val(params.qty);
			$('#iss_location').val(params.location);
			$('#iss_remarks').val(params.reason);

			$('#iss_lotno').prop('readonly', false);
			$('#iss_qty').prop('readonly', false);
			$('#iss_location').prop('readonly', false);
			$('#iss_remarks').prop('readonly', false);

			$('#fifoReasonModal').modal('hide');
		} else {
			msg('Requesting Failed.','failed');
		}

	}).fail( function(data, textStatus, jqXHR) {
		msg("There was an error while processing.",'error');
	});
}

function kittingList() {
	if ($('#kittinglist_po').val() == '' || $('#kittinglist_po').val() == '') {
		msg("All fields are required.","failed");
	} else {
		// window.location.href = kittingListURL +  '?po=' + $("#kittinglist_po").val() + '&&kitqty='+$("#kittinglist_kitqty").val();
		window.open(kittingListURL +  '?po=' + $("#kittinglist_po").val() + '&&kitqty='+$("#kittinglist_kitqty").val(),'_blank');
	}
}

function transferSlip() {
	// window.location.href = transferSlipURL + '?id=' + $("#kitinfo_id").val();
	window.open(transferSlipURL + '?id=' + $("#kitinfo_id").val(),'_blank');
}

function printBRcode(id,item,item_desc,kit_qty,issued_qty,lot_no,location,po,issuanceno) {
	window.location.href= printBarCodeURL +"?id=" +id+"&&issuanceno="+issuanceno;
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














