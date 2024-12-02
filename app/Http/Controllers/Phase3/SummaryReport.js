function summaryREpt(){
     var srdatefrom = $('#srdatefrom').val();
     var srdateto = $('#srdateto').val();
     var srprodtype = $('#srprodtype').val();
     var token = "{{ Session::token() }}";
     var paramfrom = srdatefrom.split("/");
     var paramto = srdateto.split("/");
     var datefrom = paramfrom[2]+'-'+paramfrom[0]+'-'+paramfrom[1];
     var dateto = paramto[2]+'-'+paramto[0]+'-'+paramto[1];

     // var pono = $('#pono').val();
     $.ajax({

     	url:checkDataExistsURL,
     	type:'POST',
     	dataType:'JSON',
     	data:
     	 {
     	 	_token: token,
     	 	srdatefrom: srdatefrom,
     	 	srdateto: srdateto,
     	 	srpo: $('#srpo').val()
     	 	

     	 },

      }).done(function(data, textStatus, jqXHR) {
      	console.log(data);

      	// if(){
       //     window.location = "{{ url('/summaryREpt') }}" + "?_token=" + token + "&&datefrom=" + datefrom + "&&dateto=" + dateto + "&&srprodtype=" + srprodtype;
      	// }else{
      	//    alert('No data found');
      	// }
    }).fail(function(xhr, textStatus, errorThrown) {
        console.log(errorThrown);
    });
         
}



