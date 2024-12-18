var mr_idArr = [];
var inv_idArr = [];
var qty_Arr = [];
var lot_noArr = [];
var _lot_qty = 0;
var sorting_data_arr = [];
var rework_data_arr = [];
var rtv_data_arr = [];
//Selected
var lot_no_data = [];
var mod_of_defects = [];
var judgementTab = 'ON-GOING';
var save_edit = "SAVE";

$(function () {
    $.fn.modal.Constructor.prototype.enforceFocus = function () { };
    getIQCInspection(GetIQCInspectionData);
    getOnGoing();

    getDropdowns();


    $('.timepicker').timepicker({
        showMeridian: false,
        hourStep: 1,
        minStep: 1
    });

    $('#tbl_available_lot thead').on('click', 'tr', '#check_all_items', function () {
        if ($('#check_all_items').is(":checked")) {
            $('.check_item').each(function () {
                $(this).prop('checked', true);
            });
        }
        if (!$('#check_all_items').is(":checked")) {
            $('.check_item').each(function () {
                $(this).prop('checked', false);
            });
        }
    });

    $('#tbl_available_lot tbody').on('click', 'tr', '.check_item', function () {
        var rowsTotal = 0;
        var rowsSelected = 0;
        $('.check_item').each(function () {
            if ($(this).is(":checked")) {	
                rowsSelected++;	
            }
            rowsTotal++;	
        });
	
        rowsTotal === rowsSelected ? $('#check_all_items').prop('checked', true) : $('#check_all_items').prop('checked', false);	
    });

    $('#tbl_lot_no thead').on('click', 'tr', '#check_all_lot_no', function () {	
        if ($('#check_all_lot_no').is(":checked")) {
            $('.check_lot_no').each(function () {
                $(this).prop('checked', true);
            });
        }
        if (!$('#check_all_lot_no').is(":checked")) {
            $('.check_lot_no').each(function () {	
                $(this).prop('checked', false);	
            });	
        }
    });

    $('#tbl_lot_no tbody').on('click', 'tr', '.check_lot_no', function () {
        var rowsTotal = 0;
        var rowsSelected = 0;
        $('.check_lot_no').each(function () {
            if ($(this).is(":checked")) {	
                rowsSelected++;
            }
            rowsTotal++;
        });
        rowsTotal === rowsSelected ? $('#check_all_lot_no').attr("checked", true) : $('#check_all_lot_no').attr("checked", false)
    });

    $('#btn_savemodal').on('click', function () {
        // saveModeOfDefectsInspection(); 
        saveInspection();        
    });

    // $('#partcodelbl').hide();
    // $('#partcode').hide();
    // //$('#partcode').select2('container').hide();

    // $('#btn_backModal').on('click', function() {
    // 	$('#partcodelbl').hide();
    // 	$('#partcode').hide();
    // 	//$('#partcode').select2('container').hide();
    // });

    $('#btn_lot_numbers').on('click', function () {
        // if ($('#save_status').val() == 'ADD') {
        //     getAvailableLotNumbers($('#invoice_no').val(), $('#partcode').val());
        // }
        
        // $('#LotNoModal').modal('show');
        if(judgementTab === 'INSPECTED'){
            $('#div_tbl_available_lot').attr("class", "col-md-8");
            $('#div_tbl_lot_no').attr("class", "col-md-4");
            $('#insert_iqc_lot_no').show();
            $('#div_tbl_available_lot').show();
        }else{
            $('#div_tbl_available_lot').attr("class", "col-md-8");
            $('#div_tbl_lot_no').attr("class", "col-md-4");
            $('#insert_iqc_lot_no').show();
            $('#div_tbl_available_lot').show();
        }
        getAvailableLotNumbers($('#invoice_no').val(), $('#partcode').val());
        $('#LotNoModal').modal('show');
    });

    // $('#insert_iqc_lot_no').on('click', function(){
    //     var invoice_no = $('#invoice_no').val();
    //     var partcode = $('#partcode').val();
    //     var lot_no = $('#lot_no').val();

    //     var table = $('#tbl_available_lot').DataTable();
    //     var checkedLotNo = $('.check_item');
    //     let inc = 0;
    //     var lot_qty = 0;

    //     bootbox.confirm({
    //         message: "Are you sure to save this Lot number?",
    //         buttons: {
    //             confirm: {
    //                 label: 'Yes',
    //                 className: 'btn-success'
    //             },
    //             cancel: {
    //                 label: 'No',
    //                 className: 'btn-danger'
    //             }
    //         },
    //         callback: function (result) {
    //             if (result) {
    //                 $.each(checkedLotNo, function (index) {
    //                     if (checkedLotNo[index].checked) {
    //                         if(jQuery.inArray(table.row(index).data(), lot_no_data) === -1) {
    //                             var data = table.row(index).data();
    //                             lot_no_data.push({                       
    //                                 mr_id: data.mr_id,
    //                                 inv_id: data.id,
    //                                 lot_no: data.lot_no,                        
    //                                 qty: data.qty,
    //                                 mr_source: data.mr_source
    //                             })
            
    //                             lot_qty += data.qty;
    //                             inc++;
    //                             //console.log(table.row(inc).data().lot_no)
    //                         }
    //                         else {
    //                             inc++;
    //                             msg("The selected Lot# already exist in the other table.", 'failed')                    
    //                         }                
    //                     }            
    //                 });
    //                 console.log(lot_no_data);
    //                 SelectedLotNoDataTables(lot_no_data);    
    //             }
                                   
    //         }
    //     });
    //     //insertIQCLotNo(lot_no_data);
    //     //SelectedLotNoDataTables(lot_no_data);
    // });

    $('#insert_iqc_lot_no').on('click', function(){
      
        var invoice_no = $('#invoice_no').val();
        var partcode = $('#partcode').val();
        var lot_no = $('#lot_no').val();

        var table = $('#tbl_available_lot').DataTable();
        var table2 = $('#tbl_lot_no').DataTable();
        var checkedLotNo = $('.check_item');
        let inc = 0;
        var lot_qty = 0;

        bootbox.confirm({
            message: "Are you sure to save this Lot number?",
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
            callback: function (result){
                if(result){
                    $.each(table.context[0].aiDisplay, function(i, x) {
                        var data = table.context[0].aoData[x]._aData;
                        if (table.context[0].aoData[x].anCells[0].firstChild.checked === true) {
                          
                            lot_no_data.push({                       
                                mr_id: data.mr_id,
                                inv_id: data.id,
                                lot_no: data.lot_no,                        
                                qty: data.qty,
                                mr_source: data.mr_source
                            })
        
                            lot_qty += data.qty;
                        }
                    });

                    console.log(lot_no_data);
                    SelectedLotNoDataTables(lot_no_data);
                }
            }
        });
    });

    $('#rq_inspection_body').scroll(function () {
        if ($('#rq_inspection_body').scrollTop() + $('#rq_inspection_body').height() >= $('#rq_inspection_body').height()) {
            row = row + 2;
            getRequalification(row);
        }
    });

    $('#frm_upload').on('submit', function (event) {
        var formObj = $('#frm_upload');
        var formURL = formObj.attr("action");
        var formData = new FormData(this);
        event.preventDefault();

        var inspection_data = $('#inspection_data').val();
        var inspection_mod = $('#inspection_mod').val();
        var requali_data = $('#requali_data').val();
        var requali_mod = $('#requali_mod').val();

        if (inspection_data != '' && checkFile(inspection_data) == false) {
            msg("The Inspection data in not a valid excel file.", 'failed')
        } else if (inspection_mod != '' && checkFile(inspection_mod) == false) {
            msg("The Mode of defects for Inspection data in not a valid excel file.", 'failed')
        } else if (requali_data != '' && checkFile(requali_data) == false) {
            msg("The Re-qualification data in not a valid excel file.", 'failed')
        } else if (requali_mod != '' && checkFile(requali_mod) == false) {
            msg("The Mode of defects for Re-qualification data in not a valid excel file.", 'failed')
        } else {
            $.ajax({
                url: formURL,
                method: 'POST',
                data: formData,
                mimeType: "multipart/form-data",
                contentType: false,
                cache: false,
                processData: false,
            }).done(function (data, textStatus, jqXHR) {
                var out = JSON.parse(data);
                msg(out.msg, 'success')
                console.log(out);
            }).fail(function (data, textStatus, jqXHR) {
                msg("There's an error occurred while processing.", 'failed')
            });
        }
    });

    // $('#time_ins_from').on('change', function () {
    //     var time = setTime($(this).val());
    //     if (time.includes('::')) {
    //         $(this).val(time.replace('::', ':'));
    //     } else {
    //         $(this).val(time);
    //     }
    // });

    // $('#time_ins_to').on('change', function () {
    //     var time = setTime($(this).val());
    //     if (time.includes('::')) {
    //         $(this).val(time.replace('::', ':'));
    //     } else {
    //         $(this).val(time);
    //     }
    // });

    // $('#time_ins_from_man').on('change', function () {
    //     var time = setTime($(this).val());
    //     if (time.includes('::')) {
    //         $(this).val(time.replace('::', ':'));
    //     } else {
    //         $(this).val(time);
    //     }
    // });

    // $('#time_ins_to_man').on('change', function () {
    //     var time = setTime($(this).val());
    //     if (time.includes('::')) {
    //         $(this).val(time.replace('::', ':'));
    //     } else {
    //         $(this).val(time);
    //     }
    // });

    // INSPECTION SIDE
    $('.timepicker-no-seconds').timepicker({
        autoclose: true,
        minuteStep: 5
    });

    $('#IQCresultModal').on('show.bs.modal', function() {
        $('#lot_no').select2({
            placeholder: 'Select Lot No.',
            dropdownParent: $('#IQCresultModal .modal-content'),
            theme: 'bootstrap',
            width: 'auto',
            allowClear: true,
        });
    });


    // Select2 Inputs
    $('#partcode').select2({
        placeholder: 'Select Part Code',
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCInspectionPartCode,
            data: function (params) {
                var invoiceno = $('#invoice_no').val() || "";
                var partcode = $('#partcode').val() || "";
                var query = "", mpquery = "", lpquery = "";

                if (invoiceno) {
                    if (partcode == "") {
                        partcode = params.term || "";
                    }

                    if (partcode != "") {
                        mpquery = "AND m.item LIKE '%" + partcode + "%' "
                        lpquery = "AND l.item LIKE '%" + partcode + "%' "
                    }

                    query = "SELECT DISTINCT m.item as id, m.item as `text` \
                                FROM tbl_wbs_material_receiving_batch as m \
                                WHERE m.not_for_iqc = 0 \
                                AND m.invoice_no = '"+ invoiceno + "' " +
                        mpquery +
                        "UNION \
                                SELECT DISTINCT l.item as id, l.item as `text` \
                                FROM tbl_wbs_local_receiving_batch as l \
                                WHERE l.not_for_iqc = 0 \
                                AND l.invoice_no = '"+ invoiceno + "' " +
                        lpquery;
                }
                return {
                    q: params.term,
                    id: '',
                    text: '',
                    table: '',
                    condition: '',
                    display: 'id&text',
                    orderBy: '',
                    sql_query: query,
                    invoiceno: invoiceno,
                    partcode: partcode

                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
        }
    });

    // var lot_no_select2 = {
    //     url: GetIQCInspectionLotNo,
    //     data: function (params) {
    //         var iqc_id = $('#iqc_result_id').val() || "";
    //         var invoiceno = $('#invoice_no').val() || "";
    //         var partcode = $('#partcode').val() || "";
    //         var lot_no = $('#lot_no').val() || "";
    //         var query = "";

    //         if ((invoiceno != "" || invoiceno != null) && (partcode != "" || partcode != null)) {
    //             if (lot_no == "") {
    //                 lot_no = params.term || "";
    //             }
    //             query = "select l.lot_no as id, \
    //                             l.lot_no as `text`, \
    //                             l.qty as qty, \
    //                             l.id as mr_id, \
    //                             (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id, \
    //                             'LR' as `source` \
    //                             from tbl_wbs_local_receiving_batch as l \
    //                             where l.invoice_no = '"+ invoiceno + "' \
    //                             and l.item = '"+ partcode + "' \
    //                             union \
    //                             select m.lot_no as id, \
    //                             m.lot_no as `text`, \
    //                             m.qty as qty, \
    //                             m.id as mr_id, \
    //                             (select id from tbl_wbs_inventory where mat_batch_id = m.id limit 1) as inv_id, \
    //                             'MR' as `source` \
    //                             from tbl_wbs_material_receiving_batch as m \
    //                             where m.invoice_no = '"+ invoiceno + "' \
    //                             and m.item = '"+ partcode + "'";
    //         }

    //         return {
    //             q: params.term,
    //             id: '',
    //             text: '',
    //             table: '',
    //             condition: '',
    //             display: 'id&text',
    //             orderBy: '',
    //             sql_query: query,
    //             invoiceno: invoiceno,
    //             partcode: partcode,
    //             mode: 'inspection'
    //         };
    //     },
    //     processResults: function (data) {
    //         mr_idArr = [];
    //         inv_idArr = [];
    //         qty_Arr = [];
    //         lot_noArr = [];

    //         $.each(data, function (i, x) {
    //             lot_noArr.push(x.id);
    //             mr_idArr.push(x.mr_id);
    //             inv_idArr.push(x.inv_id);
    //             qty_Arr.push(x.qty);
    //         });
    //         // $(this).attr('data-mr_id', mr_idArr)
    //         // $(this).attr('data-inv_id', inv_idArr)
    //         // $(this).attr('data-qty', qty)
    //         //console.log($(this).attr('data-inv_id'));
    //         console.log(lot_noArr);
    //         return {
    //             results: data
    //         };
    //     },
    // };

    var dispo_lot_no_select2 = {
        url: GetIQCInspectionLotNo,
        data: function (params) {
            var iqc_id = $('#iqc_result_id').val() || "";
            var invoiceno = $('#invoice_no').val() || "";
            var partcode = $('#partcode').val() || "";
            var lot_no = $('#lot_no').val() || "";
            var query = "";

            return {
                q: params.term,
                id: '',
                text: '',
                table: '',
                condition: '',
                display: 'id&text',
                orderBy: '',
                sql_query: query,
                iqc_id: iqc_id,
                invoiceno: invoiceno,
                partcode: partcode,
                mode: 'disposition'
            };
        },
        processResults: function (data) {
            mr_idArr = [];
            inv_idArr = [];
            qty_Arr = [];
            lot_noArr = [];

            $.each(data, function (i, x) {
                lot_noArr.push(x.id);
                mr_idArr.push(x.mr_id);
                inv_idArr.push(x.inv_id);
                qty_Arr.push(x.qty);
            });
            console.log(lot_noArr);
            return {
                results: data
            };
        },
    };

    // $('#lot_no').select2({
    //     placeholder: 'Select Lot No.',
    //     dropdownParent: $('#IQCresultModal .modal-content'),
    //     theme: 'bootstrap',
    //     width: 'auto',
    //     allowClear: true,
    //     ajax: lot_no_select2
    // });

    $('#lot_no').on('change', function (e) {
        //calculateLotQty($(this).val());

        var lot = $(this).val();
        var qty = 0;

        var arrINV = [];
        var arrMR = [];

        if (lot != null) {
            $.each(lot_noArr, function (i, x) {
                if (lot.includes(x)) {
                    qty += qty_Arr[i];
                    arrINV.push(inv_idArr[i]);
                    arrMR.push(mr_idArr[i]);
                }
            });

            $('#inv_id').val(arrINV);
            $('#mr_id').val(arrMR);

            if (lot.length > 1) {
                $('#is_batching').prop('checked', true);
                $('#is_batching').prop('disabled', true);
            } else {
                $('#is_batching').prop('checked', false);
                $('#is_batching').prop('disabled', false);
            }
        }

        //$('#lot_qty').val(qty);
        

        // $('#inv_id').val(inv_idArr);
        // $('#mr_id').val(mr_idArr);
    });

    $('#sorting_lot_no').select2({
        placeholder: 'Select Lot No.',
        dropdownParent: $('#sorting_Modal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: dispo_lot_no_select2
    });

    $('#sorting_lot_no').on('change', function (e) {
        var lot = $(this).val();
        var qty = 0
        $.each(lot_noArr, function(i,x) {
            if (x == lot) {
                qty += qty_Arr[i]; 
                $('#sorting_inv_id').val(inv_idArr[i]);
                $('#sorting_mr_id').val(mr_idArr[i]);
            }
        });

        $('#sorting_total_qty').val(qty);
    });

    $('#rework_lot_no').select2({
        placeholder: 'Select Lot No.',
        dropdownParent: $('#rework_Modal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: dispo_lot_no_select2
    });

    $('#rework_lot_no').on('change', function (e) {
        var lot = $(this).val();
        var qty = 0
        $.each(lot_noArr, function (i, x) {
            if (x == lot) {
                qty += qty_Arr[i]; 
                $('#rework_inv_id').val(inv_idArr[i]);
                $('#rework_mr_id').val(mr_idArr[i]);
            }
        });
        $('#rework_total_qty').val(qty);
    });

    $('#rtv_lot_no').select2({
        placeholder: 'Select Lot No.',
        dropdownParent: $('#rtv_Modal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: dispo_lot_no_select2
    });

    $('#rtv_lot_no').on('change', function (e) {
        var lot = $(this).val();
        var qty = 0;
        $.each(lot_noArr, function (i, x) {
            if (x == lot) {
                qty += qty_Arr[i];
                $('#rtv_inv_id').val(inv_idArr[i]);
                $('#rtv_mr_id').val(mr_idArr[i]);
            }
        });

        $('#rtv_total_qty').val(qty);
    });

    $('#status_NGR').select2({
        placeholder: "Select NGR Status",
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCSelect2Data,
            data: function (params) {
                var query = "";

                query = "select id as `id`, description as `text` \
                            from iqc_ngr_master \
                            where category = 'STATUS'";

                return {
                    q: params.term,
                    id: '',
                    text: '',
                    table: '',
                    condition: '',
                    display: 'id&text',
                    orderBy: '',
                    sql_query: query,
                    connection: 'mysql'
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
        }
    });

    $('#disposition_NGR').select2({
        placeholder: "Select NGR Disposition",
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCSelect2Data,
            data: function (params) {
                var query = "";

                query = "select description as `id`, description as `text` \
                            from iqc_ngr_master \
                            where category = 'DISPOSITION'";

                return {
                    q: params.term,
                    id: '',
                    text: '',
                    table: '',
                    condition: '',
                    display: 'id&text',
                    orderBy: '',
                    sql_query: query,
                    connection: 'mysql'
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
        }
    });

    $('#mod_inspection').select2({
        placeholder: "Select Mode of Defects",
        dropdownParent: $('#mod_inspectionModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCSelect2Data,
            data: function (params) {
                var query = "";
                let name = 'ModeofDefect-IQCInspection';

                var category = name.replace(" ", "").toLowerCase();

                query = "SELECT description as id, description as `text` \
                            FROM tbl_mdropdowns \
                            WHERE category = (SELECT id \
                                            FROM tbl_mdropdown_category \
                                            WHERE LOWER(REPLACE(category, ' ', ''))='"+ category + "')";

                return {
                    q: params.term,
                    id: '',
                    text: '',
                    table: '',
                    condition: '',
                    display: 'id&text',
                    orderBy: '',
                    sql_query: query
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
        }
    });

    $('#search_partcode').select2({
        placeholder: "Select Partcode",
        dropdownParent: $('#SearchModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCItemSearch,
            data: function (params) {
                return {
                    q: params.term,
                    id: '',
                    text: '',
                    table: '',
                    condition: '',
                    display: 'id&text',
                    orderBy: '',
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
        }
    });

    $('#status_NGR').on('change', function () {
        if ($(this).val() == 14 || $(this).val() == 23) {
            $('#disposition_ngr_div').show();
        } else {
            $('#disposition_ngr_div').hide();
        }
    })

    $('#btn_iqcresult').on('click', function () {
        clear();
        // $('#invoice_no').prop('readonly',false);
        // $('#partcode').prop('readonly',true);
        // $('#lot_no').prop('readonly',true);
        // 

        $('#is_batching').prop('checked',false);
		$('#is_batching').prop('disabled',false);

        getFiscalYear();

        //
        getIQCworkWeek();

        $('#classification_manual').hide();

        $('#no_defects_label').hide();
        $('#no_of_defects').hide();
        $('#mode_defects_label').hide();
        $('#btn_mod_ins').hide();

        $('.ngr_details').hide();
        $('.ngr_buttons').hide();

        $('#save_status').val('ADD');

        $('#partcodelbl').hide();
        $('#partcode').show();
        $('#partcode').prop('disabled', false);
        $('#partname').prop('readonly', false);

        $('#IQCresultModal').modal('show');
    });

    $('#btn_iqcresult_man').on('click', function () {
        clear();
        // $('#invoice_no').prop('readonly',false);
        // $('#partcode').prop('readonly',true);
        // $('#lot_no').prop('readonly',true);

        //getDropdowns_man();
        getFiscalYear();
        getIQCworkWeek();

        // $('#no_defects_label_man').hide();
        // $('#no_of_defects_man').hide();
        // $('#mode_defects_label_man').hide();
        // $('#btn_mod_ins_man').hide();

        // $('#save_status_man').val('ADD');

        //$('#ManualModal').modal('show');

        $('#classification_manual').show();

        $('#no_defects_label').hide();
        $('#no_of_defects').hide();
        $('#mode_defects_label').hide();
        $('#btn_mod_ins').hide();

        $('.ngr_details').hide();
        $('.ngr_buttons').hide();

        $('#save_status').val('ADD');

        $('#partcodelbl').hide();
        $('#partcode').show();

        $('#IQCresultModal').modal('show');
    });

    $('#btn_upload').on('click', function () {
        $('#uploadModal').modal('show');
    });

    $('#btn_groupby').on('click', function () {
        $('#GroupByModal').modal('show');
    });

    $('#btn_history').on('click', function () {
        $('#historyModal').modal('show');
    });

    $('#btn_search').on('click', function () {
        //getPartcodeSearch();
        $('#SearchModal').modal('show');
    });

    $('#btn_searchnow').on('click', function () {
        $('#iqcdatatable').DataTable().ajax.reload();
        //searchItemInspection();
    });

    $('#invoice_no').on('change', function () {
        $('#er_invoice_no').html('');
        //getItemDetails();
    });

    $('#partcode').on('change', function () {
        var iqc_id = $('#iqc_result_id').val();
        var partcode = ($(this).val() == null && $(this).val() == "") ? "" : $(this).val();
        if (partcode !== "") {
            $('#lot_no').html('').select2({ data: [] });
            $('#lot_no').val(null).trigger('change');
            getItemDetails(iqc_id);
        }
    });

    $('#severity_of_inspection').on('change', function () {
        if ($(this).val() !== '') {
            samplingPlan($(this).val(), $('#inspection_lvl').val(), $('#aql').val(),$('#lot_qty').val(),'soi_change');
        }
    });

    $('#inspection_lvl').on('change', function () {
        if ($(this).val() !== '') {
            samplingPlan($('#severity_of_inspection').val(), $(this).val(), $('#aql').val(),$('#lot_qty').val(), 'il_change');
        }
    });

    $('#aql').on('change', function () {
        if ($(this).val() !== '') {
            samplingPlan($('#severity_of_inspection').val(), $('#inspection_lvl').val(), $(this).val(),$('#lot_qty').val(), 'aql_change');
        }
    });

    $('#btn_clearmodal').on('click', function () {
        clear();
    });

    // $('#btn_mod_ins').on('click', function () {
    //     if ($('#iqc_result_id').val() !== "") {
    //         iqcdbgetmodeofdefectsinspection();
    //     }
        
    //     populateDropdown();
    //     defectsDataTables(mod_of_defects)  
    //     $('#mod_inspectionModal').modal('show');        
    // });

    $('#btn_mod_ins').on('click', function () {

        if($('#save_status').val() == 'ADD'){
            mod_of_defects = [];
        }

        // if(judgementTab == 'ON-GOING'){
        //     mod_of_defects = [];
        // }

        // $('#tbl_modeofdefect').DataTable().clear();
        // $('#tbl_modeofdefect').DataTable().destroy();

        $("#qty_inspection").val("");

        if ($('#iqc_result_id').val() !== "" && judgementTab != 'ON-GOING') {
            iqcdbgetmodeofdefectsinspection();
        }

        populateDropdown();
        defectsDataTables(mod_of_defects)  

        $('#mod_inspectionModal').modal('show');   
     
    });

    // REWORK
    $('#btn_rework_details').on('click', function () {
        $('#rework_Modal').modal('show');
    });

    //RTV
    $('#btn_rtv_details').on('click', function () {
        $('#rtv_Modal').modal('show');
    });

    $("#tbl_modeofdefect tbody").on("click", ".modinspection_edititem", function () {
        var data = $("#tbl_modeofdefect")
          .DataTable()
          .row($(this).parents("tr"))
          .data();
        console.log(data);
        //$("#mode_save_status").val("EDIT");
        $("#selected_lot").val(data.lot_no)
        $("#mod_inspection").val(data.mod)
        $("#qty_inspection").val(data.qty)
        save_edit = "EDIT"
    });

    // $('#bt_save_modeofdefectsinspection').on('click', function () {
    //     //saveModeOfDefectsInspection();
    //     bootbox.confirm({
    //         message: "Are you sure to save this mode of defect?",
    //         buttons: {
    //             confirm: {
    //                 label: 'Yes',
    //                 className: 'btn-success'
    //             },
                
    //             cancel: {
    //                 label: 'No',
    //                 className: 'btn-danger'
    //             }
    //         },
    //         callback: function (decision) {
    //             if (decision) 
    //             {                    
    //                 console.log(decision);
    //                 var selected_lot = $('#selected_lot').val();
    //                 var mode_of_defect = $('#mod_inspection').val();
    //                 var quantity = $('#qty_inspection').val(); 
    //                 var exist = 0;                   
    //                 if (selected_lot == "" || mode_of_defect == "" || quantity == "") {
    //                     msg("Fill out all input fields.", "error");
    //                 }                    
    //                 else
    //                 {                        
    //                     // $.each(mod_of_defects, function (i, x) 
    //                     // {
    //                     //     if (x.mod == mode_of_defect && x.lot_no == selected_lot) 
    //                     //     {                                                                                      
    //                     //         exist++;
    //                     //     }
    //                     //     else{
    //                     //         exist = 0
    //                     //     }                            
    //                     // });
                        
    //                     // if(exist > 0){
    //                     //     msg("Please select a different Lot number/Mode of defect.", "failed");
    //                     // }
    //                     // else{
    //                     //     if(save_edit == "SAVE"){
    //                     //         mod_of_defects.push({
    //                     //             id: -1,
    //                     //             lot_no:selected_lot,
    //                     //             mod:mode_of_defect,
    //                     //             qty:quantity
    //                     //         })
    //                     //         if (mod_of_defects.length > 0) {
    //                     //             defectsDataTables(mod_of_defects);
    //                     //         }
    //                     //     }
    //                     //     else{
    //                     //         $.each(mod_of_defects, function (i, x) {
    //                     //             if (x.mod == mode_of_defect && x.lot_no == selected_lot) {
    //                     //                 mod_of_defects[i].lot_no = selected_lot;
    //                     //                 mod_of_defects[i].mod = mode_of_defect;
    //                     //                 mod_of_defects[i].qty = quantity;
    //                     //             }
    //                     //           });
                                
    //                     //     }
                            
    //                     // }
    //                     // save_edit = "SAVE";
    //                     if(!checkIfExistInArray(mod_of_defects, selected_lot, mode_of_defect) > 0 && save_edit == "SAVE"){
    //                         mod_of_defects.push({
    //                             id: -1,
    //                             lot_no:selected_lot,
    //                             mod:mode_of_defect,
    //                             qty:quantity
    //                         })
    //                         if (mod_of_defects.length > 0) {
    //                             defectsDataTables(mod_of_defects);
    //                         }
    //                     }
    //                     else{
    //                         $.each(mod_of_defects, function (i, x) {
    //                             if (x.mod == mode_of_defect && x.lot_no == selected_lot) {
    //                                 mod_of_defects[i].lot_no = selected_lot;
    //                                 mod_of_defects[i].mod = mode_of_defect;
    //                                 mod_of_defects[i].qty = quantity;
    //                             }
    //                         });
    //                         if (mod_of_defects.length > 0) {
    //                             defectsDataTables(mod_of_defects);
    //                         }
    //                     }
    //                     save_edit = "SAVE";
    //                 }                    
    //             }
    //             console.log(mod_of_defects)                     
    //         }
    //     });
    // });

    // $('#bt_delete_modeofdefectsinspection').on('click', function () {        
    //     bootbox.confirm({
    //         message: "Do you want to remove this Defect?",
    //         buttons: {
    //           confirm: {
    //             label: "Yes",
    //             className: "btn-success",
    //           },
    //           cancel: {
    //             label: "No",
    //             className: "btn-danger",
    //           },
    //         },
    //         callback: function (result) {
    //           if (result) {
    //             // $(".defect_check_item:checked").each(function () {
    //             //   var defects = $(this).attr("data-defects");
    //             //   $.each(mod_of_defects, function (i, x) {
    //             //     if (x.mod == defects) {                        
    //             //       mod_of_defects.splice(i, 1);
    //             //     }
    //             //   });
    //             // });

    //             var table = $('#tbl_modeofdefect').DataTable();
    //             var checked = [];
             
    //             $.each(table.context[0].aiDisplay, function(i, x) {
    //                 if (table.context[0].aoData[x].anCells[0].firstChild.checked === true) {
    //                     checked.push(mod_of_defects[x].lot_no);
    //                 }
    //             });
             
    //             console.log(checked);

    //             for(var i = 0; i < checked.length; i++){
    //                 for(var j = 0; j < mod_of_defects.length; j++){
    //                     if(mod_of_defects[j].lot_no == checked[i]){
    //                         mod_of_defects.splice(j, 1);
    //                     }
    //                 }
    //             }
        
    //             defectsDataTables(mod_of_defects);
    //             console.log(mod_of_defects)
    //           }
    //         },
    //       });
    // });

    $('#bt_save_modeofdefectsinspection').on('click', function () {
        bootbox.confirm({
            message: "Are you sure to save this mode of defect?",
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
            callback: function (decision) {
                if (decision) 
                {                    
                    console.log(decision);
                    var selected_lot = $('#selected_lot').val();
                    var mode_of_defect = $('#mod_inspection').val();
                    var quantity = $('#qty_inspection').val(); 
                    var exist = 0;                   
                    if (selected_lot == "" || mode_of_defect == "" || quantity == "") {
                        msg("Fill out all input fields.", "error");
                    }                    
                    else
                    {       
                        if(!checkIfExistInArray(mod_of_defects, selected_lot, mode_of_defect) > 0 && save_edit == "SAVE"){
                            mod_of_defects.push({
                                id: -1,
                                lot_no:selected_lot,
                                mod:mode_of_defect,
                                qty:quantity
                            })
                            if (mod_of_defects.length > 0) {
                                defectsDataTables(mod_of_defects);
                            }
                        }
                        else{
                            $.each(mod_of_defects, function (i, x) {
                                if (x.mod == mode_of_defect && x.lot_no == selected_lot) {
                                    mod_of_defects[i].lot_no = selected_lot;
                                    mod_of_defects[i].mod = mode_of_defect;
                                    mod_of_defects[i].qty = quantity;
                                }
                            });
                            if (mod_of_defects.length > 0) {
                                defectsDataTables(mod_of_defects);
                            }
                        }
                        save_edit = "SAVE";
                    }          

                    var clearOption = new Option("", "", true, true);
                    
                    $('#selected_lot').append(clearOption).trigger("change");
                    $('#mod_inspection').append(clearOption).trigger("change");
                    $('#qty_inspection').val("");    
                }
                console.log(mod_of_defects)                     
            }
        });
    });


    $('#bt_delete_modeofdefectsinspection').on('click', function () {        
        bootbox.confirm({
            message: "Do you want to remove this Defect?",
            buttons: {
              confirm: {
                label: "Yes",
                className: "btn-success",
              },
              cancel: {
                label: "No",
                className: "btn-danger",
              },
            },
            callback: function (result) {
                if (result) {
                    var table = $('#tbl_modeofdefect').DataTable();
                    var checked = [];
                
                    $.each(table.context[0].aiDisplay, function(i, x) {
                        if (table.context[0].aoData[x].anCells[0].firstChild.checked === true) {
                            checked.push({
                                defect : mod_of_defects[x].mod,
                                lot_no :  mod_of_defects[x].lot_no,
                            });
                        }
                    });
                
                    console.log(checked);

                    for(var i = 0; i < checked.length; i++){
                        for(var j = 0; j < mod_of_defects.length; j++){
                            if( mod_of_defects[j].lot_no == checked[i].lot_no &&  
                                mod_of_defects[j].mod == checked[i].defect){
                                    mod_of_defects.splice(j, 1);
                            }
                        }
                    }

                    var clearOption = new Option("", "", true, true);

                    $('#selected_lot').append(clearOption).trigger("change");
                    $('#mod_inspection').append(clearOption).trigger("change");
                    $('#qty_inspection').val("");   

                    defectsDataTables(mod_of_defects);
                    console.log(mod_of_defects)
                }
            },
        });
    });


    $('#lot_accepted').on('change', function () {
        openModeOfDefects();
    });

    $('#tblformodinspection').on('click', '.modinspection_edititem', function () {
        var mod = $(this).attr('data-mod');
        var qty = $(this).attr('data-qty');
        var id = $(this).attr('data-id');

        var $defects = $("<option selected='selected'></option>").val(mod).text(mod);
        $("#mod_inspection").append($defects).trigger('change');

        // $('#mod_inspection').select2('data', {
        //     id: mod,
        //     text: mod
        // });
        $('#qty_inspection').val(qty);
        $('#mod_id').val(id);
        $('#status_inspection').val('EDIT');
    });

    $('.checkAllitemsinspection').on('change', function (e) {
        $('input:checkbox.modinspection_checkitem').not(this).prop('checked', this.checked);
    });

    $('.iqc_checkall').on('change', function (e) {
        $('input:checkbox.iqc_checkitems').not(this).prop('checked', this.checked);
    });

    $('.ongoing_checkall').on('change', function (e) {
        $('input:checkbox.ongiong_checkitems').not(this).prop('checked', this.checked);
    });

    $('#tblforiqcinspection').on('click', '.btn_editiqc', function () {
        judgementTab = 'INSPECTED';
        lot_no_data = [];
        // clear input values
        $('.clear').val('');
        $('#inspector').val('');
        $(".clearselect")[0].selectedIndex = 0;
        // assign input values
        var data = $('#iqcdatatable').DataTable().row($(this).parents('tr')).data();
        console.log(data);

        $('#iqc_result_id').val(data.id);

        if (data.ngr_status_id == 14 || data.ngr_status_id == 23) {
            $('#disposition_ngr_div').show();
        } else {
            $('#disposition_ngr_div').hide();
        }

        $('#invoice_no').prop('readonly', true);
        $('#invoice_no').val(data.invoice_no);

        var $partcode = $("<option selected='selected'></option>").val(data.partcode).text(data.partcode);
        $("#partcode").append($partcode).trigger('change').prop('disabled', true);
        $('#partcodelbl').val(data.partcode);

        getItemDetailsEdit(data.lot_no, "inspection");
        
        $('#family').val(data.family);

        $('#partname').val(data.partname);
        $('#supplier').val(data.supplier);
        $('#app_date').val(data.app_date);
        $('#app_time').val(data.app_time);
        $('#app_no').val(data.app_no);

        $('#type_of_inspection').val(data.type_of_inspection);
        $("#severity_of_inspection").val(data.severity_of_inspection);
        $("#inspection_lvl").val(data.inspection_lvl);
        $("#aql").val(data.aql);

        $('#accept').val(data.accept);
        $('#reject').val(data.reject);
        $('#date_inspected').val(data.date_ispected);
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


        // var $shift = $("<option selected='selected'></option>").val(data.shift).text(data.shift);
        // $("#shift").append($shift).trigger('change');
        $("#shift").val(data.shift);

        $('#inspector').val(data.inspector);

        // var $submission = $("<option selected='selected'></option>").val(data.submission).text(data.submission);
        // $("#submission").append($submission).trigger('change');

        $("#submission").val(data.submission);


        $('#lot_inspected').val(data.lot_inspected);
        $('#lot_accepted').val(data.lot_accepted);
        $('#sample_size').val(data.sample_size);
        $('#no_of_defects').val(data.no_of_defects);
        $('#remarks').val(data.remarks);

        $('#control_no_NGR').val(data.ngr_control_no);
        $('#date_NGR').val(data.ngr_issued_date);

        $('#mr_id').val(data.mr_id);
        $('#inv_id').val(data.inv_id);

        $('#lot_qty').val(data.lot_qty);

        samplingPlan(data.severity_of_inspection,data.inspection_lvl,data.aql,data.lot_qty, 'open_modal');

        var mr_id = (data.mr_id == null)? "" : data.mr_id.toString();
        var inv_id = (data.inv_id == null) ? "" : data.inv_id.toString();

        mr_idArr = mr_id.split(',');
        inv_idArr = inv_id.split(',');

        var $status_NGR = $("<option selected='selected'></option>").val(data.ngr_status_id).text(data.ngr_status);
        $("#status_NGR").append($status_NGR).trigger('change');

        var $disposition_NGR = $("<option selected='selected'></option>").val(data.ngr_disposition).text(data.ngr_disposition);
        $("#disposition_NGR").append($disposition_NGR).trigger('change');

        $('#no_defects_label').hide();
        $('#no_of_defects').hide();
        $('#mode_defects_label').hide();
        $('#btn_mod_ins').hide();

        $('#save_status').val('EDIT');
        
        openModeOfDefects();

        $('#judgement').val(data.judgement);

        if (data.judgement == "Special Accept") {
            //$('#msg_special_accept').removeAttr('hidden');
            //$('#btn_savemodal').attr('disabled', 'true');
        } else {
            $('#btn_savemodal').removeAttr('disabled');
            //$('#msg_special_accept').attr('hidden', 'true');
        }

        if (data.judgement == "Sorted") {
            //$('#btn_savemodal').attr('disabled', 'true');
            $('#btn_sorting_details').removeClass('hidden');
            $('#btn_sorting_details').show();
        } else {
            //$('#btn_savemodal').removeAttr('disabled');
            $('#btn_sorting_details').addClass('hidden');
            $('#btn_sorting_details').hide();
        }

        if (data.judgement == "Reworked") {
            //$('#btn_savemodal').attr('disabled', 'true');
            $('#btn_rework_details').removeClass('hidden');
            $('#btn_rework_details').show();
        } else {
            //$('#btn_savemodal').removeAttr('disabled');
            $('#btn_rework_details').addClass('hidden');
            $('#btn_rework_details').hide();
        }

        if (data.judgement == "RTV") {
            //$('#btn_savemodal').attr('disabled', 'true');
            $('#btn_rtv_details').removeClass('hidden');
            $('#btn_rtv_details').show();
        } else {
            $('#btn_savemodal').removeAttr('disabled');
            $('#btn_rtv_details').addClass('hidden');
            $('#btn_rtv_details').hide();
        }

        $check_judgment = ["Rejected", "Sorted", "Reworked", "RTV", "Special Accept"];

        //check if the judgement is rejected
        if ($check_judgment.includes(data.judgement)) {
            $('.ngr_details').show();
        } else {
            $('.ngr_details').hide();
            $('.ngr_buttons').hide();
        }

        if ($(this).attr('data-batching') > 0) {
			$('#is_batching').prop('checked', true);
		} else {
			$('#is_batching').prop('checked', false);
		}

        $('#partname').prop('readonly', true);

        getAvailableLotNumbers(data.invoice_no, data.partcode);

        $('#IQCresultModal').modal('show');
    });

    $('#tblforongoing').on('click', '.btn_editongiong', function () {
        judgementTab = 'ON-GOING';
        lot_no_data = [];
        // clear input values
        $('.clear').val('');
        $('#inspector').val('');
        $(".clearselect")[0].selectedIndex = 0;
        // assign input values
        var data = $('#on-going-inspection').DataTable().row($(this).parents('tr')).data();
        console.log(data);

        $('#iqc_result_id').val(data.id);

        $('#invoice_no').prop('readonly', true);
        $('#invoice_no').val(data.invoice_no);
        $('#partcodelbl').val(data.partcode);

        var $partcode = $("<option selected='selected'></option>").val(data.partcode).text(data.partcode);
        $("#partcode").append($partcode).trigger('change').prop('disabled', true);

        getItemDetailsEdit(data.lot_no, "on-going");

        $('#partname').val(data.partname);
        $('#supplier').val(data.supplier);
        $('#app_date').val(data.app_date);
        $('#app_time').val(data.app_time);
        $('#app_no').val(data.app_no);

        $('#type_of_inspection').val(data.type_of_inspection);
        $("#severity_of_inspection").val(data.severity_of_inspection);
        $("#inspection_lvl").val(data.inspection_lvl);
        $("#aql").val(data.aql);
        
                        // var $lot_no = $("<option selected='selected'></option>").val([data.lot_no]).text([data.lot_no]);
                        // $("#lot_no").append($lot_no).trigger('change');

                        // var $type_of_inspection = $("<option selected='selected'></option>").val(data.type_of_inspection).text(data.type_of_inspection);
                        // $("#type_of_inspection").append($type_of_inspection).trigger('change');

                        // var $severity_of_inspection = $("<option selected='selected'></option>").val(data.severity_of_inspection).text(data.severity_of_inspection);
                        // $("#severity_of_inspection").append($severity_of_inspection).trigger('change');

                        // var $inspection_lvl = $("<option selected='selected'></option>").val(data.inspection_lvl).text(data.inspection_lvl);
                        // $("#inspection_lvl").append($inspection_lvl).trigger('change');

                        // var $aql = $("<option selected='selected'></option>").val(data.aql).text(data.aql);
                        // $("#aql").append($aql).trigger('change');        
        
        $('#accept').val(data.accept);
        $('#reject').val(data.reject);
        $('#date_inspected').val(data.date_ispected);
        $('#ww').val(data.ww);
        $('#fy').val(data.fy);

        // var from = time_ins(data.time_ins_from);
        // $('#time_ins_hour_from').val(from.hr);
        // $('#time_ins_mins_from').val(from.mn);

        // var to = time_ins(data.time_ins_to);
        // $('#time_ins_hour_to').val(to.hr);
        // $('#time_ins_mins_to').val(to.mn);

        $('#time_ins_from').val(data.time_ins_from);
        $('#time_ins_to').val(data.time_ins_to);

                        // var $shift = $("<option selected='selected'></option>").val(data.shift).text(data.shift);
                        // $("#shift").append($shift).trigger('change');
        $("#shift").val(data.shift);

        $('#inspector').val(data.inspector);

                        // var $submission = $("<option selected='selected'></option>").val(data.submission).text(data.submission);
                        // $("#submission").append($submission).trigger('change');
        $("#submission").val(data.submission);

        $('#judgement').val(data.judgement);
        $('#lot_inspected').val(data.lot_inspected);
        $('#lot_accepted').val(data.lot_accepted);
        $('#sample_size').val(data.sample_size);
        $('#no_of_defects').val(data.no_of_defects);
        $('#remarks').val(data.remarks);

        $('#mr_id').val(data.mr_id);
        $('#inv_id').val(data.inv_id);

        $('#lot_qty').val(data.lot_qty);

        var mr_id = (data.mr_id == null) ? "" : data.mr_id.toString();
        var inv_id = (data.inv_id == null) ? "" : data.inv_id.toString();

        mr_idArr = mr_id.split(',');
        inv_idArr = inv_id.split(',');

        $('#no_defects_label').hide();
        $('#no_of_defects').hide();
        $('#mode_defects_label').hide();
        $('#btn_mod_ins').hide();

        $('#save_status').val('EDIT');

        $('#partcodelbl').hide();
        $('#partcode').show();
                        //$('#partcode').select2('container').show();

        if ($(this).attr('data-batching') > 0) {
			$('#is_batching').prop('checked', true);
		} else {
			$('#is_batching').prop('checked', false);
		}

        $('#family').val(data.family);

        openModeOfDefects();
        getIQCworkWeek();
        getFiscalYear();

        $('.ngr_details').hide();
        $('.ngr_buttons').hide();

        $('#partname').prop('readonly', true);

        getAvailableLotNumbers(data.invoice_no, data.partcode);

        $('#IQCresultModal').modal('show');
    });

    //Special Accept button clicked
    $('#btn_special_accept').on('click', function () {
        $('#loading').modal('show');
        if (requiredFields(':input.required') == true) {
            var url = PostSpecialAccept;
            var token = $('meta[name=csrf-token]').attr("content");
            var batching = 0;

            if ($('#is_batching').is(":checked")) {
                batching = 1;
            }
            $('#batching').val(batching);

            $.ajax({
                url: url,
                type: "POST",
                dataType: "JSON",
                data: $('#frm_iqc_inspection').serialize()
            }).done(function (data, textStatus, jqXHR) {
                $('#loading').modal('hide');

                if (data.return_status == 'success') {
                    msg(data.msg, 'success');
                    //clear();
                    // $('#IQCresultModal').modal('hide');
                    getIQCInspection(GetIQCInspectionData);
                    getOnGoing();
                    $('#msg_special_accept').removeAttr('hidden');
                    $('#btn_special_accept').hide();
                } else {
                    msg(data.msg, 'failed');
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                msg("There's some error while processing.", 'failed');
            }).always( function () {
                $('#loading').modal('hide');
            });
        } else {
            $('#loading').modal('hide');
            msg("Please fill out all required fields.", 'failed');
        }
    });

    $('#bt_delete_modeofdefectsinspection').on('click', function () {
        $('#delete_type').val('modins');
        $('#confirmDeleteModal').modal('show');
    });

    $('#btn_deleteyes').on('click', function () {
        var type = $('#delete_type').val();

        if (type == 'inspection') {
            deleteInspection();
        }

        if (type == 'requali') {
            deleteRequali();
        }

        if (type == 'modrq') {
            deleteModRQ();
        }

        if (type == 'modins') {
            deleteModIns();
        }

        if (type == 'on-going') {
            deleteOnGoing();
        }

        $('#confirm_modal').modal('hide');
    });


    $('#btn_delete_inspected').on('click', function () {
        $('#delete_type').val('inspection');
        $('#confirmDeleteModal').modal('show');
    });

    //DELETE LOT NO - NEW FUNCTIONALITY
    $('#btn_delete_lot_no').on('click', function(){
        var table = $('#tbl_lot_no').DataTable();
        var checked = [];
        bootbox.confirm({
            message: "Do you want to remove this Lot Number?",
            buttons: {
                confirm: {
                label: "Yes",
                className: "btn-success",
                },
                cancel: {
                label: "No",
                className: "btn-danger",
                },
            },
            callback: function (result) {
                if(result){
                    
                    // $.each(table.context[0].aiDisplay, function(i, x) {
                    //     if (table.context[0].aoData[x].anCells[0].firstChild.checked === true) {
                    //         lot_no_data.splice(x, 1);
                    //     }
                    // });
                    $.each(table.context[0].aiDisplay, function(i, x) {
                        if (table.context[0].aoData[x].anCells[0].firstChild.checked === true) {
                            checked.push(lot_no_data[x].lot_no);
                        }
                    });
                    
                    console.log(checked);

                    for(var i = 0; i < checked.length; i++){
                        for(var j = 0; j < lot_no_data.length; j++){
                            if(lot_no_data[j].lot_no == checked[i]){
                                lot_no_data.splice(j, 1);
                            }
                        }
                    }
                    console.log(lot_no_data);
                    SelectedLotNoDataTables(lot_no_data);
                }
            },
        });
    });

    $('#btn_delete_ongoing').on('click', function () {
        // $('#delete_type').val('on-going');
        // $('#confirmDeleteModal').modal('show');

        bootbox.prompt({
            title: "Are you sure to delete this On-going Inspection? All Lot numbers will be reverted back as Pending status.",
            inputType: 'textarea',
            message: "Input your remarks.",
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
            callback: function (remarks) {
                switch (remarks) {
                    case null:
                        break;

                    case "":
                        msg("Please input Remarks.","failed");
                        break;
                
                    default:
                        deleteOnGoing(remarks);
                        break;
                }                
            }
        });
        
    });

    //REQUALIFICATION
    $('#btn_requali').on('click', function () {
        getItemsRequalification();
        getDropdownsRequali();
        $('#no_defects_label_rq').hide();
        $('#no_of_defects_rq').hide();
        $('#mode_defects_label_rq').hide();
        $('#btn_mod_rq').hide();
        $('#save_status_rq').val('ADD');

        getRequalification(5);

        $('#ReQualiModal').modal('show');
    });

    $('#partcode_rq').on('change', function () {
        getAppNo();
    });

    $('#app_no_rq').on('change', function () {
        getDetailsRequalification();
        getVisualInspectionRequalification();
    });

    $('#lot_no_rq').on('change', function () {
        calculateLotQtyRequalification($(this).select2('val'));
    });

    $('#btn_mod_rq').on('click', function () {
        $('#status_requalification').val('ADD');
        iqcdbgetmodeofdefectsRequali();
        $('#mod_requalificationModal').modal('show');
    });

    $('#lot_accepted_rq').on('change', function () {
        if ($(this).val() == 0) {
            $('#no_defects_label_rq').show();
            $('#no_of_defects_rq').show();
            $('#mode_defects_label_rq').show();
            $('#btn_mod_rq').show();
            $('#judgement_rq').val('Rejected');
        } else {
            $(this).val(1);
            $('#no_defects_label_rq').hide();
            $('#no_of_defects_rq').hide();
            $('#mode_defects_label_rq').hide();
            $('#btn_mod_rq').hide();

            $('#judgement_rq').val('Accepted');
        }
    });

    $('#rq_inspection_body').on('click', '.btn_editRequali', function () {
        $('#ctrl_no_rq').val($(this).attr('data-ctrl_no'));
        $('#partcode_rq').select2('val', $(this).attr('data-partcode'));
        getDropdownsRequali();
        getAppNo();

        $('#partname_rq').val($(this).attr('data-partname'));
        $('#supplier_rq').val($(this).attr('data-supplier'));
        $('#app_date_rq').val($(this).attr('data-app_date'));
        $('#app_time_rq').val($(this).attr('data-app_time'));
        $('#app_no_rq').val([$(this).attr('data-app_no')]);

        getDetailsRequalification();
        getVisualInspectionRequalification();

        $('#lot_no_rq').val([$(this).attr('data-lot_no')]);
        $('#lot_qty_rq').val($(this).attr('data-lot_qty'));

        $('#date_ispected_rq').val($(this).attr('data-date_ispected'));
        $('#ww_rq').val($(this).attr('data-ww'));
        $('#fy_rq').val($(this).attr('data-fy'));
        $('#time_ins_from_rq').val($(this).attr('data-time_ins_from'));
        $('#time_ins_to_rq').val($(this).attr('data-time_ins_to'));
        $('#shift_rq').val([$(this).attr('data-shift')]);
        $('#inspector_rq').val($(this).attr('data-inspector'));
        $('#submission_rq').val([$(this).attr('data-submission')]);
        $('#judgement_rq').val($(this).attr('data-judgement'));
        $('#lot_inspected_rq').val($(this).attr('data-lot_inspected'));
        $('#lot_accepted_rq').val($(this).attr('data-lot_accepted'));
        $('#no_of_defects_rq').val($(this).attr('data-no_of_defects'));
        $('#remarks_rq').val($(this).attr('data-remarks'));
        $('#id_rq').val($(this).attr('data-id'));

        $('#save_status_rq').val('EDIT');
    });

    $('.checkAllitemsrq').on('change', function (e) {
        $('input:checkbox.checitemrq').not(this).prop('checked', this.checked);
    });

    $('#btn_deleteRequali').on('click', function () {
        $('#delete_type').val('requali');
        $('#confirmDeleteModal').modal('show');
    });

    $('.checkAllitemsrequalification').on('change', function (e) {
        $('input:checkbox.modrq_checkitem').not(this).prop('checked', this.checked);
    });

    $('#btn_deletemodrq').on('click', function () {
        $('#delete_type').val('modrq');
        $('#confirmDeleteModal').modal('show');
    });

    $('#tblformodrequalification').on('change', '.modrq_edititem', function () {
        var mod = $(this).attr('data-mod');
        var qty = $(this).attr('data-qty');
        var id = $(this).attr('data-id');

        $('#mod_rq').select2('data', {
            id: mod,
            text: mod
        });
        $('#qty_rq').val(qty);
        $('#id_requalification').val(id);
        $('#status_requalification').val('EDIT');
    });

    // EXPORTS
    $('#btn_pdf').on('click', function () {
        window.location.href = pdfURL + "?gfrom=" + $("#gfrom").val() + "&gto=" + $("#gto").val();
    });

    $('#btn_excel').on('click', function () {
        window.location.href = excelSummaryURL + "?gfrom=" + $("#gfrom").val() + "&gto=" + $("#gto").val();
    });

    $('#btn_searchHistory').on('click', function () {
        var tblhistorybody = '';
        $('#tblhistorybody').html('');
        var url = GetIQCInspectionHistory;
        
        var data = {
            _token: token,
            item: $('#hs_partcode').val(),
            lotno: $('#hs_lotno').val(),
            judgement: $('#hs_judgement').val(),
            from: $('#hs_from').val(),
            to: $('#hs_to').val(),
        };

        $.ajax({
            url: url,
            type: "GET",
            dataType: "JSON",
            data: data
        }).done(function (data, textStatus, jqXHR) {
            var color = '';
            $.each(data, function (i, x) {
                if (x.judgement == 'Accepted') {
                    color = '#009490';
                } else {
                    color = '#f04646';
                }
                tblhistorybody = '<tr>' +
                    '<td style="width: 11.67%">' + x.invoice_no + '</td>' +
                    '<td style="width: 11.67%">' + x.partcode + '</td>' +
                    '<td style="width: 30.67%">' + x.partname + '</td>' +
                    '<td style="width: 16.67%">' + x.lot_no + '</td>' +
                    '<td style="width: 12.67%">' + x.lot_qty + '</td>' +
                    '<td style="background-color:' + color + '; width: 16%;">' + x.judgement + '</td>' +
                    '</tr>';
                $('#tblhistorybody').append(tblhistorybody);
            });
        }).fail(function (data, textStatus, jqXHR) {
            msg("There's some error while processing."), 'failed';
        });
    });

    //MANUAL INPUT
    $('#severity_of_inspection_man').on('change', function () {
        samplingPlan_man();
    });

    $('#inspection_lvl_man').on('change', function () {
        samplingPlan_man();
    });

    $('#aql_man').on('change', function () {
        samplingPlan_man();
    });

    $('#btn_clearmodal_man').on('click', function () {
        clear();
    });

    $('#btn_mod_ins_man').on('click', function () {
        //iqcdbgetmodeofdefectsinspection();
        $('#mod_inspectionModal').modal('show');
    });

    $('#lot_accepted_man').on('change', function () {
        openModeOfDefects_man();
    });


    // SORTING
    $('#btn_sorting_details').on('click', function () {
        var iqc_id = $('#iqc_result_id').val();

        SortingData(iqc_id);
        
        $('#sorting_Modal').modal('show');
    });

    $('#sorting_category').on('change', function() {
        if ($(this).val() == '') {
            $('#sorting_disposal_date_div').hide();
            $('#sorting_disposal_slip_div').hide();
            $('#sorting_ngr_control_no_div').hide();
            $('#sorting_packinglist_no_div').hide();
        } else {
            if ($(this).val() == 'Local Disposal') {
                $('#sorting_disposal_date_div').show();
                $('#sorting_disposal_slip_div').show();
                $('#sorting_ngr_control_no_div').show();
                $('#sorting_ngr_control_no').val($('#control_no_NGR').val());
                $('#sorting_packinglist_no_div').hide();
            } else {
                $('#sorting_disposal_date_div').show();
                $('#sorting_disposal_slip_div').hide();
                $('#sorting_ngr_control_no_div').hide();
                $('#sorting_packinglist_no_div').show();
            }
        }  
    });

    $('#sorting_act_qty').on('change', function () {
        var ng_qty = ($('#sorting_ng_qty').val() == '' || $('#sorting_ng_qty').val() == null) ? 0 : parseFloat($('#sorting_ng_qty').val());
        var gd_qty = ($('#sorting_good_qty').val() == '' || $('#sorting_good_qty').val() == null) ? 0 : parseFloat($('#sorting_good_qty').val());
        var actual_qty = parseInt($('#sorting_act_qty').val());
        var sum_qty = gd_qty + ng_qty;
        if (sum_qty !== actual_qty) {
            msg("Good Qty & NG Qty were not equal with Actual Qty.", "failed");
        }
    })

    $('#btn_add_sorting').on('click', function() {
        var ng_qty = ($('#sorting_ng_qty').val() == '' || $('#sorting_ng_qty').val() == null)? 0 : parseFloat($('#sorting_ng_qty').val());
        var gd_qty = ($('#sorting_good_qty').val() == '' || $('#sorting_good_qty').val() == null)? 0 : parseFloat($('#sorting_good_qty').val());
        var lot_qty = ($('#sorting_total_qty').val() == '' || $('#sorting_total_qty').val() == null)? 0 : parseFloat($('#sorting_total_qty').val());
        var actual_qty = parseInt($('#sorting_act_qty').val());
        var lot_no = $('#sorting_lot_no').val();
        var sum_qty = gd_qty + ng_qty;
        var idx = ($('#sorting_index').val() == '')? 0 : $('#sorting_index').val();
        var id = ($('#sorting_id').val() == '') ? 0 : $('#sorting_id').val();
        
        if (sum_qty !== actual_qty) {
            msg("Good Qty & NG Qty were not equal with Actual Qty.","failed");
        } else if (lot_no == null || lot_no == "") {
            msg("Please select Lot Number.", "failed");
        } else {

            if ($('#sorting_state').val() == 'ADD') {
                var same = false;
                $.each(sorting_data_arr, function (i, x) {
                    if (x.lot_no == lot_no) {
                        same = true;
                        return false;
                    }
                });

                if (!same) {
                    sorting_data_arr.push({
                        id: '',
                        lot_no: lot_no,
                        good_qty: gd_qty,
                        ng_qty: ng_qty,
                        actual_qty: actual_qty,
                        total_qty: lot_qty,
                        category: $('#sorting_category').val(),
                        disposal_date: $('#sorting_disposal_date').val(),
                        disposal_slip_no: $('#sorting_disposal_slip_no').val(),
                        ngr_control_no: $('#sorting_ngr_control_no').val(),
                        packinglist_no: $('#sorting_packinglist_no').val(),
                        remarks: $('#sorting_remarks').val(),
                        mr_id: $('#sorting_mr_id').val(),
                        inv_id: $('#sorting_inv_id').val(),
                        iqc_id: $('#iqc_result_id').val(),
                    });
                }
            } else {
                sorting_data_arr[idx].id = id;
                sorting_data_arr[idx].lot_no = lot_no;
                sorting_data_arr[idx].good_qty = gd_qty;
                sorting_data_arr[idx].ng_qty = ng_qty; 
                sorting_data_arr[idx].actual_qty = actual_qty; 
                sorting_data_arr[idx].total_qty = lot_qty;
                sorting_data_arr[idx].category = $('#sorting_category').val();
                sorting_data_arr[idx].disposal_date = $('#sorting_disposal_date').val();
                sorting_data_arr[idx].disposal_slip_no = $('#sorting_disposal_slip_no').val();
                sorting_data_arr[idx].ngr_control_no = $('#sorting_ngr_control_no').val();
                sorting_data_arr[idx].packinglist_no = $('#sorting_packinglist_no').val();
                sorting_data_arr[idx].remarks = $('#sorting_remarks').val();
                sorting_data_arr[idx].mr_id = $('#sorting_mr_id').val();
                sorting_data_arr[idx].inv_id = $('#sorting_inv_id').val();
                sorting_data_arr[idx].iqc_id = $('#iqc_result_id').val();

                $('#btn_add_sorting').html('<i class="fa fa-plus"></i> Add');
                $('#sorting_state').val('ADD')
            }

            SortingDataTable(sorting_data_arr);
            
        }

        if (sorting_data_arr.length > 0) {
            $('#btn_save_sorting').prop('disabled', false);
            $('#btn_cancel_sorting').prop('disabled', false);
        }

        $('.sorting_clear').val('');
        $('.sorting_clear_select2').val(null).trigger('change');
    });

    $('#tbl_sorting tbody').on('click', '.btn_sorting_remove', function() {
        var indx = $('#tbl_sorting').DataTable().row($(this).parents('tr')).index();
        console.log(indx);
        console.log(sorting_data_arr);
        sorting_data_arr.splice(indx,1);
        SortingDataTable(sorting_data_arr);

        if (sorting_data_arr.length < 1) {
            $('#btn_save_sorting').prop('disabled', true);
            $('#btn_cancel_sorting').prop('disabled', true);
        }
    });

    $('#tbl_sorting tbody').on('click', '.btn_sorting_edit', function () {
        var data = $('#tbl_sorting').DataTable().row($(this).parents('tr')).data();
        var indx = $('#tbl_sorting').DataTable().row($(this).parents('tr')).index();
        console.log(data);

        $('#sorting_index').val(indx);
        $('#sorting_id').val(data.id);
        $('#sorting_mr_id').val(data.mr_id);
        $('#sorting_inv_id').val(data.inv_id);

        $('#sorting_lot_no').val(null).trigger('change');
        var $sorting_lot_no = $("<option selected='selected'></option>").val(data.lot_no).text(data.lot_no);
        $("#sorting_lot_no").append($sorting_lot_no).trigger('change');

        $('#sorting_total_qty').val(data.total_qty);
        $('#sorting_good_qty').val(data.good_qty);
        $('#sorting_ng_qty').val(data.ng_qty);
        $('#sorting_act_qty').val(data.actual_qty);
        $('#sorting_category').val(data.category),
        $('#sorting_disposal_date').val(data.disposal_date),
        $('#sorting_disposal_slip_no').val(data.disposal_slip_no),
        $('#sorting_ngr_control_no').val(data.ngr_control_no),
        $('#sorting_packinglist_no').val(data.packinglist_no),
        $('#sorting_remarks').val(data.remarks);

        $('#btn_add_sorting').html('<i class="fa fa-pencil"></i> Update');
        $('#sorting_state').val('EDIT')
    });

    $('#btn_save_sorting').on('click', function() {
        var sorting_data = $('#tbl_sorting').DataTable().rows().data();
        var sort_data = [];

        $.each(sorting_data, function(i,x) {
            sort_data.push(x);
        });

        console.log(sort_data);

        $('#loading').modal('show');
        $.ajax({
            url: PostSaveSortingData,
            type: "POST",
            dataType: "JSON",
            data: {
                _token: token,
                sorting_data: sort_data
            }
        }).done(function (data, textStatus, jqXHR) {
            msg(data.msg,data.status);
            if (data.status == 'success') {
                sorting_data_arr = data.sort_data;
                SortingDataTable(sorting_data_arr);
            }

            $('#sorting_state').val('ADD')
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            msg("There's some error while processing.", 'failed');
        }).always( function() {
            $('#loading').modal('hide');
        });
    });

    checkAllCheckboxesInDispoTable('#tbl_sorting', '#sorting_check_all', '.sorting_check_item', '#btn_delete_sorting');

    $('#btn_cancel_sorting').on('click', function() {
        $('.sorting_clear').val('');
        $('.sorting_clear_select2').val(null).trigger('change');

        var iqc_id = $('#iqc_result_id').val();

        $('#btn_add_sorting').html('<i class="fa fa-plus"></i> Add');
        $('#sorting_state').val('ADD')

        SortingData(iqc_id);
    });

    $('#btn_delete_sorting').on('click', function() {
        var checked = [];
        var table = $('#tbl_sorting').DataTable();
        var iqc_id = $('#iqc_result_id').val();

        for (var x = 0; x < table.context[0].aoData.length; x++) {
            var aoData = table.context[0].aoData[x];
            if (aoData.anCells !== null && aoData.anCells[0].firstChild.checked == true) {
                checked.push(aoData.anCells[0].firstChild.value);
            }
        }

        if (checked.length > 0) {
            var del_msg = "Do you want to delete this sorting detail?";
            if (checked.length > 1) {
                del_msg = "Do you want to delete these sorting details?";
            }

            bootbox.confirm({
                message: del_msg,
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
                        $('#loading').modal('show');
                        $.ajax({
                            url: PostDeleteSortingData,
                            type: "POST",
                            dataType: "JSON",
                            data: {
                                _token: token,
                                ids: checked,
                                iqc_id: iqc_id
                            }
                        }).done(function (data, textStatus, jqXHR) {
                            msg(data.msg, data.status);
                            if (data.status == 'success') {
                                SortingDataTable(data.sort_data);
                            }
                        }).fail(function (jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR);
                            msg("There's some error while processing.", 'failed');
                        }).always(function () {
                            $('#loading').modal('hide');
                        });
                    }
                }
            });
        }
    });

    // REWORK
    $('#btn_rework_details').on('click', function () {
        var iqc_id = $('#iqc_result_id').val();

        ReworkData(iqc_id);
        
        $('#rework_Modal').modal('show');
    });

    $('#rework_category').on('change', function() {
        if ($(this).val() == '') {
            $('#rework_disposal_date_div').hide();
            $('#rework_disposal_slip_div').hide();
            $('#rework_ngr_control_no_div').hide();
            $('#rework_packinglist_no_div').hide();
        } else {
            if ($(this).val() == 'Local Disposal') {
                $('#rework_disposal_date_div').show();
                $('#rework_disposal_slip_div').show();
                $('#rework_ngr_control_no_div').show();
                $('#rework_ngr_control_no').val($('#control_no_NGR').val());
                $('#rework_packinglist_no_div').hide();
            } else {
                $('#rework_disposal_date_div').show();
                $('#disposal_slip_div').hide();
                $('#rework_ngr_control_no_div').hide();
                $('#rework_packinglist_no_div').show();
            }
        }  
    });


    $('#rework_act_qty').on('change', function () {
        var ng_qty = ($('#rework_ng_qty').val() == '' || $('#rework_ng_qty').val() == null) ? 0 : parseFloat($('#rework_ng_qty').val());
        var gd_qty = ($('#rework_good_qty').val() == '' || $('#rework_good_qty').val() == null) ? 0 : parseFloat($('#rework_good_qty').val());
        var actual_qty = parseInt($('#rework_act_qty').val());
        var sum_qty = gd_qty + ng_qty;
        if (sum_qty !== actual_qty) {
            msg("Good Qty & NG Qty were not equal with Actual Qty.", "failed");
        }
    })

    $('#btn_add_rework').on('click', function() {
        var ng_qty = ($('#rework_ng_qty').val() == '' || $('#rework_ng_qty').val() == null)? 0 : parseFloat($('#rework_ng_qty').val());
        var gd_qty = ($('#rework_good_qty').val() == '' || $('#rework_good_qty').val() == null)? 0 : parseFloat($('#rework_good_qty').val());
        var lot_qty = ($('#rework_total_qty').val() == '' || $('#rework_total_qty').val() == null)? 0 : parseFloat($('#rework_total_qty').val());

        var actual_qty = parseInt($('#rework_act_qty').val());
        var lot_no = $('#rework_lot_no').val();
        var sum_qty = gd_qty + ng_qty;
        var idx = ($('#rework_index').val() == '')? 0 : $('#rework_index').val();
        var id = ($('#rework_id').val() == '') ? 0 : $('#rework_id').val();
        
        if (sum_qty !== actual_qty) {
            msg("Good Qty & NG Qty were not equal with Actual Qty.","failed");
        } else if (lot_no == null || lot_no == "") {
            msg("Please select Lot Number.", "failed");
        } else {

            if ($('#rework_state').val() == 'ADD') {
                var same = false;
                $.each(rework_data_arr, function (i, x) {
                    if (x.lot_no == lot_no) {
                        same = true;
                        return false;
                    }
                });

                if (!same) {
                    rework_data_arr.push({
                        id: '',
                        lot_no: lot_no,
                        good_qty: gd_qty,
                        ng_qty: ng_qty,
                        actual_qty: actual_qty,
                        total_qty: lot_qty,
                        category: $('#rework_category').val(),
                        disposal_date: $('#rework_disposal_date').val(),
                        disposal_slip_no: $('#rework_disposal_slip_no').val(),
                        ngr_control_no: $('#rework_ngr_control_no').val(),
                        packinglist_no: $('#rework_packinglist_no').val(),
                        remarks: $('#rework_remarks').val(),
                        mr_id: $('#rework_mr_id').val(),
                        inv_id: $('#rework_inv_id').val(),
                        iqc_id: $('#iqc_result_id').val(),
                    });
                }
            } else {
                rework_data_arr[idx].id = id;
                rework_data_arr[idx].lot_no = lot_no;
                rework_data_arr[idx].good_qty = gd_qty;
                rework_data_arr[idx].ng_qty = ng_qty;
                rework_data_arr[idx].actual_qty = actual_qty;
                rework_data_arr[idx].total_qty = lot_qty;
                rework_data_arr[idx].category = $('#rework_category').val();
                rework_data_arr[idx].disposal_date = $('#rework_disposal_date').val();
                rework_data_arr[idx].disposal_slip_no = $('#rework_disposal_slip_no').val();
                rework_data_arr[idx].ngr_control_no = $('#rework_ngr_control_no').val();
                rework_data_arr[idx].packinglist_no = $('#rework_packinglist_no').val();
                rework_data_arr[idx].remarks = $('#rework_remarks').val();
                rework_data_arr[idx].mr_id = $('#rework_mr_id').val();
                rework_data_arr[idx].inv_id = $('#rework_inv_id').val();
                rework_data_arr[idx].iqc_id = $('#iqc_result_id').val();

                $('#btn_add_rework').html('<i class="fa fa-plus"></i> Add');
                $('#rework_state').val('ADD')
            }

            ReworkDataTable(rework_data_arr);
            
        }

        if (rework_data_arr.length > 0) {
            $('#btn_save_rework').prop('disabled', false);
            $('#btn_cancel_rework').prop('disabled', false);
        }

        $('.rework_clear').val('');
        $('.rework_clear_select2').val(null).trigger('change');
    });

    $('#tbl_rework tbody').on('click', '.btn_rework_remove', function() {
        var indx = $('#tbl_rework').DataTable().row($(this).parents('tr')).index();
        console.log(indx);
        console.log(rework_data_arr);
        rework_data_arr.splice(indx,1);
        ReworkDataTable(rework_data_arr);

        if (rework_data_arr.length < 1) {
            $('#btn_save_rework').prop('disabled', true);
            $('#btn_cancel_rework').prop('disabled', true);
        }
    });

    $('#tbl_rework tbody').on('click', '.btn_rework_edit', function () {
        var data = $('#tbl_rework').DataTable().row($(this).parents('tr')).data();
        var indx = $('#tbl_rework').DataTable().row($(this).parents('tr')).index();
        console.log(data);

        $('#rework_index').val(indx);
        $('#rework_id').val(data.id);
        $('#rework_mr_id').val(data.mr_id);
        $('#rework_inv_id').val(data.inv_id);

        $('#rework_lot_no').val(null).trigger('change');
        var $rework_lot_no = $("<option selected='selected'></option>").val(data.lot_no).text(data.lot_no);
        $("#rework_lot_no").append($rework_lot_no).trigger('change');

        $('#rework_total_qty').val(data.total_qty);
        $('#rework_good_qty').val(data.good_qty);
        $('#rework_ng_qty').val(data.ng_qty);
        $('#rework_act_qty').val(data.actual_qty);
        $('#rework_category').val(data.category),
        $('#rework_disposal_date').val(data.disposal_date),
        $('#rework_disposal_slip_no').val(data.disposal_slip_no),
        $('#rework_ngr_control_no').val(data.ngr_control_no),
        $('#rework_packinglist_no').val(data.packinglist_no),
        $('#rework_remarks').val(data.remarks);

        $('#btn_add_rework').html('<i class="fa fa-pencil"></i> Update');
        $('#rework_state').val('EDIT')
    });

    $('#btn_save_rework').on('click', function() {
        var rework_datatable = $('#tbl_rework').DataTable().rows().data();
        var rework_data = [];

        $.each(rework_datatable, function(i,x) {
            rework_data.push(x);
        });

        console.log(rework_data);

        $('#loading').modal('show');
        $.ajax({
            url: PostSaveReworkData,
            type: "POST",
            dataType: "JSON",
            data: {
                _token: token,
                rework_data: rework_data
            }
        }).done(function (data, textStatus, jqXHR) {
            msg(data.msg,data.status);
            if (data.status == 'success') {
                rework_data_arr = data.rework_data;
                ReworkDataTable(rework_data_arr);
            }

            $('#btn_add_rework').html('<i class="fa fa-plus"></i> Add');
            $('#rework_state').val('ADD')
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            msg("There's some error while processing.", 'failed');
        }).always( function() {
            $('#loading').modal('hide');
        });
    });

    checkAllCheckboxesInDispoTable('#tbl_rework', '#rework_check_all', '.rework_check_item', '#btn_delete_rework');

    $('#btn_cancel_rework').on('click', function() {
        $('.rework_clear').val('');
        $('.rework_clear_select2').val(null).trigger('change');

        var iqc_id = $('#iqc_result_id').val();

        $('#btn_add_rework').html('<i class="fa fa-plus"></i> Add');
        $('#rework_state').val('ADD')

        ReworkData(iqc_id);
    });

    $('#btn_delete_rework').on('click', function() {
        var checked = [];
        var table = $('#tbl_rework').DataTable();
        var iqc_id = $('#iqc_result_id').val();

        for (var x = 0; x < table.context[0].aoData.length; x++) {
            var aoData = table.context[0].aoData[x];
            if (aoData.anCells !== null && aoData.anCells[0].firstChild.checked == true) {
                checked.push(aoData.anCells[0].firstChild.value);
            }
        }

        if (checked.length > 0) {
            var del_msg = "Do you want to delete this rework detail?";
            if (checked.length > 1) {
                del_msg = "Do you want to delete these rework details?";
            }

            bootbox.confirm({
                message: del_msg,
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
                        $('#loading').modal('show');
                        $.ajax({
                            url: PostDeleteReworkData,
                            type: "POST",
                            dataType: "JSON",
                            data: {
                                _token: token,
                                ids: checked,
                                iqc_id: iqc_id
                            }
                        }).done(function (data, textStatus, jqXHR) {
                            msg(data.msg, data.status);
                            if (data.status == 'success') {
                                ReworkDataTable(data.sort_data);
                            }
                        }).fail(function (jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR);
                            msg("There's some error while processing.", 'failed');
                        }).always(function () {
                            $('#loading').modal('hide');
                        });
                    }
                }
            });
        }
    });

    // RTV
    $('#btn_rtv_details').on('click', function () {
        var iqc_id = $('#iqc_result_id').val();

        RTVData(iqc_id);

        $('#rtv_Modal').modal('show');
    });

    $('#rtv_category').on('change', function() {
        if ($(this).val() == '') {
            $('#rtv_disposal_date_div').hide();
            $('#rtv_disposal_slip_div').hide();
            $('#rtv_ngr_control_no_div').hide();
            $('#rtv_packinglist_no_div').hide();
        } else {
            if ($(this).val() == 'Local Disposal') {
                $('#rtv_disposal_date_div').show();
                $('#rtv_disposal_slip_div').show();
                $('#rtv_ngr_control_no_div').show();
                $('#rtv_ngr_control_no').val($('#control_no_NGR').val());
                $('#rtv_packinglist_no_div').hide();
            } else {
                $('#rtv_disposal_date_div').show();
                $('#rtv_disposal_slip_div').hide();
                $('#rtv_ngr_control_no_div').hide();
                $('#rtv_packinglist_no_div').show();
            }
        }  
    });

    $('#btn_add_rtv').on('click', function () {
        var lot_qty = ($('#rtv_total_qty').val() == '' || $('#rtv_total_qty').val() == null) ? 0 : parseFloat($('#rtv_total_qty').val());
        var rtv_qty = ($('#rtv_qty').val() == '' || $('#rtv_qty').val() == null) ? 0 : parseFloat($('#rtv_qty').val());

        var lot_no = $('#rtv_lot_no').val();
        var idx = ($('#rtv_index').val() == '') ? 0 : $('#rtv_index').val();
        var id = ($('#rtv_id').val() == '') ? 0 : $('#rtv_id').val();

        if (lot_no == null || lot_no == "") {
            msg("Please select Lot Number.", "failed");
        } else {

            if ($('#rtv_state').val() == 'ADD') {
                var same = false;
                $.each(rtv_data_arr, function (i, x) {
                    if (x.lot_no == lot_no) {
                        same = true;
                        return false;
                    }
                });

                if (!same) {
                    rtv_data_arr.push({
                        id: '',
                        lot_no: lot_no,
                        total_qty: lot_qty,
                        rtv_qty: rtv_qty,
                        category: $('#rtv_category').val(),
                        disposal_date: $('#rtv_disposal_date').val(),
                        disposal_slip_no: $('#rtv_disposal_slip_no').val(),
                        ngr_control_no: $('#rtv_ngr_control_no').val(),
                        packinglist_no: $('#rtv_packinglist_no').val(),
                        remarks: $('#rtv_remarks').val(),
                        mr_id: $('#rtv_mr_id').val(),
                        inv_id: $('#rtv_inv_id').val(),
                        iqc_id: $('#iqc_result_id').val(),
                    });
                }
            } else {
                rtv_data_arr[idx].id = id;
                rtv_data_arr[idx].lot_no = lot_no;
                rtv_data_arr[idx].total_qty = lot_qty;
                rtv_data_arr[idx].rtv_qty = rtv_qty;
                rtv_data_arr[idx].category = $('#rtv_category').val(),
                rtv_data_arr[idx].disposal_date = $('#rtv_disposal_date').val();
                rtv_data_arr[idx].disposal_slip_no = $('#rtv_disposal_slip_no').val();
                rtv_data_arr[idx].ngr_control_no = $('#rtv_ngr_control_no').val();
                rtv_data_arr[idx].packinglist_no = $('#rtv_packinglist_no').val();
                rtv_data_arr[idx].remarks = $('#rtv_remarks').val();
                rtv_data_arr[idx].mr_id = $('#rtv_mr_id').val();
                rtv_data_arr[idx].inv_id = $('#rtv_inv_id').val();
                rtv_data_arr[idx].iqc_id = $('#iqc_result_id').val();

                $('#btn_add_rtv').html('<i class="fa fa-plus"></i> Add');
                $('#rtv_state').val('ADD')
            }

            RTVDataTable(rtv_data_arr);

        }

        if (rtv_data_arr.length > 0) {
            $('#btn_save_rtv').prop('disabled', false);
            $('#btn_cancel_rtv').prop('disabled', false);
        }

        $('.rtv_clear').val('');
        $('.rtv_clear_select2').val(null).trigger('change');
    });

    $('#tbl_rtv tbody').on('click', '.btn_rtv_remove', function () {
        var indx = $('#tbl_rtv').DataTable().row($(this).parents('tr')).index();
        console.log(indx);
        console.log(rtv_data_arr);
        rtv_data_arr.splice(indx, 1);
        RTVDataTable(rtv_data_arr);

        if (rtv_data_arr.length < 1) {
            $('#btn_save_rtv').prop('disabled', true);
            $('#btn_cancel_rtv').prop('disabled', true);
        }
    });

    $('#tbl_rtv tbody').on('click', '.btn_rtv_edit', function () {
        var data = $('#tbl_rtv').DataTable().row($(this).parents('tr')).data();
        var indx = $('#tbl_rtv').DataTable().row($(this).parents('tr')).index();
        console.log(data);

        $('#rtv_index').val(indx);
        $('#rtv_id').val(data.id);
        $('#rtv_mr_id').val(data.mr_id);
        $('#rtv_inv_id').val(data.inv_id);

        $('#rtv_lot_no').val(null).trigger('change');
        var $rtv_lot_no = $("<option selected='selected'></option>").val(data.lot_no).text(data.lot_no);
        $("#rtv_lot_no").append($rtv_lot_no).trigger('change');

        $('#rtv_total_qty').val(data.total_qty);
        $('#rtv_qty').val(data.rtv_qty);
        $('#rtv_category').val(data.category);
        $('#rtv_disposal_date').val(data.disposal_date);
        $('#rtv_disposal_slip_no').val(data.disposal_slip_no);
        $('#rtv_ngr_control_no').val(data.ngr_control_no);
        $('#rtv_packinglist_no').val(data.packinglist_no);
        $('#rtv_remarks').val(data.remarks);

        $('#btn_add_rtv').html('<i class="fa fa-pencil"></i> Update');
        $('#rtv_state').val('EDIT')
    });

    $('#btn_save_rtv').on('click', function () {
        var rtv_datatable = $('#tbl_rtv').DataTable().rows().data();
        var rtv_data = [];

        $.each(rtv_datatable, function (i, x) {
            rtv_data.push(x);
        });

        console.log(rtv_data);

        $('#loading').modal('show');
        $.ajax({
            url: PostSavertvData,
            type: "POST",
            dataType: "JSON",
            data: {
                _token: token,
                rtv_data: rtv_data
            }
        }).done(function (data, textStatus, jqXHR) {
            msg(data.msg, data.status);
            if (data.status == 'success') {
                rtv_data_arr = data.rtv_data;
                RTVDataTable(rtv_data_arr);
            }

            $('#btn_add_rtv').html('<i class="fa fa-plus"></i> Add');
            $('#rtv_state').val('ADD')
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            msg("There's some error while processing.", 'failed');
        }).always(function () {
            $('#loading').modal('hide');
        });
    });

    checkAllCheckboxesInDispoTable('#tbl_rtv', '#rtv_check_all', '.rtv_check_item', '#btn_delete_rtv');

    $('#btn_cancel_rtv').on('click', function () {
        $('.rtv_clear').val('');
        $('.rtv_clear_select2').val(null).trigger('change');

        var iqc_id = $('#iqc_result_id').val();

        $('#btn_add_rtv').html('<i class="fa fa-plus"></i> Add');
        $('#rtv_state').val('ADD')

        RTVData(iqc_id);
    });

    $('#btn_delete_rtv').on('click', function () {
        var checked = [];
        var table = $('#tbl_rtv').DataTable();
        var iqc_id = $('#iqc_result_id').val();

        for (var x = 0; x < table.context[0].aoData.length; x++) {
            var aoData = table.context[0].aoData[x];
            if (aoData.anCells !== null && aoData.anCells[0].firstChild.checked == true) {
                checked.push(aoData.anCells[0].firstChild.value);
            }
        }

        if (checked.length > 0) {
            var del_msg = "Do you want to delete this RTV detail?";
            if (checked.length > 1) {
                del_msg = "Do you want to delete these RTV details?";
            }

            bootbox.confirm({
                message: del_msg,
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
                        $('#loading').modal('show');
                        $.ajax({
                            url: PostDeletertvData,
                            type: "POST",
                            dataType: "JSON",
                            data: {
                                _token: token,
                                ids: checked,
                                iqc_id: iqc_id
                            }
                        }).done(function (data, textStatus, jqXHR) {
                            msg(data.msg, data.status);
                            if (data.status == 'success') {
                                RTVDataTable(data.sort_data);
                            }
                        }).fail(function (jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR);
                            msg("There's some error while processing.", 'failed');
                        }).always(function () {
                            $('#loading').modal('hide');
                        });
                    }
                }
            });
        }
    });
});

function setTime(time_input) {
    var time = time_input.replace('::', ':');
    var h = time.substring(0, 2);
    var m = time.substring(3, 5);
    var a = time.substring(6, 8);

    if (m == undefined || m == '' || m == null) {
        m = '00';
    }

    if (h < 12 && h > 0) {
        if (a == undefined || a == '' || a == null || a == 'A') {
            a = 'AM';
        }
        return h + ":" + m + " " + a;
    } else if (h == 00 || h == 0) {
        if (a == undefined || a == '' || a == null || a == 'A') {
            a = 'AM';
        }
        return "12" + ":" + m + " " + a;
    } else if (h == 12) {
        if (a == undefined || a == '' || a == null || a == 'P') {
            a = 'PM';
        }
        return h + ":" + m + " " + a;
    } else {
        if (a == undefined || a == '' || a == null || a == 'P') {
            a = 'PM';
        }
        switch (h) {
            case '13':
                return "01" + ":" + m + " " + a;
                break;
            case '14':
                return "02" + ":" + m + " " + a;
                break;
            case '15':
                return "03" + ":" + m + " " + a;
                break;
            case '16':
                return "04" + ":" + m + " " + a;
                break;
            case '17':
                return "05" + ":" + m + " " + a;
                break;
            case '18':
                return "06" + ":" + m + " " + a;
                break;
            case '19':
                return "07" + ":" + m + " " + a;
                break;
            case '20':
                return "08" + ":" + m + " " + a;
                break;
            case '21':
                return "09" + ":" + m + " " + a;
                break;
            case '22':
                return "10" + ":" + m + " " + a;
                break;
            case '23':
                return "11" + ":" + m + " " + a;
                break;
            default:
                return time;
                break;
        }
    }
}
// INSPECTION SIDE

// function getIQCInspection(url) {
//     $('#iqcdatatable').DataTable().clear();
//     $('#iqcdatatable').DataTable().destroy();
//     $('#iqcdatatable').DataTable({
//         processing: true,
//         serverSide: true,
//         ajax: {
//             url: url,
//             dataType: "JSON",
//             type: "GET",
//             data: function (d) {
//                 d._token = $("meta[name=csrf-token]").attr("content");
//                 d.item = $('#search_partcode').val();
//                 d.from = $('#search_from').val();
//                 d.to = $('#search_to').val();
//             },
//             error: function (response) {
//                 console.log(response);
//             }
//         },
//         deferRender: true,
//         pageLength: 10,
//         pagingType: "bootstrap_full_number",
//         columnDefs: [{
//             orderable: false,
//             targets: 0
//         }, {
//             searchable: false,
//             targets: 0
//         }, {
//             targets: 19,
//             class: 'lot'
//         }],
//         order: [
//             [13, "desc"]
//         ],
//         lengthMenu: [
//             [10, 20, 50, 100, 150, 200, 500, -1],
//             [10, 20, 50, 100, 150, 200, 500, "All"]
//         ],
//         language: {
//             aria: {
//                 sortAscending: ": activate to sort column ascending",
//                 sortDescending: ": activate to sort column descending"
//             },
//             emptyTable: "No data available in table",
//             info: "Showing _START_ to _END_ of _TOTAL_ records",
//             infoEmpty: "No records found",
//             infoFiltered: "(filtered1 from _MAX_ total records)",
//             lengthMenu: "Show _MENU_",
//             search: "Search:",
//             zeroRecords: "No matching records found",
//             paginate: {
//                 "previous": "Prev",
//                 "next": "Next",
//                 "last": "Last",
//                 "first": "First"
//             }
//         },
//         columns: [
//             {
//                 data: function (data) {
//                     return '<input type="checkbox" class="iqc_checkitems" value="' + data.id + '"/>';
//                 }, orderable: false, searchable: false, name: "i.id"
//             },
//             {
//                 data: function (data) {
//                     var batching = 0;
// 					var lot_no = data.lot_no;
// 					if (lot_no.split(',').length > 0) {
// 						batching = 1;
// 					}
//                     return '<a href="javascript:;" class="btn input-sm blue btn_editiqc" data-id="' + data.id + '" \
//                                 data-ngr_status="'+ data.ngr_status + '" \
//                                 data-ngr_disposition="'+ data.ngr_disposition + '" \
//                                 data-ngr_control_no="'+ data.ngr_control_no + '" \
//                                 data-ngr_issued_date="'+ data.ngr_issued_date + '" \
//                                 data-invoice_no="'+ data.invoice_no + '" \
//                                 data-partcode="'+ data.partcode + '" \
//                                 data-partname="'+ data.partname + '" \
//                                 data-supplier="'+ data.supplier + '" \
//                                 data-app_date="'+ data.app_date + '" \
//                                 data-app_time="'+ data.app_time + '" \
//                                 data-app_no="'+ data.app_no + '" \
//                                 data-lot_no="'+ data.lot_no + '" \
//                                 data-lot_qty="'+ data.lot_qty + '" \
//                                 data-type_of_inspection="'+ data.type_of_inspection + '" \
//                                 data-severity_of_inspection="'+ data.severity_of_inspection + '" \
//                                 data-inspection_lvl="'+ data.inspection_lvl + '" \
//                                 data-aql="'+ data.aql + '" \
//                                 data-accept="'+ data.accept + '" \
//                                 data-reject="'+ data.reject + '" \
//                                 data-date_ispected="'+ data.date_ispected + '" \
//                                 data-ww="'+ data.ww + '" \
//                                 data-fy="'+ data.fy + '" \
//                                 data-time_ins_from="'+ data.time_ins_from + '" \
//                                 data-time_ins_to="'+ data.time_ins_to + '" \
//                                 data-shift="'+ data.shift + '" \
//                                 data-inspector="'+ data.inspector + '" \
//                                 data-submission="'+ data.submission + '" \
//                                 data-judgement="'+ data.judgement + '" \
//                                 data-lot_inspected="'+ data.lot_inspected + '" \
//                                 data-lot_accepted="'+ data.lot_accepted + '" \
//                                 data-sample_size="'+ data.sample_size + '" \
//                                 data-no_of_defects="'+ data.no_of_defects + '" \
//                                 data-remarks="'+ data.remarks + '" \
//                                 data-batching="'+ batching +'" \
//                                 data-classification="'+ data.classification + '"> \
//                                 <i class="fa fa-edit"></i> \
//                             </a>';
//                 }, orderable: false, searchable: false, name: "action"
//             },
//             { data: 'judgement', name: 'i.judgement' },
//             { data: 'ngr_status', name: 'ngr_status' },
//             { data: 'ngr_disposition', name: 'i.ngr_disposition' },
//             { data: 'ngr_control_no', name: 'i.ngr_control_no' },
//             { data: 'invoice_no', name: 'i.invoice_no' },
//             { data: 'inspector', name: 'i.inspector' },
//             { data: 'date_ispected', name: 'i.date_ispected' },
//             {
//                 data: function (data) {
//                     return data.time_ins_from + ' - ' + data.time_ins_to;
//                 }, name: 'i.time_ins_from'
//             },
//             { data: 'app_no', name: 'i.app_no' },
//             { data: 'app_date', name: 'i.app_date' },
//             { data: 'app_time', name: 'i.app_time' },
//             { data: 'fy', name: 'i.fy' },
//             { data: 'ww', name: 'i.ww' },
//             { data: 'submission', name: 'i.submission' },
//             { data: 'partcode', name: 'i.partcode' },
//             { data: 'partname', name: 'i.partname' },
//             { data: 'supplier', name: 'supplier' },
//             { data: function(data) {
//                 return data.lot_no.split(",").join("<br/>");
//             }, name: 'i.lot_no' },
//             { data: 'lot_qty', name: 'i.lot_qty' },
//             { data: 'type_of_inspection', name: 'i.type_of_inspection' },
//             { data: 'severity_of_inspection', name: 'i.severity_of_inspection' },
//             { data: 'inspection_lvl', name: 'i.inspection_lvl' },
//             { data: 'accept', name: 'i.accept' },
//             { data: 'reject', name: 'i.reject' },
//             { data: 'shift', name: 'i.shift' },
//             { data: 'lot_inspected', name: 'i.lot_inspected' },
//             { data: 'lot_accepted', name: 'i.lot_accepted' },
//             { data: 'sample_size', name: 'i.sample_size' },
//             { data: 'no_of_defects', name: 'i.no_of_defects' },
//             { data: 'remarks', name: 'i.remarks' },
//             { data: 'classification', name: 'i.classification' },
//             {
//                 data: function (data) {
//                     return (data.updated_at == '0000-00-00 00:00:00') ? '' : data.updated_at;
//                 }, name: 'i.updated_at'
//             }
//         ],
//         createdRow: function (row, data, dataIndex) {
// 			var dataRow = $(row);
// 			var iqc_judgment = $(dataRow[0].cells[2]);

//             switch (data.judgement) {
//                 case 'RTV':
//                     iqc_judgment.css('background-color', '#ff6266');
//                     iqc_judgment.css('color', '#fff');
//                     break;
//                 case 'Rejected':
//                     iqc_judgment.css('background-color', '#ff6266');
//                     iqc_judgment.css('color', '#fff');
//                     break;
//                 case 'Special Accept':
//                     iqc_judgment.css('background-color', '#64dd17');
//                     iqc_judgment.css('color', '#000');
//                     break;
//                 case 'Sorted':
//                     iqc_judgment.css('background-color', '#ff9933');
//                     iqc_judgment.css('color', '#fff');
//                     break;
//                 case 'Reworked':
//                     iqc_judgment.css('background-color', '#ab47bc');
//                     iqc_judgment.css('color', '#fff');
//                     break;
//                 default:
//                     iqc_judgment.css('background-color', '#0d47a1');
//                     iqc_judgment.css('color', '#fff');
//                     break;
//             }
// 		},
//         order: [[33, 'desc']]
//     });
// }

function getIQCInspection(url) {
    $('#iqcdatatable').DataTable().clear();
    $('#iqcdatatable').DataTable().destroy();
    $('#iqcdatatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url,
            dataType: "JSON",
            type: "GET",
            data: function (d) {
                d._token = $("meta[name=csrf-token]").attr("content");
                d.item = $('#search_partcode').val();
                d.from = $('#search_from').val();
                d.to = $('#search_to').val();
            },
            error: function (response) {
                console.log("wow");
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
        }, {
            targets: 19,
            class: 'lot'
        }],
        order: [
            [13, "desc"]
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
                    return '<input type="checkbox" class="iqc_checkitems" value="' + data.id + '"/>';
                }, orderable: false, searchable: false, name: "i.id"
            },
            {
                data: function (data) {
                    //console.log(data);
                    var batching = 0;
                    var lot_no = data.lot_no;
                    if (lot_no.split(',').length > 0) {
                        batching = 1;
                    }
                    return '<a href="javascript:;" class="btn input-sm blue btn_editiqc" data-id="' + data.id + '" \
                                data-ngr_status="'+ data.ngr_status + '" \
                                data-ngr_disposition="'+ data.ngr_disposition + '" \
                                data-ngr_control_no="'+ data.ngr_control_no + '" \
                                data-ngr_issued_date="'+ data.ngr_issued_date + '" \
                                data-invoice_no="'+ data.invoice_no + '" \
                                data-partcode="'+ data.partcode + '" \
                                data-partname="'+ data.partname + '" \
                                data-supplier="'+ data.supplier + '" \
                                data-app_date="'+ data.app_date + '" \
                                data-app_time="'+ data.app_time + '" \
                                data-app_no="'+ data.app_no + '" \
                                data-lot_no="'+ data.lot_no + '" \
                                data-lot_qty="'+ data.lot_qty + '" \
                                data-type_of_inspection="'+ data.type_of_inspection + '" \
                                data-severity_of_inspection="'+ data.severity_of_inspection + '" \
                                data-inspection_lvl="'+ data.inspection_lvl + '" \
                                data-aql="'+ data.aql + '" \
                                data-accept="'+ data.accept + '" \
                                data-reject="'+ data.reject + '" \
                                data-date_ispected="'+ data.date_ispected + '" \
                                data-ww="'+ data.ww + '" \
                                data-fy="'+ data.fy + '" \
                                data-time_ins_from="'+ data.time_ins_from + '" \
                                data-time_ins_to="'+ data.time_ins_to + '" \
                                data-shift="'+ data.shift + '" \
                                data-inspector="'+ data.inspector + '" \
                                data-submission="'+ data.submission + '" \
                                data-judgement="'+ data.judgement + '" \
                                data-lot_inspected="'+ data.lot_inspected + '" \
                                data-lot_accepted="'+ data.lot_accepted + '" \
                                data-sample_size="'+ data.sample_size + '" \
                                data-no_of_defects="'+ data.no_of_defects + '" \
                                data-remarks="'+ data.remarks + '" \
                                data-batching="'+ batching +'" \
                                data-classification="'+ data.classification + '"> \
                                <i class="fa fa-edit"></i> \
                            </a>';
                }, orderable: false, searchable: false, name: "action"
            },
            { data: 'judgement', name: 'i.judgement' },
            { data: 'ngr_status', name: 'ngr_status' },
            { data: 'ngr_disposition', name: 'i.ngr_disposition' },
            { data: 'ngr_control_no', name: 'i.ngr_control_no' },
            { data: 'invoice_no', name: 'i.invoice_no' },
            { data: 'inspector', name: 'i.inspector' },
            { data: 'date_ispected', name: 'i.date_ispected' },
            {
                data: function (data) {
                    return data.time_ins_from + ' - ' + data.time_ins_to;
                }, name: 'i.time_ins_from'
            },
            { data: 'app_no', name: 'i.app_no' },
            { data: 'app_date', name: 'i.app_date' },
            { data: 'app_time', name: 'i.app_time' },
            { data: 'fy', name: 'i.fy' },
            { data: 'ww', name: 'i.ww' },
            { data: 'submission', name: 'i.submission' },
            { data: 'partcode', name: 'i.partcode' },
            { data: 'partname', name: 'i.partname' },
            { data: 'supplier', name: 'supplier' },
            { data: function(data) {
                return data.lot_no.split(",").join("<br/>");
            }, name: 'i.lot_no' },
            // { data: function (data){
            //     for(var i = 0; i < count(data.lot_no.split(",")); i++){
            //         return data.lot_qty
            //     }, name: 'i.lot_qty' },
            { data: 'lot_qty', name: 'i.lot_qty' },
            { data: 'type_of_inspection', name: 'i.type_of_inspection' },
            { data: 'severity_of_inspection', name: 'i.severity_of_inspection' },
            { data: 'inspection_lvl', name: 'i.inspection_lvl' },
            { data: 'accept', name: 'i.accept' },
            { data: 'reject', name: 'i.reject' },
            { data: 'shift', name: 'i.shift' },
            { data: 'lot_inspected', name: 'i.lot_inspected' },
            { data: 'lot_accepted', name: 'i.lot_accepted' },
            { data: 'sample_size', name: 'i.sample_size' },
            { data: 'no_of_defects', name: 'i.no_of_defects' },
            { data: 'remarks', name: 'i.remarks' },
            { data: 'classification', name: 'i.classification' },
            {
                data: function (data) {
                    return (data.updated_at == '0000-00-00 00:00:00') ? '' : data.updated_at;
                }, name: 'i.updated_at'
            }
        ],
        createdRow: function (row, data, dataIndex) {
            var dataRow = $(row);
            var iqc_judgment = $(dataRow[0].cells[2]);

            switch (data.judgement) {
                case 'RTV':
                    iqc_judgment.css('background-color', '#ff6266');
                    iqc_judgment.css('color', '#fff');
                    break;
                case 'Rejected':
                    iqc_judgment.css('background-color', '#ff6266');
                    iqc_judgment.css('color', '#fff');
                    break;
                case 'Special Accept':
                    iqc_judgment.css('background-color', '#64dd17');
                    iqc_judgment.css('color', '#000');
                    break;
                case 'Sorted':
                    iqc_judgment.css('background-color', '#ff9933');
                    iqc_judgment.css('color', '#fff');
                    break;
                case 'Reworked':
                    iqc_judgment.css('background-color', '#ab47bc');
                    iqc_judgment.css('color', '#fff');
                    break;
                default:
                    iqc_judgment.css('background-color', '#0d47a1');
                    iqc_judgment.css('color', '#fff');
                    break;
            }
        },
        order: [[33, 'desc']],
        fnInitComplete: function() {
            console.log("wow");
        }
    });
}

function getOnGoing() {
    $('#on-going-inspection').DataTable().clear();
    $('#on-going-inspection').DataTable().destroy();
    $('#on-going-inspection').DataTable({
        processing: true,
        serverSide: true,
        ajax: GetIQCOnGoing,
        columns: [
            {
                data: function (data) {
                    return '<input type="checkbox" class="ongiong_checkitems" value="' + data.id + '" data-lot_no="'+data.lot_no+'"/>';
                }, orderable: false, searchable: false, name: "id"
            },
            {
                data: function (data) {
                    var batching = 0;
					var lot_no = data.lot_no;
					if (lot_no.split(',').length > 0) {
						batching = 1;
					}

                    return "<a href='javascript:;' class='btn input-sm blue btn_editongiong' data-id='"+data.id+"' \
                                data-invoice_no='"+data.invoice_no+"' \
                                data-partcode='"+data.partcode+"' \
                                data-partname='"+data.partname+"' \
                                data-supplier='"+data.supplier+"' \
                                data-app_date='"+data.app_date+"' \
                                data-app_time='"+data.app_time+"' \
                                data-app_no='"+data.app_no+"' \
                                data-lot_no='"+data.lot_no+"' \
                                data-lot_qty='"+data.lot_qty+"' \
                                data-type_of_inspection='"+data.type_of_inspection+"' \
                                data-severity_of_inspection='"+data.severity_of_inspection+"' \
                                data-inspection_lvl='"+data.inspection_lvl+"' \
                                data-aql='"+data.aql+"' \
                                data-accept='"+data.accept+"' \
                                data-reject='"+data.reject+"' \
                                data-date_ispected='"+data.date_ispected+"' \
                                data-ww='"+data.ww+"' \
                                data-fy='"+data.fy+"' \
                                data-time_ins_from='"+data.time_ins_from+"' \
                                data-time_ins_to='"+data.time_ins_to+"' \
                                data-shift='"+data.shift+"' \
                                data-inspector='"+data.inspector+"' \
                                data-submission='"+data.submission+"' \
                                data-judgement='"+data.judgement+"' \
                                data-lot_inspected='"+data.lot_inspected+"' \
                                data-lot_accepted='"+data.lot_accepted+"' \
                                data-sample_size='"+data.sample_size+"' \
                                data-no_of_defects='"+data.no_of_defects+"' \
                                data-inv_id='"+data.inv_id+"' \
                                data-mr_id='"+data.mr_id+"' \
                                data-batching='"+ batching +"' \
                                data-remarks='"+data.remarks+"'> \
                                <i class='fa fa-edit'></i> \
                            </a>";
                }, name: 'action', orderable: false, searchable: false 
            },
            { data: 'judgement', name: 'judgement' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'inspector', name: 'inspector' },
            { data: 'date_ispected', name: 'date_ispected' },
            {
                data: function (data) {
                    return data.time_ins_from + ' - present';
                }, name: 'time_ins_from'
            },
            { data: 'app_no', name: 'app_no' },
            { data: 'fy', name: 'fy' },
            { data: 'ww', name: 'ww' },
            { data: 'submission', name: 'submission' },
            { data: 'partcode', name: 'partcode' },
            { data: 'partname', name: 'partname' },
            { data: 'supplier', name: 'supplier' },
            { data: function(data) {
                return data.lot_no.split(",").join("<br/>");
            }, name: 'lot_no' },
            { data: 'lot_qty', name: 'lot_qty' },
            { 
                data: function(data) {
                    var created_at = data.created_at;
                    if (created_at !== null) {
                        if (created_at.includes("00:00:00") ) {
                            return created_at.substring(0, 10);
                        }
                    }

                    return created_at;
                }, name: 'created_at'
            },
        ],
        aoColumnDefs: [
            {
                aTargets: [2], // You actual column with the string 'America'
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                    if (sData == "On-going") {
                        $(nTd).css('background-color', '#3598dc');
                        $(nTd).css('color', '#fff');
                    }
                },
            }
        ],
        order: [[16, 'desc']]
    });
}

// function saveInspection() {
//     $('#loading').modal('show');

//     var selected_lot = "";
//     var inv_id_value = "";
//     var mr_id_value = "";
//     var lot_no_qty = 0;
//     var mr_source = "";
//     for (let i = 0; i <= lot_no_data.length - 1; i++) {
//         selected_lot += (lot_no_data[i].lot_no + ",")
//         inv_id_value += (lot_no_data[i].inv_id + ",")
//         mr_id_value += (lot_no_data[i].mr_id + ",")
//         lot_no_qty += lot_no_data[i].qty
//         mr_source += (lot_no_data[i].mr_source + ",")
//     }

//     if (requiredFields(':input.required') == true) {
//         var url = PostSaveIQCInspection;
//         var partcode = $('#partcode').val();
//         var batching = 0;

//         if ($('#is_batching').is(":checked")) {
// 			batching = 1;
// 		}

//         if ($('#save_status').val() == 'EDIT') {
//             partcode = $('#partcodelbl').val();
//         }
//         var data = {
//             _token: token,
//             save_status: $('#save_status').val(),
//             id: $('#iqc_result_id').val(),
//             invoice_no: $('#invoice_no').val(),
//             partcode: partcode,
//             partname: $('#partname').val(),
//             supplier: $('#supplier').val(),
//             app_date: $('#app_date').val(),
//             app_time: $('#app_time').val(),
//             app_no: $('#app_no').val(),
//             // lot_no: $('#lot_no').val(),            
//             lot_no: selected_lot,
//             lot_qty: lot_no_qty,
//             type_of_inspection: $('#type_of_inspection').val(),
//             severity_of_inspection: $('#severity_of_inspection').val(),
//             inspection_lvl: $('#inspection_lvl').val(),
//             aql: $('#aql').val(),
//             accept: $('#accept').val(),
//             reject: $('#reject').val(),
//             date_inspected: $('#date_inspected').val(),
//             ww: $('#ww').val(),
//             fy: $('#fy').val(),
//             time_ins_from: $('#time_ins_from').val(),
//             time_ins_to: $('#time_ins_to').val(),
//             shift: $('#shift').val(),
//             inspector: $('#inspector').val(),
//             submission: $('#submission').val(),
//             judgement: $('#judgement').val(),
//             lot_inspected: $('#lot_inspected').val(),
//             lot_accepted: $('#lot_accepted').val(),
//             sample_size: $('#sample_size').val(),
//             no_of_defects: $('#no_of_defects').val(),
//             remarks: $('#remarks').val(),
//             classification: $('#classification').val(),
//             family: $('#family').val(),
//             inv_id: inv_id_value,	            
//             mr_id: mr_id_value,
//             is_batching: batching,
//             ngr: {
//                 status_NGR: $('#status_NGR').val(),
//                 disposition_NGR: $('#disposition_NGR').val(),
//                 control_no_NGR: $('#control_no_NGR').val(),
//                 date_NGR: $('#date_NGR').val()
//             },
//             mode_of_defect: mod_of_defects

//         };

//         $.ajax({
//             url: url,
//             type: "POST",
//             dataType: "JSON",
//             data: data
//         }).done(function (data, textStatus, jqXHR) {
//             $('#loading').modal('hide');

//             if (data.return_status == 'success') {
//                 msg(data.msg, 'success');
//                 clear();
//                 $('#IQCresultModal').modal('hide');
//                 getIQCInspection(GetIQCInspectionData);
//                 getOnGoing();
//             }
//         }).fail(function (jqXHR, textStatus, errorThrown) {
//             console.log(jqXHR);
//             msg("There's some error while processing.", 'failed');
//         }).always( function() {
//             $('#loading').modal('hide');
//         });

//         var partcode = ($('#partcode').val() == "" || $('#partcode').val() == null) ? $('#partcodelbl').val() : $('#partcode').val();
//         var iqc_id = $('#iqc_result_id').val();
//         var invoice_no = $('#invoice_no').val();        
//         $.ajax({
//             url: url_insertIQCLotNo,
//             type: "POST",
//             data: {
//                 _token: token,
//                 iqc_id: iqc_id,
//                 invoice_no: invoice_no,
//                 partcode: partcode,
//                 lot_no_data: lot_no_data
//             }
//         }).done(function (data, textStatus, jqXHR) {
//             if (data.hasOwnProperty('msg')) {
//                 msg(data.msg, data.status);
//             } else {
//                 var lot_no_data = data;
//                 getAvailableLotNumbersDatatables(lot_no_data);
//             }            
//         }).fail(function (jqXHR, textStatus, errorThrown) {
//             console.log(jqXHR);
//             msg("There's some error while processing.", 'failed');
//         }).always(function () {
//             $('#loading').modal('hide');
//         });
//     } else {
//         $('#loading').modal('hide');
//         msg("Please fill out all required fields.", 'failed');
//     }
// }

function saveInspection() {
    $('#loading').modal('show');
    var selected_lot = "";
    var inv_id_value = "";
    var mr_id_value = "";
    var mr_source = "";

    lot_no_data = uniqBy(lot_no_data, JSON.stringify);

    for (let i = 0; i <= lot_no_data.length - 1; i++) {
        selected_lot += (lot_no_data[i].lot_no + ",")
        inv_id_value += (lot_no_data[i].inv_id + ",")
        mr_id_value += (lot_no_data[i].mr_id + ",")
        mr_source += (lot_no_data[i].mr_source + ",")
    }

    if (requiredFields(':input.required') == true) {
        var url = PostSaveIQCInspection;

        var partcode = $('#partcode').val();
        var batching = 0;

        if ($('#is_batching').is(":checked")) {
            batching = 1;
        }

        if ($('#save_status').val() == 'EDIT') {
            partcode = $('#partcodelbl').val();
        }
        var time_ins_from = $('#time_ins_hour_from').val() + ":" + $('#time_ins_min_from').val();
        var time_ins_to = $('#time_ins_hour_to').val() + ":" + $('#time_ins_min_to').val();

        var data = {
            _token: token,
            save_status: $('#save_status').val(),
            id: $('#iqc_result_id').val(),
            invoice_no: $('#invoice_no').val(),
            partcode: partcode,
            partname: $('#partname').val(),
            supplier: $('#supplier').val(),
            app_date: $('#app_date').val(),
            app_time: $('#app_time').val(),
            app_no: $('#app_no').val(),
            // lot_no: $('#lot_no').val(),
            lot_no: selected_lot,
            lot_qty: $('#lot_qty').val(),
            type_of_inspection: $('#type_of_inspection').val(),
            severity_of_inspection: $('#severity_of_inspection').val(),
            inspection_lvl: $('#inspection_lvl').val(),
            aql: $('#aql').val(),
            accept: $('#accept').val(),
            reject: $('#reject').val(),
            date_inspected: $('#date_inspected').val(),
            ww: $('#ww').val(),
            fy: $('#fy').val(),
            time_ins_from: $('#time_ins_from').val(),
            time_ins_to: $('#time_ins_to').val(),
            shift: $('#shift').val(),
            inspector: $('#inspector').val(),
            submission: $('#submission').val(),
            judgement: $('#judgement').val(),
            lot_inspected: $('#lot_inspected').val(),
            lot_accepted: $('#lot_accepted').val(),
            sample_size: $('#sample_size').val(),
            no_of_defects: $('#no_of_defects').val(),
            remarks: $('#remarks').val(),
            classification: $('#classification').val(),
            family: $('#family').val(),
            inv_id: inv_id_value,
            mr_id: mr_id_value,
            is_batching: batching,
            ngr: {
                status_NGR: $('#status_NGR').val(),
                disposition_NGR: $('#disposition_NGR').val(),
                control_no_NGR: $('#control_no_NGR').val(),
                date_NGR: $('#date_NGR').val()
            },
            mod_of_defects: mod_of_defects
        };

        $.ajax({
            url: url,
            type: "POST",
            dataType: "JSON",
            data: data
        }).done(function (data, textStatus, jqXHR) {
            $('#loading').modal('hide');

            if (data.return_status == 'success') {
                msg(data.msg, 'success');
                clear();
                $('#IQCresultModal').modal('hide');
                getIQCInspection(GetIQCInspectionData);
                getOnGoing();
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            msg("There's some error while processing.", 'failed');
        }).always(function () {
            $('#loading').modal('hide');
        });
        
        var partcode = ($('#partcode').val() == "" || $('#partcode').val() == null) ? $('#partcodelbl').val() : $('#partcode').val();
        var iqc_id = $('#iqc_result_id').val();
        var invoice_no = $('#invoice_no').val();        
        $.ajax({
            url: url_insertIQCLotNo,
            type: "POST",
            data: {
                _token: token,
                iqc_id: iqc_id,
                invoice_no: invoice_no,
                partcode: partcode,
                lot_no_data: lot_no_data
            }
        }).done(function (data, textStatus, jqXHR) {
            if (data.hasOwnProperty('msg')) {
                msg(data.msg, data.status);
            } else {
                var lot_no_data = data;
                getAvailableLotNumbersDatatables(lot_no_data);
            }            
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            msg("There's some error while processing.", 'failed');
        }).always(function () {
            $('#loading').modal('hide');
        });
    } else {
        $('#loading').modal('hide');
        msg("Please fill out all required fields.", 'failed');
    }
}


function clear() {
    $('.clear').val('');
    $('.clearselect').val(null).trigger('change');
    // $('.clearselect').select2('data', {
    //     id: '',
    //     text: ''
    // })
    $('#invoice_no').prop('readonly', false);
    $('#er_invoice_no').html('');
}

function samplingPlan(severity_of_inspection,inspection_lvl,aql,lot_qty, saan) {
    (severity_of_inspection == null || severity_of_inspection == '')? $('#severity_of_inspection').val() : severity_of_inspection;
    (inspection_lvl == null || inspection_lvl == '')? $('#inspection_lvl').val() : inspection_lvl;
    (aql == null || aql == '')? $('#aql').val() : aql;
    (lot_qty == null || lot_qty == '')? $('#lot_qty').val() : lot_qty;
    
    var data = {
        _token: token,
        soi: $('#severity_of_inspection').val(),
        il: $('#inspection_lvl').val(),
        aql: $('#aql').val(),
        lot_qty: $('#lot_qty').val()
    };

    $.ajax({
        url: GetSamplingPlan,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        console.log(saan);
        console.log(data);
        $('#accept').val(data.accept);
        $('#reject').val(data.reject);
        $('#sample_size').val(data.sample_size);
        //$('#date_inspected').val(data.date_inspected);
        $('#lot_inspected').val(1);
        $('#inspector').val(data.inspector);
        //$('#ww').val(data.workweek);
        getFiscalYear();
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getDropdowns() {
    var url = GetIQCDropdowns;
    
    var data = {
        _token: token
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {

        // $('#family').select2({
        //     data: data.family,
        //     placeholder: "Select Family",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     theme: 'bootstrap',
        //     width: 'auto',
        //     allowClear: true,
        // });

        var family = '<option value=""></option>';
        $.each(data.family, function(i,x) {
            family += '<option value="'+x.id+'">'+x.text+'</option>';
        });
        $('#family').html(family);

        var type_of_inspection = '<option value=""></option>';
        $.each(data.tofinspection, function(i,x) {
            type_of_inspection += '<option value="'+x.id+'">'+x.text+'</option>';
        });
        $('#type_of_inspection').html(type_of_inspection);

        // $('#type_of_inspection').select2({
        //     data: data.tofinspection,
        //     placeholder: "Select Type of Inspection",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     theme: 'bootstrap',
        //     width: 'auto',
        //     allowClear: true,
        // });

        var severity_of_inspection = '<option value=""></option>';
        $.each(data.sofinspection, function(i,x) {
            severity_of_inspection += '<option value="'+x.id+'">'+x.text+'</option>';
        });
        $('#severity_of_inspection').html(severity_of_inspection);

        // $('#severity_of_inspection').select2({
        //     data: data.sofinspection,
        //     placeholder: "Select Severity of Inspection",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     theme: 'bootstrap',
        //     width: 'auto',
        //     allowClear: true,
        // });

        var inspection_lvl = '<option value=""></option>';
        $.each(data.inspectionlvl, function(i,x) {
            inspection_lvl += '<option value="'+x.id+'">'+x.text+'</option>';
        });
        $('#inspection_lvl').html(inspection_lvl);

        // $('#inspection_lvl').select2({
        //     data: data.inspectionlvl,
        //     placeholder: "Select Inspection Level",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     theme: 'bootstrap',
        //     width: 'auto',
        //     allowClear: true,
        // });

        var aql = '<option value=""></option>';
        $.each(data.aql, function(i,x) {
            aql += '<option value="'+x.id+'">'+x.text+'</option>';
        });
        $('#aql').html(aql);

        // $('#aql').select2({
        //     data: data.aql,
        //     placeholder: "Select AQL",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     theme: 'bootstrap',
        //     width: 'auto',
        //     allowClear: true,
        // });

        var shift = '<option value=""></option>';
        $.each(data.shift, function(i,x) {
            if (x.text !== null || x.text !=='null') {
                shift += '<option value="'+x.id+'">'+x.text+'</option>';
            }
        });
        $('#shift').html(shift);

        // $('#shift').select2({
        //     data: data.shift,
        //     placeholder: "Select Shift",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     theme: 'bootstrap',
        //     width: 'auto',
        //     allowClear: true,
        // });



        var submission = '<option value=""></option>';
        $.each(data.submission, function(i,x) {
            if (x.text !== null || x.text !=='null') {
                submission += '<option value="'+x.id+'">'+x.text+'</option>';
            }
        });
        $('#submission').html(submission);

        // $('#submission').select2({
        //     data: data.submission,
        //     placeholder: "Select Submission",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     theme: 'bootstrap',
        //     width: 'auto',
        //     allowClear: true,
        // });

        $('#submission').val('1st');
        // $('#submission').trigger('change');

        // $('#mod_inspection').select2({
        //     data: data.mod,
        //     placeholder: "Select Mode of Defects",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     width: 'auto'
        // });

        // $('#status_NGR').select2({
        //     data: data.ngr_status,
        //     placeholder: "Select NGR Status",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     width: 'auto'
        // });

        // $('#disposition_NGR').select2({
        //     data: data.ngr_disposition,
        //     placeholder: "Select NGR Disposition",
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     width: 'auto'
        // });
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getFiscalYear() {
    var date = new Date();
    var month = date.getMonth();
    var year = date.getFullYear();

    if (month < 3) {
        year = year - 1;
    }

    $('#fy').val(year);
}

function getItemDetails(iqc_id) {
    var partcode = $('#partcode').val();

    if ($('#partcode').val() == '') {
        partcode = $('#partcodelbl').val();
    }

    var url = GetIQCItemDetails;
    
    var data = {
        _token: token,
        invoiceno: $('#invoice_no').val(),
        item: partcode,
        iqc_id: iqc_id
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        if (data.hasOwnProperty('details')) {
            var details = data.details;
            $.each(details, function(i,x) {
                $('#partname').val(x.item_desc);
                $('#supplier').val(x.supplier);
                $('#app_date').val(x.app_date);
                $('#app_time').val(x.app_time);
                $('#app_no').val(x.receive_no);
            });

            var lots = [];
            var mr_id = [];
            var inv_id = [];
            var qty = [];


            $.each(data.lot, function (i, x) {
                var lot = x.id
                lots.push(lot); //lot.replace(' ', '')
                mr_id.push(x.mr_id);
                inv_id.push(x.inv_id);
                qty.push(x.qty);
            });

            mr_idArr = mr_id;
            inv_idArr = inv_id;
            lot_noArr = lots;
            qty_Arr = qty;

            $('#lot_no').select2({
                data: data.lot,
                placeholder: 'Select Lot No.',
                dropdownParent: $('#IQCresultModal .modal-content'),
                theme: 'bootstrap',
                width: 'auto',
                allowClear: true,
            });
            
        }
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getItemDetailsEdit(lot_no_val, modal_mode) {
    var partcode = ($('#partcode').val() == "" || $('#partcode').val() == null) ? $('#partcodelbl').val() : $('#partcode').val();
    var iqc_id = $('#iqc_result_id').val();
    var url = GetIQCItemDetails;
    
    var data = {
        _token: token,
        invoiceno: $('#invoice_no').val(),
        item: partcode,
        iqc_id: iqc_id,
        modal_mode: modal_mode
    };

    $('#loading').modal('show');

    $.ajax({
        url: url,
        type: "GET",
        data: data,
    }).done(function (response, textStatus, jqXHR) {
        var details = response.details;
        var lots = [];
        var mr_id = [];
        var inv_id = [];
        var qty = [];
        var source = [];

        $.each(response.lot, function (i, x) {
            var lot_id = x.id
            lots.push(x.lot_no);
            mr_id.push(x.mr_id);
            inv_id.push(x.inv_id);
            qty.push(x.qty);
            source.push(x.source);
        });

        mr_idArr = mr_id;
        inv_idArr = inv_id;
        lot_noArr = lots;
        qty_Arr = qty;

        // $('#lot_no').select2({
        //     data: response.lot,
        //     placeholder: 'Select Lot No.',
        //     dropdownParent: $('#IQCresultModal .modal-content'),
        //     theme: 'bootstrap',
        //     width: 'auto',
        //     allowClear: true,
        // });
        // $("#lot_no").val(null).trigger('change');

        var lot_no = (lot_no_val == null) ? [] : lot_no_val.split(',');

        // $.each(lot_no, function (i, x) {
        //     var $lot_no = $("<option selected='selected'></option>").val(x).text(x);
        //     $("#lot_no").append($lot_no);
        // });

        $("#lot_no").val(lot_no).trigger('change');

        var lot_qty = 0;
        var selectedINV = [];
        var selectedMR = [];
        $.each(lot_noArr, function (i, x) {
            if (lot_no.includes(x)) {
                lot_qty = lot_qty + qty_Arr[i];
                selectedINV.push(inv_idArr[i]);
                selectedMR.push(mr_idArr[i]);

                // for lot number datatable
                lot_no_data.push({
                    mr_id: mr_idArr[i],
                    inv_id: inv_idArr[i],
                    lot_no: x,
                    qty: qty_Arr[i],
                    mr_source: source[i]
                });
            }
        });

        if (lot_no.length > 1) {
            $('#is_batching').prop('checked', true);
            $('#is_batching').prop('disabled', true);
        } else {
            $('#is_batching').prop('checked', false);
            $('#is_batching').prop('disabled', false);
        }

        $('#lot_qty').val(lot_qty);
        $('#inv_id').val(selectedINV);
        $('#mr_id').val(selectedMR);

        // $('#mr_id').val(mr_id);
        // $('#inv_id').val(inv_id);
        console.log(lot_no_data)
        SelectedLotNoDataTables(lot_no_data);

        console.log(response.lot);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(errorThrown);
        msg("There's some error while processing.", 'failed');
    }).always( function() {
        $('#loading').modal('hide');
    });
}

function SelectedLotNoDataTables(dataArr) {

    let qty = 0;
    for (let i = 0; i <= lot_no_data.length - 1; i++) {
        qty += lot_no_data[i].qty
    }

    $('#tbl_lot_no').DataTable().clear();
    $('#tbl_lot_no').DataTable().destroy();
    $('#tbl_lot_no').DataTable({
        data: dataArr,
        searching: false,
        lengthChange: false,
        columns: [
            {
                data: function (data) {
                    //console.log(data)
                    //if (data.id !== '') {
                        return '<input type="checkbox" class="check_lot_no" value=""/>';
                    // }
                    // return '';
                }, orderable: false, searchable: false
            },
            { data: 'lot_no', orderable: false, searchable: false },
            { data: 'qty', orderable: false, searchable: false },
        ],
        order: [[1, 'asc']],
        rowCallback: function(row, data) {
            qty = (data.qty == null || data.qty == "")? 0: qty 
        }
    });

    $('#lot_qty').val(qty);
}

function populateDropdown() {
    console.log(lot_no_data)
    
    var data = []
    for (i = 0; i < lot_no_data.length; i++){
        data.push({
            id: lot_no_data[i].lot_no,
            text: lot_no_data[i].lot_no
        })
    }
    $('#selected_lot').empty();
    $('#selected_lot').select2({
        placeholder: "Select Lot No.",
        dropdownParent: $('#mod_inspectionModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        data: data,
    });
    
    var option = new Option(0, '', true, true);
    $('#selected_lot').append(option).trigger('change.select2');
}

function getAvailableLotNumbers(invoice_no,partcode) {
    $('#lot_no_invoice_no').val(invoice_no);
    $('#lot_no_part_code').val(partcode);

    $('#loading').modal('show');

    $.ajax({
        url: GetAvailableLotNumbersURL,
        type: "GET",
        data: {
            _token: token,
            invoice_no: invoice_no,
            partcode: partcode
        }
    }).done(function (data, textStatus, jqXHR) {
        if (data.hasOwnProperty('msg')) {
            msg(data.msg, data.status);
        } else {
            var lot_no = data;

            // if (sorting_data_arr.length > 0) {
            //     $('#btn_save_sorting').prop('disabled', false);
            //     $('#btn_cancel_sorting').prop('disabled', false);
            // }

            getAvailableLotNumbersDatatables(lot_no);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        msg("There's some error while processing.", 'failed');
    }).always( function() {
        $('#loading').modal('hide');
    });
}

function getAvailableLotNumbersDatatables(dataArr) {
    $('#tbl_available_lot').DataTable().clear();
    $('#tbl_available_lot').DataTable().destroy();
    qty = 0;
    var tbl_available_lot = $('#tbl_available_lot').DataTable({
        data: dataArr,
        searching: false,
        lengthChange: false,
        columns: [
            {
                data: function (data) {
                    if (data.iqc_status != 0 || data.iqc_status != null || data.iqc_status != "0") {
                        return '<input type="checkbox" class="check_item" value=""/>';
                    }
                    return '';
                }, orderable: false, searchable: false
            },
            { data: 'judgement', orderable: false, searchable: false },
            { data: 'item', orderable: false, searchable: false },
            { data: 'item_desc', orderable: false, searchable: false },
            { data: 'lot_no', orderable: false, searchable: false },
            { data: 'qty', orderable: false, searchable: false },
            { data: 'drawing_num', orderable: false, searchable: false },
            { data: 'supplier', orderable: false, searchable: false },
        ],
        order: [[4, 'asc']],
        rowCallback: function(row, data) {
            qty += (data.qty == null || data.qty == "")? 0: data.qty
        }
    });


}

// function insertIQCLotNo(lot_no_data) {
//     bootbox.confirm({
//         message: "Are you sure to save this Lot number?",
//         buttons: {
//             confirm: {
//                 label: 'Yes',
//                 className: 'btn-success'
//             },
//             cancel: {
//                 label: 'No',
//                 className: 'btn-danger'
//             }
//         },
//         callback: function (result) {
//             SelectedLotNoDataTables(lot_no_data);                       
//         }
//     });
// }

function calculateLotQty(lotno) {
    var url = GetIQCLotQty;
    
    var data = {
        _token: token,
        invoiceno: $('#invoice_no').val(),
        item: $('#partcode').val(),
        lot_no: lotno
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $('#lot_qty').val(data);
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function saveModeOfDefectsInspection() {
    var url = PostSaveModeOfDefects;
    
    var partcode = ($("#partcode").val() == "") ? $('#partcodelbl').val() : $("#partcode").val();
    var data = {
        _token: token,
        invoiceno: $('#invoice_no').val(),
        item: partcode,
        mod: mod_of_defects.mode_of_defect,
        lot_no: mod_of_defects.selected_lot,
        qty: mod_of_defects.quantity,
        // mod: $('#mod_inspection').val(),
        // lot_no: $('#selected_lot').find(":selected").text(),
        // qty: $('#qty_inspection').val(),
        status: $('#status_inspection').val(),
        iqc_id: $('#iqc_result_id').val(),
        current_count: $('#mod_total_qty').val(),
        sample_size: $('#sample_size').val(),
        id: $('#mod_id').val(),
        mod_of_defects: mod_of_defects
    };

    $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        if (data.return_status == "success") {
            msg(data.msg, 'success');
            console.log(data.count);
        } else {
            msg(data.msg, 'failed');
            console.log(data.count);
        }
        mod_of_defects = []
        //iqcdbgetmodeofdefectsinspection();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        msg("There's some error while processing.", 'failed');
    });
}

function getModinspectionlist(data) {
    var cnt = 0;
    var no_of_defectives = 0;
    var qty = 0;
    var max = [];
    $.each(data, function (i, x) {
        //cnt++;
        tblformodinspection = '<tr>' +
            '<td style="width: 8%">' +
            '<input type="checkbox" class="modinspection_checkitem checkboxes" value="' + x.id + '">' +
            '</td>' +
            '<td style="width: 12%">' +
            '<a href="javascript:;" class="btn blue input-sm modinspection_edititem" data-mod="' + x.mod + '" data-qty="' + x.qty + '" data-id="' + x.id + '">' +
            '<i class="fa fa-edit"></i>' +
            '</a>' +
            '</td>' +
            '<td style="width: 33%">' + x.lot_no + '</td>' +
            '<td style="width: 33%">' + x.mod + '</td>' +
            '<td style="width: 14%">' + x.qty + '</td>' +
            '</tr>';

        if (x.qty == $('#sample_size').val()) {
            no_of_defectives = x.qty;
        } else {
            max.push(x.qty);
            no_of_defectives = Math.max.apply(null, max);
        }
        //no_of_defectives = parseFloat(no_of_defectives) + parseFloat(x.qty);

        qty = parseFloat(qty) + parseFloat(x.qty);
        $('#tblformodinspection').append(tblformodinspection);
    });
    $('#mod_count').val(cnt);
    $('#mod_total_qty').val(qty);
    $('#no_of_defects').val(qty); //no_of_defectives
    $('#status_inspection').val('ADD');
}

function iqcdbgetmodeofdefectsinspection() {
    $('#tblformodinspection').html('');
    var tblformodinspection = '';
    var url = GetModeOfDefects;
    
    var partcode = ($("#partcode").val() == "") ? $('#partcodelbl').val() : $("#partcode").val();
    var data = {
        _token: token,
        invoiceno: $('#invoice_no').val(),
        item: partcode,
        lot_no: $('#lot_no').val(),
        iqc_id: $('#iqc_result_id').val()
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        if (data.hasOwnProperty('msg')) {
            msg(data.msg, 'failed');
        } else {
            //getModinspectionlist(data);
            mod_of_defects = data;
            defectsDataTables(mod_of_defects)  
        }
    }).fail(function (data, textStatus, jqXHR) {
        console.log((jqXHR));
        msg("There's some error while processing.", 'failed');
    });
}

function getAllChecked(element) {
    var chkArray = [];

    $(element + ":checked").each(function () {
        chkArray.push($(this).val());
    });

    return chkArray;
}

function getInspectionMonth(date) {
    var monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    var d = new Date(date);
    return monthNames[d.getMonth()];
}

function requiredFields(requiredClass) {
    var reqlength = $(requiredClass).length;
    var value = $(requiredClass).filter(function () {
        var id = this.id;
        if (this.value == '') {
            console.log(id);
        }
        return this.value != '';
    });

    //console.log($(requiredClass));

    if (value.length !== reqlength) {
        return false;
    } else {
        console.log('true');
        return true;
    }
}

function searchItemInspection() {
    var tblforiqcinspection = '';
    $('#tblforiqcinspection').html('');
    var url = GetIQCInspectionSearch;
    
    var data = {
        _token: token,
        item: $('#search_partcode').val(),
        from: $('#search_from').val(),
        to: $('#search_to').val()
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        getIQCdataTable(data, tblforiqcinspection);
        $('#SearchModal').modal('hide');
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

//REQUALIFICATION
function getItemsRequalification() {
    var url = GetIQCRequaliItemData;
    
    var data = {
        _token: token,
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        if (data == null || data == "") {
            $('#er_partcode_rq').html("No Inspections Available");
        } else {
            $('#partcode_rq').select2({
                data: data,
                placeholder: "Select an Item"
            });
        }
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getAppNo() {
    var url = GetIQCRequaliAppNo;
    
    var data = {
        _token: token,
        item: $('#partcode_rq').val()
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        if (data == null || data == "") {
            $('#er_app_no_rq').html("No Available Application Number.");
        } else {
            $('#app_no_rq').select2({
                data: data,
                placeholder: "Select an Item"
            });
        }
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getDetailsRequalification() {
    var url = GetIQCRequaliItemDetails;
    
    var data = {
        _token: token,
        item: $('#partcode_rq').val(),
        app_no: $('#app_no_rq').val()
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        var details = data.details;
        $('#partname_rq').val(details.partname);
        $('#supplier_rq').val(details.supplier);
        $('#app_date_rq').val(details.app_date);
        $('#app_time_rq').val(details.app_time);
        $('#lot_qty_rq').val(details.lot_qty);

        $('#lot_no_rq').select2({
            tags: true,
            data: data.lots,
            placeholder: 'Select Lot Number'
        });

        $('#lot_no_rq').select2('val', data.lotval);
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function calculateLotQtyRequalification(lotno) {
    var url = GetIQCRequaliLotQty;
    
    var data = {
        _token: token,
        app_no: $('#app_no_rq').val(),
        item: $('#partcode_rq').val(),
        lot_no: lotno
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $('#lot_qty_rq').val(data);
        console.log(data);
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getVisualInspectionRequalification() {
    var url = GetIQCRequaliVisualInspection;
    
    var data = {
        _token: token,
        app_no: $('#app_no_rq').val(),
        item: $('#partcode_rq').val(),
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $.each(data, function (i, x) {
            $('#date_ispected_rq').val(x.date_ispected);
            $('#ww_rq').val(x.ww);
            $('#fy_rq').val(x.fy);
            $('#time_ins_from_rq').val(x.time_ins_from);
            $('#time_ins_to_rq').val(x.time_ins_to);
            $('#shift_rq').select2('val', [x.shift]);
            $('#inspector_rq').val(x.inspector);
            $('#submission_rq').select2('val', [x.submission]);
            $('#judgement_rq').val(x.judgement);
            $('#lot_inspected_rq').val(x.lot_inspected);
            $('#lot_accepted_rq').val(x.lot_accepted);
            $('#no_of_defects_rq').val(x.no_of_defects);
            $('#remarks_rq').val(x.remarks);
        });

        if ($('#lot_accepted_rq').val() < 1) {
            $('#no_defects_label_rq').show();
            $('#no_of_defects_rq').show();
            $('#mode_defects_label_rq').show();
            $('#btn_mod_rq').show();
        } else {
            $('#no_defects_label_rq').hide();
            $('#no_of_defects_rq').hide();
            $('#mode_defects_label_rq').hide();
            $('#btn_mod_rq').hide();;
        }
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getDropdownsRequali() {
    var url = GetIQCRequaliDropdowns;
    
    var data = {
        _token: token
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $('#shift_rq').select2({
            data: data.shift,
            placeholder: "Select Shift"
        });
        $('#submission_rq').select2({
            data: data.submission,
            placeholder: "Select Submission"
        });
        $('#mod_rq').select2({
            data: data.mod,
            placeholder: "Select Mode of Defects"
        });
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function saveRequalification() {
    $('#loading').modal('show');
    if (requiredFields(':input.requiredRequali') == true) {
        var url = PostIQCRequalSaveInspection;
        

        var data = {
            _token: token,
            save_status: $('#save_status_rq').val(),
            id: $('#id_rq').val(),
            ctrlno: $('#ctrl_no_rq').val(),
            partcode: $('#partcode_rq').val(),
            partname: $('#partname_rq').val(),
            supplier: $('#supplier_rq').val(),
            app_date: $('#app_date_rq').val(),
            app_time: $('#app_time_rq').val(),
            app_no: $('#app_no_rq').val(),
            lot_no: $('#lot_no_rq').val(),
            lot_qty: $('#lot_qty_rq').val(),
            date_inspected: $('#date_ispected_rq').val(),
            ww: $('#ww_rq').val(),
            fy: $('#fy_rq').val(),
            time_ins_from: $('#time_ins_from_rq').val(),
            time_ins_to: $('#time_ins_to_rq').val(),
            shift: $('#shift_rq').val(),
            inspector: $('#inspector_rq').val(),
            submission: $('#submission_rq').val(),
            judgement: $('#judgement_rq').val(),
            lot_inspected: $('#lot_inspected_rq').val(),
            lot_accepted: $('#lot_accepted_rq').val(),
            no_of_defects: $('#no_of_defects_rq').val(),
            remarks: $('#remarks_rq').val(),
        };

        $.ajax({
            url: url,
            type: "POST",
            dataType: "JSON",
            data: data
        }).done(function (data, textStatus, jqXHR) {
            $('#loading').modal('hide');

            if (data.return_status == 'success') {
                msg(data.msg, 'success');
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            msg("There's some error while processing.", 'failed');
        }).always( function() {
            $('#loading').modal('hide');
        });
    } else {
        $('#loading').modal('hide');
        msg("Please fill out all required fields.", 'failed');
    }
}

function getRequalification(row) {
    var rq_inspection_body = '';
    $('#rq_inspection_body').html('');
    var url = GetIQCRequaliInspectionData;
    
    var data = {
        _token: token,
        row: row
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        getRequalidataTable(data, rq_inspection_body);
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getRequalidataTable(data, rq_inspection_body) {
    $.each(data, function (i, x) {
        rq_inspection_body = '<tr>' +
            '<td class="table-checkbox" style="width: 2%">' +
            '<input type="checkbox" class="checkboxes checitemrq" value="' + x.id + '"/>' +
            '</td>' +
            '<td>' +
            '<a href="javascript:;" class="btn btn-primary input-sm btn_editRequali" ' +
            'data-ctrl_no="' + x.ctrl_no_rq + '" ' +
            'data-partcode="' + x.partcode_rq + '" ' +
            'data-partname="' + x.partname_rq + '" ' +
            'data-supplier="' + x.supplier_rq + '" ' +
            'data-app_date="' + x.app_date_rq + '" ' +
            'data-app_time="' + x.app_time_rq + '" ' +
            'data-app_no="' + x.app_no_rq + '" ' +
            'data-lot_no="' + x.lot_no_rq + '" ' +
            'data-lot_qty="' + x.lot_qty_rq + '" ' +
            'data-date_ispected="' + x.date_ispected_rq + '" ' +
            'data-ww="' + x.ww_rq + '" ' +
            'data-fy="' + x.fy_rq + '" ' +
            'data-shift="' + x.shift_rq + '" ' +
            'data-time_ins_from="' + x.time_ins_from_rq + '" ' +
            'data-time_ins_to="' + x.time_ins_to_rq + '" ' +
            'data-inspector="' + x.inspector_rq + '" ' +
            'data-submission="' + x.submission_rq + '" ' +
            'data-judgement="' + x.judgement_rq + '" ' +
            'data-lot_inspected="' + x.lot_inspected_rq + '" ' +
            'data-lot_accepted="' + x.lot_accepted_rq + '" ' +
            'data-no_of_defects="' + x.no_of_defects_rq + '" ' +
            'data-remarks="' + x.remarks_rq + '"' +
            'data-id="' + x.id + '">' +
            '<i class="fa fa-edit"></i>' +
            '</a>' +
            '</td>' +
            '<td>' + x.ctrl_no_rq + '</td>' +
            '<td>' + x.partcode_rq + '</td>' +
            '<td>' + x.partname_rq + '</td>' +
            '<td>' + x.lot_no_rq + '</td>' +
            '<td>' + x.app_date_rq + '</td>' +
            '<td>' + x.app_time_rq + '</td>' +
            '<td>' + x.app_no_rq + '</td>' +
            '</tr>';
        $('#rq_inspection_body').append(rq_inspection_body);
    });
}

function iqcdbgetmodeofdefectsRequali() {
    $('#tblformodinspection').html('');
    var tblformodinspection = '';
    var url = GetIQCRequaliModeOfDefects;
    
    var data = {
        _token: token,
        item: $('#partcode_rq').val(),
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        getModrqlist(data);
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getModrqlist(data) {
    var cnt = 1;
    var no_of_defectives = 0;
    $.each(data, function (i, x) {
        tblformodrequalification = '<tr>' +
            '<td>' +
            '<input type="checkbox" class="modrq_checkitem checkboxes" value="' + x.id + '">' +
            '</td>' +
            '<td>' +
            '<a href="javascript:;" class="btn blue input-sm modrq_edititem" data-mod="' + x.mod + '" data-qty="' + x.qty + '" data-id="' + x.id + '">' +
            '<i class="fa fa-edit"></i>' +
            '</a>' +
            '</td>' +
            '<td>' + cnt + '</td>' +
            '<td>' + x.mod + '</td>' +
            '<td>' + x.qty + '</td>' +
            '</tr>';
        cnt++;
        no_of_defectives = parseFloat(no_of_defectives) + parseFloat(x.qty);
        $('#tblformodrequalification').append(tblformodrequalification);
    });

    $('#no_of_defects_rq').val(no_of_defectives);
    $('#status_requalification').val('ADD');
}

function saveModeOfDefectsRequali() {
    var url = PostIQCRequaliModeOfDefects;
    
    var data = {
        _token: token,
        item: $('#partcode_rq').val(),
        mod: $('#mod_rq').val(),
        qty: $('#qty_rq').val(),
        status: $('#status_requalification').val(),
        id: $('#id_requalification').val()
    };

    $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        if (data.return_status == "success") {
            msg(data.msg, 'success');
        } else {
            msg(data.msg, 'failed');
        }
        iqcdbgetmodeofdefectsRequali();
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function checkFile(fileName) {
    var ext = fileName.split('.').pop();
    if (ext == 'xls' || ext == 'XLS') {
        return true
    } else {
        return false;
    }
}

function deleteInspection() {
    var url = PostIQCInspectionDelete;
    
    var data = {
        id: getAllChecked('.iqc_checkitems'),
        _token: token
    }

    $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        if (data.return_status == "success") {
            msg(data.msg, 'success');
        } else {
            msg(data.msg, 'failed');
        }
        getIQCInspection(GetIQCInspectionData);
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    }).always( function() {
        $('#confirmDeleteModal').modal('hide');
    });
}

function deleteRequali() {
    var url = PostIQCRequaliDelete;
    
    var data = {
        id: getAllChecked('.checitemrq'),
        _token: token
    }

    $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        if (data.return_status == "success") {
            msg(data.msg, 'success');
        } else {
            msg(data.msg, 'failed');
        }
        getRequalification(5);
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    }).always( function() {
        $('#confirmDeleteModal').modal('hide');
    });
}

function deleteModRQ() {
    var url = PostIQCRequaliDeleteModeOfDefects;
    
    var data = {
        id: getAllChecked('.modrq_checkitem'),
        _token: token
    }

    $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        if (data.return_status == "success") {
            msg(data.msg, 'success');
        } else {
            msg(data.msg, 'failed');
        }
        iqcdbgetmodeofdefectsRequali();
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    }).always( function() {
        $('#confirmDeleteModal').modal('hide');
    });
}

function deleteModIns() {
    var url = PostIQCDeleteModeOfDefects;
    
    var data = {
        id: getAllChecked('.modinspection_checkitem'),
        _token: token
    }

    $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        if (data.return_status == "success") {
            msg(data.msg, 'success');
        } else {
            msg(data.msg, 'failed');
        }
        iqcdbgetmodeofdefectsinspection();
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    }).always( function() {
        $('#confirmDeleteModal').modal('hide');
    });
}

function deleteOnGoing(remarks) {
    var url = PostIQCDeleteOnGoing;
    
    // var lot_arr = [];
    var checkID = [];
    var table = $('#on-going-inspection').DataTable();

    for (var x = 0; x < table.context[0].aoData.length; x++) {
        var aoData = table.context[0].aoData[x];
        if (aoData.anCells !== null && aoData.anCells[0].firstChild.checked == true) {
            checkID.push(aoData.anCells[0].firstChild.value);
            //lot_nos += $(aoData.anCells[0].firstChild).attr('data-lot_no')+",";
        }
    }

    // var lastChar = lot_nos.substr(lot_nos.length - 1);

    // if (lastChar == ",") {
    //     lot_nos = lot_nos.slice(0, -1)
    // }

    // lot_arr = lot_nos.split(",");

    var data = {
        id: checkID,//getAllChecked('.ongiong_checkitems'),
        remarks: remarks,
        //lot_nos: lot_nos,
        _token: token
    }

    $('#loading').modal('show');

    $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        if (data.return_status == "success") {
            msg(data.msg, 'success');
        } else {
            msg(data.msg, 'failed');
        }
        getOnGoing();
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    }).always( function() {
        $('#loading').modal('hide');
    });
}

function openModeOfDefects() {
    if ($('#lot_accepted').val() == 0) {
        $('#no_defects_label').show();
        $('#no_of_defects').show();
        $('#mode_defects_label').show();
        $('#btn_mod_ins').show();
        $('#judgement').val('Rejected');
        $('.ngr_details').show();
    } else {
        $('#lot_accepted').val(1);
        $('#no_defects_label').hide();
        $('#no_of_defects').hide();
        $('#mode_defects_label').hide();
        $('#btn_mod_ins').hide();
        $('#judgement').val('Accepted');
        $('.ngr_details').hide();
        $('.ngr_buttons').hide();
    }
}

function samplingPlan_man() {
    var url = GetSamplingPlan;
    
    var data = {
        _token: token,
        soi: $('#severity_of_inspection_man').val(),
        il: $('#inspection_lvl_man').val(),
        aql: $('#aql_man').val(),
        lot_qty: $('#lot_qty_man').val()
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $('#accept_man').val(data.accept);
        $('#reject_man').val(data.reject);
        $('#sample_size_man').val(data.sample_size);
        $('#date_inspected_man').val(data.date_inspected);
        $('#lot_inspected_man').val(1);
        $('#inspector_man').val(data.inspector);
        $('#ww_man').val(data.workweek);
        getFiscalYear_man();
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getDropdowns_man() {
    var url = GetIQCDropdowns;
    
    var data = {
        _token: token
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $('#type_of_inspection_man').select2({
            data: data.tofinspection,
            placeholder: "Select Type of Inspection"
        });
        $('#severity_of_inspection_man').select2({
            data: data.sofinspection,
            placeholder: "Select Severity of Inspection"
        });
        $('#inspection_lvl_man').select2({
            data: data.inspectionlvl,
            placeholder: "Select Inspection Level"
        });
        $('#aql_man').select2({
            data: data.aql,
            placeholder: "Select AQL"
        });
        $('#shift_man').select2({
            data: data.shift,
            placeholder: "Select Shift"
        });
        $('#submission_man').select2({
            data: data.submission,
            placeholder: "Select Submission"
        });
        $('#submission_man').val('1st');
        $('#submission_man').trigger('change');

        $('#mod_inspection_man').select2({
            data: data.mod,
            placeholder: "Select Mode of Defects"
        });
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getFiscalYear_man() {
    var date = new Date();
    var month = date.getMonth();
    var year = date.getFullYear();

    if (month < 3) {
        year = year - 1;
    }

    $('#fy_man').val(year);
}

function openModeOfDefects_man() {
    if ($('#lot_accepted_man').val() == 0) {
        $('#no_defects_label_man').show();
        $('#no_of_defects_man').show();
        $('#mode_defects_label_man').show();
        $('#btn_mod_ins_man').show();
        $('#judgement_man').val('Rejected');
    } else {
        $('#lot_accepted_man').val(1);
        $('#no_defects_label_man').hide();
        $('#no_of_defects_man').hide();
        $('#mode_defects_label_man').hide();
        $('#btn_mod_ins_man').hide();
        $('#judgement_man').val('Accepted');
    }
}

function saveInspection_man() {
    $('#loading').modal('show');

    //if (requiredFields(':input.required') == true) {
    var url = PostSaveIQCInspection;
    
    var data = {
        _token: token,
        save_status: $('#save_status_man').val(),
        id: $('#iqc_result_id_man').val(),
        invoice_no: $('#invoice_no_man').val(),
        partcode: $('#partcode_man').val(),
        partname: $('#partname_man').val(),
        supplier: $('#supplier_man').val(),
        app_date: $('#app_date_man').val(),
        app_time: $('#app_time_man').val(),
        app_no: $('#app_no_man').val(),
        lot_no: $('#lot_no_man').val(),
        lot_qty: $('#lot_qty_man').val(),
        type_of_inspection: $('#type_of_inspection_man').val(),
        severity_of_inspection: $('#severity_of_inspection_man').val(),
        inspection_lvl: $('#inspection_lvl_man').val(),
        aql: $('#aql_man').val(),
        accept: $('#accept_man').val(),
        reject: $('#reject_man').val(),
        date_inspected: $('#date_inspected_man').val(),
        ww: $('#ww_man').val(),
        fy: $('#fy_man').val(),
        time_ins_from: $('#time_ins_from_man').val(),
        time_ins_to: $('#time_ins_to_man').val(),
        shift: $('#shift_man').val(),
        inspector: $('#inspector_man').val(),
        submission: $('#submission_man').val(),
        judgement: $('#judgement_man').val(),
        lot_inspected: $('#lot_inspected_man').val(),
        lot_accepted: $('#lot_accepted_man').val(),
        sample_size: $('#sample_size_man').val(),
        no_of_defects: $('#no_of_defects_man').val(),
        remarks: $('#remarks_man').val(),
        classification: $('#classification_man').val(),
    };

    $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $('#loading').modal('hide');

        if (data.return_status == 'success') {
            msg(data.msg, 'success');
            clear();
            $('#ManualModal').modal('hide');
            getIQCInspection(GetIQCInspectionData);
            getOnGoing();
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        msg("There's some error while processing.", 'failed');
    }).always( function() {
        $('#loading').modal('hide');
    });
    // } else {
    // 	$('#loading').modal('hide');
    // 	msg("Please fill out all required fields.",'failed');
    // }	
}

function getIQCworkWeek() {
    var url = GetWorkWeek;
    
    var data = {
        _token: token
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $('#ww').val(data.workweek);
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function checkAllCheckboxesInDispoTable(tbl_id, check_all_id, check_item_class, delete_btn_id) {
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

// SORTING
function SortingDataTable(data) {
    $('#tbl_sorting').DataTable().clear();
    $('#tbl_sorting').DataTable().destroy();
    $('#tbl_sorting').DataTable({
        data: data,
        searching: false,
        lengthChange: false,
        columns: [
            {
                data: function (data) {
                    if (data.id !== '') {
                        return '<input type="checkbox" class="sorting_check_item" value="' + data.id + '"/>';
                    }
                    return '';
                }, orderable: false, searchable: false
            },
            { 
                data: function (data) {
                    if (data.id == '') {
                        return '<button type="button" class="btn btn-sm red btn_sorting_remove"><i class="fa fa-times"></button>';
                    }
                    return '<button type="button" class="btn btn-sm blue btn_sorting_edit"><i class="fa fa-edit"></button>';
                }, orderable: false, searchable: false
            },
            { data: 'lot_no', orderable: false, searchable: false },
            { data: 'good_qty', orderable: false, searchable: false },
            { data: 'ng_qty', orderable: false, searchable: false }, 
            { data: 'actual_qty', orderable: false, searchable: false },
            { data: 'category', orderable: false, searchable: false },
            { data: 'disposal_date', orderable: false, searchable: false },
            { data: 'disposal_slip_no', orderable: false, searchable: false },
            { data: 'ngr_control_no', orderable: false, searchable: false },
            { data: 'packinglist_no', orderable: false, searchable: false },
            { data: 'remarks', orderable: false, searchable: false },
        ],
        order: [[2,'asc']]
    });

}

function SortingData(iqc_id) {
    $.ajax({
        url: GetSortingData,
        type: "GET",
        data: {
            _token: token,
            iqc_id: iqc_id
        }
    }).done(function (data, textStatus, jqXHR) {
        if (data.hasOwnProperty('msg')) {
            msg(data.msg,data.status);
        } else {
            sorting_data_arr = data;

            if (sorting_data_arr.length > 0) {
                $('#btn_save_sorting').prop('disabled', false);
                $('#btn_cancel_sorting').prop('disabled', false);
            }

            SortingDataTable(data);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        msg("There's some error while processing.", 'failed');
    });
}



// REWORK
function ReworkDataTable(data) {
    $('#tbl_rework').DataTable().clear();
    $('#tbl_rework').DataTable().destroy();
    $('#tbl_rework').DataTable({
        data: data,
        searching: false,
        lengthChange: false,
        columns: [
            {
                data: function (data) {
                    if (data.id !== '') {
                        return '<input type="checkbox" class="rework_check_item" value="' + data.id + '"/>';
                    }
                    return '';
                }, orderable: false, searchable: false
            },
            {
                data: function (data) {
                    if (data.id == '') {
                        return '<button type="button" class="btn btn-sm red btn_rework_remove"><i class="fa fa-times"></button>';
                    }
                    return '<button type="button" class="btn btn-sm blue btn_rework_edit"><i class="fa fa-edit"></button>';
                }, orderable: false, searchable: false
            },
            { data: 'lot_no', orderable: false, searchable: false },
            { data: 'good_qty', orderable: false, searchable: false },
            { data: 'ng_qty', orderable: false, searchable: false },
            { data: 'actual_qty', orderable: false, searchable: false },
            { data: 'category', orderable: false, searchable: false },
            { data: 'disposal_date', orderable: false, searchable: false },
            { data: 'disposal_slip_no', orderable: false, searchable: false },
            { data: 'ngr_control_no', orderable: false, searchable: false },
            { data: 'packinglist_no', orderable: false, searchable: false },
            { data: 'remarks', orderable: false, searchable: false },
        ],
        order: [[2, 'asc']]
    });

}

function ReworkData(iqc_id) {
    $.ajax({
        url: GetReworkData,
        type: "GET",
        data: {
            _token: token,
            iqc_id: iqc_id
        }
    }).done(function (data, textStatus, jqXHR) {
        if (data.hasOwnProperty('msg')) {
            msg(data.msg, data.status);
        } else {
            rework_data_arr = data;

            if (rework_data_arr.length > 0) {
                $('#btn_save_rework').prop('disabled', false);
                $('#btn_cancel_rework').prop('disabled', false);
            }

            ReworkDataTable(data);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        msg("There's some error while processing.", 'failed');
    });
}


// RTV
function RTVDataTable(data) {
    $('#tbl_rtv').DataTable().clear();
    $('#tbl_rtv').DataTable().destroy();
    $('#tbl_rtv').DataTable({
        data: data,
        searching: false,
        lengthChange: false,
        columns: [
            {
                data: function (data) {
                    if (data.id !== '') {
                        return '<input type="checkbox" class="rtv_check_item" value="' + data.id + '"/>';
                    }
                    return '';
                }, orderable: false, searchable: false
            },
            {
                data: function (data) {
                    if (data.id == '') {
                        return '<button type="button" class="btn btn-sm red btn_rtv_remove"><i class="fa fa-times"></button>';
                    }
                    return '<button type="button" class="btn btn-sm blue btn_rtv_edit"><i class="fa fa-edit"></button>';
                }, orderable: false, searchable: false
            },
            { data: 'lot_no', orderable: false, searchable: false },
            { data: 'total_qty', orderable: false, searchable: false },
            { data: 'rtv_qty', orderable: false, searchable: false },
            { data: 'category', orderable: false, searchable: false },
            { data: 'disposal_date', orderable: false, searchable: false },
            { data: 'disposal_slip_no', orderable: false, searchable: false },
            { data: 'ngr_control_no', orderable: false, searchable: false },
            { data: 'packinglist_no', orderable: false, searchable: false },
            { data: 'remarks', orderable: false, searchable: false },
        ],
        order: [[2, 'asc']]
    });

}

function RTVData(iqc_id) {
    $.ajax({
        url: GetrtvData,
        type: "GET",
        data: {
            _token: token,
            iqc_id: iqc_id
        }
    }).done(function (data, textStatus, jqXHR) {
        if (data.hasOwnProperty('msg')) {
            msg(data.msg, data.status);
        } else {
            rtv_data_arr = data;

            if (rtv_data_arr.length > 0) {
                $('#btn_save_rtv').prop('disabled', false);
                $('#btn_cancel_rtv').prop('disabled', false);
            }

            RTVDataTable(data);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        msg("There's some error while processing.", 'failed');
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

function defectsDataTables(data) {
    var qty = 0;
    $('#tbl_modeofdefect').DataTable().clear();
    $('#tbl_modeofdefect').DataTable().destroy();
    $('#tbl_modeofdefect').DataTable({
        data: data,
        searching: false,
        lengthChange: false,
        columns: [
            {
                data: function (data) {
                    if (data.id !== '') {
                        return '<input type="checkbox" class="defect_check_item" value="' + data.id + '" data-defects="' + data.mod + '"/>';
                    }
                    return '';
                }, orderable: false, searchable: false
            },
            {
                data: function (data) {                    
                    return '<button type="button" class="btn btn-sm blue modinspection_edititem"><i class="fa fa-edit"></button>';
                }, orderable: false, searchable: false
            },
            { data: 'mod', orderable: false, searchable: false },
            { data: 'qty', orderable: false, searchable: false },            
            { data: 'lot_no', orderable: false, searchable: false },
        ],
        order: [[2, 'asc']],
        rowCallback: function(row,data) {
            qty += (data.qty == null || data.qty == "")? 0: parseFloat(data.qty);
        }
    });

    $('#no_of_defects').val(qty);
}

function checkIfExistInArray(arrData, lot, mod) {
	var exist = 0;
	$.each(arrData, function (i, x) {
		if (x.lot_no == lot && x.mod == mod) {
			exist++;
		}
	});

	return exist;
}

function uniqBy(array, key) {
    var seen = {};
    return array.filter(function(item) {
        var k = key(item);
        return seen.hasOwnProperty(k) ? false : (seen[k] = true);
    })
}


// $('#time_ins_hour').on('change', function () {
//     var h = $('#time_ins_hour').val();
//     var m = $('#time_ins_mins').val();
//     $('#time_ins_from').val(h + ':' + m);
// })
// $('#time_ins_mins').on('change', function () {
//     var h = $('#time_ins_hour').val();
//     var m = $('#time_ins_mins').val();
//     $('#time_ins_from').val(h + ':' + m);
// })

// $('#time_ins_hour_to').on('change', function () {
//     var h = $('#time_ins_hour_to').val();
//     var m = $('#time_ins_mins_to').val();
//     $('#time_ins_to').val(h + ':' + m);
// })
// $('#time_ins_mins_to').on('change', function () {
//     var h = $('#time_ins_hour_to').val();
//     var m = $('#time_ins_mins_to').val();
//     $('#time_ins_to').val(h + ':' + m);
// })

// $('.time_dp').on('click', function () {
//     $('#time_ins_hour').val('');

//     for (let i = 0; i <= 23; i++) {
//         let zero = i < 10 ? '0' : '';
//         var h = zero + i;
//         hour = '<option class="hour_select">' + h + '</option>';
//         $('#time_ins_hour').append(hour);
//         $('#time_ins_hour_to').append(hour);
//     }
// })

