var pya_arr = [];
var rejects = 0;
var inputs = 0;

// makePyaTable(pya_arr);
// pyafieldcomputation();
$(function(e) {
    checkAllCheckboxesInTable('.check_all_pya','.check_item_pya');
    // GetDropdownByName();
    getFamilyList();
    getProductList();
    getSeriesDropdown();
    getfinalinspectionDropdown();
    getmodeofdeffects();
    DisabledButton();
    DisabledALL();    
    $('#yieldingstation').change(function(){
        $('#yieldingstation').val($(this).val());
        if($(this).val() == "Machine"){
            $('#rework').val(0);
        }
        if($(this).val() == "First Visual Inspection"){
            $('#rework').val(0);
        }
        if($(this).val() == "Final Visual Inspection"){
            $('#rework').val(0);
        }
    });
    
    $('#btndiscard').click(function(){
        $('#rework').val("");
        $('#pono').val("");
        DisabledButton();
        DisabledALL();
        ClearAll();
        pya_arr = [];
        makePyaTable();
        // pyafieldcomputation();
    });

    $('#btnload').on("click",function(){
       // addnew
        // addpya();
        ClearAll();
        pya_arr = [];
         // makePyaTable();
        // pyafieldcomputation();
        GETPoDetails();       
    });

    $('#btnadd').on('click',function(){
        addnew();
    });

    $('#btnloadpya').on('click',function(){

      addpya();
    });

    $('#classification').change(function(){
        if($(this).val() == "NDF"){
            $('#mod').attr('disabled',true);
            // $('#qty').attr('readonly',true);
            $('#remarks').attr('readonly',true);
            $('#er5').html(""); 
            
        }else{
            $('#mod').attr('disabled',false);
            // $('#qty').attr('readonly',false);
            $('#remarks').attr('readonly',false);
        }
            // $('#qty').val("0");
    });

    $('#btnsave').on('click', function() {
         if($('#poqty').val() == ""){            
              $('#error1').html("Please click the load button"); 
              $('#error1').css('color', 'red');       
              return false;  
             // alert('po quantity required');
        }else{
             $('#loading').modal('show');
             save_yield();    
        }
    });

    $('#tbl_pyas').on('click', '.btn_edit_pya',function() {
        $('#row').val($(this).attr('data-row'));
        $('#productiondate').val($(this).attr('data-productiondate'));
        $('#yieldingstation').val($(this).attr('data-yieldingstation'));
        $('#classification').val($(this).attr('data-classification'));
        $('#mod').val($(this).attr('data-mod')).trigger('change');
        $('#rework').val($(this).attr('data-rework'));
        $('#accumulatedoutput').val($(this).attr('data-accumulatedoutput'));
        $('#btnloadpya').removeClass('bg-green');
        $('#btnloadpya').addClass('bg-blue');
        $('#btnloadpya').html('<i class="fa fa-check"></i>');

        if ($(this).attr('data-classification') == 'NDF') {
            $('#mod').prop('disabled', true);
            $('#qty').prop('readonly', true);
            $('#remarks').prop('readonly', true);
        } else {
            $('#mod').prop('disabled', false);
            $('#qty').prop('readonly', false);
            $('#remarks').prop('readonly', false);
        }
    });

});

function addnew(){
    $('#btnsearch').addClass("disabled");
    $('#btnloadpya').addClass("disabled");
    $('#btndiscard').removeClass("disabled");
    $('#btnadd').addClass("disabled");
    $('#hdstatus').val("ADD");
    var hdyieldingno = $('#hdyieldingno').val();
    $('input[name=yieldingno]').val(hdyieldingno);
    ClearAll(); 
    $('#btnsave').removeClass("disabled");
    $('#btnload').removeClass("disabled");
    $('input[name=pono]').attr('disabled',false);

    $('#btnload').click(function(){
        $('#error1').html("");  
    });
    $('#family').click(function(){
        $('#er2').html(""); 
    });
    $('#series').click(function(){
        $('#er3').html(""); 
    });
    $('#prodtype').click(function(){
        $('#erprodtype').html(""); 
    });
    $('#classification').click(function(){
        $('#er4').html(""); 
    });
    $('#accumulatedoutput').keyup(function(){
        $('#er7').html(""); 
    });
    $('#mod').click(function(){
        $('#er5').html(""); 
    });
    $('#yieldingstation').click(function(){
        $('#er6').html(""); 
    });
    $('#toutput').keyup(function(){
        $('#er8').html(""); 
    });
    $('#tinput').keyup(function(){
        $('#er8').html(""); 
    });
    $('#treject').keyup(function(){
        $('#er9').html(""); 
    });
    $('#qty').keyup(function(){
        $('#er10').html(""); 
    });
}

function save_yield(){
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
        _token :token
        ,id: $('#id').val()
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
        ,productiondate : $('input[name=productiondate]').val()
        ,yieldingstation : yieldingstation
        ,accumulatedoutput : accumulatedoutput
        ,toutput : toutput
        ,tinput : tinput
        ,treject : treject
        ,tmng : tmng
        ,tpng : tpng
        ,ywomng :ywomng 
        ,twoyield : twoyield
        ,status : hdstatus
        ,newaccumulatedoutput:$('input[name="pyaaccumulatedoutput[]"]').map(function(){return $(this).val();}).get()
        ,newproductiondate:$('input[name="pyaproductiondate[]"]').map(function(){return $(this).val();}).get()
        ,newyieldingstation:$('input[name="pyayieldingstation[]"]').map(function(){return $(this).val();}).get()
        ,newclassification:$('input[name="pyaclassification[]"]').map(function(){return $(this).val();}).get()
        ,newmod:$('input[name="pyamod[]"]').map(function(){return $(this).val();}).get()
        // ,newqty:$('input[name="pyaqty[]"]').map(function(){return $(this).val();}).get()
        ,newrework:$('input[name="pyarework[]"]').map(function(){return $(this).val();}).get()
        ,remarks:$('input[name="pyaremarks[]"]').map(function(){return $(this).val();}).get()
    };

    $.ajax({
        url: saveURL,
        type: 'POST',
        dataType: 'JSON',
        data: myData,
    }).done(function(data, textStatus, jqXHR){
         if(data.msg,data.status){
             setTimeout(function(){
                 $('#loading').modal('hide');
                   // console.log(data);
                    ClearAll();
                    msg(data.msg,data.status);
                    DisabledALL();
                    DisabledButton();       
                    pya_arr = [];
                    makePyaTable([]);
                    pyafieldcomputation();
                    $('input[name=pono]').val("");
                  }, 3000);  
        }    
    }).fail(function(jqXHR, textStatus, errorThrown){
           msg(errorThrown,textStatus);
    }).always(function() {
        console.log("complete");
    });
}

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
    // var qty = $('input[name=qty]').val();
    var rework = parseInt($('#rework').val());
    var row = $('#row').val();
    var total_input = parseInt($('#tinput').val());
    var forreworkreject = $('#treject').val() == ""?0:parseInt($('#treject').val());
    var total_rework = forreworkreject + rework;

    // if(total_rework>total_input){
    //    msg("Total rework is greater than total input","failed");
    //    clear();
    // }else{
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
    if (yieldingstation == "0" || yieldingstation == null){
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

    if(classification !== "NDF"){
        if (mod == null || mod == "0"){
            $('#er5').html("Please Select Mode of Defect field is empty"); 
            $('#er5').css('color', 'red');
            return false;
        }
        if (rework == 0){
            $('#er10').html("Rework not accepting 0 value"); 
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
            rework: rework,
            remarks: $('#remarks').val()
        });     
    } else {
        pya_arr.splice(row,1,{
            id: '',
            yield_id: $('#id').val(),
            productiondate: productiondate,
            yieldingstation: yieldingstation,
            accumulatedoutput: accumulatedoutput,
            classification: classification,
            mod: mod,
            rework: rework,
            remarks: $('#remarks').val()
        });
    }
    
       pyafieldcomputation();
       makePyaTable(pya_arr);
   // pyafieldcomputation();
       clear();   
        $('#mod').attr('disabled',false);
        $('#rework').attr('disabled',false);
        $('#remarks').attr('disabled',false);
    //}
   
}

function makePyaTable(arr) {
    var row = -1;
    $('#tbl_pyas').dataTable().fnClearTable();
    $('#tbl_pyas').dataTable().fnDestroy();
    $('#tbl_pyas').dataTable({
        data: arr,
        bLengthChange : false,
        scrollY: "200px",
        paging: false,
        searching: false,
        columns: [
            { data: function(x) {
                row++;
                return "<input type='checkbox' class='check_item_pya checkboxesPYA' value='"+row+"' data-id='"+x.id+"' >";
            }, searchable: false, orderable: false },

            { data: function(x) {
               return "<button class='btn btn-sm bg-blue btn_edit_pya' data-row='"+row+"' "+
                    "data-id='"+x.id+"' data-rework='"+x.rework+"' data-productiondate='"+x.productiondate+"' "+
                    "data-yieldingstation='"+x.yieldingstation+"'"+ "data-accumulatedoutput='"+x.accumulatedoutput+"' "+
                    "data-classification='"+x.classification+"' data-mod='"+x.mod+"' data-yield_id='"+x.yield_id+"'>"+
                        "<i class='fa fa-edit'></i>"+
                    "</button>";
            }},

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
                return x.rework+"<input type='hidden' name='pyarework[]' value='"+x.rework+"'>";
            }},

            { data: function(x) {
                return x.remarks+"<input type='hidden' name='pyaremarks[]' value='"+x.remarks+"'>";
            }},
        ]
    });
}

function deletepya(){
    var id =""
    $(".check_item_pya:checked").each(function () {
        id = $(this).val();
        pya_arr.splice(id,1);
        makePyaTable(pya_arr);
        pyafieldcomputation();
        msg("Yield Performance was successfully remove.","success");
        clear();
    });
    if (id == "") {
         msg("Please select at least 1 Set.","failed");
    }
}

function pyafieldcomputation(){
    var totalrework =0;
    var totalClasification ="";
    var totalOutput =0;
    var totalInput  =0;
    var totalrework =0;
    var totalPNG = 0;
    var totalMNG = 0;
    var treject = 0;
    // var rejects = treject;
    // var inputs = totalInput;
    
    $.each(pya_arr, function(i, x) {

        totalInput += parseInt(x.accumulatedoutput) + parseInt(x.rework);
        totalOutput += parseInt(x.accumulatedoutput);
        totalrework = x.rework;
        totalClasification = x.classification;

        // if (totalClasification == "NDF") {
        //     totalqty = x.qty;
        // }

        if (totalClasification == "Production NG (PNG)") {
            if(totalPNG == 0){
                var sample = (+treject);
                var sum = sample + (+totalrework);
                totalPNG = (+totalPNG)+(+totalrework);
            } else {
                var value = (+treject);
                var sum = value + (+totalrework);
                totalPNG = (+totalPNG)+(+totalrework);
            }  
        }

        if (totalClasification == "Material NG (MNG)") {
            if (totalMNG  == 0){
                totalMNG = (+totalMNG)+(+totalrework);
                if ($('#tpng').val()) {
                    var x = (+treject)+(+totalrework)
                } else {
                    var x =treject+(+totalrework);
                }
                  treject = x;
            } else {
                totalMNG = (+totalMNG)+(+totalrework);
                treject = (+treject)+(+totalrework);    
            }

        } else {
            if (treject == 0){
                 treject = treject + (+totalrework);      
            } else {
                 treject = (+treject) + (+totalrework);       
            }
        }

        if (totalMNG == 0) {

            var tempdppm = (+totalOutput) + (+totalPNG); 
            var tempdppm2nd = totalPNG / tempdppm;
            var tempdppm3rd = tempdppm2nd * 1000000;
            var tempdppm4th = tempdppm3rd.toFixed(2);
            $('#dppm').val(tempdppm4th);

            // var toutputandtr = parseInt(totalOutput) + parseInt(treject);
            // var tempdppm = totalPNG/toutputandtr; 
            // $('#dppm').val((tempdppm * 1000000).toFixed(2));
        } else {

            var tempdppm = (+totalOutput) + (+totalPNG); 
            var tempdppm2nd = totalPNG / tempdppm;
            var tempdppm3rd = tempdppm2nd * 1000000;
            var tempdppm4th = tempdppm3rd.toFixed(2);
            $('#dppm').val(tempdppm4th);
            
            // var toutputandtr = parseInt(totalOutput) + parseInt(treject);
            // var tempdppm = totalPNG/toutputandtr; 
            // $('#dppm').val((tempdppm * 1000000).toFixed(2));    
        }

        // totalOutput = totalInput - treject;  
        
           if (totalMNG == "0") {
            var toaddtp = (+totalOutput) + totalPNG;
            var toaddtr = (+totalOutput) / toaddtp;
            var dev = toaddtr * 100;
            var final = dev.toFixed(2);
            $('#ywomng').val(final);
        } else { 

            var toaddtp = (+totalOutput) + totalPNG;
            var toaddtr = (+totalOutput) / toaddtp;
            var dev = toaddtr * 100;
            var final = dev.toFixed(2);
            $('#ywomng').val(final);
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

        if (totalOutput == "0") {
            $('#ywomng').val("0");
        } 
      
        $('#toutput').val(totalOutput);
        $('#tinput').val(totalInput)
        $('#treject').val(treject);
        $('#tpng').val(totalPNG);
        $('#tmng').val(totalMNG);
    });
}

function GETPoDetails(){
    pono = $('#pono').val();
    $.ajax({
      url: getPODetailsURL,
      type: 'GET',
      dataType: 'json',
      data: { _token: token, po: $('#pono').val() },
      success: function(returnData) {
           if (returnData.effect == "1")
            {    var details = returnData.po_details;
                // $('#device').val(details.device_name);
                // $('#poqty').val(details.po_qty);
                // $('#family').val(details.family);
                // $('#series').val(details.series);
                // $('#prodtype').val(details.prodtype);

                $('#pono').val(details.pono);
                $('#poqty').val(details.poqty);
                $('#device').val(details.device);
                $('#family').val(details.family);
                $('#series').val(details.series);
                $('#prodtype').val(details.prodtype);
                $('#qty').val(details.treject);
                $('#hqty').val(details.treject);
                // $('#tinput').val(details.treject);

            }

            if (returnData.effect == "2") {
                var yld = returnData.yield_data[0];
                if (returnData.yield_data.length > 0) {
                    $('#id').val(yld.id);
                    $('#pono').val(yld.pono);
                    $('#poqty').val(yld.poqty);
                    $('#device').val(yld.device);
                    $('#family').val(yld.family);
                    $('#series').val(yld.series);
                    $('#prodtype').val(yld.prodtype);
                    $('#classification').val(yld.classification);
                    $('#mod').val(yld.mod);
                    $('#qty').val(yld.qty);
                    $('#hqty').val(yld.qty);
                    $('#tinput').val(yld.tinput);
                    $('#toutput').val(yld.toutput);
                    $('#treject').val(yld.treject);
                    $('#tmng').val(yld.tmng);
                    $('#tpng').val(yld.tpng);
                    $('#ywomng').val(yld.ywomng);
                    $('#twoyield').val(yld.twoyield);
                    $('#dppm').val(yld.dppm);

                    $('#tinput').val();

                    pya_arr = [];
                    var prod_date = '';

                    $.each(returnData.pya, function(i, x) {
                        pya_arr.push({
                            id: x.id,
                            yieldingno: yld.yieldingno,
                            productiondate: x.productiondate,
                            yieldingstation: x.yieldingstation,
                            accumulatedoutput: x.accumulatedoutput,
                            classification: x.classification,
                            mod: x.mod,
                            rework: x.rework,
                            remarks: x.remarks
                        });

                        prod_date = x.productiondate;
                    });
                    makePyaTable(pya_arr);
                    // retrievepyafieldcomputation();
                    pyafieldcomputation();
                }

                $('#productiondate').val(prod_date);
            }
                

            if($('#poqty').val() != ''){
                $('#btnloadpya').removeClass("disabled");
                
                $('input[name=productiondate]').attr('disabled',false);
                $('#family').attr('disabled',true);
                $('#series').attr('disabled',true);
                $('#prodtype').attr('disabled',true);
                $('#rework').attr('disabled',false);
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
          url: getFamilyDropdownURL,
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
          url: getProdtypeDropdownURL,
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

function getSeriesDropdown(){
   var select = $('#series');
   $.ajax({
          url: getSeriesDropdownURL,
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

function getfinalinspectionDropdown(){
   var select = $('#yieldingstation');
   $.ajax({
          url: GetFianlVisualInspectionURL,
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
function getmodeofdeffects(){
   var select = $('#mod');
   $.ajax({
          url: GetModdeffectsURL,
          type: "get",
          dataType: "json",
          success: function (returndata) {
               // console.log(data);
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

// GetModdeffectsURL

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
    $('#rework').attr('disabled',true);
    $('#prodtype').attr('disabled',true);
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

function DisabledButton(){
    $('#btnsave').addClass("disabled");
    $('#btnload').addClass("disabled");
    $('#btnloadpya').addClass("disabled");
    $('#btndiscard').addClass("disabled");
    $('#btnadd').removeClass("disabled");
}

function ClearAll(){
        $('#row').val("");
        $('#id').val("");
        $('input[name=yieldingno]').val("");
        $('input[name=poqty]').val("");
        $('input[name=device]').val("");
        $('#classification').val("");
        $("#mod").select2("val", "");
        $('input[name=qty]').val("");
        $('input[name=accumulatedoutput]').val("");
        $('#yieldingstation').val("");
        $('input[name=toutput]').val(""); 
        $('input[name=tinput]').val(""); 
        $('input[name=treject]').val("");
        $('input[name=tmng]').val("");
        $('input[name=tpng]').val("");
        $('input[name=ywomng]').val("");
        $('input[name=twoyield]').val(""); 
        $('#dppm').val("");
        $('#hdstatus').val("");          
        $('#family').val("");
        $('#series').val("");
        $('#prodtype').val("");
        $('#tbldetails').html("");
        $('#tblsummary').html("");
        $('#tbody1').html("");
        $('#tbody2').html("");
        $('#btnloadpya').removeClass('bg-blue');
        $('#btnloadpya').addClass('bg-green');
        $('#btnloadpya').html('<i class="fa fa-plus"></i>');
}
function clear(){
    $('#row').val("");
    $('#classification').val("");
    $('#mod').val("");
    $('#yieldingstation').val("");
    $('#rework').val("");
    // $('input[name=qty]').val("");
    $('input[name=accumulatedoutput]').val("");
    $('#btnloadpya').removeClass('bg-blue');
    $('#btnloadpya').addClass('bg-green');
    $('#btnloadpya').html('<i class="fa fa-plus"></i>');
}
function back(){
    window.location.href=backURL;    
}