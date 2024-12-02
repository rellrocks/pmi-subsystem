var pya_arr = new Array();
var PyaTable ="";
var g_row = 0;
var Total_Input =0;
var Total_Output = 0;
var Total_Reject =0;
var total_MNG =0;
var total_PNG = 0;
var Yield_WOMNG = 0;
var Total_Yield = 0;
var DDPM = 0;
var trework = 0;
var totalrework = 0;

pyafieldcomputation();
$(function(){
  getProductList();
  getFamilyList();
  getseriesDropdown();
  makePyaTable([]);

  $('#btnadd').on('click',function(){

    AddState();
    
  });

  $('#btndiscard').on('click',function(){

    DiscardChanges();
    ClearAll();


  });

  $('#btnload').on('click',function(){

    pya_arr = [];
    getDataInYieldingPerformance();
    $('#rework').prop('disabled',false);
    // refreshPyaTable([]);
    // pyafieldcomputation();
    // DropdownDisabled();
    // $('#family').prop('disabled',true);
    
  });

  $('#btnloadpya').on('click',function(){

    addpya();


  });

  $('#btnsave').on('click',function(){

    rework_yield();

  });

$('#classification').change(function(){
        if($(this).val() == "NDF"){
            $('#mod').attr('disabled',true);
            $('#qty').attr('readonly',true);
            $('#remarks').attr('readonly',true);
            $('#er5').html(""); 
            
        }else{
            $('#mod').attr('disabled',false);
            $('#qty').attr('readonly',false);
            $('#remarks').attr('readonly',false);
        }
            $('#qty').val("0");
    });

 $('#tbl_pya').on('click', '.btn_edit_pya',function() {
        // $('#id').val($(this).attr('data-id'));
        // $('#yield_id').val($(this).attr('data-yield_id'));
        $('#row').val($(this).attr('data-row'));
        $('#productiondate').val($(this).attr('data-productiondate'));
        $('#yieldingstation').val($(this).attr('data-yieldingstation'));
        $('#accumulatedoutput').val($(this).attr('data-accumulatedoutput'));
        $('#classification').val($(this).attr('data-classification'));
        $('#mod').val($(this).attr('data-mod'));
        $('#qty').val($(this).attr('data-qty'));
        $('#rework').val($(this).attr('data-rework'));
        $('#oldrework').val($(this).attr('data-rework'));
        $('#btnloadpya').removeClass('bg-green');
        $('#btnloadpya').addClass('bg-blue');
        $('#btnloadpya').html('<i class="fa fa-check"></i>');
        $('#btnloadpya').prop('disabled',false);
        $('#qty').prop('disabled',true);

        if ($(this).attr('data-classification') == 'NDF') {
            $('#mod').prop('disabled', true);
            $('#qty').prop('readonly', true);
            $('#remarks').prop('readonly', true);
             $('#btnloadpya').prop('disabled',false);
        } else {
            $('#mod').prop('disabled', false);
            // $('#qty').prop('readonly', false);
            $('#remarks').prop('readonly', false);
            $('#family').prop('disabled',true);
            $('#series').prop('disabled',true);
            $('#prodtype').prop('disabled',true);
            $('#accumulatedoutput').prop('disabled',true);
            $('#yieldingstation').prop('disabled',true);



        }
    });


});


function AddState(){

  $('#btndiscard').prop('disabled', false);
  $('#btnsave').prop('disabled', false);
  $('#pono').prop('disabled', false);
  $('#btnload').prop('disabled', false);

}

function DiscardChanges(){
  $('#btndiscard').prop('disabled', true);
  $('#btnsave').prop('disabled', true);
  $('#pono').prop('disabled', true);  
  $('#btnload').prop('disabled', true);
  $('#qty').prop('disabled',true);
  $('#mod').prop('disabled',true);
  $('#rework').attr('disabled',true);
  $('#classification').prop('disabled',true);
  $('#yieldingstation').prop('disabled',true);
  $('#accumulatedoutput').prop('disabled',true);
  $('#prodtype').prop('disabled',true);
  $('#series').prop('disabled',true); 
  $('#family').prop('disabled',true);
  $('#btnloadpya').prop('disabled',true);
  $('#id').val('');
  $('#pono').val('');
  $('#poqty').val('');
  $('#device').val('');
  $('#family').val('');
  $('#series').val('');
  $('#prodtype').val('');
  $('#productiondate').val('');
  $('#yieldingstation').val('');
  $('#accumulatedoutput').val('');
  $('#classification').val('');
  $('#mod').val('');
  $('#qty').val('');
  $('#rework').val('');
  $('#remarks').val('');
  $('#tinput').val('');
  $('#toutput').val('');
  $('#treject').val('');
  $('#trework').val('');
  $('#tmng').val('');
  $('#tpng').val('');
  $('#ywomng').val('');
  $('#twoyield').val('');
  $('#dppm').val('');
  PyaTable.clear();
  PyaTable.draw();
}

function getDataInYieldingPerformance(){
    pono = $('#pono').val();
    $.ajax({
      url: GetDataInYieldingPerformance,
      type: 'GET',
      dataType: 'json',
      data: {

         _token: token, 
         
              po: $('#pono').val()

     },
      success: function(returnData) {

            
            if (returnData.effect == "2") {
                var yld = returnData.yield_data[0];
                if (returnData.yield_data.length > 0) {
                    console.table("New YLD",yld);
                    $('#id').val(yld.id);
                    $('#pono').val(yld.pono);
                    $('#poqty').val(yld.poqty);
                    $('#device').val(yld.device);
                    $('#family').val(yld.family);
                    $('#series').val(yld.series);
                    $('#prodtype').val(yld.prodtype);
                    $('#tinput').val(yld.tinput);
                    $('#trework').val(yld.trework);
                    $('#classification').val(yld.classification);
                    $('#mod').val(yld.mod);
                    $('#qty').val(yld.qty);
                    $('#toutput').val(yld.toutput);
                    $('#treject').val(yld.treject);
                    $('#tmng').val(yld.tmng);
                    $('#tpng').val(yld.tpng);
                    $('#ywomng').val(yld.ywomng);
                    $('#twoyield').val(yld.twoyield);
                    $('#dppm').val(yld.dppm);

                    Total_Input = $('#tinput').val(yld.tinput);
                    Total_Output = $('#toutput').val(yld.toutput);
                    Total_Reject = $('#treject').val(yld.treject);
                    total_MNG = $('#tmng').val(yld.tmng);
                    total_PNG = $('#tpng').val(yld.tpng);
                    Yield_WOMNG = $('#ywomng').val(yld.ywomng);
                    Total_Yield = $('#twoyield').val(yld.twoyield);
                    DDPM = $('#ddpm').val(yld.ddpm);




                    $('#tinput').val();

                    pya_arr = [];
                    var prod_date = '';

                    $.each(returnData.pya, function(i, x) {
                        pya_arr.push({
                            id: x.id,
                            yield_id: x.yield_id,
                            yieldingno: yld.yieldingno,
                            productiondate: x.productiondate,
                            yieldingstation: x.yieldingstation,
                            accumulatedoutput: x.accumulatedoutput,
                            classification: x.classification,
                            mod: x.mod,
                            qty: x.qty,
                            rework: x.rework,
                            remarks: x.remarks
                        });

                        prod_date = x.productiondate;
                    });
                    refreshPyaTable(pya_arr);
                    reworkpyafieldcomputation();
                }

                $('#productiondate').val(prod_date);
            }               
            if($('#poqty').val() != ''){
                $('#btnloadpya').removeClass("disabled");
                
                $('input[name=productiondate]').attr('disabled',false);
                $('#family').attr('disabled',false);
                $('#series').attr('disabled',false);
                $('#prodtype').attr('disabled',false);
                $('#classification').attr('disabled',false);
                $('#mod').attr('disabled',false);
                $('input[name=qty]').attr('disabled',false);
                $('select[name=yieldingstation]').attr('disabled',false);  
                $('input[name=accumulatedoutput]').attr('disabled',false);  
                $('#pono').attr('disabled',true);
                $('#remarks').attr('disabled',false);

            }else{
                msg("Wrong Input of PO Number","failed");
            }
       }
   });
}
function getFamilyList(){
   var select = $('#family');
   $.ajax({
          url:getFamilyDropdownURL,
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

function getProductList(){
   var select = $('#prodtype');
   $.ajax({
          url:getProdtypeDropdownURL,
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

function getseriesDropdown(){
   var select = $('#series');
   $.ajax({
          url:getSeriesDropdownURL,
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

function makePyaTable(arr) {
    console.log(arr);
     g_row = -1;
    // $('#tbl_pya').dataTable().fnClearTable();
    // $('#tbl_pya').dataTable().fnDestroy();
    PyaTable = $('#tbl_pya').DataTable({
        data: arr,
        lengthChange : false,
        scrollY: "200px",
        paging: false,
        searching: false,
        columns: [
            { data: function(x) {
               g_row++;
               return "<button class='btn btn-sm bg-blue btn_edit_pya' data-row='"+g_row+"' "+
                    "data-id='"+x.id+"' data-qty='"+x.qty+"' data-rework='"+x.rework+"' data-productiondate='"+x.productiondate+"' "+
                    "data-yieldingstation='"+x.yieldingstation+"' data-accumulatedoutput='"+x.accumulatedoutput+"' "+
                    "data-classification='"+x.classification+"' data-mod='"+x.mod+"' data-yield_id='"+x.yield_id+"'>"+
                        "<i class='fa fa-edit'></i>"+
                    "</button>";
            }, searchable: false, orderable: false},

            { data: function(x) {
                return x.productiondate+"<input type='hidden' name='pyaproductiondate[]' value='"+x.productiondate+"'>";
            }},

            { data: function(x) {
                return x.yieldingstation+"<input type='hidden' name='pyayieldingstation[]' value='"+x.yieldingstation+"'>";
            }},

            { data: function(x) {
                return x.accumulatedoutput+"<input type='hidden' name='pyaaccumulatedoutput[]' value='"+x.accumulatedoutput+"'>";
            }},

            { data: function(x) {
                return x.classification+"<input type='hidden' name='pyaclassification[]' value='"+x.classification+"'>";
            }},

            { data: function(x) {
                return x.mod+"<input type='hidden' name='pyamod[]' value='"+x.mod+"'>";
            }},

            { data: function(x) {
                return x.qty+"<input type='hidden' name='pyaqty[]' value='"+x.qty+"'>";
            }},

             { data: function(x) {
                return ((x.rework == undefined)?0:x.rework)+"<input type='hidden' name='reworkyield[]' value='"+((x.rework == undefined)?0:x.rework)+"'>";
            }},
           
            { data: function(x) {
                return x.remarks+"<input type='hidden' name='pyaremarks[]' value='"+x.remarks+"'>";
            }},
        ]
    });
}

function refreshPyaTable(data){
   console.log(data);
   g_row = -1;
   PyaTable.clear();
   PyaTable.rows.add(data);
   PyaTable.draw();
  
}

function pyafieldcomputation(){
    var totalqty =0;
    var totalClasification ="";
    var totalOutput =0;
    var totalInput =0;
    var totalPNG = 0;
    var totalMNG = 0;
    var treject = 0;
    var rework = 0;
    console.log(pya_arr.length);

    console.table(pya_arr);
    $.each(pya_arr, function(i, x) {
        totalOutput += parseInt(x.accumulatedoutput) - parseInt(x.qty);
        totalInput += parseInt(x.accumulatedoutput);
        totalqty = parseInt(x.qty)-parseInt(x.rework);
        totalClasification = x.classification;

        if (totalClasification == "NDF") {
            totalqty = x.qty;
        }

        if (totalClasification == "Production NG (PNG)") {
            if(totalPNG == 0){
                var sample = parseInt(treject);
                var sum = sample + parseInt(totalqty);
                totalPNG = parseInt(totalPNG)+parseInt(totalqty);
            } else {
                var value = parseInt(treject);
                var sum = value + parseInt(totalqty);
                totalPNG = parseInt(totalPNG)+parseInt(totalqty);
            }  
        }

        if (totalClasification == "Material NG (MNG)") {
            if (totalMNG  == 0){
                totalMNG = parseInt(totalMNG)+parseInt(totalqty);
                if ($('#tpng').val()) {
                    var x = parseInt(treject)+parseInt(totalqty)
                } else {
                    var x =treject+parseInt(totalqty);
                }
                  treject = x;
            } else {
                totalMNG = parseInt(totalMNG)+parseInt(totalqty);
                treject = parseInt(treject)+parseInt(totalqty);    
            }

        } else {
            if (treject == 0){
                 treject = treject + parseInt(totalqty);      
            } else {
                 treject = parseInt(treject) + parseInt(totalqty);       
            }
        }

        if (totalMNG == "0") {
            var toaddtp = parseInt(totalOutput) + totalMNG;
            var toaddtr = parseInt(totalOutput) + treject;
            var dev = toaddtp/toaddtr * 100;
            var final = dev.toFixed(2);
            $('#ywomng').val(final);
        } else {

            var toaddtp = parseInt(totalOutput) + parseInt(totalMNG);
            var toaddtr = parseInt(totalOutput) + parseInt(treject);
            var dev = toaddtp/toaddtr * 100;
            var final = dev.toFixed(2);
            $('#ywomng').val(final);
        }

        if (totalOutput == "0") {
            $('#ywomng').val("0");
        } 

        if (totalMNG == 0) {
            var toaddtr = parseInt(totalOutput) + treject;
            var temp = totalOutput/toaddtr * 100;
            var final = temp.toFixed(2);
            $('#twoyield').val(final);    
        } else {
            var toaddtr = parseInt(totalOutput) + parseInt(treject);
            var temp = totalOutput/toaddtr * 100;
            var final = temp.toFixed(2);
            $('#twoyield').val(final);    
        }

        if (totalMNG == 0) {
            var tempdppm = parseInt(treject)/parseInt(totalOutput); 
            $('#dppm').val((tempdppm * 1000000).toFixed(2));

            // var toutputandtr = parseInt(totalOutput) + parseInt(treject);
            // var tempdppm = totalPNG/toutputandtr; 
            // $('#dppm').val((tempdppm * 1000000).toFixed(2));
        } else {
            var tempdppm = parseInt(treject)/parseInt(totalOutput); 
            $('#dppm').val((tempdppm * 1000000).toFixed(2));
            
            // var toutputandtr = parseInt(totalOutput) + parseInt(treject);
            // var tempdppm = totalPNG/toutputandtr; 
            // $('#dppm').val((tempdppm * 1000000).toFixed(2));    
        }

        console.log(totalOutput);
        console.log(totalInput);
        console.log(treject);
        console.log(totalPNG);
        console.log(totalMNG);
        console.log((tempdppm * 1000000).toFixed(2));


        $('#toutput').val(totalOutput);
        $('#treject').val(treject);
        $('#tpng').val(totalPNG);
        $('#tmng').val(totalMNG);


        $('#tinput').val(totalInput);

    });
}

function reworkpyafieldcomputation(){
    // var OldValue = $('#old_value').val();
    var Clasification = $('#classification').val();
    var totalOutput =parseInt($('#toutput').val());
    var totalInput =$('#tinput').val();
    var totalPNG = parseInt($('#tpng').val());
    var totalMNG = parseInt($('#tmng').val());
    var treject = parseInt($('#treject').val());
    var rework = parseInt($('#rework').val() == ""?0:$('#rework').val());
    var oldrework = parseInt($('#oldrework').val() == ""?0:$('#oldrework').val());
    // var reworkresult = $('#trework').val();
    var trework = parseInt($('#trework').val() == ""?0:$('#trework').val());
    // var reworktotal =+ parseInt(rework) + parseInt(reworkresult);
    var qty = $('#qty').val();

    // var x = totalrework + rework;

    var good = qty-rework;
    var oldgood = 0;
    if (oldrework > 0) {
      oldgood = qty-oldrework;
    }else{
      oldgood = oldrework;
    }


     if(Clasification == "Production NG (PNG)"){
       totalPNG = totalPNG + oldgood ;
       totalPNG = totalPNG - good;

     }else if(Clasification == "Material NG (MNG)"){


         totalMNG = totalMNG + oldgood;
         totalMNG = totalMNG - good;

     }

      totalOutput = totalOutput - oldgood;
      totalOutput = parseInt(totalOutput) + parseInt(good);

      treject = treject + oldgood;   
      treject = treject - good;


    var toaddtp = parseInt(totalOutput) + parseInt(totalMNG);
    var toaddtr = parseInt(totalOutput) + parseInt(treject);
    var dev = toaddtp/toaddtr * 100;
    var ywmngresult = dev.toFixed(2);

  
    //computation for second passed.
    
    var toaddtr = parseInt(totalInput) - parseInt(trework);
    var next = toaddtr/totalInput;
    var temp = next * 100;
    // var temp = totalOutput/toaddtr * 100;
    var final = temp.toFixed(2);


    var tempdppm = parseInt(treject)/parseInt(totalOutput); 

    //computation for total rework
    var totalrework = parseInt(rework) + parseInt(trework);

    trework = trework - oldrework;



    trework = trework + rework;

    $('#dppm').val((tempdppm * 1000000).toFixed(2));
    $('#twoyield').val(final);  
    $('#toutput').val(totalOutput);
    $('#tinput').val(totalInput);
    $('#treject').val(treject);
    $('#tpng').val(totalPNG);
    $('#tmng').val(totalMNG);
    $('#ywomng').val(ywmngresult);
    $('#trework').val(trework);
    $('#oldrework').val(0);


    // $('#old_value').val()
}

// function DropdownDisabled(){
//    $('#family').prop('disabled',true);
// }
function addpya(){
    var poqty = $('#poqty').val();
    var pono =  $('input[name=pono]').val();
    var productiondate =  $('input[name=productiondate]').val();
    var yieldingstation =  $('#yieldingstation').val();
    var accumulatedoutput =  $('input[name=accumulatedoutput]').val();
    var toutput =  $('input[name=toutput]').val();
    var tinput =  $('input[name=tinput]').val();
    var countpya = $('#countpya').val();
    var classification = $('#classification').val();
    var mod = $('#mod').val();
    var qty = $('input[name=qty]').val();
    var rework = $('input[name=rework]').val();
    var oldrework = $('#oldrework').val();
    var row = $('#row').val();
    
    // var id = $('#id').val();
    // var yield_id = $('#yield_id').val();

    $('#er8').html("");

    if(pono == ""){     
        $('#er1').html("PO number field is empty"); 
        $('#er1').css('color', 'red');       
          return false;  
    }  
    if(poqty == ""){     
        $('#error1').html("Please click the load button"); 
        $('#error1').css('color', 'red');       
        return false;  
    } 
    if (yieldingstation == ""){
        $('#er6').html("Yielding Station field is empty"); 
        $('#er6').css('color', 'red');
        return false;
    }
    if (accumulatedoutput == ""){
        $('#er7').html("Accumulated Output field is empty"); 
        $('#er7').css('color', 'red');
        return false;
    }

    if (classification == ""){
        $('#er4').html("Classification field is empty"); 
        $('#er4').css('color', 'red');
        return false;
    }
    if(classification != "NDF"){
        if (mod == ""){
            $('#er5').html("Please Select Mode of Defect field is empty"); 
            $('#er5').css('color', 'red');
            return false;
        }
        if (qty == 0){
            $('#er10').html("Qty not accepting 0 value"); 
            $('#er10').css('color', 'red');
            return false;
        }
    }


    if (row == '') {
        pya_length = pya_arr.length;
        pya_length++;

        pya_arr.push({
            id: pya_length,
            yield_id: $('#id').val(),
            productiondate: productiondate,
            yieldingstation: yieldingstation,
            accumulatedoutput: accumulatedoutput,
            classification: classification,
            mod: mod,
            qty: qty,
            rework:rework,
            remarks: $('#remarks').val()
        }); 
    } else {
          console.table(pya_arr);
            pya_arr.splice(row,1,{
                id:'',
                yield_id: $('#yield_id').val(),
                productiondate: productiondate,
                yieldingstation: yieldingstation,
                accumulatedoutput: accumulatedoutput,
                classification: classification,
                mod: mod,
                qty: qty,               
                rework: rework,
                trework: $('#trework').val(),
                remarks: $('#remarks').val()

            });
          console.table(pya_arr);
      }
     
    refreshPyaTable(pya_arr);
    reworkpyafieldcomputation();
    clear();

    $('#mod').attr('disabled',false);
    $('#qty').attr('disabled',false);
    $('#remarks').attr('disabled',false);
   
}

function rework_yield(){
    // var yield_id = $('#yield_id').val();
    var hdstatus = $('#hdstatus').val();  
    var pono = $('input[name=pono]').val();
    var poqty = $('input[name=poqty]').val();
    var device = $('input[name=device]').val();
    var family = $('#family').val();
    var series = $('#series').val();
    var prodtype = $('#prodtype').val();
    var classification = $('#classification').val();
    var mod = $('#mod').val();
    var qty = $('input[name=qty]').val();   
    var rework = $('input[name=rework]').val(); 
    var yieldingstation =  $('select[name=yieldingstation]').val();
    var accumulatedoutput =  $('input[name=accumulatedoutput]').val();
    var tinput =  $('input[name=tinput]').val();
    var toutput =  $('input[name=toutput]').val();
    var tinput =  $('input[name=tinput]').val();
    var treject = $('input[name=treject]').val();
    var tmng =  $('input[name=tmng]').val();
    var tpng =  $('input[name=tpng]').val();
    var ywomng =  $('input[name=ywomng]').val();
    var twoyield =  $('input[name=twoyield]').val();
    var trework = $('input[name=trework]').val();

    if(pono == ""){     
        $('#er1').html("PO number field is empty"); 
        $('#er1').css('color', 'red');       
        return false;  
    } 

    if(poqty == ""){     
        $('#error1').html("Please click the load button"); 
        $('#error1').css('color', 'red');       
        return false;  
    } 
     
    if (family == ""){
        $('#er2').html("Family field is empty"); 
        $('#er2').css('color', 'red');        
        return false;
    }

    if (series == ""){
        $('#er3').html("Series field is empty"); 
        $('#er3').css('color', 'red');
        return false;
    }

    if (prodtype == ""){
        $('#erprodtype').html("Series field is empty"); 
        $('#erprodtype').css('color', 'red');
        return false;
    }

    var traymod = [];
    $('#mod option:selected').each(function () {
        traymod.push($(this).val());
    });
       
    var myData = {
        _token: token
        ,id: $('#id').val()
        // ,yield_id: $('#yield_id').val()
        ,pono : pono
        ,poqty : poqty
        ,device : device
        ,family : family
        ,series : series
        ,prodtype : prodtype
        ,classification : classification
        ,mod : traymod
        ,qty : qty
        ,rework: rework
        ,trework:trework
        ,productiondate : $('input[name=productiondate]').val()
        ,yieldingstation : yieldingstation
        ,accumulatedoutput : accumulatedoutput
        ,toutput : toutput
        ,tinput : tinput
        ,treject : treject
        ,tmng : tmng
        ,tpng : tpng
        ,ywomng : ywomng
        ,twoyield : twoyield
        ,status : hdstatus
        ,newaccumulatedoutput:$('input[name="pyaaccumulatedoutput[]"]').map(function(){return $(this).val();}).get()
        ,newproductiondate:$('input[name="pyaproductiondate[]"]').map(function(){return $(this).val();}).get()
        ,newyieldingstation:$('input[name="pyayieldingstation[]"]').map(function(){return $(this).val();}).get()
        ,newclassification:$('input[name="pyaclassification[]"]').map(function(){return $(this).val();}).get()
        ,newmod:$('input[name="pyamod[]"]').map(function(){return $(this).val();}).get()
        ,newqty:$('input[name="pyaqty[]"]').map(function(){return $(this).val();}).get()
        ,newrework:$('input[name="reworkyield[]"]').map(function(){return $(this).val();}).get()       
        ,remarks:$('input[name="pyaremarks[]"]').map(function(){return $(this).val();}).get()

    };
      console.table(myData);

    $.ajax({
        url: saveURL,
        type: 'POST',
        dataType: 'JSON',
        data: myData,
    }).done(function(data, textStatus, jqXHR){
        DiscardChanges();
        msg(data.msg,data.status);
        DisabledALL();
        // DisabledButton();
        DiscardChanges();
        pya_arr = [];
        refreshPyaTable([]);
        pyafieldcomputation();
        $('input[name=pono]').val("");
    }).fail(function(jqXHR, textStatus, errorThrown){
        msg(errorThrown,textStatus);
    }).always(function() {
        console.log("complete");
    });
}

function clear(){
    $('#row').val("");
    $('#classification').val("");
    $('#mod').val("");
    $('#yieldingstation').val("");
    $('input[name=qty]').val("");
    $('input[name=rework]').val("");
    $('input[name=accumulatedoutput]').val("");
    $('#btnloadpya').removeClass('bg-blue');
    $('#btnloadpya').addClass('bg-blue');
    $('#btnloadpya').html('<i class="fa fa-check"></i>');
}

function ClearAll(){
        $('#row').val("");
        $('#id').val("");
        $('input[name=yieldingno]').val("");
        $('input[name=poqty]').val("");
        $('input[name=device]').val("");
        $('#classification').val("");      
        $('#mod').val("");
        $('input[name=qty]').val("");
        $('input[name=rework]').val("");
        $('input[name=accumulatedoutput]').val("");
        $('#yieldingstation').val("");
        $('input[name=toutput]').val("0"); 
        $('input[name=tinput]').val("0"); 
        $('input[name=treject]').val("0");
        $('input[name=tmng]').val("0");
        $('input[name=tpng]').val("0");
        $('input[name=ywomng]').val("0");
        $('input[name=twoyield]').val("0"); 
        $('#dppm').val("");
        $('#hdstatus').val("");          
        $('#family').val("");
        $('#series').val("");
        $('#prodtype').val("");
        $('#tbldetails').html("");
        $('#tblsummary').html("");
        $('#tbody1').html("");
        $('#tbody2').html("");
        // $('#btnloadpya').removeClass('bg-blue');
        // $('#btnloadpya').addClass('bg-green');
        // $('#btnloadpya').html('<i class="fa fa-plus"></i>');
}

function DisabledALL(){
    $('input[name=pono]').attr('disabled',true);
    $('#remarks').attr('disabled',true);
    $('input[name=poqty]').attr('disabled',true);
    $('input[name=device]').attr('disabled',true);
    $('input[name=treject]').attr('disabled',true);
    $('input[name=toutput]').attr('disabled',true);
    $('input[name=tinput]').attr('readonly',true);
    $('#family').attr('disabled',true);
    $('#series').attr('disabled',true);
    $('#prodtype').attr('disabled',true);
    $('#rework').attr('disabled',true);
    $('#classification').attr('disabled',true);
    $('#mod').attr('disabled',true);
    $('input[name=qty]').attr('disabled',true);
    $('input[name=productiondate]').attr('disabled',true);
    $('#yieldingno').val("");
    $('#hdstatus').val("");
    $('#yieldingstation').attr('disabled',true);
    $('input[name=accumulatedoutput]').attr('disabled',true);
    $('#btnremove_detail').addClass("disabled");
    $('.checkAllitemsPYA').attr('disabled',false);
    $('input[name=yieldingno]').attr('disabled',false);
}

// function DisabledButton(){
//     $('#btnsave').addClass("disabled");
//     $('#btnload').addClass("disabled");
//     $('#btnloadpya').addClass("disabled");
//     // $('#btndiscard').addClass("disabled");
//     // $('#btnadd').removeClass("disabled");
// }





