$("#readfileform").on("submit",function(e){
	$('#loading').modal('show');
});

$('#processform').on('submit', function() {
	$('#processdone').modal('hide');
	$('#newproduct').modal('show')
});


