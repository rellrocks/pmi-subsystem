var mr_idArr = [];
var inv_idArr = [];
var _lot_qty = 0;

$(function () {
    $.fn.modal.Constructor.prototype.enforceFocus = function () { };
    getIQCInspection(GetIQCInspectionData);
    getOnGoing();

    // $('#partcodelbl').hide();
    // $('#partcode').hide();
    // //$('#partcode').select2('container').hide();

    // $('#btn_backModal').on('click', function() {
    // 	$('#partcodelbl').hide();
    // 	$('#partcode').hide();
    // 	//$('#partcode').select2('container').hide();
    // });

    $('#btn_lotno').on('click', function () {
        $('#LotNoModal').modal('show');
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

    $('#time_ins_from').on('change', function () {
        var time = setTime($(this).val());
        if (time.includes('::')) {
            $(this).val(time.replace('::', ':'));
        } else {
            $(this).val(time);
        }
    });

    $('#time_ins_to').on('change', function () {
        var time = setTime($(this).val());
        if (time.includes('::')) {
            $(this).val(time.replace('::', ':'));
        } else {
            $(this).val(time);
        }
    });

    $('#time_ins_from_man').on('change', function () {
        var time = setTime($(this).val());
        if (time.includes('::')) {
            $(this).val(time.replace('::', ':'));
        } else {
            $(this).val(time);
        }
    });

    $('#time_ins_to_man').on('change', function () {
        var time = setTime($(this).val());
        if (time.includes('::')) {
            $(this).val(time.replace('::', ':'));
        } else {
            $(this).val(time);
        }
    });

    // INSPECTION SIDE
    $('.timepicker-no-seconds').timepicker({
        autoclose: true,
        minuteStep: 5
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

    $('#lot_no').select2({
        placeholder: 'Select Lot No.',
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCInspectionLotNo,
            data: function (params) {
                var invoiceno = $('#invoice_no').val() || "";
                var partcode = $('#partcode').val() || "";
                var lot_no = $('#lot_no').val() || "";
                var query = "";

                if ((invoiceno != "" || invoiceno != null) && (partcode != "" || partcode != null)) {
                    if (lot_no == "") {
                        lot_no = params.term || "";
                    }
                    query = "select l.lot_no as id, \
                                l.lot_no as `text`, \
                                l.qty as qty, \
                                l.id as mr_id, \
                                (select id from tbl_wbs_inventory where loc_batch_id = l.id limit 1) as inv_id, \
                                'LR' as `source` \
                                from tbl_wbs_local_receiving_batch as l \
                                where l.invoice_no = '"+ invoiceno + "' \
                                and l.item = '"+ partcode + "' \
                                union \
                                select m.lot_no as id, \
                                m.lot_no as `text`, \
                                m.qty as qty, \
                                m.id as mr_id, \
                                (select id from tbl_wbs_inventory where mat_batch_id = m.id limit 1) as inv_id, \
                                'MR' as `source` \
                                from tbl_wbs_material_receiving_batch as m \
                                where m.invoice_no = '"+ invoiceno + "' \
                                and m.item = '"+ partcode + "'";
                    // query = "SELECT DISTINCT m.lot_no as id, m.lot_no as `text`, m.id as lot_id, i.id as inv_id \
                    //         FROM tbl_wbs_material_receiving_batch as m \
                    //         join tbl_wbs_inventory as i \
                    //         on i.mat_batch_id = m.id \
                    //         WHERE m.not_for_iqc = 0 \
                    //         AND m.invoice_no = '"+ invoiceno +"' \
                    //         AND m.item = '"+ partcode +"' \
                    //         AND m.lot_no LIKE '%"+ lot_no +"%' \
                    //         UNION \
                    //         SELECT DISTINCT l.lot_no as id, l.lot_no as `text`, l.id as lot_id, i.id as inv_id \
                    //         FROM tbl_wbs_local_receiving_batch as l \
                    //         join tbl_wbs_inventory as i \
                    //         on i.loc_batch_id = l.id \
                    //         WHERE l.not_for_iqc = 0 \
                    //         AND l.invoice_no = '"+ invoiceno +"' \
                    //         AND l.item = '"+ partcode +"' \
                    //         AND l.lot_no LIKE '%"+ lot_no +"%'";
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
                mr_idArr = [];
                inv_idArr = [];

                $.each(data, function (i, x) {
                    mr_idArr.push(x.mr_id);
                    inv_idArr.push(x.inv_id);
                });
                console.log(mr_idArr);
                console.log(inv_idArr);
                return {
                    results: data
                };
            },
        }
    });

    $('#type_of_inspection').select2({
        placeholder: "Select Type of Inspection",
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCSelect2Data,
            data: function (params) {
                var query = "";
                let name = "TypeofInspection";

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

    $('#severity_of_inspection').select2({
        placeholder: "Select Severity of Inspection",
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCSelect2Data,
            data: function (params) {
                var query = "";
                let name = "severityofinspection";

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

    $('#inspection_lvl').select2({
        placeholder: "Select Inspection Level",
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCSelect2Data,
            data: function (params) {
                var query = "";
                let name = "InspectionLevel";

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

    $('#aql').select2({
        placeholder: "Select AQL",
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCSelect2Data,
            data: function (params) {
                var query = "";
                let name = "AQL";

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

    $('#shift').select2({
        placeholder: "Select Shift",
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCSelect2Data,
            data: function (params) {
                var query = "";
                let name = "Shift";

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

    $('#submission').select2({
        placeholder: "Select Submission",
        dropdownParent: $('#IQCresultModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetIQCSelect2Data,
            data: function (params) {
                var query = "";
                let name = "Submission";

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
        placeholder: "Select Mode of Defects",
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
            $('.ngr_buttons').show();
        } else {
            $('.ngr_buttons').hide();
        }
    })

    $('#btn_iqcresult').on('click', function () {
        clear();
        // $('#invoice_no').prop('readonly',false);
        // $('#partcode').prop('readonly',true);
        // $('#lot_no').prop('readonly',true);
        // 
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
        //getItems();
    });

    $('#partcode').on('change', function () {
        getItemDetails();
    });

    $('#lot_no').on('change', function (e) {
        calculateLotQty($(this).val());

        $('#inv_id').val(inv_idArr);
        $('#mr_id').val(mr_idArr);
    });

    $('#severity_of_inspection').on('change', function () {
        samplingPlan();
    });

    $('#inspection_lvl').on('change', function () {
        samplingPlan();
    });

    $('#aql').on('change', function () {
        samplingPlan();
    });

    $('#btn_clearmodal').on('click', function () {
        clear();
    });

    $('#btn_mod_ins').on('click', function () {
        iqcdbgetmodeofdefectsinspection();
        $('#mod_inspectionModal').modal('show');
    });

    // SORTING
    $('#btn_sorting_details').on('click', function () {
        $('#sorting_Modal').modal('show');
    });


    // REWORK
    $('#btn_rework_details').on('click', function () {
        $('#rework_Modal').modal('show');
    });

    //RTV
    $('#btn_rtv_details').on('click', function () {
        $('#rtv_Modal').modal('show');
    });


    $('#bt_save_modeofdefectsinspection').on('click', function () {
        saveModeOfDefectsInspection();
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
        var data = $('#iqcdatatable').DataTable().row($(this).parents('tr')).data();
        console.log(data);

        $('#invoice_no').prop('readonly', true);
        $('#invoice_no').val(data.invoice_no);

        var $partcode = $("<option selected='selected'></option>").val(data.partcode).text(data.partcode);
        $("#partcode").append($partcode).trigger('change');
        $('#partcodelbl').val(data.partcode);
        getItemDetailsEdit();
        //$('#partcode').hide();

        $('#partname').val(data.partname);
        $('#supplier').val(data.supplier);
        $('#app_date').val(data.app_date);
        $('#app_time').val(data.app_time);
        $('#app_no').val(data.app_no);

        $("#lot_no").val(null).trigger('change');

        var $lot_no = $("<option selected='selected'></option>").val([data.lot_no]).text([data.lot_no]);
        $("#lot_no").append($lot_no).trigger('change');

        var $type_of_inspection = $("<option selected='selected'></option>").val(data.type_of_inspection).text(data.type_of_inspection);
        $("#type_of_inspection").append($type_of_inspection).trigger('change');

        var $severity_of_inspection = $("<option selected='selected'></option>").val(data.severity_of_inspection).text(data.severity_of_inspection);
        $("#severity_of_inspection").append($severity_of_inspection).trigger('change');

        var $inspection_lvl = $("<option selected='selected'></option>").val(data.inspection_lvl).text(data.inspection_lvl);
        $("#inspection_lvl").append($inspection_lvl).trigger('change');

        var $aql = $("<option selected='selected'></option>").val(data.aql).text(data.aql);
        $("#aql").append($aql).trigger('change');

        $('#accept').val(data.accept);
        $('#reject').val(data.reject);
        $('#date_inspected').val(data.date_ispected);
        $('#ww').val(data.ww);
        $('#fy').val(data.fy);
        $('#time_ins_from').val(data.time_ins_from);
        $('#time_ins_to').val(data.time_ins_to);

        var $shift = $("<option selected='selected'></option>").val(data.shift).text(data.shift);
        $("#shift").append($shift).trigger('change');

        $('#inspector').val(data.inspector);

        var $submission = $("<option selected='selected'></option>").val(data.submission).text(data.submission);
        $("#submission").append($submission).trigger('change');


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

        var mr_id = data.mr_id.toString();
        var inv_id = data.inv_id.toString();

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
        $('#iqc_result_id').val(data.id);

        //$('#partcodelbl').show();
        //$('#partcode').hide();
        //$('#partcode').select2('container').hide();

        openModeOfDefects();

        $('#judgement').val(data.judgement);

        if (data.judgement == "Special Accept") {
            //$('#msg_special_accept').removeAttr('hidden');
            $('#btn_savemodal').attr('disabled', 'true');
        } else {
            $('#btn_savemodal').removeAttr('disabled');
            //$('#msg_special_accept').attr('hidden', 'true');
        }

        if (data.judgement == "Sorted") {
            $('#btn_savemodal').attr('disabled', 'true');
            $('#btn_sorting_details').removeClass('hidden');
            $('#btn_sorting_details').show();
        } else {
            $('#btn_savemodal').removeAttr('disabled');
            $('#btn_sorting_details').addClass('hidden');
            $('#btn_sorting_details').hide();
        }

        if (data.judgement == "Reworked") {
            $('#btn_savemodal').attr('disabled', 'true');
            $('#btn_rework_details').removeClass('hidden');
            $('#btn_rework_details').show();
        } else {
            $('#btn_savemodal').removeAttr('disabled');
            $('#btn_rework_details').addClass('hidden');
            $('#btn_rework_details').hide();
        }

        if (data.judgement == "RTV") {
            $('#btn_savemodal').attr('disabled', 'true');
            $('#btn_rtv_details').removeClass('hidden');
            $('#btn_rtv_details').show();
        } else {
            $('#btn_savemodal').removeAttr('disabled');
            $('#btn_rtv_details').addClass('hidden');
            $('#btn_rtv_details').hide();
        }

        $check_judgment = ["Rejected", "Sorted", "Reworked", "RTV"];

        //check if the judgement is rejected
        if ($check_judgment.includes(data.judgement)) {
            $('.ngr_details').show();
            if (data.judgement == 'Rejected' && (data.ngr_status_id == 14 || data.ngr_status_id == 23)) {
                $('.ngr_buttons').show();
            } else {
                $('.ngr_buttons').hide();
            }
        } else {
            $('.ngr_details').hide();
            $('.ngr_buttons').hide();
        }

        $('#IQCresultModal').modal('show');
    });

    $('#tblforongoing').on('click', '.btn_editongiong', function () {
        var data = $('#on-going-inspection').DataTable().row($(this).parents('tr')).data();
        console.log(data);

        $('#invoice_no').prop('readonly', true);
        $('#invoice_no').val(data.invoice_no);
        $('#partcodelbl').val(data.partcode);

        var $partcode = $("<option selected='selected'></option>").val(data.partcode).text(data.partcode);
        $("#partcode").append($partcode).trigger('change');

        getItemDetailsEdit(data.partcode);

        $('#partname').val(data.partname);
        $('#supplier').val(data.supplier);
        $('#app_date').val(data.app_date);
        $('#app_time').val(data.app_time);
        $('#app_no').val(data.app_no);

        $("#lot_no").val(null).trigger('change');
        var $lot_no = $("<option selected='selected'></option>").val([data.lot_no]).text([data.lot_no]);
        $("#lot_no").append($lot_no).trigger('change');

        var $type_of_inspection = $("<option selected='selected'></option>").val(data.type_of_inspection).text(data.type_of_inspection);
        $("#type_of_inspection").append($type_of_inspection).trigger('change');

        var $severity_of_inspection = $("<option selected='selected'></option>").val(data.severity_of_inspection).text(data.severity_of_inspection);
        $("#severity_of_inspection").append($severity_of_inspection).trigger('change');

        var $inspection_lvl = $("<option selected='selected'></option>").val(data.inspection_lvl).text(data.inspection_lvl);
        $("#inspection_lvl").append($inspection_lvl).trigger('change');

        var $aql = $("<option selected='selected'></option>").val(data.aql).text(data.aql);
        $("#aql").append($aql).trigger('change');

        $('#accept').val(data.accept);
        $('#reject').val(data.reject);
        $('#date_inspected').val(data.date_ispected);
        $('#ww').val(data.ww);
        $('#fy').val(data.fy);
        $('#time_ins_from').val(data.time_ins_from);
        $('#time_ins_to').val(data.time_ins_to);

        var $shift = $("<option selected='selected'></option>").val(data.shift).text(data.shift);
        $("#shift").append($shift).trigger('change');

        $('#inspector').val(data.inspector);

        var $submission = $("<option selected='selected'></option>").val(data.submission).text(data.submission);
        $("#submission").append($submission).trigger('change');
        $('#judgement').val(data.judgement);
        $('#lot_inspected').val(data.lot_inspected);
        $('#lot_accepted').val(data.lot_accepted);
        $('#sample_size').val(data.sample_size);
        $('#no_of_defects').val(data.no_of_defects);
        $('#remarks').val(data.remarks);

        $('#mr_id').val(data.mr_id);
        $('#inv_id').val(data.inv_id);

        $('#lot_qty').val(data.lot_qty);

        var mr_id = data.mr_id.toString();
        var inv_id = data.inv_id.toString();

        mr_idArr = mr_id.split(',');
        inv_idArr = inv_id.split(',');

        $('#no_defects_label').hide();
        $('#no_of_defects').hide();
        $('#mode_defects_label').hide();
        $('#btn_mod_ins').hide();

        $('#save_status').val('EDIT');
        $('#iqc_result_id').val(data.id);

        $('#partcodelbl').hide();
        $('#partcode').show();
        //$('#partcode').select2('container').show();

        openModeOfDefects();
        getIQCworkWeek();
        getFiscalYear();

        $('.ngr_details').hide();
        $('.ngr_buttons').hide();

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
            }).fail(function (data, textStatus, jqXHR) {
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
    });

    $('#btn_delete_inspected').on('click', function () {
        $('#delete_type').val('inspection');
        $('#confirmDeleteModal').modal('show');
    });

    $('#btn_delete_ongoing').on('click', function () {
        $('#delete_type').val('on-going');
        $('#confirmDeleteModal').modal('show');
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
        window.location.href = pdfURL;
    });

    $('#btn_excel').on('click', function () {
        window.location.href = excelURL;
    });

    $('#btn_searchHistory').on('click', function () {
        var tblhistorybody = '';
        $('#tblhistorybody').html('');
        var url = GetIQCInspectionHistory;
        var token = token;
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
        iqcdbgetmodeofdefectsinspection();
        $('#mod_inspectionModal').modal('show');
    });

    $('#lot_accepted_man').on('change', function () {
        openModeOfDefects_man();
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
                }, orderable: false, searchable: false, name: "id"
            },
            {
                data: function (data) {
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
                                data-classification="'+ data.classification + '"> \
                                <i class="fa fa-edit"></i> \
                            </a>';
                }, orderable: false, searchable: false, name: "action"
            },
            { data: 'judgement', name: 'judgement' },
            { data: 'ngr_status', name: 'ngr_status' },
            { data: 'ngr_disposition', name: 'ngr_disposition' },
            { data: 'ngr_control_no', name: 'ngr_control_no' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'inspector', name: 'inspector' },
            { data: 'date_ispected', name: 'date_ispected' },
            {
                data: function (data) {
                    return data.time_ins_from + ' - ' + data.time_ins_to;
                }, name: 'time_ins_from'
            },
            { data: 'app_no', name: 'app_no' },
            { data: 'app_date', name: 'app_date' },
            { data: 'app_time', name: 'app_time' },
            { data: 'fy', name: 'fy' },
            { data: 'ww', name: 'ww' },
            { data: 'submission', name: 'submission' },
            { data: 'partcode', name: 'partcode' },
            { data: 'partname', name: 'partname' },
            { data: 'supplier', name: 'supplier' },
            { data: 'lot_no', name: 'lot_no' },
            { data: 'lot_qty', name: 'lot_qty' },
            { data: 'type_of_inspection', name: 'type_of_inspection' },
            { data: 'severity_of_inspection', name: 'severity_of_inspection' },
            { data: 'inspection_lvl', name: 'inspection_lvl' },
            { data: 'accept', name: 'accept' },
            { data: 'reject', name: 'reject' },
            { data: 'shift', name: 'shift' },
            { data: 'lot_inspected', name: 'lot_inspected' },
            { data: 'lot_accepted', name: 'lot_accepted' },
            { data: 'sample_size', name: 'sample_size' },
            { data: 'no_of_defects', name: 'no_of_defects' },
            { data: 'remarks', name: 'remarks' },
            { data: 'classification', name: 'classification' },
            { data: 'updated_at', name: 'updated_at' }
        ],
        aoColumnDefs: [
            {
                aTargets: [2], // You actual column with the string 'America'
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).css('font-weight', '700');
                    if (sData == "Accepted") {
                        $(nTd).css('background-color', '#0000ff');
                        $(nTd).css('color', '#fff');
                    }
                    if (sData == "Rejected" | sData == "RTV") {
                        $(nTd).css('background-color', '#ff0000');
                        $(nTd).css('color', '#fff');
                    }
                    if (sData == "On-going") {
                        $(nTd).css('background-color', '#3598dc');
                        $(nTd).css('color', '#fff');
                    }
                    if (sData == "Special Accept") {
                        $(nTd).css('background-color', '#00ff00');
                        $(nTd).css('color', '#fff');
                    }
                    if (sData == "Sorted") {
                        $(nTd).css('background-color', '#ff9933');
                        $(nTd).css('color', '#fff');
                    }
                    if (sData == "Reworked") {
                        $(nTd).css('background-color', '#ff33cc');
                        $(nTd).css('color', '#fff');
                    }
                },
            }
        ],
        order: [[33, 'desc']]
    });
}

function getOnGoing() {
    $('#on-going-inspection').dataTable().fnClearTable();
    $('#on-going-inspection').dataTable().fnDestroy();
    $('#on-going-inspection').DataTable({
        processing: true,
        serverSide: true,
        ajax: GetIQCOnGoing,
        columns: [
            {
                data: function (data) {
                    return '<input type="checkbox" class="ongiong_checkitems" value="' + data.id + '"/>';
                }, orderable: false, searchable: false, name: "id"
            },
            { 
                data: function (data) {
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
            { data: 'lot_no', name: 'lot_no' },
            { data: 'lot_qty', name: 'lot_qty' },
            { 
                data: function(data) {
                    var created_at = data.created_at;

                    if (created_at.includes("00:00:00") ) {
                        return created_at.substring(0, 10);
                    }

                    return created_at;
                }, name: 'created_at'
            },
        ],
        aoColumnDefs: [
            {
                aTargets: [2], // You actual column with the string 'America'
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).css('font-weight', '700');
                    if (sData == "Accepted") {
                        $(nTd).css('background-color', '#c49f47');
                        $(nTd).css('color', '#fff');
                    }
                    if (sData == "Rejected") {
                        $(nTd).css('background-color', '#cb5a5e');
                        $(nTd).css('color', '#fff');
                    }
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

function saveInspection() {
    $('#loading').modal('show');

    if (requiredFields(':input.required') == true) {
        var url = PostSaveIQCInspection;
        var token = token;
        var partcode = $('#partcode').val();

        if ($('#save_status').val() == 'EDIT') {
            partcode = $('#partcodelbl').val();
        }
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
            lot_no: $('#lot_no').val(),
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
            inv_id: $('#inv_id').val(),
            mr_id: $('#mr_id').val(),
            ngr: {
                status_NGR: $('#status_NGR').val(),
                disposition_NGR: $('#disposition_NGR').val(),
                control_no_NGR: $('#control_no_NGR').val(),
                date_NGR: $('#date_NGR').val()
            }

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
        }).fail(function (data, textStatus, jqXHR) {
            $('#loading').modal('hide');
            msg("There's some error while processing.", 'failed');
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

function samplingPlan() {
    var url = GetSamplingPlan;
    var token = token;
    var data = {
        _token: token,
        soi: $('#severity_of_inspection').val(),
        il: $('#inspection_lvl').val(),
        aql: $('#aql').val(),
        lot_qty: $('#lot_qty').val()
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $('#accept').val(data.accept);
        $('#reject').val(data.reject);
        $('#sample_size').val(data.sample_size);
        $('#date_inspected').val(data.date_inspected);
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
    var token = token;
    var data = {
        _token: token
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data
    }).done(function (data, textStatus, jqXHR) {
        $('#type_of_inspection').select2({
            data: data.tofinspection,
            placeholder: "Select Type of Inspection",
            dropdownParent: $('#IQCresultModal .modal-content'),
            width: 'auto'
        });
        $('#severity_of_inspection').select2({
            data: data.sofinspection,
            placeholder: "Select Severity of Inspection",
            dropdownParent: $('#IQCresultModal .modal-content'),
            width: 'auto'
        });
        $('#inspection_lvl').select2({
            data: data.inspectionlvl,
            placeholder: "Select Inspection Level",
            dropdownParent: $('#IQCresultModal .modal-content'),
            width: 'auto'
        });
        $('#aql').select2({
            data: data.aql,
            placeholder: "Select AQL",
            dropdownParent: $('#IQCresultModal .modal-content'),
            width: 'auto'
        });
        $('#shift').select2({
            data: data.shift,
            placeholder: "Select Shift",
            dropdownParent: $('#IQCresultModal .modal-content'),
            width: 'auto'
        });
        $('#submission').select2({
            data: data.submission,
            placeholder: "Select Submission",
            dropdownParent: $('#IQCresultModal .modal-content'),
            width: 'auto'
        });
        $('#submission').val('1st');
        $('#submission').trigger('change');

        $('#mod_inspection').select2({
            data: data.mod,
            placeholder: "Select Mode of Defects",
            dropdownParent: $('#IQCresultModal .modal-content'),
            width: 'auto'
        });

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

function getItemDetails() {
    // $('#lot_no').select2({
    //     tags: true,
    //     data: '',
    //     placeholder: 'Select Lot Number'
    // });

    var partcode = $('#partcode').val();

    if ($('#partcode').val() == '') {
        partcode = $('#partcodelbl').val();
    }

    var url = GetIQCItemDetails;
    var token = token;
    var data = {
        _token: token,
        invoiceno: $('#invoice_no').val(),
        item: partcode
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        if (data.hasOwnProperty('item_desc')) {
            var details = data.details;
            console.log(details);

            $('#partname').val(details.item_desc);
            $('#supplier').val(details.supplier);
            $('#app_date').val(details.app_date);
            $('#app_time').val(details.app_time);
            $('#app_no').val(details.receive_no);
        }
        // $('#lot_no').select2({
        //     tags: true,
        //     data: data.lot,
        //     placeholder: 'Select Lot Number'
        // });
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getItemDetailsEdit(partcode) {
    if (partcode == "" || partcode == null) {
        partcode = $('#partcode').val();

        if ($('#partcode').val() == '') {
            partcode = $('#partcodelbl').val();
        }
    }

    var url = GetIQCItemDetails;
    var token = token;
    var data = {
        _token: token,
        invoiceno: $('#invoice_no').val(),
        item: partcode
    };

    $.ajax({
        url: url,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        var details = data.details;
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function calculateLotQty(lotno) {
    var url = GetIQCLotQty;
    var token = token;
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
    var token = token;
    var partcode = ($("#partcode").val() == "") ? $('#partcodelbl').val() : $("#partcode").val();
    var data = {
        _token: token,
        invoiceno: $('#invoice_no').val(),
        item: partcode,
        mod: $('#mod_inspection').val(),
        lot_no: $('#lot_no').val(),
        qty: $('#qty_inspection').val(),
        status: $('#status_inspection').val(),
        iqc_id: $('#iqc_result_id').val(),
        current_count: $('#mod_total_qty').val(),
        sample_size: $('#sample_size').val(),
        id: $('#mod_id').val()
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
        iqcdbgetmodeofdefectsinspection();
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function getModinspectionlist(data) {
    var cnt = 0;
    var no_of_defectives = 0;
    var qty = 0;
    var max = [];
    $.each(data, function (i, x) {
        cnt++;
        tblformodinspection = '<tr>' +
            '<td style="width: 8%">' +
            '<input type="checkbox" class="modinspection_checkitem checkboxes" value="' + x.id + '">' +
            '</td>' +
            '<td style="width: 12%">' +
            '<a href="javascript:;" class="btn blue input-sm modinspection_edititem" data-mod="' + x.mod + '" data-qty="' + x.qty + '" data-id="' + x.id + '">' +
            '<i class="fa fa-edit"></i>' +
            '</a>' +
            '</td>' +
            '<td style="width: 5%">' + cnt + '</td>' +
            '<td style="width: 55%">' + x.mod + '</td>' +
            '<td style="width: 20%">' + x.qty + '</td>' +
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
    $('#no_of_defects').val(no_of_defectives);
    $('#status_inspection').val('ADD');
}

function iqcdbgetmodeofdefectsinspection() {
    $('#tblformodinspection').html('');
    var tblformodinspection = '';
    var url = GetModeOfDefects;
    var token = token;
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
            getModinspectionlist(data);
        }
    }).fail(function (data, textStatus, jqXHR) {
        console.log(jqXHR);
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
        return this.value != '';
    });

    if (value.length !== reqlength) {
        return false;
    } else {
        console.log('true');
        return true;
    }
}

//search button
// function getPartcodeSearch() {
//     var url = GetIQCItemSearch;
//     var token = token;
//     var data = {
//         _token: token,
//     };

//     $.ajax({
//         url: url,
//         type: "GET",
//         data: data,
//     }).done(function (data, textStatus, jqXHR) {
//         if (data == null || data == "") {
//             $('#search_partcode_error').html("No Inspections Available");
//         } else {
//             $('#search_partcode').select2({
//                 data: data,
//                 placeholder: "Select an Item"
//             });
//         }
//     }).fail(function (data, textStatus, jqXHR) {
//         msg("There's some error while processing.", 'failed');
//     });
// }

function searchItemInspection() {
    var tblforiqcinspection = '';
    $('#tblforiqcinspection').html('');
    var url = GetIQCInspectionSearch;
    var token = token;
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
    var token = token;
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
    var token = token;
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
    var token = token;
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
    var token = token;
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
    var token = token;
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
    var token = token;
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
        var token = token;
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
        }).fail(function (data, textStatus, jqXHR) {
            $('#loading').modal('hide');
            msg("There's some error while processing.", 'failed');
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
    var token = token;
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
    var token = token;
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
    var token = token;
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
    var token = token;
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
    });
}

function deleteRequali() {
    var url = PostIQCRequaliDelete;
    var token = token;
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
    });
}

function deleteModRQ() {
    var url = PostIQCRequaliDeleteModeOfDefects;
    var token = token;
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
    });
}

function deleteModIns() {
    var url = PostIQCDeleteModeOfDefects;
    var token = token;
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
    });
}

function deleteOnGoing() {
    var url = PostIQCDeleteOnGoing;
    var token = token;
    var data = {
        id: getAllChecked('.ongiong_checkitems'),
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
        getOnGoing();
    }).fail(function (data, textStatus, jqXHR) {
        msg("There's some error while processing.", 'failed');
    });
}

function openModeOfDefects() {
    if ($('#lot_accepted').val() == 0) {
        $('#no_defects_label').show();
        $('#no_of_defects').show();
        $('#mode_defects_label').show();
        $('#btn_mod_ins').show();
        $('#judgement').val('Rejected');
    } else {
        $('#lot_accepted').val(1);
        $('#no_defects_label').hide();
        $('#no_of_defects').hide();
        $('#mode_defects_label').hide();
        $('#btn_mod_ins').hide();
        $('#judgement').val('Accepted');
    }
}

function samplingPlan_man() {
    var url = GetSamplingPlan;
    var token = token;
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
    var token = token;
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
    var token = token;
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
    }).fail(function (data, textStatus, jqXHR) {
        $('#loading').modal('hide');
        msg("There's some error while processing.", 'failed');
    });
    // } else {
    // 	$('#loading').modal('hide');
    // 	msg("Please fill out all required fields.",'failed');
    // }	
}

function getIQCworkWeek() {
    var url = GetWorkWeek;
    var token = token;
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