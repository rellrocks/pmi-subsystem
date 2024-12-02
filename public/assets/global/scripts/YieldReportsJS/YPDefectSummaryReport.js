$( function() {
     $('#dsr-po').on('change', function() {
          if ($(this).val() == '') {

          } else {
               $.ajax({
                    url: searchPOdetailsURL,
                    type: 'GET',
                    dataType: 'JSON',
                    data: {
                         _token: token,
                         po: $(this).val()
                    },
               }).done(function(data, textStatus, xhr) {
                    console.log(data);
                    $('#dsr-ptype').val(data.prod_type);
                    $('#dsr-family').val(data.family);
                    $('#dsr-series').val(data.series);
                    $('#dsr-device').val(data.device_name);
               }).fail(function(xhr, textStatus, errorThrown) {
                    console.log("error");
               });               
          }
     });

     $('#btn_defect_export').on('click',function(){
          CheckifExist();
     })
});
function CheckifExist(){
     $.ajax({
          url:CheckDefectSummaryURL,
          type: 'GET',
          dataType: 'JSON',
          data: {
               dsrdatefrom:$('#dsr-datefrom').val(),
               dsrdateto:$('#dsr-dateto').val()
               // srpo: $('#srpo').val()
          },
     })
     .done(function(data, textStatus, xhr) {
          $.each(data,function(index, val) {
               var result = val.rowcount;
               if(result > 0){
                   window.location.href = defectsummaryRptURL + "?_token=" + token + 
                   "&&datefrom=" + $('#dsr-datefrom').val() + 
                  "&&dateto=" + $('#dsr-dateto').val() + 
                  '&&ptype=' + $('#dsr-ptype').val() +
                  '&&family=' + $('#dsr-family').val() +
                  '&&series=' + $('#dsr-series').val() +
                  '&&device=' + $('#dsr-device').val() +
                  '&&po=' + $('#dsr-po').val();  
               }else{
                     msg('No Deffect','failed');
                   window.location.href = defectsummaryRptURL + "?_token=" + token + 
                   "&&datefrom=" + $('#dsr-datefrom').val() + 
                  "&&dateto=" + $('#dsr-dateto').val() + 
                  '&&ptype=' + $('#dsr-ptype').val() +
                  '&&family=' + $('#dsr-family').val() +
                  '&&series=' + $('#dsr-series').val() +
                  '&&device=' + $('#dsr-device').val() +
                  '&&po=' + $('#dsr-po').val();  
               }               
          });
     })
     .fail(function(xhr, textStatus, errorThrown) {
          console.log("error");
     })
     .always(function() {
          console.log("complete");
     });
}
// function defectsummaryRpt(){
//      window.location.href = defectsummaryRptURL + "?_token=" + token + 
//           "&&datefrom=" + $('#dsr-datefrom').val() + 
//           "&&dateto=" + $('#dsr-dateto').val() + 
//           '&&ptype=' + $('#dsr-ptype').val() +
//           '&&family=' + $('#dsr-family').val() +
//           '&&series=' + $('#dsr-series').val() +
//           '&&device=' + $('#dsr-device').val() +
//           '&&po=' + $('#dsr-po').val();
// }

