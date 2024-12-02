var po_reg_arr = [];

$(function() {
    loadDevice(loadporegdevice);
    dispalytablePoReg();
    checkAllCheckboxesInTable('.check_all_po_reg','.check_item_po_reg');

    $('#tbl_device').on('click','.updatesinglebtn', function() {
        $('#device_code').prop('readonly',false);
        $('#poreg_Modal').modal('show');
        $('#pono').val($(this).attr('data-pono'));
        $('#device_code').val($(this).attr('data-device_code'));
        $('#device_name').val($(this).attr('data-device_name'));
        $('#poqty').val($(this).attr('data-poqty'));
        $('#family').val($(this).attr('data-family'));
        $('#series').val($(this).attr('data-series'));
        $('#prod_type').val($(this).attr('data-prod_type'));
        $('#poregstatus').val('ADD');
        
        getDropdowns();
    });

     $('#tbl_poregistration').on('click','.btn_edit_po_reg', function() {
        $('#device_code').prop('readonly',false);
        $('#pono').val($(this).attr('data-pono'));
        $('#id').val($(this).attr('data-id'));
        $('#device_code').val($(this).attr('data-device_code'));
        $('#device_name').val($(this).attr('data-device_name'));
        $('#poqty').val($(this).attr('data-poqty'));
        $('#family').val($(this).attr('data-family'));
        $('#series').val($(this).attr('data-series'));
        $('#prod_type').val($(this).attr('data-prod_type'));
        $('#poregstatus').val('EDIT');
        
        getDropdowns();
    });

    $('.group-checkable').on('change', function(e) {
        $('input:checkbox.chk').not(this).prop('checked', this.checked);
    });

    $('#update').on('click', function(e) {
        clearALLfields()
        update();
    });

    $('#add').on('click', function(e) {
        clearALLfields()
        $('#poreg_Modal').modal('show');
        $('#poregstatus').val('ADD');
        getDropdowns();
        $('#device_code').prop('readonly',false);
    });

    

    $('#btn_save_po_reg').on('click', function() {
        poregistration();
    });

    $('#btn_remove_po_reg').on('click', function() {
         delete_set();
    });
});

function dispalytablePoReg() { 
    $.ajax({
         url:displayporeg,
         data:  {_token: token }
    }).done(function(data){
         po_reg_arr = data.po_reg;
         makePOregTable(po_reg_arr);
    }).fail(function(data,textStatus,jqXHR) {
        msg("There's some error while processing.",'failed');
    });
}

function getItems(id,bid) {
    var data = {
    _token: token,
        id : id
    };

    $.ajax({
        url: displayporegitem,
        type: "GET",
        data: data,
    }).done(function(data, textStatus, jqXHR) {
        var item = '',device_code = '',device_name = '', family = '',series = '',product_type = '';
        $.each(data, function(index, x) {
            device_code = x.device_code;
            device_name = x.device_name;
            family = x.family;
            series = x.series;
            product_type = x.product_type;
        });

        $('#device_code').prop('disabled',true);
        $('#device_code').val(device_code);
        $('#device_name').val(device_name);
        $('#family').val(family);
        $('#series').val(series);
        $('#prod_type').val(product_type);
    }).fail( function(data, textStatus, jqXHR) {
        $('#loading').modal('hide');
        failedMsg("There's some error while processing.");
    });
}

function getDropdowns() {
    var data = {
        _token: token
    };

    $.ajax({
        url: getdropdownlang,
        type: "GET",
        data: data
    }).done(function(data,textStatus,jqXHR) {
        $('#family').select2({
            data:data.family,
            placeholder: "Select --"
        });
        $('#series').select2({
            data:data.series,
            placeholder: "Select --"
        });
        $('#prod_type').select2({
            data:data.product_type,
            placeholder: "Select --"
        });
    }).fail(function(data,textStatus,jqXHR) {
        msg("There's some error while processing.",'failed');
    });
}


function getFamilyList(){
    var select = $('#family');
    $.ajax({
        url: loadfamilylist,
          type: "get",
          dataType: "json",
          success: function (returndata) {
               select.empty();
               select.append($('<option></option>').val(0).html("- SELECT -"));
               if (returndata.length > 0) {
                  for(var x=0;x<returndata.length;x++){
                         select.append($('<option></option>').val(returndata[x].description).html(returndata[x].description));
                  }
               }
          }
    });
}

function getSeriesList(){
    var select = $('#series');
    $.ajax({
      url: loadserieslist,
      type: "get",
      dataType: "json",
      success: function (returndata) {
           select.empty();
           select.append($('<option></option>').val(0).html("- SELECT -"));
           if (returndata.length > 0) {
              for(var x=0;x<returndata.length;x++){
                     select.append($('<option></option>').val(returndata[x].description).html(returndata[x].description));
              }
           }
      }
    });
}

function loadDevice(url) {
    $('#tbl_device').dataTable().fnClearTable();
    $('#tbl_device').dataTable().fnDestroy();
    $('#tbl_device').DataTable({
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: url,
        columns: [
            { data: 'pono', name: 'pono' },
            { data: 'device_code', name: 'device_code' },
            { data: 'device_name', name: 'device_name' },
            { data: 'poqty', name: 'poqty' },
            { data: 'family', name: 'family' },
            { data: 'series', name: 'series' },
            { data: 'prod_type', name: 'prod_type' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        aoColumnDefs: [
            {
                aTargets:[13], // You actual column with the string 'America'
                fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                    $(nTd).css('font-weight', '700');
                    if(sData == "Accepted") {
                        $(nTd).css('background-color', '#c49f47');
                        $(nTd).css('color', '#fff');
                    }
                    if(sData == "Rejected") {
                        $(nTd).css('background-color', '#cb5a5e');
                        $(nTd).css('color', '#fff');
                    }
                    if(sData == "On-going") {
                        $(nTd).css('background-color', '#3598dc');
                        $(nTd).css('color', '#fff');
                    }
                },
                defaultContent: '',
                targets: '_all'
            }
        ],
        order: [[0,'desc']]
    });
}

function searchstatus() {
    var token = "{{ Session::token() }}";
    loadDevice('{{url("/getdevicesearch")}}'+'?_token='+token+
                                                '&&from='+$('#from').val()+
                                                '&&to='+$('#to').val()+
                                                '&&recno='+$('#recno').val()+
                                                '&&status='+$('#status').val()+
                                                '&&itemno='+$('#itemno').val()+
                                                '&&lotno='+$('#lotno').val()+
                                                '&&invoice_no='+$('#invoice_no').val());
}

function getAllChecked() {
    /* declare an checkbox array */
    var chkArray = [];

    /* look for all checkboes that have a class 'chk' attached to it and check if it was checked */
    $(".chk:checked").each(function() {
        chkArray.push($(this).val());
    });

    return chkArray;
}

function getAllCheckedItemCode() {
    /* declare an checkbox array */
    var chkArray = [];

    /* look for all checkboes that have a class 'chk' attached to it and check if it was checked */
    $(".chk:checked").each(function() {
        chkArray.push($(this).attr('data-code'));
    });

    return chkArray;
}

function isCheck(element) {
    if(element.is(':checked')) {
        element.parent('tr').removeAttr('checked');
        element.prop('checked',false)
    }
}

function saveporeg() {
    $('#loading').modal('show');
    var url = "{{url('/savedevicedetails')}}";
    // if (requiredFields(':input.required') == true) {
        var token = "{{Session::token()}}";
        
        var data = {
            _token: token,
            pono: $('#pono').val(),
            device_code: $('#device_code').val(),
            device_name: $('#device_name').val(),
            poqty: $('#poqty').val(),
            family: $('#family').val(),
            series: $('#series').val(),
            product_type: $('#product_type').val(),
        };
        $.ajax({
            url: url,
            type: "POST",
            dataType: "JSON",
            data: data
        }).done( function(data,textStatus,jqXHR) {
            $('#loading').modal('hide');
            if (data.return_status == 'success') {
                msg(data.msg,'success');
                loadDevice("{{url('/getdevice')}}");
            }

        }).fail( function(data,textStatus,jqXHR) {
            $('#loading').modal('hide');
            msg("There's some error while processing.",'failed');
        });
    // } else {
    //     $('#loading').modal('hide');
    //     msg("Please fill out all required fields.",'failed');
    // }   
}

function clearALLfields(){
    $('#pono').val("");
    $('#device_code').val("");
    $('#device_name').val("");
    $('#poqty').val("");
    $('#devicefamily').val("");
    $('#series').val("");
    $('#prod_type').val("");
}

function poregistration(){
    $('#loading').show();
    var pono = $('#pono').val();
    var id = $('#id').val();
    var device_code = $('#device_code').val();
    var device_name = $('#device_name').val();
    var poqty = $('#poqty').val();
    var family = $('#family').val();
    var Series = $('#series').val();
    var prod_type = $('#prod_type').val();
    var editsearch = $('#hdporegid').val();
    var status = $('#poregstatus').val();

    $.ajax({
        url: getpoypics,
        type: 'GET',
        dataType: 'json',
        data: {
            _token: token,
            po: pono
        },
    }).done(function(data, textStatus, xhr) {
        if (data > 0 && status == "ADD") {
            msg("PO number Already Exist","failed");
        } else {
            var id = $('#id').val();
            $.ajax({
                url: addpodata,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    _token: token, 
                   id:id,
                   pono: pono,
                   device_code: device_code,
                   device_name:device_name,
                   poqty:poqty,
                   family:family,
                   series:Series,
                   prod_type:prod_type,
                   status:status
                },
            }).done(function(data, textStatus, xhr) {
                msg(data.msg,data.status);
                po_reg_arr = data.po_reg;

                makePOregTable(po_reg_arr);
            }).fail(function(xhr, textStatus, errorThrown) {
                 var errors = xhr.responseJSON;
                 showErrors(errors);
            }).always(function() {
                $('#loading').hide();
            });
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        msg(errorThrown,textStatus);
    })
    .always(function() {
        $('#loading').hide();
    });
}

function makePOregTable(arr) {
    $('#tbl_poregistration').dataTable().fnClearTable();
    $('#tbl_poregistration').dataTable().fnDestroy();
    $('#tbl_poregistration').dataTable({
        data: arr,
        columns: [
            { data: function(x) {
                return "<input type='checkbox' class='check_item_po_reg' data-id='"+x.id+"' value='"+x.id+"'>";
            }, searchable: false, orderable: false },

            { data: function(x) {
                return "<button class='btn btn-sm btn-primary btn_edit_po_reg' "+
                                "data-pono='"+x.pono+"'"+
                                "data-device_code='"+x.device_code+"'"+
                                "data-device_name='"+x.device_name+"'"+
                                "data-poqty='"+x.poqty+"'"+
                                "data-family='"+x.family+"'"+
                                "data-series='"+x.series+"'"+
                                "data-prod_type='"+x.prod_type+"'"+
                                "data-id='"+x.id+"'>"+
                            "<i class='fa fa-edit'></i>"+
                        "</button>";
            }, searchable: false, orderable: false },

            { data: 'pono' },
            { data: 'device_code' },
            { data: 'device_name' },
            { data: 'poqty' },
            { data: 'family' },
            { data: 'series' },
            { data: 'prod_type' },
        ]
    });
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

function update() {
    $('#loading').modal('show');
    var data = {
        _token : token,
    };
            
    $.ajax({
        url: loadypicsdevice,
        method: 'POST',
        data:  data,
    }).done( function(data, textStatus, jqXHR) {
        console.log(data)
        msg('Successfully updated.','success');
        loadDevice(loadporegdevice);
    }).fail( function(data, textStatus, jqXHR) {
        msg('There is an error while updating.','error');
    }).always( function() {
        $('#loading').modal('hide');
    });
}

function delete_set() {
    var tray = [];
    $(".check_item_po_reg:checked").each(function () {
        tray.push($(this).val());
    });
    var traycount =tray.length;
   if (tray.length > 0) {
    $.ajax({
        url: deleteporeg,
        method: 'get',
        data:  { 
            _token:token,
            tray : tray, 
            traycount : traycount},  
        }).done(function(data, textStatus, xhr) {
            msg("Successfully deleted.", textStatus);
            dispalytablePoReg();
            clearALLfields();
        }).fail(function(xhr, textStatus, errorThrown) {
             alert(errorThrown);
        });
        
    } else {
        msg("Please select at least 1 Set.", "failed");
    }
    $('.check_all_po_reg').prop('checked',false);
}