var target_reg_arr = [];

$(document).ready(function(e) {
     loadTarget();

     $('#targetsave').on('click', function() {
        targetregistration();
     });
     $('#saveTarget').on('click', function() {
        removetargetreg();
     });
     $('#targetclear').click(function(){
          $('#target-datefrom').val("");
          $('#target-dateto').val("");
          $('#targetyield').val("");
          $('#targetdppm').val("");
          $('#targetptype').val("");
          $('#targetid').val("");
          IDS = "";
     });

     FieldsValidations();
     
});//end of script-------------------------------------------------------------------------------------

function targetregistration(){
     $('#loading').show();
     var datefrom = $('#target-datefrom').val();
     var dateto = $('#target-dateto').val();
     var yielding = $('#targetyield').val();
     var dppm = $('#targetdppm').val();
     var ptype = $('#targetptype').val();
     var status = $('#targetstatus').val();
     var id = $('#targetid').val();
     var created_at = "";
     var updated_at = "";
     if(datefrom == ""){     
        $('#er_target-datefrom').html("Date From field is empty"); 
        $('#er_target-datefrom').css('color', 'red');       
        return false;  
     }
     if(dateto == ""){     
        $('#er_target-dateto').html("Date To field is empty"); 
        $('#er_target-dateto').css('color', 'red');       
        return false;  
     }
     if(yielding == ""){     
        $('#er_targetyield').html("Target Yield field is empty"); 
        $('#er_targetyield').css('color', 'red');       
        return false;  
     }
     if(dppm == ""){     
        $('#er_targetdppm').html("Target dppm field is empty"); 
        $('#er_targetdppm').css('color', 'red');       
        return false;  
     }
     if(ptype == ""){     
        $('#er_targetptype').html("Product Type field is empty"); 
        $('#er_targetptype').css('color', 'red');       
        return false;  
     }

     var id = (typeof IDs != 'undefined')?IDs:1;
     $.ajax({
          url: addtarget,
          type: 'POST',
          dataType: 'JSON',
          data: {
               _token: token, 
               id:id,
               datefrom:datefrom,
               dateto:dateto,
               yielding:yielding,
               dppm:dppm,
               ptype:ptype,
               created_at:created_at,
               updated_at:updated_at,
               status:"ADD",
          },
     }).done(function(data, textStatus, xhr) {
          msg(data.msg,data.status);
          target_reg_arr = data.target_reg;
          loadTarget();
     }).fail(function(xhr, textStatus, errorThrown) {
          msg(errorThrown,textStatus);
     }).always(function() {
          $('#loading').hide();
     });

           
}
function makeTargetregTable(arr) {
    $('#modreg-table').dataTable().fnClearTable();
    $('#modreg-table').dataTable().fnDestroy();
    $('#modreg-table').dataTable({
        data: arr,
        columns: [
            { data: function(x) {
                return "<input type='checkbox' class='form-control input-sm checkboxestarget' data-id='"+x.id+"' value='"+x.id+"'>";
            }, searchable: false, orderable: false },

            // { data: function(x) {
            //     return "<button class='btn btn-sm btn-primary edit-targetreg' "+
            //                     "data-target-datefrom='"+x.target-datefrom+"'"+
            //                     "data-target-dateto='"+x.target-dateto+"'"+
            //                     "data-targetyield='"+x.targetyield+"'"+
            //                     "data-targetdppm='"+x.targetdppm+"'"+
            //                     "data-targetptype='"+x.targetptype+"'"+
            //                     "data-id='"+x.id+"'>"+
            //                 "<i class='fa fa-edit'></i>"+
            //             "</button>";
            // }, searchable: false, orderable: false },

            { data: 'datefrom', name: 'datefrom' },
            { data: 'dateto', name: 'dateto'},
            { data: 'yield', name: 'yield'},
            { data: 'dppm', name: 'dppm'},
            { data: 'ptype', name: 'ptype'},
        ]
    });
}
function loadTarget() {
     target_reg_arr = [];
     $.ajax({
          url: displaytarget,
          type: 'GET',
          dataType: 'JSON',
          data: {_token:token}
     }).done( function(data,textStatus,xhr) {
          target_reg_arr = data;
          makeTargetregTable(target_reg_arr);
     }).fail( function(xhr,textStatus,errorThrown) {
          msg(errorThrown,textStatus);
     });
}
function removeYIELD(){
     var tray = [];
     $(".checkboxesYield:checked").each(function () {
          tray.push($(this).val());
     });
     var traycount =tray.length;
     $.ajax({
          url: "{{ url('/deleteYIELD') }}",
          method: 'get',
          data:  { 
               tray : tray, 
               traycount : traycount
          },  
          success:function(){
               msg("Yield Deleted","success"); 
               getDatatable('tbl_yield',"{{ url('/getYieldPerformanceDT')}}",dataColumnYIELD,[],0);  

          }
     });
}
function removetargetreg(){
     var tray = [];
     $(".checkboxestarget:checked").each(function () {
          tray.push($(this).val());
     });
     var traycount =tray.length;
   //  $('#tblfortarget').html("");
     $.ajax({
          url: removetarget,
          method: 'get',
          data:  { 
               tray : tray, 
               traycount : traycount
          },
          success:function(){
               msg("Target Yield Deleted","success"); 
               // getDatatable('modreg-table',"{{ url('/getTargetYield')}}",dataColumnYieldTarget,[],0);
               loadTarget();
          }    
     });
}
function DatePickers(){
     $('#target-datefrom').datepicker();
     $('#target-dateto').datepicker();
     $('#target-datefrom').on('change',function(){
          $(this).datepicker('hide');
     });
     $('#target-dateto').on('change',function(){
          $(this).datepicker('hide');
     });
}
function ButtonsClicked(){
     $('#btn_target').click(function(){
          $('#targetreg_Modal').modal('show');
          $('#mAndr-Modal').modal('hide');
          $('.targetreg_modal-title').html("Target Yield Registration");
          $('#targetstatus').val("ADD");
          $('#target-datefrom').val("");
          $('#target-dateto').val("");
          $('#targetyield').val("");
          $('#targetdppm').val("");
          $('#targetid').val("");
          getProductTargetList('targetptype');
          getDatatable('modreg-table',"{{ url('/getTargetYield')}}",dataColumnYieldTarget,[],0);
          IDS="";
          STATUS = "ADD"
     });

}
function FieldsValidations(){
     $('#target-datefrom').click(function(){
        $('#er_target-datefrom').html(""); 
     });
     $('#target-dateto').click(function(){
        $('#er_target-dateto').html(""); 
     });
     $('#targetyield').keyup(function(){
        $('#er_targetyield').html(""); 
     });
     $('#targetdppm').keyup(function(){
        $('#er_targetdppm').html(""); 
     });
}

