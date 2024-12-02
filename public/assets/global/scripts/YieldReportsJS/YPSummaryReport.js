$( function() {
     $('#srpo').on('change', function() {
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
                    if(data == null){
                        msg('No data found','failed');
                    }else{
                    console.log(data);
                    $('#srprodtype').val(data.prodtype);
                    $('#srfamily').val(data.family);
                    $('#srseries').val(data.series);
                    $('#srdevice').val(data.device);
                    }
                  
               }).fail(function(xhr, textStatus, errorThrown) {
                    console.log("error");
               });
               
          }
     });

     $('#export_btn').on('click',function(){
          summaryREpt();
     });
});

function summaryREpt(){
   
   // console.log('data');

     $.ajax({
          url:checkDataExistsURL,
          type:'GET',
          dataType:'JSON',
          data:
           {
               srdatefrom:$('#srdatefrom').val(),
               srdateto:$('#srdateto').val(),
               srpo: $('#srpo').val()
           }

     })
     .done(function(data, textStatus, xhr) {
          $.each(data, function(index, val) {
              var result = val.rowcount;
               if(result > 0){
                     window.location.href = summaryREptURL + "?_token=" + token + 
                    "&&datefrom=" + $('#srdatefrom').val() + 
                    "&&dateto=" + $('#srdateto').val() + 
                    '&&ptype=' + $('#srprodtype').val() +
                    '&&family=' + $('#srfamily').val() +
                    '&&series=' + $('#srseries').val() +
                    '&&device=' + $('#srdevice').val() + 
                    '&&po=' + $('#srpo').val();
               }else{
                    msg('No data found','failed');
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