var target_arr = [];

$( function() {
  DatePickers();
  getOutputs();
  checkAllCheckboxesInTable('.checkAllitemstarget','.checkboxestarget');

    $('.validate').on('keyup', function(e) {
        var no_error = $(this).attr('id');
        hideErrors(no_error)
    });

    $('.select-validate').on('change', function(e) {
        var no_error = $(this).attr('id');
        hideErrors(no_error)
    });

  $("body").on("click",".edit-targetreg",function(e){
          var editsearch = $(this).val();
          $.ajax({
               url: edittargetreg,
               method: 'get',
               data:  { 
                    editsearch : editsearch, 
               }, 
          }).done(function(data, textStatus, jqXHR) {
               $('#datefrom').val(data[0]['datefrom']);
               $('#id').val(data[0]['id']);
               $('#dateto').val(data[0]['dateto']);
               $('#yield').val(data[0]['yield']);
               $('#dppm').val(data[0]['dppm']);
               $('#ptype').val(data[0]['ptype']); 
          }).fail(function(jqXHR, textStatus, errorThrown) {
               console.log(errorThrown+'|'+textStatus);
          });
  });

  $("#formtarget").on('submit',function(e){
     e.preventDefault();
     var form_action = $(this).attr("action");
     jQuery.ajax({
      dataType: 'json',
      type: 'POST',
      url: form_action,
      data:  $(this).serialize(),
      success:function(returnData){
        msg(returnData.msg,returnData.status); 
        target_arr = returnData.data;
        makeDataTable(target_arr);
        $("#formtarget")[0].reset(); 
        },
      error: function(xhr, textStatus, errorThrown) {
        var errors = xhr.responseJSON;
        showErrors(errors);
      }
     });
  });

});  

function getOutputs() {
    target_arr = [];
    $.ajax({
        url: getOutputsURL,
        type: 'GET',
        dataType: 'JSON',
        data: {_token: token},
    }).done(function(data, textStatus, xhr){
        console.log(data);
        target_arr = data;
        makeDataTable(target_arr);
    }).fail( function(xhr, textStatus, errorThrown) {
        msg(errorThrown,textStatus);
    })
    .always(function() {
        console.log("complete");
    });
    
}
//Display table
function makeDataTable(arr) {
  $('#modregtable').dataTable().fnClearTable();
    $('#modregtable').dataTable().fnDestroy();
    $('#modregtable').dataTable({
        data: arr,
        bLengthChange : false,
        scrollY: "200px",
        paging: false,
        searching: false,
        columns: [ 
          { data: function(x) {
                return "<input type='checkbox' class='input-sm checkboxestarget' value='"+x.id+"'>";
            }, searchable: false, orderable: false },
             { data: function(x) {
                return "<button class='btn btn-sm btn-primary edit-targetreg' value='"+x.id+"'><i class='fa fa-edit'></i></button>";
            }, searchable: false, orderable: false },
            {data:'datefrom'},
            {data:'dateto'},
            {data:'yield'},
            {data:'dppm'},
            {data:'ptype'},
        ]
    });
}

function DatePickers(){
     $('#datefrom').datepicker();
     $('#dateto').datepicker();
     $('#datefrom').on('change',function(){
          $(this).datepicker('hide');
     });
     $('#dateto').on('change',function(){
          $(this).datepicker('hide');
     });
}

function removetargetreg(){
  var tray = [];
  $(".checkboxestarget:checked").each(function () {
    tray.push($(this).val());
  });
  var traycount =tray.length;
  $.ajax({
      url: deleteTarget,
      method: 'get',
      data: {tray:tray,traycount:traycount},
      success:function(data){
          msg("Target Yield Deleted","success"); 
          target_arr = data;
          makeDataTable(target_arr);
         $("#formtarget")[0].reset(); }
     });
}
