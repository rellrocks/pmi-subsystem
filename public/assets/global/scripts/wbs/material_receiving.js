var matRec_id = 0;
//mat - upload
$(function () {
    ViewState();
    tblDetails();
    tblSummary();
    tblBatch();
    supplierDropdown('#add_inputSupplier');
    supplierDropdown('#edit_inputSupplier');
    supplierDropdown('#inv_supplier');
    getMRdata('','');
    drawNeedModificationItemDatatable([]);
    checkAllCheckboxesMR('#tbl_item','#check_all_qc','.qc_check_item','#btn_no_need_modify');
    //#region For Details Update from QC
    $('#btn_for_details_update_qc').on('click', function() {
        showNeedsModificationItem();
        $('#editItemModal').modal('show');
    });
    $('#btn_details_update').on('click', function() {
        $('#loading').modal('show');
        var param = {
            _token: token,
            id: $('#inv_id').val(),
            item: $('#inv_item').val(),
            item_desc: $('#inv_item_desc').val(),
            qty: $('#inv_qty').val(),
            lot_no: $('#inv_lot_no').val(),
            supplier: $('#inv_supplier').val(),
            mr_id: $('#inv_mr_id').val(),
            mr_source: $('#inv_mr_source').val(),
        };
        $.ajax({
            url: ModifyItemURL,
            type: 'POST',
            dataType: 'JSON',
            data: param
        }).done(function (data, textStatus, jqXHR) {
            if (data.status == 'success') {
                showNeedsModificationItem();
                successMsg(data.msg);
                $('.clear_qc_item').val('');
            } else {
                failedMsg(data.msg);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
        }).always( function() {
            $('#loading').modal('hide');
        });
    });
    $('#btn_no_need_modify').on('click', function() {
        var checkID = [];
        var table = $('#tbl_item').DataTable();
        for (var x = 0; x < table.context[0].aoData.length; x++) {
            var aoData = table.context[0].aoData[x];
            if (aoData.anCells !== null && aoData.anCells[0].firstChild.checked == true) {
                checkID.push(aoData.anCells[0].firstChild.value);
            }
        }

        var msgs = "Are you sure that this item doesn't need to modify?";
        if (checkID.length > 1) {
            msgs = "Are you sure that these items doesn't need to modify?";
        }

        if (checkID.length > 0) {
            bootbox.confirm({
                message: msgs,
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
                        noNeedModification(checkID);
                    }             
                }
            });
        }
    });
    $('#tbl_item').on('click', '.btn_qc_edit', function() {
        var data = $('#tbl_item').DataTable().row($(this).parents('tr')).data();
        $('#inv_id').val(data.id);
        $('#inv_item').val(data.item);
        $('#inv_item_desc').val(data.item_desc);
        $('#inv_qty').val(data.qty);
        $('#inv_lot_no').val(data.lot_no);
        $('#inv_supplier').val(data.supplier);
        $('#inv_mr_id').val(data.mr_id);
        $('#inv_mr_source').val(data.mr_source);
    });
    //#endregion
    //#region Search Modal
    $('#btn_search').on('click', function (e) {
        $('.reset').val('');
        $('.search_row').remove();
        $('#searchModal').modal('show');
    });
    $('#btn_filter').on('click', function () {
        $('#loading').modal('show');
        $('#tbl_search_body').html('');
        var tbl_search = '';
        
        var data = {
            _token: token,
            from: $('#srch_from').val(),
            to: $('#srch_to').val(),
            invoiceno: $('#srch_invoiceno').val(),
            invfrom: $('#srch_invfrom').val(),
            invto: $('#srch_invto').val(),
            item: $('#srch_item').val(),
            open: $('#srch_open').val(),
            close: $('#srch_close').val(),
            cancelled: $('#srch_cancelled').val(),
        };

        $.ajax({
            url: GetReceivingSearch,
            type: "GET",
            data: data,
        }).done(function (data, textStatus, jqXHR) {
            $('#loading').modal('hide');
            var status = '';
            var iqc_status = '';
            $.each(data, function (index, x) {
                if (x.status == 'O') {
                    status = 'Open';
                }
                if (x.status == 'X') {
                    status = 'Closed';
                }

                if (x.status == 'C') {
                    status = 'Cancelled';
                }

                if (x.iqc_status == 1) {
                    iqc_status = 'Accepted';
                }
                if (x.iqc_status == 2) {
                    iqc_status = 'Rejected';
                }

                if (x.iqc_status == 3) {
                    iqc_status = 'On-going';
                }

                if (x.iqc_status == 0) {
                    iqc_status = 'Pending';
                }

                tbl_search = '<tr class="search_row">' +
                    '<td>' +
                    '<a href="javascript:;" class="btn blue input-sm look_search" data-id="' + x.id + '">' +
                    '<i class="fa fa-edit"></i>' +
                    '</a>' +
                    '</td>' +
                    '<td>' + x.receive_no + '</td>' +
                    '<td>' + x.received_date + '</td>' +
                    '<td>' + x.invoice_no + '</td>' +
                    '<td>' + x.invoice_date + '</td>' +
                    '<td>' + x.item + '</td>' +
                    '<td>' + x.lot_no + '</td>' +
                    '<td>' + x.qty + '</td>' +
                    '<td>' + status + '</td>' +
                    '<td>' + iqc_status + '</td>' +
                    '<td>' + x.create_user + '</td>' +
                    '<td>' + x.created_at + '</td>' +
                    '<td>' + x.update_user + '</td>' +
                    '<td>' + x.updated_at + '</td>' +
                    '</tr>';
                $('#tbl_search_body').append(tbl_search);
            });
        }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
            $('#loading').modal('hide');
            failedMsg("There's some error while processing.");
        });
    });
    $('#btn_reset').on('click', function () {
        $('.reset').val('');
        $('.search_row').remove();
    });
    $('#tbl_search_body').on('click', '.look_search', function (e) {
        var id = $(this).attr('data-id');
        var data = {
            _token: token,
            id: id
        };
        $.ajax({
            url: GetReceivingViewItem,
            type: "GET",
            data: data,
        }).done(function (d) {
            $('#searchModal').modal('hide');
            $('#loading').modal('hide');
            if(d.success) {
                MRdataInfo(d.data);
                ViewState();
            }else {
                failedMsg(data.msg);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            $('#loading').modal('hide');
            failedMsg("There's some error while processing.");
        });
    });
    //#endregion
    //#region PRINT and Apply to IQC
    $('#btn_print').on('click', function () {
        if ($('#invoiceno').val() == '' || $('#receivingno').val() == '') {
            failedMsg("Please provide some values for Invoice Number or Material Receiving Number.");
        } else {
            
            var url = GetReceivingPrintLabel + '?receivingno=' + $('#receivingno').val() + '&&_token=' + token;

            window.location.href = url;
        }
    });
    $('#btn_print_iqc').on('click', function () {
        if ($('#invoiceno').val() == '' || $('#receivingno').val() == '') {
            failedMsg("Please provide some values for Invoice Number or Material Receiving Number.");
        } else {
            
            var url = GetReceivingIQCList + '?receivingno=' + $('#receivingno').val() + '&&_token=' + token;

            window.location.href = url;
        }
    });
    //#endregion
    //#region REFRESH INVOICE
    $('#btn_refresh').on('click', function () {
        refreshInvoice();
    });
    //#endregion
    //#region CANCEL INVOICE
    $('#btn_cancel_mr').on('click', function () {
        $('#confirm_status').val('cancelmr');
        $('#confirm').modal('show');
    });

    $("#confirmyes").click(function () {
        if ($('#confirm_status').val() == 'deletebatch') {
            deleteBatchItem();
        } else {
            cancelInvoice();
        }
    });
    //#endregion
    //#region SUMMARY
    $('#btn_cancel').on('click', function () {
        window.location.href = CancelTransaction;
    });
    $('#btn_addnew').on('click', function () {
        clear();
        $('#invoiceno').prop('readonly', false);
        $('#btn_addnew').hide();
        $('#btn_edit').hide();
        $('#btn_cancel').show();
        $('#btn_search').hide();
        $('#btn_cancel_mr').hide();
        $('#btn_print').hide();
        $('#btn_print_iqc').hide();
        $('#tbl_details_body').html('');
        $('#tbl_summary_body').html('');
        $('#tbl_batch_body').html('');
    });
    $('#btn_checkinv').on('click', function (e) {
        $('.clearinv').val('');
        $('#loading').modal('show');
        $('.details_remove').remove();
        $('.summary_remove').remove();
        $('.batch_remove').remove();

        var tbl_summary = '';
        var tbl_details = '';
        
        var data = {
            _token: token,
            invoiceno: $('#invoiceno').val()
        };

        if ($('#invoiceno').val() != '') {
            $.ajax({
                url: PostSearchInvoiceNo,
                type: "POST",
                data: data,
            }).done(function (data, textStatus, jqXHR) {
                if (data.request_status == 'success') {
                    var invdata = data.invoicedata;

                    $('#receivingno').val(invdata.receiving_no);
                    $('#receivingdate').val(invdata.receiving_date);
                    $('#invoiceno').val(invdata.invoiceno);
                    $('#hdninvoiceno').val(invdata.invoiceno);
                    $('#invoicedate').val(invdata.invoice_date);
                    $('#totalqty').val(invdata.total_qty);
                    $('#totalvar').val(invdata.total_var);
                    $('#totalamt').val(invdata.total_amt);
                    $('#status').val(invdata.status);
                    $('#createdby').val(invdata.created_by);
                    $('#createddate').val(invdata.created_date);
                    $('#updatedby').val(invdata.updated_by);
                    $('#updateddate').val(invdata.updated_date);
                    $('#palletno').prop('readonly', false);

                    $.each(data.detailsdata, function (index, x) {
                        tbl_details = '<tr class="details_remove">' +
                            '<td class="col-xs-2">' + x.item +
                            '<input type="hidden" name="item_details[]" value="' + x.item + '"/>' +
                            '</td>' +
                            '<td class="col-xs-3">' + x.description +
                            '<input type="hidden" name="desc_details[]" value="' + x.description + '"/>' +
                            '</td>' +
                            '<td class="col-xs-1">' + x.qty +
                            '<input type="hidden" name="qty_details[]" value="' + x.qty + '"/>' +
                            '</td>' +
                            '<td class="col-xs-2">' + x.pr +
                            '<input type="hidden" name="pr_details[]" value="' + x.pr + '"/>' +
                            '</td>' +
                            '<td class="col-xs-2">' + x.price +
                            '<input type="hidden" name="price_details[]" value="' + x.price + '"/>' +
                            '</td>' +
                            '<td class="col-xs-2">' + x.amount +
                            '<input type="hidden" name="amount_details[]" value="' + x.amount + '"/>' +
                            '</td>' +
                            '</tr>';
                        $('#tbl_details_body').append(tbl_details);
                    });
                    $.each(data.summarydata, function (index, x) {
                        var checked = '';
                        if (x.vendor == 'PPD' || x.vendor == 'PPS' || x.vendor == 'PPC' || x.nr == 1) {
                            checked = 'checked="checked"';
                        }
                        tbl_summary = '<tr class="summary_remove">' +
                            '<td class="col-xs-1"></td>' +
                            '<td class="col-xs-2">' + x.item +
                            '<input type="hidden" name="item_summary[]" value="' + x.item + '" />' +
                            '</td>' +
                            '<td class="col-xs-2">' + x.description +
                            '<input type="hidden" name="desc_summary[]" value="' + x.description + '" />' +
                            '</td>' +
                            '<td class="col-xs-2">' + x.qty +
                            '<input type="hidden" name="qty_summary[]" value="' + x.qty + '" />' +
                            '</td>' +
                            '<td class="col-xs-2">' + x.r_qty +
                            '<input type="hidden" name="r_qty_summary[]" value="' + x.r_qty + '" />' +
                            '</td>' +
                            '<td class="col-xs-2">' + x.variance +
                            '<input type="hidden" name="variance_summary[]" value="' + x.variance + '" />' +
                            '</td>' +
                            '<td class="col-xs-1">' +
                            '<input type="checkbox" name="iqc_summary[]" class="iqc_chk" ' + checked + ' value="' + x.item + '"/>' +
                            '</td>' +
                            '</tr>';
                        $('#tbl_summary_body').append(tbl_summary);
                    });
                    AddState();
                } else {
                    failedMsg(data.msg);
                    ViewState();
                    getMRdata('','');
                }
                $('#loading').modal('hide');
            }).fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                $('#loading').modal('hide');
                getMRdata('','');
                failedMsg("There's some error while processing.");
            });
        } else {
            $('#loading').modal('hide');
            failedMsg("Please fill out the Invoice Number input field.");
            ViewState();
        }
    });
    $('#tbl_summary_body').on('click', '.addBatchsummary', function () {
        $('#add_inputItemNo').prop('disabled', false);
        $('.clearbatch').val('');
        // $('#add_inputBox').select2('data', {
        //     id: '',
        //     text: ''
        // });

        var $add_inputItemNo = $("<option selected='selected'></option>").val($(this).attr('data-item')).text($(this).attr('data-item') + ' | ' + $(this).attr('data-item_desc'));
        $("#add_inputItemNo").append($add_inputItemNo).trigger('change');

        // $('#add_inputItemNo').select2('data', {
        //     id: $(this).attr('data-item'),
        //     text: $(this).attr('data-item') + ' | ' + $(this).attr('data-item_desc')
        // });
        // getItemData();
        $('#add_inputItemNoHidden').val($(this).attr('data-item'));
        $('#add_inputItemDesc').val($(this).attr('data-item_desc'));

        var checked = [];

        $(".iqc_chk:checked").each(function () {
            checked.push($(this).attr('data-item'));
        });

        var notiqc = '';
        $.each(checked, function (i, x) {
            if (x == $(this).attr('data-item')) {
                $('#add_notForIqc').val('1');
            } else {
                $('#add_notForIqc').val($(this).attr('data-check'));
            }
        });

        $.ajax({
            url: GetReceivingLocation,
            type: 'GET',
            dataType: 'JSON',
            data: {
                code: $(this).attr('data-item')
            }
        })
            .done(function (data, textStatus, jqXHR) {
                // console.log(data);
                $('#add_inputLocation').val(data[0].location);
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                console.log("error");
            })
        $('#add_notForIqc').val($(this).attr('data-check'));
        $('#add_inputQty').val($(this).attr('data-var'));
        $('#batchItemModal').modal('show');

    });
    //#endregion
    //#region BATCHING
    $('#uploadbatchfiles').on('submit', function (e) {
        $('#progress-close').prop('disabled', true);
        $('#progressbar').addClass('progress-striped active');
        $('#progressbar-color').addClass('progress-bar-success');
        $('#progressbar-color').removeClass('progress-bar-danger');
        $('#progress').modal('show');

        var formObj = $('#uploadbatchfiles');
        var formURL = formObj.attr("action");
        var formData = new FormData(this);
        var fileName = $("#batchfiles").val();
        var ext = fileName.split('.').pop();
        var tbl_batch = '';
        e.preventDefault(); //Prevent Default action.

        if ($("#batchfiles").val() == '') {
            $('#progress-close').prop('disabled', false);
            $('#progress-msg').html("Upload field is empty");
        } else {
            if (fileName != '') {
                if (ext == 'xls' || ext == 'xlsx' || ext == 'XLS' || ext == 'XLSX') {
                    $('.myprogress').css('width', '0%');
                    $('#progress-msg').html('Uploading in progress...');
                    var percent = 0;

                    $.ajax({
                        url: formURL,
                        type: 'POST',
                        data: formData,
                        mimeType: "multipart/form-data",
                        contentType: false,
                        cache: false,
                        processData: false,
                        xhr: function () {
                            var xhr = new window.XMLHttpRequest();
                            if (xhr.upload) {
                                xhr.upload.addEventListener("progress", function (evt) {

                                    var loaded = evt.loaded;
                                    var total = evt.total;

                                    if (evt.lengthComputable) {
                                        percent = Math.ceil(loaded / total * 100);
                                        // var percentComplete = evt.loaded / evt.total;
                                        // percentComplete = parseInt(percentComplete * 100);

                                        //if (percentComplete < 100) {
                                        // $('.myprogress').text(percent + '%');
                                        $('.myprogress').css('width', percent + '%');
                                        //}
                                        if (percent == 100) {
                                            $('.myprogress').css('width', percent + '%');
                                            $('#progress-msg').html('Finalizing upload...');
                                        }
                                    }
                                }, false);
                            }
                            return xhr;
                        }
                    }).done(function (data) {
                        $('#progressbar').removeClass('progress-striped active');
                        var datas = JSON.parse(data);
                        console.log(datas);
                        if (datas.return_status == 'success') {
                            getMRdata('',datas.receivingno);
                            $('#progress-close').prop('disabled', false);
                            $('#progress-msg').html("Items were successfully uploaded.");
                        } else {
                            $('#progress-close').prop('disabled', false);
                            $('#progressbar-color').removeClass('progress-bar-success');
                            $('#progressbar-color').addClass('progress-bar-danger');
                            if (datas.receivingno != '') {
                                $('#progress-msg').html("Please check this error.[" + datas.receivingno + "]");
                            }
                            if (datas.item != '') {
                                $('#progress-msg').html("Please check this error.[" + datas.item + "]");
                            }
                            if (datas.invoice != '') {
                                $('#progress-msg').html("Please check this error.[" + datas.invoice + "]");
                            }
                            if (datas.msg != '') {
                                $('#progress-msg').html(datas.msg);
                            }
                        }
                    }).fail(function (data) {
                        $('#progress-close').prop('disabled', false);
                        $('#progressbar').removeClass('progress-striped active');
                        $('#progressbar-color').removeClass('progress-bar-success');
                        $('#progressbar-color').addClass('progress-bar-danger');
                        $('#progress-msg').html("There's some error while processing.");
                    });
                } else {
                    $('#progress-close').prop('disabled', false);
                    $('#progress-msg').html("Please upload a valid excel file.");
                }
            }
        }
    });
    $('#add_inputItemNo').select2({
        placeholder: 'Select Item No.',
        dropdownParent: $('#batchItemModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
        ajax: {
            url: GetReceivingItems,
            data: function (params) {
                var query = "";
                var invoice_no = $('#invoiceno').val();
                var receivingno = $('#receivingno').val();
                var item_code_cond = "";
                var paramsVal = (params.term == undefined) ? "" : params.term;

                if (paramsVal != "") {
                    item_code_cond = " AND (S.CODE LIKE '%" + paramsVal + "%' OR H.NAME LIKE '%" + paramsVal + "%'";
                }

                query = "select distinct \
                                item as id, \
                                CONCAT(item, ' | ', item_desc) AS `text` \
                        from tbl_wbs_material_receiving_summary \
                        where wbs_mr_id = '" + receivingno + "' \
                        and (item like '%" + paramsVal + "%' or item_desc LIKE '%" + paramsVal + "%')"

                // query = "SELECT DISTINCT S.CODE AS id, \
                //         CONCAT(S.CODE, ' | ', H.NAME) AS[text], \
                //             Z.RACKNO AS[location] \
                //         FROM XSACT as S \
                //         LEFT JOIN(SELECT DISTINCT z.CODE, z.RACKNO \
                //                                     FROM XZAIK as z \
                //                                     WHERE z.JYOGAI = 0 \
                //                                     AND z.RACKNO <> '') AS Z \
                //         on Z.CODE = S.CODE \
                //         JOIN XHEAD as H \
                //         on H.CODE = S.CODE \
                //         WHERE S.INVOICE_NUM <> '' \
                //         AND S.INVOICE_NUM = '"+ invoice_no + "' " + item_code_cond;

                return {
                    q: params.term,
                    id: '',
                    text: '',
                    table: '',
                    condition: '',
                    display: 'id&text',
                    orderBy: '',
                    sql_query: query,
                    invoice_no: invoice_no,
                    receivingno: receivingno
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
        }
    });
    $('#add_inputItemNo').on('change', function () {
        var item = $(this).val();
        if (item !== null || item !== "") {
            getItemData();
            checkIfNotForIQC($(this).val());
        }
    });
    $('#receivingno').on('change',function(){
        getMRdata('',$(this).val());
    });
    $('#btn_discard').on('click', function () {
        location.reload(true);
    });
    $('#btn_add_batch').on('click', function () {
        $('#add_inputItemNo').prop('disabled', false);
        $('.clearbatch').val('');
        $('#add_inputItemNo').val(null).trigger('change');
        $('#batchItemModal').modal('show');
    });
    $('#btn_add_batch_modal').on('click', function () {
        batching();
    });
    $('#btn_edit').on('click', function (e) {
        EditState();
    });
    $('#btn_delete_batch').on('click', function () {
        $('#confirm_status').val('deletebatch');
        if (isCheck($('.chk_del_batch'))) {
            $('#confirm').modal('show');
        } else {
            failedMsg("Please select at least 1 batched item.");
        }
    });
    $('#btn_save').on('click', function () {
        if ($('#receivingdate').val() == '') {
            failedMsg('Empty receiving date!', 'failed');
        } else {
            var status = getMRStatus($('#status').val());
            var notForIQC = [];
            var notForIQCbatch = [];
            var IsPrinted = [];

            $(".iqc_chk:checked").each(function () {
                notForIQC.push($(this).val());
            });

            $(".notforiqc_batch:checked").each(function () {
                notForIQCbatch.push($(this).val());
            });

            $(".print_barcode:checked").each(function () {
                IsPrinted.push($(this).val());
            });

            var mrdata = {
                receive_no: $('input[name=receivingno]').val(),
                receive_date: $('input[name=receivingdate]').val(),
                invoice_no: $('input[name=invoiceno]').val(),
                invoice_date: $('input[name=invoicedate]').val(),
                pallet_no: $('input[name=palletno]').val(),
                total_qty: $('input[name=totalqty]').val(),
                total_var: $('input[name=totalvar]').val(),
                total_amt: $('input[name=totalamt]').val(),
                status: status,
            }
            var summarydata = {
                item: $('input[name="item_summary[]"]').map(function () { return $(this).val(); }).get(),
                description: $('input[name="desc_summary[]"]').map(function () { return $(this).val(); }).get(),
                qty: $('input[name="qty_summary[]"]').map(function () { return $(this).val(); }).get(),
                r_qty: $('input[name="r_qty_summary[]"]').map(function () { return $(this).val(); }).get(),
                variance: $('input[name="variance_summary[]"]').map(function () { return $(this).val(); }).get(),
            };
            var mrdataedit = {
                receive_no: $('#receivingno').val(),
                receive_date: $('#receivingdate').val(),
                invoice_no: $('#invoiceno').val(),
                pallet_no: $('input[name=palletno]').val(),
                total_qty: $('input[name=totalqty]').val(),
                total_var: $('input[name=totalvar]').val(),
            }
            var summarydataedit = {
                item: $('input[name="item_summary[]"]').map(function () { return $(this).val(); }).get(),
                itemall: $('input[name="itemall[]"]').map(function () { return $(this).val(); }).get(),
                id: $('input[name="id[]"]').map(function () { return $(this).val(); }).get(),
            };
            var batchdata = {
                id: $('input[name="id_batch[]"]').map(function () { return $(this).val(); }).get(),
                item: $('input[name="item_batch[]"]').map(function () { return $(this).val(); }).get(),
                item_desc: $('input[name="item_desc_batch[]"]').map(function () { return $(this).val(); }).get(),
                qty: $('input[name="qty_batch[]"]').map(function () { return $(this).val(); }).get(),
                batch_qty: $('input[name="qty_batch[]"]').map(function () { return $(this).attr('data-batch_qty'); }).get(),
                box: $('input[name="box_batch[]"]').map(function () { return $(this).val(); }).get(),
                box_qty: $('input[name="box_qty_batch[]"]').map(function () { return $(this).val(); }).get(),
                lot_no: $('input[name="lot_no_batch[]"]').map(function () { return $(this).val(); }).get(),
                location: $('input[name="location_batch[]"]').map(function () { return $(this).val(); }).get(),
                supplier: $('input[name="supplier_batch[]"]').map(function () { return $(this).val(); }).get(),
            };
            var savestate = $('#savestate').val();
            if (savestate == 'ADD') {
                saveMR(mrdata, summarydata, notForIQC, savestate);
            } else {
                saveMrWithBatch(mrdataedit, summarydataedit, batchdata, notForIQC, notForIQCbatch, IsPrinted, savestate)
            }

        }
    });
    $('#tbl_batch_body').on('click', '.edit_item_batch', function (e) {
        var id = $(this).attr('data-id');
        var bid = $(this).attr('data-bid');
        getSingleBatchItem(id, bid);
        $('#EditbatchItemModal').modal('show');
    });
    $('#btn_edit_batch_modal').on('click', function () {
        $('#EditbatchItemModal').modal('hide');
        var bid = $('#edit_inputBatchId').val();
        var item = $('#edit_inputItemNoHidden').val();
        var hiddenQty = $('#edit_inputQtyHidden').val();
        var qty = $('#edit_inputQty').val();
        var box = $('#edit_inputBox').val();
        var boxqty = $('#edit_inputBoxQty').val();
        var lot = $('#edit_inputLotNo').val();
        var supplier = $('#edit_inputSupplier').val();
        var newqty = qty - hiddenQty;
        if (newqty == 0) {
            newqty = qty
        }
        $('#td_batch_qty' + bid).html(qty + '<input type="hidden" name="qty_batch[]" id="in_batch_qty' + bid + '" value="' + newqty + '">');
        $('#td_batch_box' + bid).html(box + '<input type="hidden" name="box_batch[]" id="in_batch_box' + bid + '" value="' + box + '">');
        $('#td_batch_boxqty' + bid).html(boxqty + '<input type="hidden" name="box_qty_batch[]" id="in_batch_boxqty' + bid + '" value="' + boxqty + '">');
        $('#td_batch_lot' + bid).html(lot + '<input type="hidden" name="lot_no_batch[]" id="in_batch_lot' + bid + '" value="' + lot + '">');
        $('#td_batch_supplier' + bid).html(supplier + '<input type="hidden" name="supplier_batch[]" id="in_batch_supplier' + bid + '" value="' + supplier + '">');

        $('#in_batch_qty' + bid).val(newqty);
        $('#in_batch_qty' + bid).attr('data-batch_qty', qty);
        $('#in_batch_box' + bid).val($('#edit_inputBox').val());
        $('#in_batch_boxqty' + bid).val($('#edit_inputBoxQty').val());
        $('#in_batch_lot' + bid).val($('#edit_inputLotNo').val());
        $('#in_batch_supplier' + bid).val($('#edit_inputSupplier').val());
    });
    $('#tbl_batch_body').on('click', '.barcode_item_batch', function (e) {
        $('#loading').modal('show');
        var id = $(this).attr('data-id');
        var txnno = $(this).attr('data-txnno');
        var txndate = $(this).attr('data-txndate');
        var itemno = $(this).attr('data-itemno');
        var itemdesc = $(this).attr('data-itemdesc');
        var qty = $(this).attr('data-qty');
        var bcodeqty = $(this).attr('data-bcodeqty');
        var lotno = $(this).attr('data-lotno');
        var location = $(this).attr('data-location');
        var barcode = $(this).attr('data-barcode');
        
        var data = {
            _token: token,
            receivingno: $('#receivingno').val(),
            receivingdate: $('#receivingdate').val(),
            id: id,
            txnno: txnno,
            txndate: txndate,
            itemno: itemno,
            itemdesc: itemdesc,
            qty: qty,
            bcodeqty: bcodeqty,
            lotno: lotno,
            location: location,
            barcode: barcode,
            state: 'single'
        };

        if ($('#invoiceno').val() == '' || $('#receivingno').val() == '') {
            failedMsg("Please provide some values for Invoice Number or Material Receiving Number.");
            $('#err_msg').html("");
        } else {
            $.ajax({
                url: PostReceivingPrintBarcode,
                type: "POST",
                data: data,
            }).done(function (data, textStatus, jqXHR) {
                $('#loading').modal('hide');
                if (data.request_status == 'success') {
                    successMsg(data.msg);
                    $('#print_br_' + itemno).val(itemno);
                    $('#print_br_' + itemno).prop('checked', 'true');
                } else {
                    failedMsg(data.msg);
                }
            }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
                $('#loading').modal('hide');
                failedMsg("There's some error while processing.");
            });
        }
    });
    $('#tbl_batch_body').on('click', '.x_remove_batch', function (e) {
        var thisclass = $(this).attr('data-id');
        var qty = $(this).attr('data-qty');
        var item = $(this).attr('data-item');
        $('.' + thisclass).remove();

        var r_qty = parseInt($('#in_r_qty_' + item).val()) - parseInt(qty);
        var variance = $('#in_var_' + item).val();
        var new_var_qty = parseInt(variance) + parseInt(qty);

        $('#td_r_qty_' + item).html(r_qty + '<input type="hidden" name="received_qty[]" id="in_r_qty_' + item + '"/>');
        $('#in_r_qty_' + item).val(r_qty);
        $('#td_var_' + item).html(new_var_qty + '<input type="hidden" name="variance[]" id="in_var_' + item + '"/>');
        $('#in_var_' + item).val(new_var_qty);
    });
    //#endregion


    $('#btn_all_batch').on('click', function () {
        var data = {
            _token: token,
            invoiceno: $('#invoiceno').val(),
            receivingno: $('#receivingno').val(),
            received_date: $('#receivingdate').val()
        }

        $.ajax({
            url: PostReceivingBatchReceiveAll,
            type: 'POST',
            dataType: 'JSON',
            data: data
        }).done(function (data, textStatus, jqXHR) {
            console.log(data);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
        });
    });
    $('#checkbox_iqc').on('change', function (e) {
        $('input:checkbox.iqc_chk').not(this).prop('checked', this.checked);
    });

});
//#region MRA DATA
function getMRdata(nav,receivingno){
    $('.details_remove').remove();
    $('.summary_remove').remove();
    $('.batch_remove').remove();
    var params = {
        to : nav,
        receivingno : receivingno
    };
    $.ajax({
        url: GetReceivingNumber,
        type: "GET",
        data: {
            _token: token,
            params: params,
        },
    }).done(function (d) {
        if(d.success) {
            MRdataInfo(d.data);
            ViewState();
        }else {
            failedMsg(data.msg);
        }
        $('#loading').modal('hide');
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });
}
function MRdataInfo(d){
    var table = '';
    var status = '';
    var mrdata = d.mrdata;
    var detailsdata = d.detailsdata;
    var summarydata = d.summarydata;
    var batchdata = d.batchdata;
    if (mrdata.total_var < 1) {
        status = 'Closed'
    } else {
        if (mrdata.status == 'O') {
            status = 'Open';
        } else if (mrdata.status == 'X') {
            status = 'Closed';
        } else {
            status = 'Cancelled';
        }
    }
    var mrdata = d.mrdata;
    var detailsdata = d.detailsdata;
    var summarydata = d.summarydata;
    var batchdata = d.batchdata;
    $('#receivingno').val(mrdata.receive_no);
    $('#receiveNoupload').val(mrdata.receive_no);
    $('#receivingdate').val(mrdata.receive_date);
    $('#invoiceno').val(mrdata.invoice_no);
    $('#hdninvoiceno').val(mrdata.invoice_no);
    $('#invoicedate').val(mrdata.invoice_date);
    $('#palletno').val(mrdata.pallet_no);
    $('#totalqty').val(mrdata.total_qty);
    $('#totalvar').val(mrdata.total_var);
    $('#status').val(status);
    $('#createdby').val(mrdata.create_user);
    $('#createddate').val(mrdata.created_at);
    $('#updatedby').val(mrdata.update_user);
    $('#updateddate').val(mrdata.updated_at);

    $('#tbl_details_body').html('');
    $('#summarycount').html('');
    $('#tbl_batch_body').html('');

    //#region DETAILS TABLE
    $.each(detailsdata, function (index, x) {
        table = '<tr class="details_remove">' +
            '<td class="col-xs-2">' + x.item + '</td>' +
            '<td class="col-xs-3">' + x.item_desc + '</td>' +
            '<td class="col-xs-1">' + x.qty + '</td>' +
            '<td class="col-xs-2">' + x.pr_no + '</td>' +
            '<td class="col-xs-2">' + x.unit_price + '</td>' +
            '<td class="col-xs-2">' + x.amount + '</td>' +
            '</tr>';
        $('#tbl_details_body').append(table);
    });
    //#endregion
    table = '';
    var cnt = 0;
    var checkedval = null;
    var checked_kit = '';
    var checked_print = '';
    //#region SUMMARY TABLE
    $.each(summarydata, function (index, x) {
        if (x.vendor == 'PPD' || x.vendor == 'PPS' || x.vendor == 'PPC' || x.not_for_iqc == 1) {
            var checked = 'checked="checked"';
            checkedval = 1;
        } else {
            checkedval = 0;
        }
        table = '<tr class="summary_remove">' +
            '<td class="col-xs-1 text-center">' +
            '<a href="javascript:;" class="btn btn-sm green addBatchsummary" data-item="' + x.item + '" data-item_desc="' + x.item_desc + '" data-qty="' + x.qty + '" data-var="' + x.variance + '" data-check="' + checkedval + '">' +
            '<i class="fa fa-plus-circle"></i>' +
            '</a>' +
            '</td>' +
            '<td class="col-xs-2" id="td_item_' + x.item + '">' + x.item +
            '<input type="hidden" name="item[]" class="remove_qty" id="in_item_' + x.item + '" value="' + x.item + '" />' +
            '<input type="hidden" name="id[]" class="remove_qty" id="in_id_' + x.item + '" value="' + x.id + '" />' +
            '<input type="hidden" name="itemall[]" value="' + x.item + '" />' +
            '</td>' +
            '<td class="col-xs-2">' + x.item_desc + '</td>' +
            '<td class="col-xs-2">' + x.qty + '</td>' +
            '<td class="col-xs-2" id="td_r_qty_' + x.item + '">' + x.received_qty +
            '<input type="hidden" id="in_r_qty_' + x.item + '" class="remove_qty" value="' + x.received_qty + '" />' +
            '</td>' +
            '<td class="col-xs-2" id="td_var_' + x.item + '">' + x.variance +
            '<input type="hidden" id="in_var_' + x.item + '" class="remove_qty" value="' + x.variance + '" />' +
            '</td>' +
            '<td class="col-xs-1 text-center" id="td_iqc_chk_' + x.item + '">' +
            '<input type="checkbox" name="iqc[]" class="iqc_chk" id="in_iqc_chk_' + x.item + '" ' + checked + ' value="' + x.item + '" readonly>' +
            '</td>' +
            '</tr>';
        $('#tbl_summary_body').append(table);
        cnt++;
    });
    $('#summarycount').html(cnt);
    //#endregion

    //#region BATCH DETAILS
    $.each(batchdata, function (index, x) {
        var checked_kit = '';
        var checked_print = '';

        if (x.not_for_iqc == 1) {
            checked_kit = 'checked="checked"';
        }

        if (x.is_printed == 1) {
            checked_print = 'checked="checked"';
        }
        cnt++;

        table = '<tr class="batch_remove">' +
            '<td style="width:2.1%">' +
            '<input type="checkbox" name="del_batch" disabled="disabled" class="chk_del_batch" data-qty="' + x.qty + '" data-item="' + x.item + '" value="' + x.id + '">' +
            '</td>' +
            '<td style="width:4.1%">' +
            '<a href="javascript:;" class="btn input-sm blue edit_item_batch" data-bid="' + cnt + '" disabled="disabled" data-id="' + x.id + '">' +
            '<i class="fa fa-edit"></i>' +
            '<a>' +
            '</td>' +
            '<td style="width:6.1%">' + cnt + '</td>' +
            '<td style="width:7.1%">' + x.item +
            '<input type="hidden" name="item_batch[]" value="' + x.item + '">' +
            '<input type="hidden" name="id_batch[]" value="' + x.id + '">' +
            '</td>' +
            '<td style="width:18.1%">' + x.item_desc +
            '<input type="hidden" name="item_desc_batch[]" value="' + x.item_desc + '">' +
            '</td>' +
            '<td style="width:7.1%" id="td_batch_qty' + cnt + '">' + x.qty +
            '<input type="hidden" name="qty_batch[]" id="in_batch_qty' + cnt + '" value="' + x.qty + '"  data-batch_qty="' + x.qty + '">' +
            '</td>' +
            '<td style="width:10.1%" id="td_batch_box' + cnt + '">' + x.box +
            '<input type="hidden" name="box_batch[]" id="in_batch_box' + cnt + '" value="' + x.box + '">' +
            '</td>' +
            '<td style="width:7.1%" id="td_batch_boxqty' + cnt + '">' + x.box_qty +
            '<input type="hidden" name="box_qty_batch[]" id="in_batch_boxqty' + cnt + '" value="' + x.box_qty + '">' +
            '</td>' +
            '<td style="width:7.1%" id="td_batch_lot' + cnt + '">' + x.lot_no +
            '<input type="hidden" name="lot_no_batch[]" id="in_batch_lot' + cnt + '" value="' + x.lot_no + '">' +
            '</td>' +
            '<td style="width:7.1%">' + x.location +
            '<input type="hidden" name="location_batch[]" value="' + x.location + '">' +
            '</td>' +
            '<td style="width:7.1%" id="td_batch_supplier' + cnt + '">' + x.supplier +
            '<input type="hidden" name="supplier_batch[]" id="in_batch_supplier' + cnt + '" value="' + x.supplier + '">' +
            '</td>' +
            '<td style="width:6.1%" class="text-center">' +
            '<input type="checkbox" class="notforiqc_batch" name="notforiqc_batch[]" value="' + x.item + '" ' + checked_kit + ' disabled="disabled">' +
            '</td>' +
            '<td style="width:5.1%" class="text-center">' +
            '<input type="checkbox" name="print_barcode[]" class="print_barcode" value="' + x.item + '" ' + checked_print + ' id="print_br_' + x.item + '" disabled="disabled">' +
            '</td>' +
            '<td style="width:5.1%">' +
            '<a href="javascript:;" class="btn input-sm grey-gallery barcode_item_batch" data-id="' + x.id + '" data-txnno="' + $('#invoiceno').val() + '" data-txndate="' + $('#invoicedate').val() + '" data-itemno="' + x.item + '" data-itemdesc="' + x.item_desc + '" data-qty="' + x.qty + '" data-bcodeqty="' + x.box_qty + '" data-lotno="' + x.lot_no + '" data-location="' + x.location + '">' +
            '<i class="fa fa-barcode"></i>' +
            '<a>' +
            '</td>' +
            '</tr>';
        $('#tbl_batch_body').append(table);
    });
    //#endregion
}
function batching() {

    var Valid = true;
    var itemcode = [];
    var QtyLists = [];
    var LotList = [];
    var data = [];
    var tabledata = $('#tbl_batch');
    tabledata.find('tr').each(function (rowIndex, r) {
        var cols = [];
        $(this).find('th,td').each(function (colIndex, c) {
            cols.push(c.textContent);
        });
        data.push(cols);

        itemcode.push(cols[3]);
        QtyLists.push(cols[5]);
        LotList.push(cols[8]);
    });


    $('#batchItemModal').modal('hide');
    var tbl_batch = '';
    var InvoiceNo = $('#add_invoice_no').val();
    var item = $('#add_inputItemNoHidden').val();
    var item_desc = $('#add_inputItemDesc').val();
    var qty = parseInt($('#add_inputQty').val());
    var box = $('#add_inputBox').val();
    var box_qty = $('#add_inputBoxQty').val();
    var lot_no = $('#add_inputLotNo').val();
    var location = $('#add_inputLocation').val();
    var supplier = $('#add_inputSupplier').val();
    var pressed_date = $('#pressed_date').val();
    var plating_date = $('#plating_date').val();
    if ($.trim(pressed_date) == "" || pressed_date == null) {
        pressed_date = "N/A";
    }
    if ($.trim(plating_date) == "" || pressed_date == null) {
        plating_date = "N/A";
    }
    //   var r_qty = 0;
    //   var variance = 0;
    //   var new_var_qty = 0;
    var item_code = $('#in_item_' + item).val();
    var item_id = $('#in_id_' + item).val();

    if (item == '' || qty == '' || box == '' || box_qty == '' || lot_no == '' || supplier == '') {
        failedMsg('Please fill out all the inputs.');
    } else {
        if ($('#add_notForIqc').val() == 1) {
            var not_for_iqc = 'checked="checked"';
        }

        $('#td_item_' + item).html(item + '<input type="hidden" name="item_summary[]" id="in_item_' + item + '"/>' +
            '<input type="hidden" name="id[]" id="in_id_' + item + '"/>');
        $('#in_item_' + item).val(item_code);
        $('#in_id_' + item).val(item_id);
        $('#td_iqc_chk_' + item).html('<input type="checkbox" class="iqc_chk" name="iqc[]" ' + not_for_iqc + ' value="' + item + '" disabled="disabled">');

        var cnt = $('#tbl_batch_body tr').length + 1;
        $('.remove_qty').remove();
        $('#in_iqc_chk_' + item).remove();

        tbl_batch = '<tr class="batch_remove thisremove_' + cnt + '">' +
            '<td style="width:2.1%">' +
            '<a href="javascript:;" class="x_remove_batch close" data-id="thisremove_' + cnt + '" data-qty="' + qty + '" data-item="' + item + '"><i class="fa fa-times"></i></a>' +
            '</td>' +
            '<td style="width:4.1%">' +
            '<a href="javascript:;" class="btn input-sm blue edit_item_batch" data-bid="' + cnt + '" disabled="disabled">' +
            '<i class="fa fa-edit"></i>' +
            '<a>' +
            '</td>' +
            '<td style="width:3.1%">' + cnt + '</td>' +
            '<td style="width:7.1%">' + item +
            '<input type="hidden" name="item_batch[]" value="' + item + '">' +
            '<input type="hidden" name="id_batch[]" value="">' +
            '</td>' +
            '<td style="width:15.1%">' + item_desc +
            '<input type="hidden" name="item_desc_batch[]" value="' + item_desc + '">' +
            '</td>' +
            '<td style="width:7.1%">' + qty +
            '<input type="hidden" name="qty_batch[]" value="' + qty + '">' +
            '</td>' +
            '<td style="width:7.1%">' + box +
            '<input type="hidden" name="box_batch[]" value="' + box + '">' +
            '</td>' +
            '<td style="width:5.1%">' + box_qty +
            '<input type="hidden" name="box_qty_batch[]" value="' + box_qty + '">' +
            '</td>' +
            '<td style="width:18.1%">' + lot_no +
            '<input type="hidden" name="lot_no_batch[]" value="' + lot_no + '">' +
            '</td>' +
            '<td style="width:7.1%">' + location +
            '<input type="hidden" name="location_batch[]" value="' + location + '">' +
            '</td>' +
            '<td style="width:7.1%">' + supplier +
            '<input type="hidden" name="supplier_batch[]" value="' + supplier + '">' +
            '</td>' +
            '<td style="width:7.1%;display:none;">' + pressed_date +
            '<input type="hidden" name="pressed_date_batch[]" value="' + pressed_date + '">' +
            '</td>' +
            '<td style="width:7.1%;display:none;">' + plating_date +
            '<input type="hidden" name="plating_date_batch[]" value="' + plating_date + '">' +
            '</td>' +
            '<td style="width:6.1%">' +
            '<input type="checkbox" class="notforiqc_batch" name="notforiqc_batch[]" value="' + item + '" disabled="disabled " ' + not_for_iqc + '>' +
            '</td>' +
            '<td style="width:5.1%">' +
            '<input type="checkbox" name="print_barcode[]" class="print_barcode" disabled="disabled" id="print_br_' + item + '" value="' + item + '">' +
            '</td>' +
            '<td style="width:5.1%">' +
            '<a href="javascript:;" class="btn input-sm grey-gallery barcode_item_batch" data-txnno="' + $('#invoiceno').val() + '" data-txndate="' + $('#invoicedate').val() + '" data-itemno="' + item + '" data-itemdesc="' + item_desc + '" data-qty="' + qty + '" data-bcodeqty="' + box_qty + '" data-lotno="' + lot_no + '" data-location="' + location + '">' +
            '<i class="fa fa-barcode"></i>' +
            '<a>' +
            '</td>' +
            '</tr>';



        if (itemcode.indexOf($('#add_inputItemNoHidden').val()) > -1 &&
            QtyLists.indexOf($('#add_inputQty').val()) > -1 &&
            LotList.indexOf($('#add_inputLotNo').val()) > -1) {

            // failedMsg("Item code already exist with same qty");
            Valid = false;

        }

        if (Valid) {

            $('#tbl_batch_body').append(tbl_batch);
        }
        else {
            // var r = confirm("Item Code already exist do you want to overwrite the existing data?");
            // if (r == true) {
            //   $('#tbl_batch_body').append(tbl_batch);
            // }
            bootbox.confirm("Item Code already exist do you want to overwrite the existing data?", function (result) {
                if (result) {
                    $('#tbl_batch_body').append(tbl_batch);
                }
            });
        }
    }
}
function getItemData() {
    var data = {
        _token: token,
        itemcode: $('#add_inputItemNo').val(),
        invoice_no: $('#invoiceno').val()
    };

    $.ajax({
        url: GetReceivingItemData,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        var item = '';
        var itemname = '';
        var rackno = '';
        $.each(data, function (i, x) {
            item = x.code;
            itemname = x.name;
            rackno = x.rackno;
        });

        $('#add_inputItemNoHidden').val(item);
        $('#add_inputItemDesc').val(itemname);
        $('#add_inputLocation').val(rackno);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });
}
function checkIfNotForIQC(item) {
    var data = {
        _token: token,
        item: item,
        receivingno: $('#receivingno').val(),
    };
    $.ajax({
        url: GetReceivingNotForIQC,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        if (data.check == 1) {
            $('#add_notForIqc').val('1');
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });
}
function saveMR(mrdata, summarydata, notForIQC, savestate) {
    $('#loading').modal('show');
    var data = {
        _token: token,
        savestate: savestate,
        mrdata: JSON.stringify(mrdata),
        summarydata: JSON.stringify(summarydata),
        notForIQC: JSON.stringify(notForIQC),
    };

    $.ajax({
        url: PostReceivingSaveData,
        type: "POST",
        dataType: 'json',
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        if (data.request_status == 'success') {
            successMsg(data.msg);
            ViewState();
            getMRdata('',data.receive_no);
        } else {
            failedMsg(data.msg);
            ViewState();
            getMRdata('',data.receive_no);
        }
        $('#loading').modal('hide');
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg(data.msg);
        ViewState();
    });
    $('#loading').modal('hide');
}
function saveMrWithBatch(mrdataedit, summarydata, batchdata, notForIQC, notForIQCbatch, IsPrinted, savestate) {
    $('#loading').modal('show');
    var data = {
        _token: token,
        savestate: savestate,
        mrdata: JSON.stringify(mrdataedit),
        summarydata: JSON.stringify(summarydata),
        batchdata: JSON.stringify(batchdata),
        notForIQC: JSON.stringify(notForIQC),
        notForIQCbatch: JSON.stringify(notForIQCbatch),
        IsPrinted: JSON.stringify(IsPrinted)
    };

    $.ajax({
        url: PostReceivingSaveData,
        type: "POST",
        dataType: 'json',
        data: data,
    }).done(function (d) {
        $('#loading').modal('hide');
        if (d.request_status == 'success') {
            successMsg(d.msg);
            ViewState();
            getMRdata('',mrdataedit.receive_no);
        } else {
            failedMsg(d.msg);
            ViewState();
            getMRdata('',mrdataedit.receive_no);
            
        }
        
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg(data.msg);
        ViewState();
    });
}
function getSingleBatchItem(id, bid) {
    var data = {
        _token: token,
        id: id
    };

    $.ajax({
        url: GetReceivingSingleBatchItem,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        var item = '', item_desc = '', qty = '', box = '', box_qty = '', lot_no = '', location = '', supplier = '';
        $.each(data, function (index, x) {
            item = x.item;
            item_desc = x.item_desc;
            qty = x.qty;
            box = x.box;
            box_qty = x.box_qty;
            lot_no = x.lot_no;
            location = x.location;
            supplier = x.supplier;
        });

        $('#edit_inputItemNo').prop('disabled', true);

        $('#edit_inputBatchId').val(bid);
        $('#edit_inputItemNo').val(item + ' | ' + item_desc);
        $('#edit_inputItemNoHidden').val(item);
        $('#edit_inputItemDesc').val(item_desc);
        $('#edit_inputQty').val(qty);
        $('#edit_inputQtyHidden').val(qty);

        var $edit_inputBox = $("<option selected='selected'></option>").val(box).text(box);
        $("#edit_inputBox").append($edit_inputBox).trigger('change');

        // $('#edit_inputBox').select2('data', {
        //     id: box,
        //     text: box
        // });

        $('#edit_inputBoxQty').val(box_qty);
        $('#edit_inputLotNo').val(lot_no);
        $('#edit_inputLocation').val(location);
        $('#edit_inputSupplier').val(supplier);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });
}
//#endregion

//#region For Details Update from QC
function showNeedsModificationItem() {
    $('#loading').modal('show');
    $.ajax({
        url: showNeedsModificationItemURL,
        type: 'GET',
        dataType: 'JSON',
    }).done(function (d) {
        drawNeedModificationItemDatatable(d.data);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
    }).always( function() {
        $('#loading').modal('hide');
    });
}
function drawNeedModificationItemDatatable(dataArr) {
    $('#tbl_item').DataTable().clear();
    $('#tbl_item').DataTable().destroy();
    $('#tbl_item').DataTable({
        data: dataArr,
        searching: false,
        lengthChange: false,
        columns: [
            {
                data: function (data) {
                    if (data.id !== '') {
                        return '<input type="checkbox" class="qc_check_item" value="' + data.id + '"/>';
                    }
                    return '';
                }, orderable: false, searchable: false
            },
            {
                data: function (data) {
                    return '<button type="button" class="btn btn-sm blue btn_qc_edit"><i class="fa fa-edit"></button>';
                }, orderable: false, searchable: false
            },
            { data: 'receiving_no', orderable: false, searchable: false },
            { data: 'invoice_no', orderable: false, searchable: false },
            { data: 'item', orderable: false, searchable: false },
            { data: 'item_desc', orderable: false, searchable: false },
            { data: 'qty', orderable: false, searchable: false },
            { data: 'lot_no', orderable: false, searchable: false },
            { data: 'supplier', orderable: false, searchable: false },
            { data: 'received_date', orderable: false, searchable: false },
            { data: 'ins_by', orderable: false, searchable: false },
            { data: 'qc_remarks', orderable: false, searchable: false },
        ],
        order: [[10, 'asc']]
    });
}
function noNeedModification(checkID) {
    $('#loading').modal('show');
    var param = {
        _token: token,
        ids: checkID
    };

    $.ajax({
        url: removeNeedsModificationItemURL,
        type: 'POST',
        dataType: 'JSON',
        data: param
    }).done(function (data, textStatus, jqXHR) {
        if (data.status == 'success') {
            showNeedsModificationItem();
            successMsg(data.msg);
        } else {
            failedMsg(data.msg);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
    }).always( function() {
        $('#loading').modal('hide');
    });
}
//#endregion

//#region STATES
function ViewState() {
    //clear();
    if (parseInt(access_state) !== 2) {
        $('#receivingno').prop('disabled', false);
        $('#palletno').prop('readonly', true);
        $('#receivingdate').prop('readonly', true);
        $('#invoiceno').prop('readonly', true);
        $('#btn_checkinv').prop('disabled', false);

        $('#btn_add_batch').prop('disabled', true);
        $('#btn_upload').prop('disabled', true);
        $('#btn_all_batch').prop('disabled', true);
        $('#btn_delete_batch').prop('disabled', true);

        $('#btn_save').hide();
        $('#btn_cancel').hide();
        $('#btn_discard').hide();
        $('#btn_addnew').show();

        if ($('#status').val() == 'Cancelled') {
            $('#btn_edit').hide();
        } else {
            $('#btn_edit').show();
        }

        $('#btn_search').show();
        $('#btn_barcode').show();
        $('#btn_print').show();
        $('#btn_print_iqc').show();
        $('#btn_cancel_mr').show();
        $('#btn_refresh').show();

        $('#checkbox_iqc').prop('disabled', true);
        $('.addBatchsummary').hide();
    } else {
        $('#receivingno').prop('disabled', false);
        $('#palletno').prop('readonly', true);
        $('#receivingdate').prop('readonly', true);
        $('#invoiceno').prop('readonly', true);
        $('#btn_checkinv').prop('disabled', true);

        $('#btn_add_batch').prop('disabled', true);
        $('#btn_upload').prop('disabled', true);
        $('#btn_all_batch').prop('disabled', true);
        $('#btn_delete_batch').prop('disabled', true);

        $('#btn_save').hide();
        $('#btn_cancel').hide();
        $('#btn_discard').hide();
        $('#btn_addnew').hide();

        if ($('#status').val() == 'Cancelled') {
            $('#btn_edit').hide();
        } else {
            $('#btn_edit').hide();
        }

        $('#btn_search').show();
        $('#btn_barcode').hide();
        $('#btn_print').hide();
        $('#btn_print_iqc').hide();
        $('#btn_cancel_mr').hide();
        $('#btn_refresh').hide();

        $('#checkbox_iqc').prop('disabled', true);
        $('.addBatchsummary').hide();
        $('#batchfiles').prop('disabled', true);
        $('#uploadbatchfiles > div > div.col-sm-6 > div > span').css({ 'pointer-events': 'none', 'opacity': '0.4' });
    }
}
function AddState() {
    $('.chk_del_batch').prop('disabled', false);
    $('.edit_item_batch').removeAttr('disabled');
    $('#btn_add_batch').prop('disabled', true);
    $('#btn_upload').prop('disabled', true);
    $('#btn_all_batch').prop('disabled', true);
    $('#btn_delete_batch').prop('disabled', true);
    $('#palletno').prop('readonly', false);
    $('#invoiceno').prop('readonly', false);

    $('#btn_edit').hide();
    $('#btn_discard').hide();
    $('#btn_search').hide();
    $('#btn_barcode').hide();
    $('#btn_print').hide();
    $('#btn_print_iqc').hide();
    $('#btn_cancel_mr').hide();
    $('#btn_save').show();
    $('#btn_cancel').show();
    $('#btn_addnew').hide();

    $('#add_inputItemNo').prop('disabled', false);
    $('#receivingno').prop('readonly', true);
    $('#receivingdate').prop('readonly', false);

    $('#checkbox_iqc').prop('disabled', false);
    $('#checkbox_iqc').removeAttr('readonly');
    $('.iqc_chk').prop('disabled', false);

    //$('.barcode_item_batch').prop('disabled',false);
    $('.addBatchsummary').hide();

    $('#savestate').val('ADD');

    // getPackage();
    // getItems();
}
function EditState() {
    $('#btn_edit').hide();
    $('#btn_cancel').hide();
    $('#btn_search').hide();
    $('#btn_barcode').hide();
    $('#btn_print').hide();
    $('#btn_print_iqc').hide();
    $('#btn_cancel_mr').hide();
    $('#btn_addnew').hide();
    $('#btn_save').show();
    $('#btn_discard').show();

    $('.chk_del_batch').prop('disabled', false);
    $('.edit_item_batch').removeAttr('disabled');
    $('#btn_add_batch').prop('disabled', false);
    $('#btn_upload').prop('disabled', false);
    $('#btn_all_batch').prop('disabled', false);
    $('#btn_delete_batch').prop('disabled', false);
    $('#add_inputItemNo').prop('disabled', false);
    $('#receivingno').prop('disabled', true);
    $('#palletno').prop('readonly', false);
    $('.barcode_item_batch').prop('disabled', false);
    $('#invoiceno').prop('disabled', true);
    $('#receivingdate').prop('disabled', false);

    $('#checkbox_iqc').prop('disabled', false);
    $('#checkbox_iqc').removeAttr('readonly');
    $('.iqc_chk').prop('disabled', false);
    $('.addBatchsummary').show();

    $('#savestate').val('EDIT');

    // getPackage();
    // getItems();
}
//#endregion

//#region OTHERS
function tblDetails() {
    var table = `
    <table class="table table-bordered sortable table-fixedheader table-striped" id="tbl_details">
        <thead>
            <tr>
                <td class="col-xs-2">Item/Part No.</td>
                <td class="col-xs-3">Item Description</td>
                <td class="col-xs-1">Quantity</td>
                <td class="col-xs-2">PO/PR No.</td>
                <td class="col-xs-2">Unit Price</td>
                <td class="col-xs-2">Amount</td>
            </tr>
        </thead>
    <tbody id="tbl_details_body" style="font-size:10px;"></tbody>
    </table>`;
    $('#div_tbl_details').append(table);
}
function tblSummary() {
    var table = `
        <table class="table table-bordered sortable table-fixedheader table-striped" id="tbl_summary">
        <thead>
            <tr>
                <td class="col-xs-1"></td>
                <td class="col-xs-2">Item/Part No.</td>
                <td class="col-xs-2">Item Description</td>
                <td class="col-xs-2">Quantity</td>
                <td class="col-xs-2">Received Qty.</td>
                <td class="col-xs-2">Variance</td>
                <td class="col-xs-1">
                    <div class="checker disabled" id="uniform-checkbox_iqc">
                        <span><input type="checkbox" id="checkbox_iqc" name="checkbox_iqc" disabled="disabled" class="input-sm checkboxes" style="margin: 0px;" /></span>
                    </div>
                    Not Reqd
                </td>
            </tr>
        </thead>
        <tbody id="tbl_summary_body" style="font-size: 10px;"></tbody>
        <tfoot></tfoot>
    </table>`;
    $('#div_tbl_summary').append(table);
}
function tblBatch() {
    var table = `
        <table class="table table-bordered table-fixedheader table-striped" id="tbl_batch">
        <thead id="th_batch">
            <tr>
                <td class="table-checkbox" style="font-size: 10px; width: 2.1%;"></td>
                <td style="width: 4.1%;"></td>
                <td style="width: 6.1%;">Batch ID</td>
                <td style="width: 7.1%;">Item/Part No.</td>
                <td style="width: 18.1%;">Item Description</td>
                <td style="width: 7.1%;">Quantity</td>
                <td style="width: 10.1%;">Package Category</td>
                <td style="width: 7.1%;">Package Qty.</td>
                <td style="width: 7.1%;">Lot No.</td>
                <td style="width: 7.1%;">Location</td>
                <td style="width: 7.1%;">Supplier</td>
                <td style="width: 6.1%;">Not Reqd</td>
                <td style="width: 5.1%;">Printed</td>
                <td style="width: 5.1%;"></td>
            </tr>
        </thead>
        <tbody id="tbl_batch_body" style="font-size: 10px;"></tbody>
    </table>`;
    $('#div_tbl_batch').append(table);
}
function supplierDropdown(el) {
    var opt = '<option value="">-- Select Supplier --</option>';
    $(el).html(opt);
    $.ajax({
        url: GetReceivingSupplier,
        type: 'GET',
        dataType: 'JSON',
        data: {
            _token: "{{Session::token()}}",
            name: "Material Receiving Supplier Input"
        },
    }).done(function (data, textStatus, jqXHR) {
        $.each(data, function (i, x) {
            opt = '<option value="' + x.description + '">' + x.description + '</option>';
            $(el).append(opt);
        });
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });

}
function successMsg(msg) {
    $('#title').html('<strong><i class="fa fa-check"></i></strong> Success!')
    $('#err_msg').html(msg);
    $('#msg').modal('show');
}
function failedMsg(msg) {
    $('#title').html('<strong><i class="fa fa-exclamation-triangle"></i></strong> Failed!')
    $('#err_msg').html(msg);
    $('#msg').modal('show');
}
function clear() {
    $('.clear').val('');
}
function checkAllCheckboxesMR(tbl_id, check_all_id, check_item_class, delete_btn_id) {
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
function cancelInvoice() {
    var data = {
        _token: token,
        receivingno: $('#receivingno').val(),
    };
    $.ajax({
        url: PostReceivingCancelInvoice,
        type: "POST",
        data: data,
    }).done(function (d) {
        if(d.success) {
            MRdataInfo(d.data);
            ViewState();
        }else {
            var receivingno = $('#receivingno').val();
            $('#mat_info .clear').val("");
            $('#receivingno').val(receivingno);
            ViewState();
            failedMsg("There's some error while processing.");
        }
        $('#loading').modal('hide');
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });
}
function deleteBatchItem() {
    $('#loading').modal('show');
    var delete_batch_arr = [];
    $(".chk_del_batch:checked").each(function () {
        delete_batch_arr.push({
            batch_id : $(this).val(),
            qty : $(this).attr('data-qty'),
            item : $(this).attr('data-item')
        });
    });
    var receive_no = $('#receivingno').val();
    var params = {
        delete_batch_arr : delete_batch_arr,
        receivingno: receive_no
    };
    $('.details_remove').remove();
    $('.summary_remove').remove();
    $('.batch_remove').remove();
    $.ajax({
        url: PostReceivingDeleteBatch,
        type: "POST",
        data: {
            _token: token,
            params : params
        },
    }).done(function (d) {
        if(d.success) {
            MRdataInfo(d.data);
            ViewState();
        }else {
            var receivingno = $('#receivingno').val();
            $('#mat_info .clear').val("");
            $('#receivingno').val(receivingno).trigger('change');
            ViewState();
            failedMsg("There's some error while processing.");
        }
        $('#loading').modal('hide');
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });
}
function refreshInvoice() {
    $('#loading').modal('show');
    
    var data = {
        _token: token,
        invoiceno: $('#invoiceno').val()
    };

    $.ajax({
        url: GetReceivingRefreshInvoice,
        type: 'GET',
        dataType: 'JSON',
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        if (data.return_status == 'success') {
            $('#loading').modal('hide');
            successMsg(data.msg)
            setTimeout(() => {
                getMRdata('',data.receivingno);
            }, 200);
        } else {
            failedMsg(data.msg);
        }
        $('#loading').modal('hide');
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });
}
function isCheck(element) {
    if (element.is(':checked')) {
        return true;
    } else {
        return false;
    }
}
function getMRStatus(status) {
    if (status == 'Open') {
        return 'O'
    }

    if (status == 'Cancelled') {
        return 'C'
    }

    if (status == 'Closed') {
        return 'X'
    }
}
function getPackage() {
    var data = {
        _token: token
    };

    $.ajax({
        url: GetReceivingPackage,
        type: "GET",
        data: data,
    }).done(function (data, textStatus, jqXHR) {
        $('#add_inputBox').select2({
            data: data
        });
        $('#edit_inputBox').select2({
            data: data
        });
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR);
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });
}
function navigate(to){
    getMRdata(to,$('#receivingno').val());
}
//#endregion