var dataColumn = [
    {
        data: function (data) {
            return '<input type="checkbox" class="input-sm checkboxes" value="' + data.id + '" name="checkitem" id="checkitem"></input>';
        }, name: 'id'
    },
    { data: 'action', name: 'action', orderable: false, searchable: false },
    {
        data: function (data) {
            return data.date + '<input type="hidden" id="hd_date_inspected" value="' + data.date + '" name="hd_date_inspected[]">';
        }, name: 'date'
    },
    {
        data: function (data) {
            return data.po_no + '<input type="hidden" id="hd_pono" value="' + data.po_no + '" name="hd_pono[]">';
        }, name: 'po_no'
    },
    {
        data: function (data) {
            return data.device_name + '<input type="hidden" id="hd_device_name" value="' + data.device_name + '" name="hd_device_name[]">';
        }, name: 'device_name'
    },
    {
        data: function (data) {
            var qty = '';
            if (data.qty == null) {
                qty = 0;
            } else {
                qty = data.qty;
            }
            return qty + '<input type="hidden" id="hd_qty" value="' + qty + '" name="hd_qty[]">';
        }, name: 'qty'
    },
    {
        data: function (data) {
            return data.total_num_of_lots + '<input type="hidden" id="hd_total_lots" value="' + data.total_num_of_lots + '" name="hd_total_lots[]">';
        }, name: 'total_num_of_lots'
    }
];
$(function () {
    loadFGSdata(GetFGSdata + "?mode=");
    $('#hd_report_status').val("");
    /*$('#fgsdatatable').DataTable();*/
    $('select[name=group1]').select2({
        placeholder: 'Select Field',
        dropdownParent: $('#GroupByModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
    });
    $('select[name=group2]').select2({
        placeholder: 'Select Field',
        dropdownParent: $('#GroupByModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
    });
    $('select[name=group3]').select2({
        placeholder: 'Select Field',
        dropdownParent: $('#GroupByModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
    });
    $('select[name=group4]').select2({
        placeholder: 'Select Field',
        dropdownParent: $('#GroupByModal .modal-content'),
        theme: 'bootstrap',
        width: 'auto',
        allowClear: true,
    });
    $('#date').datepicker();
    $('#groupby_datefrom').datepicker();
    $('#groupby_dateto').datepicker();
    $('#search_from').datepicker();
    $('#search_to').datepicker();

    $('#date').on('change', function () {
        $(this).datepicker('hide');
    });

    $('#groupby_datefrom').on('change', function () {
        $(this).datepicker('hide');
    });

    $('#groupby_dateto').on('change', function () {
        $(this).datepicker('hide');
    });

    $('#search_from').on('change', function () {
        $(this).datepicker('hide');
    });

    $('#search_to').on('change', function () {
        $(this).datepicker('hide');
    });

    $('#btn_add').on('click', function () {
        $('#AddNewModal').modal('show');
        $('#hd_status').val("ADD");
        $('#po_no').val("");
        $('#device_name').val("");
        $('#quantity').val("");
        $('#total_lots').val("");
        $('#er_po_no').html("");
        $('#er_device_name').html("");
        $('#er_quantity').html("");
        $('#er_total_lots	').html("");
    });

    $('#btn_clear').click(function () {
        $('#po_no').val("");
        $('#device_name').val("");
        $('#quantity').val("");
        $('#total_lots').val("");
        $('#hd_status').val("");
    });

    $('#btn_groupby').on('click', function () {
        $('#GroupByModal').modal('show');
        $('#groupby_datefrom').val("");
        $('#groupby_dateto').val("");
        $('#group1').select2('val', "");
        $('#group1content').select2('val', "");

        //to classify group by when reporting----------
        $('#hd_report_status').val("GROUPBY");
        $('#hd_search_from').val("");
        $('#hd_search_to').val("");
        $('#hd_search_pono').val("");
    });

    $('#btn_search').on('click', function () {
        $('#SearchModal').modal('show');
        $('#search_pono').val("");
        $('#search_from').val("");
        $('#search_to').val("");
        $('#er_search_from').html("");
        $('#er_search_to').html("");

        //to classify group by when reporting----------
        $('#hd_report_status').val("SEARCH");
        $('#hd_search_from').val("");
        $('#hd_search_to').val("");
        $('#hd_groupfield').val("");
        $('#hd_value').val("");
    });

    $('#btn_clear').on('click', function () {
        $('#po_no').val('');
        $('#device_name').val('');
        $('#quantity').val('');
        $('#total_lots').val('');
    });

    $('.checkAllitems').change(function () {
        if ($('.checkAllitems').is(':checked')) {
            $('.deleteAll-task').removeClass("disabled");
            $('#add').addClass("disabled");
            $('input[name=checkitem]').parents('span').addClass("checked");
            $('input[name=checkitem]').prop('checked', this.checked);
        } else {
            $('input[name=checkitem]').parents('span').removeClass("checked");
            $('input[name=checkitem]').prop('checked', this.checked);
            $('.deleteAll-task').addClass("disabled");
            $('#add').removeClass("disabled");
        }
    });

    $('.checkboxes').change(function () {
        $('input[name=checkAllitem]').parents('span').removeClass("checked");
        $('input[name=checkAllitem]').prop('checked', false);
        if ($('.checkboxes').is(':checked')) {
            $('.deleteAll-task').removeClass("disabled");
            $('#add').addClass("disabled");
        } else {
            $('.deleteAll-task').addClass("disabled");
            $('#add').removeClass("disabled");
        }
    });

    $('#tblforfgs').on('click', '.edit-task', function () {
        $('#AddNewModal').modal('show');
        $('#hd_status').val("EDIT");
        var edittext = $(this).val().split('|');
        var editid = edittext[0];
        var date = edittext[2];
        var pono = edittext[1];
        var device = edittext[3];
        var qty = edittext[4];
        var tlots = edittext[5];
        var dbcon = edittext[6];
        $('#date').val(date);
        $('#po_no').val(pono);
        $('#device_name').val(device);
        $('#quantity').val(qty);
        $('#total_lots').val(tlots);
        $('#dbcon').val(dbcon);
        $('#id').val(editid);
    });
    /*    if (window.event.keyCode == 13 ) return false;*/
    $('#po_no').keyup(function () {
        $('#er_po_no').html("");
    });

    $('#device_name').keyup(function () {
        $('#er_device_name').html("");
    });
    $('#quantity').keyup(function () {
        $('#er_quantity').html("");
    });
    $('#total_lots').keyup(function () {
        $('#er_total_lots').html("");
    });
    $('#search_from').click(function () {
        $('#er_search_from').html("");
    });
    $('#search_to').click(function () {
        $('#er_search_to').html("");
    });

    $('#po_no').on('change', function () {
        var pono = $(this).val();
        $.ajax({
            url: GetFGSYPICSRecordsPO,
            method: 'get',
            data: {
                pono: pono
            },
        }).done(function (data, textStatus, jqXHR) {
            console.log(data);
            $('#device_name').val(data[0]['DEVNAME']);
            if (pono == "") {
                $('#device_name').val("");
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown + '|' + textStatus);
        });
    });

    $('#btn_pdf').on('click', function () {
        var searchpono = $('#search_pono').val();
        var datefrom = $('#search_from').val();
        var dateto = $('#search_to').val();
        var status = $('#hd_report_status').val();

        var tableData = {
            date_inspected: $('input[name^="hd_date_inspected[]"]').map(function () { return $(this).val(); }).get(),
            pono: $('input[name^="hd_pono[]"]').map(function () { return $(this).val(); }).get(),
            device_name: $('input[name^="hd_device_name[]"]').map(function () { return $(this).val(); }).get(),
            qty: $('input[name^="hd_qty[]"]').map(function () { return $(this).val(); }).get(),
            total_lots: $('input[name^="hd_total_lots[]"]').map(function () { return $(this).val(); }).get(),
            status: status,
            searchpono: searchpono,
            datefrom: datefrom,
            dateto: dateto
        };
        var url = GetFGSPrintReport + "?data=" + encodeURIComponent(JSON.stringify(tableData));
        window.location.href = url;
    });

    $('#btn_excel').on('click', function () {
        var searchpono = $('#search_pono').val();
        var datefrom = $('#search_from').val();
        var dateto = $('#search_to').val();
        var status = $('#hd_report_status').val();

        var tableData = {
            date_inspected: $('input[name^="hd_date_inspected[]"]').map(function () { return $(this).val(); }).get(),
            pono: $('input[name^="hd_pono[]"]').map(function () { return $(this).val(); }).get(),
            device_name: $('input[name^="hd_device_name[]"]').map(function () { return $(this).val(); }).get(),
            qty: $('input[name^="hd_qty[]"]').map(function () { return $(this).val(); }).get(),
            total_lots: $('input[name^="hd_total_lots[]"]').map(function () { return $(this).val(); }).get(),
            status: status,
            searchpono: searchpono,
            datefrom: datefrom,
            dateto: dateto
        };

        var url = GetFGSPrintReportExcel + "?data=" + encodeURIComponent(JSON.stringify(tableData));
        window.location.href = url;
    });

});

function Save() {
    var date = $('#date').val();
    var pono = $('#po_no').val();
    var device = $('#device_name').val();
    var quantity = $('#quantity').val();
    var tlots = $('#total_lots').val();
    var status = $('#hd_status').val();
    var dbcon = dbcon;
    var id = $('#id').val();
    var myData = { date: date, pono: pono, device: device, quantity: quantity, tlots: tlots, status: status, dbcon: dbcon, id: id };
    if (pono == "") {
        $('#er_po_no').html("PO Number field is empty");
        $('#er_po_no').css('color', 'red');
        return false;
    }
    if (device == "") {
        $('#er_device_name').html("Device Name field is empty");
        $('#er_device_name').css('color', 'red');
        return false;
    }
    if (quantity == "") {
        $('#er_quantity').html("Quantity field is empty");
        $('#er_quantity').css('color', 'red');
        return false;
    }
    if (tlots == "") {
        $('#er_total_lots').html("Total Lots field is empty");
        $('#er_total_lots').css('color', 'red');
        return false;
    }

    $.post(PostFGSSave,
        {
            _token: $('meta[name=csrf-token]').attr('content'),
            data: myData
        }).done(function (data, textStatus, jqXHR) {
            /*console.log(data);*/
            window.location.href = GetFGSPage;
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(errorThrown + '|' + textStatus);
        });

}

function deleteAllchecked() {
    var tray = [];
    $('.checkboxes:checked').each(function () {
        tray.push($(this).val());
    });

    var traycount = tray.length;
    var myData = { tray: tray, traycount: traycount };
    $.ajax({
        url: PostFGSDelete,
        method: 'get',
        data: myData

    }).done(function (data, textStatus, jqXHR) {
        window.location.href = GetFGSPage;
        /* alert(data);*/
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(errorThrown + '|' + textStatus);
    });
}

function groupby() {
    var datefrom = $('#groupby_datefrom').val();
    var dateto = $('#groupby_dateto').val();
    var g1 = $('select[name=group1]').val();
    var g2 = $('select[name=group2]').val();
    var g3 = $('select[name=group3]').val();
    var urls = GetFGSdata + "?_token=" + token + "&&mode=group" + "&&g1=" + g1 + "&&g2=" + g2 + "&&g3=" + g3 + "&&datefrom=" + datefrom + "&&dateto=" + dateto;

    loadFGSdata(urls);
}

function searchby() {
    var datefrom = $('#search_from').val();
    var dateto = $('#search_to').val();
    var pono = $('#search_pono').val();
    var urls = GetFGSdata + "?_token=" + token + "&&mode=search" + "&&datefrom=" + datefrom + "&&dateto=" + dateto + "&&pono" + pono;

    loadFGSdata(urls);
}

function loadFGSdata(urls) {
    // $.get(urls,
    // {
    //     _token:$('meta[name=csrf-token]').attr('content')
    // }).done(function(data, textStatus, jqXHR){
    //     console.log(data);
    //     var cnt = 0;
    //     FGSgetDataTable(data);
    // });
    getDatatable('fgsdatatable', urls, dataColumn, [], 0);
}

function FGSgetDataTable(data) {
    var cnt = 0;
    $.each(data, function (i, val) {
        cnt++;
        var report_status = $('#hd_report_status').val();
        var qty = '';
        if (val.qty == null) {
            qty = 0;
        } else {
            qty = val.qty;
        }

        var tblrow = '<tr>' +
            '<td width="4.28%">' +
            '<input type="checkbox" class="input-sm checkboxes" value="' + val.id + '" name="checkitem" id="checkitem"></input> ' +
            '</td>' +
            '<td width="5.28%">' +
            '<button type="button" name="edit-task" class="btn btn-sm btn-primary edit-task" value="' + val.id + '|' + val.po_no + '|' + val.date + '|' + val.device_name + '|' + val.qty + '|' + val.total_num_of_lots + '|' + val.dbcon + '">' +
            '   <i class="fa fa-edit"></i> ' +
            '</button>' +
            '</td>' +
            '<td width="14.28%">' + val.date + '<input type="hidden" id="hd_date_inspected" value="' + val.date + '" name="hd_date_inspected[]"></td>' +
            '<td width="20.28%">' + val.po_no + '<input type="hidden" id="hd_pono" value="' + val.po_no + '" name="hd_pono[]"></td>' +
            '<td width="27.28%">' + val.device_name + '<input type="hidden" id="hd_device_name" value="' + val.device_name + '" name="hd_device_name[]"></td>' +
            '<td width="14.28%">' + qty + '<input type="hidden" id="hd_qty" value="' + qty + '" name="hd_qty[]"></td>' +
            '<td width="14.28%">' + val.total_num_of_lots + '<input type="hidden" id="hd_total_lots" value="' + val.total_num_of_lots + '" name="hd_total_lots[]"></td>' +
            '</tr>';
        $('#tblforfgs').append(tblrow);
    });
}