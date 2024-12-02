var i =0;

$( function(e) {
     var d = new Date();
     var month = d.getMonth()+1;
     var months = d.getMonth();
     var day = d.getDate();
     var lastMonth = d.getFullYear() + '-' + (month<10 ? '0' : '') + month + '-' +(day<10 ? '0' : '') + day
     var today = d.getFullYear() + '-' + (months<10 ? '0' : '') + months + '-' +(day<10 ? '0' : '') + day
     $('#datefroms').val(today);
     $('#datetos').val(lastMonth);

     loadchart();
     getReportRecords();

     $('#btnxport-summaryrpt').click(function(){
          $('#summaryrpt_Modal').modal('show');
          $('#Export-Modal').modal('hide');
          $('.summaryrpt-title').html("Yield Performance Summary Report");
     });
     $('#btnxport-defectsummaryrpt').click(function(){
          $('#defectsummaryrpt_Modal').modal('show');
          $('#Export-Modal').modal('hide');
          $('.defectsummaryrpt-title').html("Defect Summary Report");
     });
     $('#btnxport-yieldpsrpt').click(function(){
          $('#yieldpsrpt_Modal').modal('show');
          $('#Export-Modal').modal('hide');
          $('.yieldpsrpt-title').html("Yield Performance Summary/Family");
     });
     $('#btnxport-yieldsfrpt').click(function(){
          $('#yieldsfrpt_Modal').modal('show');
          $('#Export-Modal').modal('hide');
          $('.yieldsfrpt-title').html("Summary Report per Family");
     });
     $('#btnxport').click(function(){
          $('#Export-Modal').modal('show');
     });
     $("body").on("click",".edittaskx",function(e){
          $('#updateyield_Modal').modal('show');
     });

     $('#btnxport-defectsummaryrpt').click(function(){
          var icsocket = $('#dsr-icsocket').val();
          var fol = $('#dsr-fol').val();
         
          $('#dsr-icsocket').change(function(){
               if($('#dsr-icsocket').is(':checked')){
                    $('input[name=dsr-fol]').parents('span').removeClass("checked");
                    $('input[name=dsr-fol]').prop('checked',false);    
               }
          });
          $('#dsr-fol').change(function(){
               if($('#dsr-fol').is(':checked')){
                    $('input[name=dsr-icsocket]').parents('span').removeClass("checked");
                    $('input[name=dsr-icsocket]').prop('checked',false);
               }
          });
         
     });
});

function loadchart(){
     var datefroms = $('#datefroms').val();
     var datetos = $('#datetos').val();
     var data = {_token: token, datefroms:datefroms, datetos:datetos};
     $.ajax({
          url: loadchartURL,
          method:'post',
          data: data
     }).done(function(data, textStatus, jqXHR){
         console.log(data);
     /*    alert(data[0]['toutput']);
         var treject =data[0]['treject'];*/

          var chart = new CanvasJS.Chart("chartContainer",
          {
               theme: "theme3",
                        animationEnabled: true,
               title:{
                    text: "Chart Summary",
                    fontSize: 30
               },
               toolTip: {
                    shared: true
               },             
               axisY: {
                    title: "Total Quantity"
               },
               
               data: [ 
               {
                    type: "column",     
                    name: "Total Outputs",
                    legendText: "Total Output",
                    showInLegend: true, 
                    dataPoints:
                    [

                    /*{label: data[0].family, y: parseInt(data[0]['toutputs'])},*/
                    
                    ]
               },
               {
                    type: "column",     
                    name: "Total Rejects",
                    legendText: "Total Rejects",
                    axisYType: "secondary",
                    showInLegend: true,
                    dataPoints:
                    [
                   
                   /*{label: data[0].family, y: parseInt(data[0]['treject'])},
                   */
                    
                    ]
               } 
               ],
               legend:
               {
                    cursor:"pointer",
                    itemclick: function(e){
                    if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                         e.dataSeries.visible = false;
                    }
                    else {
                         e.dataSeries.visible = true;
                    }
                         chart.render();
                    }
               },
          });
          for(var i = 0; i < data.length; i++)
          {
               var length = chart.options.data[0].dataPoints.length;
               chart.options.data[0].dataPoints.push({label: data[i].family, y: parseInt(data[i]['toutput'])});
               chart.render();
          }
          
          for(var i = 0; i < data.length; i++)
          {
               var length = chart.options.data[1].dataPoints.length;
               chart.options.data[1].dataPoints.push({label: data[i].family, y: parseInt(data[i]['qty'])});
               chart.render();
          }

    // });
     }).fail(function(jqXHR,textStatus,errorThrown){
          console.log(errorThrown+'|'+textStatus);
     });     
}

function update(){
     var yieldingno = $('input[name=yieldingno2]').val();
     var pono = $('input[name=pono2]').val();
     var poqty = $('input[name=poqty2]').val();
     var device = $('input[name=device2]').val();
     var family = $('#family2').val();
     var series = $('#series2').val();
     var toutput =  $('input[name=toutput2]').val();
     var treject =  $('input[name=treject2]').val();
     var twoyield =  $('input[name=twoyield2]').val();
     var masterid =  $('input[name=masterid]').val();

     var myData ={
                       'pono' : pono
                     ,'poqty' : poqty
                    ,'device' : device
                    ,'family' : family
                    ,'series' : series
                   ,'toutput' : toutput
                   ,'treject' : treject
                  ,'twoyield' : twoyield
                  ,'masterid' : masterid
               };

     $.post(updateyieldsummary,
     { 
          _token: token
          , data: myData
     }).done(function(data, textStatus, jqXHR){
          /*console.log(data);*/
          window.location.href=ReportYieldPerformance;
     }).fail(function(jqXHR, textStatus, errorThrown){
          console.log(errorThrown+'|'+textStatus);
     });
}

function EditButtons(){
     $('.edit-task').on('click', function(e) {
          var edittext = $(this).val().split('|');
          var editid = edittext[0];
          var pono = edittext[1];
          var poqty = edittext[2];
          var device = edittext[3];
          var series = edittext[4];
          var family = edittext[5];
          var toutput = edittext[6];
          var treject = edittext[7];
          var twoyield = edittext[8];

          $('#masterid').val(editid);
          $('.updatetitle').html('Update Yielding Summary');
          $('#updateyield_Modal').modal('show');
          $('#pono2').val(pono);
          $('#poqty2').val(poqty);
          $('#device2').val(device);
          $('#series2').val(series);
          $('#family2').val(family);
          $('#toutput2').val(toutput);
          $('#treject2').val(treject);
          $('#twoyield2').val(twoyield);
          $('#masterid').val(editid);        

          $('#name').keyup(function(){
             $('#er1').html(""); 
          });
          $('#desc').keyup(function(){
             $('#er2').html(""); 
          });
          $('#val').keyup(function(){
             $('#er3').html(""); 
          });
     });
}

function getReportRecords() {
     $.ajax({
          url: reportRecordsURL,
          type: 'GET',
          dataType: 'JSON',
          data: {
               _token: token
          },
     }).done(function(data,textStatus,jqXHR) {
          makeReportTable(data)
     }).fail(function(data,textStatus,jqXHR) {
          console.log("error");
     }).always(function() {
          console.log("complete");
     });
     
}


function makeReportTable(arr) {
     $('#tbl_reports').dataTable().fnClearTable();
     $('#tbl_reports').dataTable().fnDestroy();
     $('#tbl_reports').dataTable({
          data: arr,
          columns: [
               { data: 'pono' },
               { data: 'poqty' },
               { data: 'device' },
               { data: 'series' },
               { data: 'family' },
               { data: 'tinput' },
               { data: 'toutput' },
               { data: 'qty' },
               { data: function(x) {
                    var xx = parseFloat(x.tinput) - parseFloat(x.qty);
                    var yy = 0;

                    if (xx !== 0){
                         yy =   xx / parseFloat(x.tinput) ;
                    }

                    var twoyield = yy * 100;

                    return (twoyield).toFixed(2);
               } },
          ]
     });
}