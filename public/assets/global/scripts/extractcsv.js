$(function(){
    GetTime();
	$('#btn_save').on('click',function(){
		if ($('#id').val() == "") {
			TimeSetting();
		}else{
			Update();
		}	
	});
	$('#btn_cancel').on('click',function(){
		alert('cancel');
	});

	$('#checkall').change(function () {
		if(this.checked) {
	        $('.extract .checker span').addClass('checked');
	        $('.test').prop('checked',true);
	    } else {
	        $('.extract .checker span').removeClass('checked');
	        $('.test').prop('checked',false);
	    }
	});

	$('.test').change(function () {
		if ($('.test:checked').length == $('.test').length){
			$('.checkall_container .checker span').addClass('checked');
			$('#checkall').prop('checked',true);
		}
		else {
			$('.checkall_container .checker span').removeClass('checked');
			$('#checkall').prop('checked',false);
		}
	});

	$('#btn_force_dl').on('click',function(){
		var from = $('#date_from').val();
		var to = $('#date_to').val();

		var checkedpline = $('.pline:checked').length;
		var checkedpertable = $('.test:checked').length;
		if($('#date_from').val() == "" || $('#date_to').val() == ""){

		   msg('Please fill up the dates!','failed');
			return false;
		}

		if(checkedpline == 0 && checkedpertable == 0 ){

			msg('Please select max 1 productline and 1 table to download','failed');
			return false;
		}
		if(checkedpline != 1){
			msg('Please select only 1 productline','failed');
			return false;
		}
	    if(checkedpertable != 1){
			msg('Please select only 1 table','failed');
			return false;
		}
		$('#loading').modal('show');	

		var ptable = ''; 
		var checkboxPertable = $('.test:checked').map(function(){
	 	    var $this = $(this);
	  		if ($this.is(':checked')) {
	      
	     	   return $(this).val();
	 		 }
		}).get();
		
		var pertablecounter = checkboxPertable.length;
			pertablecounter = pertablecounter - 1;
		$.each(checkboxPertable,function(index, value) {
			 // alert(checkboxPertable);
			if(pertablecounter > index){
				ptable = ptable + value + ',';
			}else{
				ptable = ptable + value;
			}
		});

		var plines = ''; 
		var checkboxValues = $('.pline:checked').map(function(){
	 	    var $this = $(this);
	  		if ($this.is(':checked')) {
	     		 return $(this).val();
	 		 }
		}).get();

		var plinecounter = checkboxValues.length;
		 	plinecounter = plinecounter -1;
		$.each(checkboxValues,function(index, value) {	
		 	if(plinecounter > index){
  				 plines = plines + value + ',';
		 	}else{
		 		 plines = plines  + value;
		 	}
		});		

		    ExtractData(plines,ptable,from,to);
		     
			setTimeout(function() {
				$('#loading').modal('hide');
				msg('Downloaded successfully!','success');
			}, 2500);
	});

	$('#btn_cancel_dl').on('click',function(){
		// console.log($('#date_from').val());
		$('.extract .checker span').addClass('checked');
	    $('.test').prop('checked',true);
		$('.checkall_container .checker span').addClass('checked');
		$('#checkall').prop('checked',true);
		$('.check_productline .checker span').addClass('checked')
		$('.pline').prop('checked',true);
	});
});

function ExtractData(plines,ptable,from,to){

   window.location = ExportCSVURL + '?from='+from + '&to='+to + '&productline='+plines  + '&pertable='+ptable;
   // msg('successfully','success');
}

function TimeSetting(){
	var hour = $('#hour').val();
	var minute = $('#minute').val();
	var am_pm = $('#am_pm').val();

	 if (hour == "") {
	 	  $('#error1').html("This field is required!"); 
       	  $('#error1').css('color', 'red');       
       	  return false;  
	 }
	  if (minute == "") {
	 	  $('#error2').html("This field is required!"); 
       	  $('#error2').css('color', 'red');       
       	  return false;  
	 }
	  if (am_pm == "") {
	 	  $('#er3').html("This field is required!"); 
       	  $('#er3').css('color', 'red');       
       	  return false;  
	 }  
	$.ajax({
		url: TimeSettingURL,
		type: 'POST',
		dataType: 'JSON',
		data:
		 {
		 	_token: token,
		 	hour: $('#hour').val(),
		 	minute: $('#minute').val(),
		 	am_pm: $('#am_pm').val()
		 },
	})
	.done(function(data, textStatus, jqXHR) {
		msg('Set time success!','success');
		GetTime();
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log("error");
	});
}

function GetTime(){
	$.ajax({
		url: GetTimeURL,
		type: 'GET',
		dataType: 'JSON',
	})
	.done(function(data, textStatus, jqXHR) {
		if(data.length == 0){
			msg('Please set time');
		}else{
		$('#id').val(data["0"].id);
		$('#hour').val(data["0"].hour);	
		$('#minute').val(data["0"].minute);
		$('#am_pm').val(data["0"].am_pm);
		console.log("success");

		}
		
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log("error");
	});
}
function Update(){
	var id = $('#id').val();
	var hour = $('#hour').val();
	var minute = $('#minute').val();
	var am_pm = $('#am_pm').val();

	 if (hour == "") {
	 	  $('#error1').html("This field is required!"); 
       	  $('#error1').css('color', 'red');       
       	  return false;  
	 }
	  if (minute == "") {
	 	  $('#error2').html("This field is required!"); 
       	  $('#error2').css('color', 'red');       
       	  return false;  
	 }
	  if (am_pm == "") {
	 	  $('#er3').html("This field is required!"); 
       	  $('#er3').css('color', 'red');       
       	  return false;  
	 }  

	$.ajax({
		url: UpdateTimeURL,
		type: 'POST',
		dataType: 'JSON',
		data: 
		{
			_token:token,
			id:id,
			hour:hour,
			minute:minute,
			am_pm:am_pm
		},
	})
	.done(function(data, textStatus, jqXHR) {
		msg('Set time successfully updated!','success');
		GetTime();
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log("error");
	});	
}
