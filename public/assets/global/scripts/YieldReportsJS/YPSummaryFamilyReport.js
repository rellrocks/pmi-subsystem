$(function(){


$('#btn_export_yield_performance_summary_per_family').on('click',function(){
	CheckYieldSummaryPerFamily();
});

});

function CheckYieldSummaryPerFamily(){
	 var ysfdatefrom = $('#ysf-datefrom').val();
	 var ysfdateto = $('#ysf-dateto').val();
	 var ptype = $('#ysf-ptype').val();
	 var family = $('#ysf-family').val();

		$.ajax({
			url: CheckYieldSummaryPerFamilyURL,
			type: 'GET',
			dataType: 'JSON',
			data: {

				ysfdatefrom: ysfdatefrom,
				ysfdateto: ysfdateto,
				ptype: ptype,
				family: family
			},
		})
		.done(function(data, textStatus, xhr) {
			$.each(data, function(index, val) {
				 result = val.rowcount;
				 if(result > 0){
				 	 window.location = yieldsumfamRptURL + "?_token=" + token + "&&datefrom=" + ysfdatefrom + "&&dateto=" + ysfdateto + 
                         "&&prodtype=" + ptype + "&&family=" + family;
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

