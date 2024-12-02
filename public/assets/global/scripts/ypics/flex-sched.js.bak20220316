var _ProdBalanceErrors = [];
$( function() {
    ErrorsDataTable(_ProdBalanceErrors);

    $('#btn_show_error').on('click', function() {
        $('#errors_modal').modal('show');
    });

    $('#btn_download_errors').on('click', function() {
        var url = downloadZYMRURL + "?folder=ProdBalance&&filename=ZYPF5210_PMI_TS.xls";
        window.open(url, '_blank');
    });

    $('#btn_inventory_data_download').on('click', function () {
        $('#loading').modal('show');
        $.ajax({
            url: inventory_data_url,
            type: 'GET',
            dataType: 'JSON',
            data: {
                _token: token
            }
        }).done( function(data, textStatus, xhr) {
            msg(data.msg,data.status);

            if (data.status == "success") {
                var url = downloadInventoryURL + "?folder=InventoryData&&filename=StockQuery.csv";
                window.open(url, '_blank');
            }

        }).fail( function(xhr, textStatus, errorThrown) {

        }).always( function() {
            $('#loading').modal('hide');
        });
    });

    $('#frmPartsIncoming').on('submit', function (e) {
        var formObj = $(this);
        var formURL = formObj.attr("action");
        var formData = new FormData(this);
        var fileName = $("#pps_del_file").val();
        var ext = fileName.split('.').pop();
        var pros = $('#pps_del_file').val().replace("C:\fakepath", "");
        var fileN = pros.substring(12, pros.length);
        e.preventDefault();
        if ($("#pps_del_file").val() == '') {
            msg("No File was selected. Please select a file.","failed");
        } else {
            if (fileName != '') {
                if (ext == 'xls' || ext == 'xlsx' || ext == 'XLS' || ext == 'XLSX' || ext == 'Xls') {

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
                        $('#file_checking_msg').html("Processing PPS Delivery data with YPICS Data...");

                        $.ajax({
                            url: processPPSDeliveryFileURL,
                            type: 'GET',
                            dataType: 'JSON',
                            data: {
                                _token: token
                            }
                        }).done(function (data, textStatus, xhr) {
                            msg(data.msg, data.status);

                            if (data.status == "success") {
                                var url = downloadPPSdeliveryURL + "?folder=PartsIncomingPlan&&filename=MRP.csv";
                                window.open(url, '_blank');
                            }

                            $("#pps_del_file").empty();
                        }).fail(function (xhr, textStatus, errorThrown) {
                            console.log(xhr);
                        }).always(function () {
                            $('#file_checking_modal').modal('hide');
                        });

                    }).fail(function (xhr, textStatus, errorThrown) {
                        console.log(xhr);
                        $('#file_checking_modal').modal('hide');
                    }).always(function () {
                        //$('#file_checking_modal').modal('hide');
                    });
                } else {
                    $('#file_checking_modal').modal('hide');
                    msg("File Format not supported.","failed");
                }
            }
        }
    });

    $('#frmProdBalance').on('submit', function (e) {
        var formObj = $(this);
        var formURL = formObj.attr("action");
        var formData = new FormData(this);
        var fileName = $("#zymr_file").val();
        var ext = fileName.split('.').pop();
        var pros = $('#zymr_file').val().replace("C:\fakepath", "");
        var fileN = pros.substring(12, pros.length);
        e.preventDefault();
        if ($("#zymr_file").val() == '') {
            msg("No File was selected. Please select a file.","failed");
        } else {
            if (fileName != '') {
                if (ext == 'txt' || ext == 'TXT') {

                    $('#file_checking_msg').html("Checking "+fileN+" File..");
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
                        response = JSON.parse(returns);
                        $('#file_checking_msg').html("Processing "+fileN+" data with YPICS Data...");

                        if (response.status == "success") {
                            $.ajax({
                                url: processZYMRFileURL,
                                type: 'GET',
                                dataType: 'JSON',
                                data: {
                                    _token: token
                                }
                            }).done(function (data, textStatus, xhr) {
                                _ProdBalanceErrors = data.errors_data;
                                var error_count = _ProdBalanceErrors.length;

                                $('#error_count').html(error_count);
                                ErrorsDataTable(_ProdBalanceErrors);

                                msg(data.msg, data.status);

                                if (data.status == "success") {
                                    var url = downloadZYMRURL + "?folder=ProdBalance&&filename=PRODUCTION_PMI_TS.csv";
                                    window.open(url, '_blank');
                                }

                                $("#zymr_file").empty();
                            }).fail(function (xhr, textStatus, errorThrown) {
                                console.log(xhr);
                            }).always(function () {
                                $('#file_checking_modal').modal('hide');
                            });
                        } else {
                            $('#file_checking_modal').modal('hide');

                            msg(response.msg,response.status);
                        }
                            

                    }).fail(function (xhr, textStatus, errorThrown) {
                        console.log(xhr);
                        $('#file_checking_modal').modal('hide');
                    }).always(function () {
                        //$('#file_checking_modal').modal('hide');
                    });
                } else {
                    $('#file_checking_modal').modal('hide');
                    msg("File Format not supported.","failed");
                }
            }
        }
    });
});

function ErrorsDataTable(dataArr) {
	$('#tbl_errors').DataTable().clear();
	$('#tbl_errors').DataTable().destroy();
	$('#tbl_errors').DataTable({
		processing: true,
		data: dataArr,
		deferRender: true,
		pageLength: 10,
		pagingType: "bootstrap_full_number",
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
            { data: 'Haccyuu_No' },
            { data: 'Haccyuu_Hoban' },
            { data: 'Hinmoku_Code' },
            { data: 'Hinmoku_tekisuto' },
            { data: 'Hokanbasyo' },
            { data: 'MRPKanrisya' },
            { data: 'Haccyuu_Bi' },
            { data: 'Siiresaki_Code' },
            { data: 'Shiiresaki_Tekisuto' },
            { data: 'Haccyuu_Qty' },
            { data: 'Haccyuu_Zan_Qty' },
            { data: 'Toukei_kannrenn_nounyuu_Bi' },
            { data: 'Kaitou_Nouki' },
            { data: 'Kaitou_Jikoku' },
            { data: 'Kaitou_Qty' },
            { data: 'Tokuisaki_Code' },
            { data: 'Tokuisaki_Mei' },
            { data: 'Tokuisaki_Nouki' }
		],
        fnDrawCallback: function() {
            $("#tbl_errors").wrap("<div style='overflow:auto; width:100%;position:relative;'></div>");
        },
	});
}

