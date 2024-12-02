var dataColumn = [
        { data: function(data) {
            return '<input type="checkbox" class="input-sm checkboxes" value="'+data.id+'" name="checkitem" id="checkitem"></input>';
        }, name: 'id' },

        { data: 'action', name: 'action', orderable: false, searchable: false },

        { data: function(data) {
            return data.date_inspected+'<input type="hidden" id="hd_date_inspected" value="'+data.date_inspected+'" name="hd_date_inspected[]">';
        }, name: 'date_inspected' },

        { data: function(data) {
            return data.shipment_date+'<input type="hidden" id="hd_shipment_date" value="'+data.shipment_date+'" name="hd_shipment_date[]">';
        }, name: 'shipment_date' },

        { data: function(data) {
            return data.device_name+'<input type="hidden" id="hd_device_name" value="'+data.device_name+'" name="hd_device_name[]">';
        }, name: 'device_name' },

        { data: function(data) {
            return data.po_num+'<input type="hidden" id="hd_po_num" value="'+data.po_num+'" name="hd_po_num[]">';
        }, name: 'po_num' },

        { data: function(data) {
            return data.packing_operator+'<input type="hidden" id="hd_packing_operator" value="'+data.packing_operator+'" name="hd_packing_operator[]">';
        }, name: 'packing_operator' },

        { data: function(data) {
            return data.inspector+'<input type="hidden" id="hd_inspector" value="'+data.inspector+'" name="hd_inspector[]">';
        }, name: 'inspector' },

        { data: function(data) {
            return data.packing_type+'<input type="hidden" id="hd_packing_type" value="'+data.packing_type+'" name="hd_packing_type[]">';
        }, name: 'packing_type' },

        { data: function(data) {
            return data.unit_condition+'<input type="hidden" id="hd_unit_condition" value="'+data.unit_condition+'" name="hd_unit_condition[]">';
        }, name: 'unit_condition' },

        { data: function(data) {
            return data.packing_code_series+'<input type="hidden" id="hd_packing_code_series" value="'+data.packing_code_series+'" name="hd_packing_code_series[]">';
        }, name: 'packing_code_series' },

        { data: function(data) {
            return data.carton_num+'<input type="hidden" id="hd_carton_num" value="'+data.carton_num+'" name="hd_carton_num[]">';
        }, name: 'carton_num' },

        { data: function(data) {
            return data.packing_code+'<input type="hidden" id="hd_packing_code" value="'+data.packing_code+'" name="hd_packing_code[]">';
        }, name: 'packing_code' },

        { data: function(data) {
            return data.total_qty+'<input type="hidden" id="hd_total_qty" value="'+data.total_qty+'" name="hd_total_qty[]">';
        }, name: 'total_qty' },

        { data: function(data) {
            return data.judgement+'<input type="hidden" id="hd_judgement" value="'+data.judgement+'" name="hd_judgement[]">';
        }, name: 'judgement' },

        { data: function(data) {
            return data.remarks+'<input type="hidden" id="hd_remarks" value="'+data.remarks+'" name="hd_remarks[]">';
        }, name: 'remarks' }
    ];
$(function() {
    initData();
    loadPackdata("{{ url('/packgetrows') }}"+"?mode=");
    lot_accepted();
    totalmod();
    $('#hd_report_status').val("");
    $('#groupby_datefrom').datepicker();
    $('#groupby_dateto').datepicker();
    $('#groupby_datefrom').on('change',function(){
          $(this).datepicker('hide');
    });
    $('#groupby_dateto').on('change',function(){
          $(this).datepicker('hide');
    });

    $('#btn_pdf').click(function(){
        window.location.href="{{ url('/packinginspection') }}";
    });
    $('#btn_excel').click(function(){
        window.location.href="{{ url('/packinginspection') }}";
    });

    $('#btn_groupby').on('click', function() {
        $('#GroupByModal').modal('show');
        $('#groupby_datefrom').val("");
        $('#groupby_dateto').val("");
        $('#group1').select2('val',"");
        $('#group1content').select2('val',"");
        $('#group2').select2('val',"");
        $('#group2content').select2('val',"");
        $('#group3').select2('val',"");
        $('#group3content').select2('val',"");
        $('#group4').select2('val',"");
        $('#group4content').select2('val',"");
        $('#group5').select2('val',"");
        $('#group5content').select2('val',"");

        //to classify group by when reporting----------
        $('#hd_report_status').val("GROUPBY");
        $('#hd_search_from').val("");
        $('#hd_search_to').val("");
        $('#hd_search_pono').val("");
    });

    $('#btn_search').on('click', function() {
        $('#SearchModal').modal('show');
        $('#search_pono').val("");
        $('#search_from').val("");
        $('#search_to').val("");
        $('#er_search_from').html(""); 
        $('#er_search_to').html(""); 

        //to classify group by when reporting----------
        $('#hd_report_status').val("SEARCH");
        $('#hd_search_from').val("");
        $('#hd_search_to').val("");
        $('#hd_groupfield').val("");
        $('#hd_value').val("");
    });

    $('#btn_add').on('click', function() {
        $('#AddNewModal').modal('show');
        $('#status').val("ADD");
        $('#rc_packcode').val();
        $('#po_no').val("");
        $('#insp_date').val("");
        $('#ship_date').val("");
        $('#series_name').val("");
        $('#packing_type').val("");
        $('#unit_condition').val("");
        $('#packing_operator').val("");
        $('#remarks').val("");
        $('#pack_code_per_series').val("");
        $('#carton_no').val("");
        $('#pack_code').val("");
        $('#judgement').val("");
        $('#total_qty').val("");
        $('#no_of_defects').val("");
    });

    $('#pack_code_per_series').on('change', function() {
        _packcodeperseries = $(this).val();
        _packingcode = _packcodeperseries+'-'+_month+'-'+_carton+_stamp;
        $('#pack_code').val(_packingcode);
    });

    $('#btn_groupby').on('click', function() {
        $('#GroupByModal').modal('show');
    });

    $('#btn_search').on('click', function() {
        $('#SearchModal').modal('show');
        $('#search_pono').val("");
        $('#search_from').val("");
        $('#search_to').val("");
        $('#er_search_from').html(""); 
        $('#er_search_to').html(""); 
    });

    $('#btn_runcard').on('click', function() {
        $('#RuncardModal').modal('show');
        $('#rc_status').val("ADD");
        $('#rc_packcode').val($('#po_no').val());
        $('#rc_no').val("");
        $('#rc_qty').val("");
        $('#rc_id').val("");
        $('#rc_remarks').val("");
        $('#er_rc_no').html("");
        $('#er_rc_qty').html("");
        $('#er_rc_remarks').html("");
        var pono = $('#po_no').val();
        var cartonno = $('#carton_no').val();
        var myData = {pono:pono,cartonno:cartonno};
        $('#tblforruncard').html("");
        $.ajax({
            url:"{{ url('/display_runcard') }}",
            method:'get',
            data:myData,
        }).done(function(data, textStatus, jqXHR){
            console.log(data);
            $.each(data,function(i,val){
                var tblrow = '<tr>'+
                                '<td style="width: 2%">'+
                                    '<input type="checkbox" class="form-control input-sm rccheckboxes" value="'+val.id+'" name="rccheckitem" id="rccheckitem"></input> '+
                                '</td> '+                       
                                '<td style="width: 5%"> '+          
                                    '<button type="button" name="rcedit-task" value="'+val.id+'|'+val.pono+ '|' +val.carton_no+'|'+val.runcard_no+'|'+val.runcard_qty+'|'+val.runcard_remarks+'" class="btn btn-sm btn-primary rcedit-task">'+
                                           '<i class="fa fa-edit"></i> '+
                                    '</button>'+
                                '</td>'+
                                '<td>'+val.pono+'</td>'+
                                '<td>'+val.runcard_no+'</td>'+
                                '<td>'+val.runcard_qty+'</td>'+
                                '<td>'+val.runcard_remarks+'</td>'+
                            '</tr>';
                $('#tblforruncard').append(tblrow);
                $('#rc_no').val("");
                $('#rc_qty').val("");
                $('#rc_remarks').val("");
                $('#rc_status').val("ADD");

                $('.rcedit-task').on('click', function(e) {
                    $('#rc_status').val("EDIT");
                    var edittext = $(this).val().split('|');
                    var editid = edittext[0];
                    $.ajax({
                        url:"{{ url('/packing_runcard_edit') }}",
                        method:'get',
                        data:{
                            id:editid
                        }
                    }).done(function(data, textStatus, jqXHR){
                        $('#rc_id').val(data[0]['id']);
                        $('#rc_packcode').val(data[0]['packing_code']);
                        $('#rc_no').val(data[0]['runcard_no']);
                        $('#rc_qty').val(data[0]['runcard_qty']);
                        $('#rc_remarks').val(data[0]['runcard_remarks']);
                    }).fail(function(jqXHR,textStatus,errorThrown){
                        console.log(errorThrown+'|'+textStatus)
                    });
                });
            });  
        }).fail(function(jqXHR, errorThrown, textStatus){
            console.log(errorThrown+'|'+textStatus);
        })
    });

    $('.checkAllitems').change(function(){
        if($('.checkAllitems').is(':checked')){
            $('.deleteAll-task').removeClass("disabled");
            $('#add').addClass("disabled");
            $('input[name=checkitem]').parents('span').addClass("checked");
            $('input[name=checkitem]').prop('checked',this.checked);
        }else{
            $('input[name=checkitem]').parents('span').removeClass("checked");
            $('input[name=checkitem]').prop('checked',this.checked);
            $('.deleteAll-task').addClass("disabled");
            $('#add').removeClass("disabled");
        }       
    });

    $('.checkboxes').change(function(){
        $('input[name=checkAllitem]').parents('span').removeClass("checked");
        $('input[name=checkAllitem]').prop('checked',false);
        if($('.checkboxes').is(':checked')){
            $('.deleteAll-task').removeClass("disabled");
            $('#add').addClass("disabled");
        }else{
            $('.deleteAll-task').addClass("disabled");
            $('#add').removeClass("disabled");
        }
    
    });

    $('.rccheckAllitems').change(function(){
        if($('.rccheckAllitems').is(':checked')){
            $('.deleteAll-task').removeClass("disabled");
            $('#add').addClass("disabled");
            $('input[name=rccheckitem]').parents('span').addClass("checked");
            $('input[name=rccheckitem]').prop('checked',this.checked);
        }else{
            $('input[name=rccheckitem]').parents('span').removeClass("checked");
            $('input[name=rccheckitem]').prop('checked',this.checked);
            $('.deleteAll-task').addClass("disabled");
            $('#add').removeClass("disabled");
        }       
    });

    $('.rccheckboxes').change(function(){
        $('input[name=rccheckAllitem]').parents('span').removeClass("checked");
        $('input[name=rccheckAllitem]').prop('checked',false);
        if($('.rccheckboxes').is(':checked')){
            $('.deleteAll-task').removeClass("disabled");
            $('#add').addClass("disabled");
        }else{
            $('.deleteAll-task').addClass("disabled");
            $('#add').removeClass("disabled");
        }
    
    });
    
    $('#tblforpacking').on('click','.edit-task', function(e) {
        $('#AddNewModal').modal('show');
        $('#status').val("EDIT");
        var edittext = $(this).val().split('|');
        var editid = edittext[0];
        $('#id').val(editid);
        $('#po_no').val(edittext[16]);
        $('#insp_date').val(edittext[1]);
        $('#ship_date').val(edittext[2]);
        $('#series_name').val(edittext[3]);
        $('#inspector').val(edittext[4]);
        $('#packing_type').val(edittext[5]);
        $('#unit_condition').val(edittext[6]);
        $('#packing_operator').val(edittext[7]);
        $('#remarks').val(edittext[8]);
        $('#pack_code_per_series').val(edittext[9]);
        $('#carton_no').val(edittext[10]);
        $('#pack_code').val(edittext[11]);
        $('#judgement').val(edittext[12]);
        $('#total_qty').val(edittext[13]);
        $('#no_of_defects').val(edittext[14]);
        $('#dbcon').val(edittext[15]);

        if (edittext[4] !== "{{Auth::user()->user_id}}" || edittext[4] !== "{{Auth::user()->firstname}}") {
            // $('#btn_save').prop('disabled',true);
            // $('#btn_runcard').prop('readonly',true);
            // $('#btn_packmod').prop('disabled',true);
        }
    });

    $('.rcedit-task').on('click', function(e) {
        $('#rc_status').val("EDIT");
        var edittext = $(this).val().split('|');
        var editid = edittext[0];
        $.ajax({
            url:"{{ url('/rcpackingEdit') }}",
            method:'get',
            data:{
                id:editid
            }
        }).done(function(data, textStatus, jqXHR){
            $('#rc_id').val(data[0]['id']);
            $('#rc_packcode').val(data[0]['packing_code']);
            $('#rc_no').val(data[0]['runcard_no']);
            $('#rc_qty').val(data[0]['runcard_qty']);
            $('#rc_remarks').val(data[0]['runcard_remarks']);
        }).fail(function(jqXHR,textStatus,errorThrown){
            console.log(errorThrown+'|'+textStatus)
        });
    });

    $('#po_no').keyup(function(){
        $('#er_po_no').html(""); 
    });
    $('#insp_date').click(function(){
        $('#er_insp_date').html(""); 
    });
    $('#ship_date').click(function(){
        $('#er_ship_date').html(""); 
    });
    $('#series_name').keyup(function(){
        $('#er_series_name').html(""); 
    });
    $('#inspector').click(function(){
        $('#er_inspector').html(""); 
    });
    $('#packing_type').click(function(){
        $('#er_packing_type').html(""); 
    });
    $('#unit_condition').click(function(){
        $('#er_unit_condition').html(""); 
    });
    $('#packing_operator').click(function(){
        $('#er_packing_operator').html(""); 
    });
    $('#pack_code_per_series').click(function(){
        $('#er_pack_code_per_series').html(""); 
    });
    $('#carton_no').keyup(function(){
        $('#er_carton_no').html(""); 
    });
    $('#pack_code').click(function(){
        $('#er_pack_code').html(""); 
    });
    $('#judgement').click(function(){
        $('#er_judgement').html(""); 
    });
    $('#total_qty').keyup(function(){
        $('#er_total_qty').html(""); 
    });
    $('#no_of_defects').click(function(){
        $('#er_no_of_defects').html(""); 
    });
    $('#search_from').click(function(){
        $('#er_search_from').html(""); 
    });
    $('#search_to').click(function(){
        $('#er_search_to').html(""); 
    });
    $('#rc_no').keyup(function(){
        $('#er_rc_no').html(""); 
    });
    $('#rc_qty').keyup(function(){
        $('#er_rc_qty').html(""); 
    });
   
    $('#po_no').on('change',function(){
        var pono = $('#po_no').val();
        $.ajax({
            url:"{{ url('/getpackingYPICSrecords') }}",
            method:'get',
            data:{
                pono:pono
            },
        }).done(function(data, textStatus, jqXHR){ 
            $('#series_name').val(data[0]['DEVNAME']);
            if(pono == ""){
                $('#series_name').val("");
            }
        }).fail(function(jqXHR, textStatus, errorThrown){
            console.log(errorThrown+'|'+textStatus);
        });

        $.ajax({
            url:"{{ url('/getTotalmod') }}",
            method:'get',
            data:{
                pono:pono
            },
        }).done(function(data, textStatus, jqXHR){ 
            console.log(data[0]['qty']);
            $('#no_of_defects').val(data[0]['qty']);
        }).fail(function(jqXHR, textStatus, errorThrown){
            console.log(errorThrown+'|'+textStatus);
        });
    });

    $('#carton_no').on('keyup', function() {
        _carton = $(this).val();
        _packingcode = _packcodeperseries+'-'+_month+'-'+_carton+_stamp;
        $('#pack_code').val(_packingcode);
    });

    $('#carton_no').focusout(function(){
        var pono = $('#po_no').val();
        var cartonno = $(this).val();

        $.ajax({
            url:"{{ url('/getTotalruncard') }}",
            method:'get',
            data:{
                pono:pono,
                cartonno:cartonno
            },
        }).done(function(data, textStatus, jqXHR){ 
            console.log(data[0]['qty']);
            $('#total_qty').val(data[0]['qty']);
        }).fail(function(jqXHR, textStatus, errorThrown){
            console.log(errorThrown+'|'+textStatus);
        });
    });
    
  
    $('#btn_pdf').on('click', function() {
        var searchpono = $('#search_pono').val();
        var datefrom = $('#search_from').val();
        var dateto = $('#search_to').val();
        var status = $('#hd_report_status').val();

        var tableData = {
            date_inspected: $('input[name^="hd_date_inspected[]"]').map(function(){return $(this).val();}).get(),
            shipment_date: $('input[name^="hd_shipment_date[]"]').map(function(){return $(this).val();}).get(),
            device_name: $('input[name^="hd_device_name[]"]').map(function(){return $(this).val();}).get(),
            po_num: $('input[name^="hd_po_num[]"]').map(function(){return $(this).val();}).get(),
            packing_operator: $('input[name^="hd_packing_operator[]"]').map(function(){return $(this).val();}).get(),
            inspector: $('input[name^="hd_inspector[]"]').map(function(){return $(this).val();}).get(),
            packing_type: $('input[name^="hd_packing_type[]"]').map(function(){return $(this).val();}).get(),
            unit_condition: $('input[name^="hd_unit_condition[]"]').map(function(){return $(this).val();}).get(),
            packing_code_series: $('input[name^="hd_packing_code_series[]"]').map(function(){return $(this).val();}).get(),
            carton_num: $('input[name^="hd_carton_num[]"]').map(function(){return $(this).val();}).get(),
            packing_code: $('input[name^="hd_packing_code[]"]').map(function(){return $(this).val();}).get(),
            total_qty: $('input[name^="hd_total_qty[]"]').map(function(){return $(this).val();}).get(),
            judgement: $('input[name^="hd_judgement[]"]').map(function(){return $(this).val();}).get(),
            remarks: $('input[name^="hd_remarks[]"]').map(function(){return $(this).val();}).get(),
            searchpono:searchpono,
            datefrom:datefrom,
            dateto:dateto,
            status:status,
        }
    
        var url = "{{ url('/packingprintreport?data=')  }}" + encodeURIComponent(JSON.stringify(tableData));
        window.location.href= url;
    });

    $('#btn_excel').on('click', function(){
        var searchpono = $('#search_pono').val();
        var datefrom = $('#search_from').val();
        var dateto = $('#search_to').val();
        var status = $('#hd_report_status').val();

        var tableData = {
            date_inspected: $('input[name^="hd_date_inspected[]"]').map(function(){return $(this).val();}).get(),
            shipment_date: $('input[name^="hd_shipment_date[]"]').map(function(){return $(this).val();}).get(),
            device_name: $('input[name^="hd_device_name[]"]').map(function(){return $(this).val();}).get(),
            po_num: $('input[name^="hd_po_num[]"]').map(function(){return $(this).val();}).get(),
            packing_operator: $('input[name^="hd_packing_operator[]"]').map(function(){return $(this).val();}).get(),
            inspector: $('input[name^="hd_inspector[]"]').map(function(){return $(this).val();}).get(),
            packing_type: $('input[name^="hd_packing_type[]"]').map(function(){return $(this).val();}).get(),
            unit_condition: $('input[name^="hd_unit_condition[]"]').map(function(){return $(this).val();}).get(),
            packing_code_series: $('input[name^="hd_packing_code_series[]"]').map(function(){return $(this).val();}).get(),
            carton_num: $('input[name^="hd_carton_num[]"]').map(function(){return $(this).val();}).get(),
            packing_code: $('input[name^="hd_packing_code[]"]').map(function(){return $(this).val();}).get(),
            total_qty: $('input[name^="hd_total_qty[]"]').map(function(){return $(this).val();}).get(),
            judgement: $('input[name^="hd_judgement[]"]').map(function(){return $(this).val();}).get(),
            remarks: $('input[name^="hd_remarks[]"]').map(function(){return $(this).val();}).get(),
            searchpono:searchpono,
            datefrom:datefrom,
            dateto:dateto,
            status:status,
        }

        var url = "{{ url('/packingprintreportexcel?data=')  }}" + encodeURIComponent(JSON.stringify(tableData));
        window.location.href= url;
    });

    $('#group1').on('change',function(){
        var g1 = $('select[name=group1]').val();
        var myData = {g1:g1};
        $('#group1content').html("");
        $('#tblforpacking').html("");
        $.post("{{ url('/packingselectgroupby1') }}",
        {
            _token:$('meta[name=csrf-token]').attr('content'),
            data:myData

        }).done(function(data, textStatus, jqXHR){
            console.log(data);
            /*$('#group1content').val(data);*/
            $.each(data,function(i,val){
                var sup = '';
                switch(g1) {
                    case "date_inspected":
                        var sup = '<option value="'+val.date_inspected+'">'+val.date_inspected+'</option>';
                        break;
                    case "shipment_date":
                        var sup = '<option value="'+val.shipment_date+'">'+val.shipment_date+'</option>';
                        break;
                    case "device_name":
                        var sup = '<option value="'+val.device_name+'">'+val.device_name+'</option>';
                        break;
                    case "po_num":
                        var sup = '<option value="'+val.po_num+'">'+val.po_num+'</option>';
                        break;
                    case "packing_operator":
                        var sup = '<option value="'+val.packing_operator+'">'+val.packing_operator+'</option>';
                        break;
                    case "inspector":
                        var sup = '<option value="'+val.inspector+'">'+val.inspector+'</option>';
                        break;
                    case "packing_type":
                        var sup = '<option value="'+val.packing_type+'">'+val.packing_type+'</option>';
                        break;
                    case "unit_condition":
                        var sup = '<option value="'+val.unit_condition+'">'+val.unit_condition+'</option>';
                        break;
                    case "packing_code_series":
                        var sup = '<option value="'+val.packing_code_series+'">'+val.packing_code_series+'</option>';
                        break;
                    case "carton_num":
                        var sup = '<option value="'+val.carton_num+'">'+val.carton_num+'</option>';
                        break;
                    case "packing_code":
                        var sup = '<option value="'+val.packing_code+'">'+val.packing_code+'</option>';
                        break;
                    case "total_qty":
                        var sup = '<option value="'+val.total_qty+'">'+val.total_qty+'</option>';
                        break;
                    case "judgement":
                        var sup = '<option value="'+val.judgement+'">'+val.judgement+'</option>';
                        break;
                    default:
                        var sup = '<option value=""></option>';
                }
                    
                //var option = '<option value="'+val.supplier'">'+val.supplier'</option>';
                var option = sup;
                $('#group1content').append(option);
            });
        
        }).fail(function(jqXHR, textStatus, errorThrown){
            console.log(errorThrown+'|'+textStatus);
        });
    });

    $('#group2').on('change',function(){
        var g2 = $('select[name=group2]').val();
        var myData = {g2:g2};
        $('#group2content').html("");
        $('#tblforpacking').html("");
        $.post("{{ url('/packingselectgroupby1') }}",
        {
            _token:$('meta[name=csrf-token]').attr('content'),
            data:myData

        }).done(function(data, textStatus, jqXHR){
            console.log(data);
            /*$('#group1content').val(data);*/
            $.each(data,function(i,val){
                var sup = '';
                switch(g2) {
                    case "date_inspected":
                        var sup = '<option value="'+val.date_inspected+'">'+val.date_inspected+'</option>';
                        break;
                    case "shipment_date":
                        var sup = '<option value="'+val.shipment_date+'">'+val.shipment_date+'</option>';
                        break;
                    case "device_name":
                        var sup = '<option value="'+val.device_name+'">'+val.device_name+'</option>';
                        break;
                    case "po_num":
                        var sup = '<option value="'+val.po_num+'">'+val.po_num+'</option>';
                        break;
                    case "packing_operator":
                        var sup = '<option value="'+val.packing_operator+'">'+val.packing_operator+'</option>';
                        break;
                    case "inspector":
                        var sup = '<option value="'+val.inspector+'">'+val.inspector+'</option>';
                        break;
                    case "packing_type":
                        var sup = '<option value="'+val.packing_type+'">'+val.packing_type+'</option>';
                        break;
                    case "unit_condition":
                        var sup = '<option value="'+val.unit_condition+'">'+val.unit_condition+'</option>';
                        break;
                    case "packing_code_series":
                        var sup = '<option value="'+val.packing_code_series+'">'+val.packing_code_series+'</option>';
                        break;
                    case "carton_num":
                        var sup = '<option value="'+val.carton_num+'">'+val.carton_num+'</option>';
                        break;
                    case "packing_code":
                        var sup = '<option value="'+val.packing_code+'">'+val.packing_code+'</option>';
                        break;
                    case "total_qty":
                        var sup = '<option value="'+val.total_qty+'">'+val.total_qty+'</option>';
                        break;
                    case "judgement":
                        var sup = '<option value="'+val.judgement+'">'+val.judgement+'</option>';
                        break;
                    default:
                        var sup = '<option value=""></option>';
                }
                    
                //var option = '<option value="'+val.supplier'">'+val.supplier'</option>';
                var option = sup;
                $('#group2content').append(option);
            });
        
        }).fail(function(jqXHR, textStatus, errorThrown){
            console.log(errorThrown+'|'+textStatus);
        });
    });

    $('#group3').on('change',function(){
        var g3 = $('select[name=group3]').val();
        var myData = {g3:g3};
        $('#group3content').html("");
        $('#tblforpacking').html("");
        $.post("{{ url('/packingselectgroupby1') }}",
        {
            _token:$('meta[name=csrf-token]').attr('content'),
            data:myData

        }).done(function(data, textStatus, jqXHR){
            console.log(data);
            /*$('#group1content').val(data);*/
            $.each(data,function(i,val){
                var sup = '';
                switch(g3) {
                    case "date_inspected":
                        var sup = '<option value="'+val.date_inspected+'">'+val.date_inspected+'</option>';
                        break;
                    case "shipment_date":
                        var sup = '<option value="'+val.shipment_date+'">'+val.shipment_date+'</option>';
                        break;
                    case "device_name":
                        var sup = '<option value="'+val.device_name+'">'+val.device_name+'</option>';
                        break;
                    case "po_num":
                        var sup = '<option value="'+val.po_num+'">'+val.po_num+'</option>';
                        break;
                    case "packing_operator":
                        var sup = '<option value="'+val.packing_operator+'">'+val.packing_operator+'</option>';
                        break;
                    case "inspector":
                        var sup = '<option value="'+val.inspector+'">'+val.inspector+'</option>';
                        break;
                    case "packing_type":
                        var sup = '<option value="'+val.packing_type+'">'+val.packing_type+'</option>';
                        break;
                    case "unit_condition":
                        var sup = '<option value="'+val.unit_condition+'">'+val.unit_condition+'</option>';
                        break;
                    case "packing_code_series":
                        var sup = '<option value="'+val.packing_code_series+'">'+val.packing_code_series+'</option>';
                        break;
                    case "carton_num":
                        var sup = '<option value="'+val.carton_num+'">'+val.carton_num+'</option>';
                        break;
                    case "packing_code":
                        var sup = '<option value="'+val.packing_code+'">'+val.packing_code+'</option>';
                        break;
                    case "total_qty":
                        var sup = '<option value="'+val.total_qty+'">'+val.total_qty+'</option>';
                        break;
                    case "judgement":
                        var sup = '<option value="'+val.judgement+'">'+val.judgement+'</option>';
                        break;
                    default:
                        var sup = '<option value=""></option>';
                }
                    
                //var option = '<option value="'+val.supplier'">'+val.supplier'</option>';
                var option = sup;
                $('#group3content').append(option);
            });
        
        }).fail(function(jqXHR, textStatus, errorThrown){
            console.log(errorThrown+'|'+textStatus);
        });
    });

    $('#packmod_close').click(function(){
        $('#packmod_Modal').modal('hide');
        $('#AddNewModal').modal('show');
        totalmod();
    });

    $('#btn_rc_close').click(function(){
        totalquantity();
    });

    $('.edit-taskinspection').on('click',function(){
        var field = $(this).val().split('|');
        var id = field[0];
        $.ajax({
            url:"{{ url('/packmod_edit') }}",
            method:'get',
            data:{
                id:id
            },
        }).done(function(data, textStatus, jqXHR){
            console.log(data);
            $('#mod_inspection').val(data[0]['mod']);
            $('#qty_inspection').val(data[0]['qty']);
            $('#id_inspection').val(id);
            $('#status_inspection').val("EDIT");
        }).fail(function(jqXHR, textStatus, errorThrown){
            console.log(errorThrown+'|'+textStatus);
        });
    });  

    $('input[name=inspector]').val("{{Auth::user()->firstname}}");
});//-----------------------------------------------------END OF SCRIPT-----------------------------------------


function totalquantity() {
    var pono = $('#po_no').val();
    var cartonno = $('#carton_no').val();
    $.ajax({
        url:"{{ url('/getlot') }}",
        method:'get',
        data:{
            pono:pono,
            cartonno:cartonno
        },
    }).done(function(data, textStatus, jqXHR){     
        console.log(data);
        $('#total_qty').val(data[0]['qty']);
    }).fail(function(jqXHR, textStatus, errorThrown){
        console.log(errorThrown+'|'+textStatus);
    });
}

function totalmod() {
    var pono = $('#po_no').val();
    $.ajax({
        url:"{{ url('/getmod') }}",
        method:'get',
        data:{
            pono:pono
        },
    }).done(function(data, textStatus, jqXHR){     
        console.log(data);
        $('#no_of_defects').val(data[0]['qty']);
    }).fail(function(jqXHR, textStatus, errorThrown){
        console.log(errorThrown+'|'+textStatus);
    });
}

function packingsave(){
    var pono = $('#po_no').val();
    var inspdate = $('#insp_date').val();
    var shipdate = $('#ship_date').val();
    var seriesname = $('#series_name').val();
    var inspector = $('#inspector').val();
    var packingtype = $('#packing_type').val();
    var unitcondition = $('#unit_condition').val();
    var packingoperator = $('#packing_operator').val();
    var remarks = $('#remarks').val();
    var packcodeperseries = $('#pack_code_per_series').val();
    var cartonno = $('#carton_no').val();
    var packcode = $('#pack_code').val();
    var judgement = $('#judgement').val();
    var totalqty = $('#total_qty').val();
    var mod = $('#no_of_defects').val();
    var dbcon = "{{Auth::user()->productline}}";
    var status = $('#status').val();
    var id = $('#id').val();
    var myData = {pono:pono,inspdate:inspdate,shipdate:shipdate,seriesname:seriesname,inspector:inspector,packingtype:packingtype,unitcondition:unitcondition,packingoperator:packingoperator,remarks:remarks,packcodeperseries:packcodeperseries,cartonno:cartonno,packcode:packcode,judgement:judgement,totalqty:totalqty,mod:mod,dbcon:dbcon,status:status,id:id};

    if(pono == ""){     
        $('#er_po_no').html("PO Number field is empty"); 
        $('#er_po_no').css('color', 'red');       
        return false;  
    }
    if(inspdate == ""){     
        $('#er_insp_date').html("Inspection Date field is empty"); 
        $('#er_insp_date').css('color', 'red');       
        return false;  
    }
    if(shipdate == ""){     
        $('#er_ship_date').html("Ship Date field is empty"); 
        $('#er_ship_date').css('color', 'red');       
        return false;  
    }
    if(seriesname == ""){     
        $('#er_series_name').html("Series Name field is empty"); 
        $('#er_series_name').css('color', 'red');       
        return false;  
    }
    if(inspector == ""){     
        $('#er_inspector').html("Inspector field is empty"); 
        $('#er_inspector').css('color', 'red');       
        return false;  
    }
    if(packingtype == ""){     
        $('#er_packing_type').html("Packing Type field is empty"); 
        $('#er_packing_type').css('color', 'red');       
        return false;  
    }
    if(unitcondition == ""){     
        $('#er_unit_condition').html("Unit Condition field is empty"); 
        $('#er_unit_condition').css('color', 'red');       
        return false;  
    }
    if(packingoperator == ""){     
        $('#er_packing_operator').html("Packing Operator Number field is empty"); 
        $('#er_packing_operator').css('color', 'red');       
        return false;  
    }
    if(packcodeperseries == ""){     
        $('#er_pack_code_per_series').html("Pack Code Per Series field is empty"); 
        $('#er_pack_code_per_series').css('color', 'red');       
        return false;  
    }
    if(cartonno == ""){     
        $('#er_carton_no').html("Carton Number field is empty"); 
        $('#er_carton_no').css('color', 'red');       
        return false;  
    }
    if(packcode == ""){     
        $('#er_pack_code').html("Pack Code field is empty"); 
        $('#er_pack_code').css('color', 'red');       
        return false;  
    }
    if(judgement == ""){     
        $('#er_judgement').html("Judgement field is empty"); 
        $('#er_judgement').css('color', 'red');       
        return false;  
    }
    if(totalqty == ""){     
        $('#er_total_qty').html("Total Quantity field is empty"); 
        $('#er_total_qty').css('color', 'red');       
        return false;  
    }
    if(mod == ""){     
        $('#er_no_of_defects').html("Mode of Defects field is empty"); 
        $('#er_no_of_defects').css('color', 'red');       
        return false;  
    }

    $.post("{{ url('/packingSave') }}",
    {
        _token:$('meta[name=csrf-token]').attr('content'),
        data:myData
    }).done(function(data, textStatus, jqXHR){
        console.log(data);
        window.location.href = "{{ url('/packinginspection') }}";
    }).fail(function(jqXHR, textStatus, errorThrown){
        console.log(errorThrown+'|'+textStatus);
    });
}

function rc_save(){
    var pono = $('#po_no').val();
    var carton_no = $('#carton_no').val();
    var rcid = $('#rc_id').val();
    var rcno = $('#rc_no').val();
    var rcqty = $('#rc_qty').val();
    var rcremarks = $('#rc_remarks').val();
    var rcstatus = $('#rc_status').val();
    if(rcno == ""){
        $('#er_rc_no').html("Runcard Number field is empty");
        $('#er_rc_no').css('color','red');
        return false;
    }
    if(rcqty == ""){
        $('#er_rc_qty').html("Quantity field is empty");
        $('#er_rc_qty').css('color','red');
        return false;
    }
 
    $('#tblforruncard').html("");
    var myData = {rcid:rcid,rcno:rcno,rcqty:rcqty,rcremarks:rcremarks,pono:pono,carton_no:carton_no,rcstatus:rcstatus};
    $.post("{{ url('/packing_runcard_Save') }}",
    {
        _token:$('meta[name=csrf-token]').attr('content'),
        data:myData
    }).done(function(data, textStatus, jqXHR){
        console.log(data);
        $.each(data,function(i,val){
            var tblrow = '<tr>'+
                            '<td style="width: 2%">'+
                                '<input type="checkbox" class="form-control input-sm rccheckboxes" value="'+val.id+'" name="rccheckitem" id="rccheckitem"></input> '+
                            '</td> '+                       
                            '<td style="width: 5%"> '+          
                                '<button type="button" name="rcedit-task" value="'+val.id+'|'+val.pono+ '|' +val.carton_no+'|'+val.runcard_no+'|'+val.runcard_qty+'|'+val.runcard_remarks+'" class="btn btn-sm btn-primary rcedit-task">'+
                                       '<i class="fa fa-edit"></i> '+
                                '</button>'+
                            '</td>'+
                            '<td>'+val.pono+'</td>'+
                            '<td>'+val.runcard_no+'</td>'+
                            '<td>'+val.runcard_qty+'</td>'+
                            '<td>'+val.runcard_remarks+'</td>'+
                        '</tr>';
            $('#tblforruncard').append(tblrow);
            $('#rc_no').val("");
            $('#rc_qty').val("");
            $('#rc_remarks').val("");
            $('#rc_status').val("ADD");

            $('.rcedit-task').on('click', function(e) {
                $('#rc_status').val("EDIT");
                var edittext = $(this).val().split('|');
                var editid = edittext[0];
                $.ajax({
                    url:"{{ url('/packing_runcard_edit') }}",
                    method:'get',
                    data:{
                        id:editid
                    }
                }).done(function(data, textStatus, jqXHR){
                    $('#rc_id').val(data[0]['id']);
                    $('#rc_packcode').val(data[0]['packing_code']);
                    $('#rc_no').val(data[0]['runcard_no']);
                    $('#rc_qty').val(data[0]['runcard_qty']);
                    $('#rc_remarks').val(data[0]['runcard_remarks']);
                }).fail(function(jqXHR,textStatus,errorThrown){
                    console.log(errorThrown+'|'+textStatus)
                });
            });
        });  
    }).fail(function(jqXHR, textStatus, errorThrown){
        console.log(errorThrown+'|'+textStatus);
    });
}

function packingDelete(){
    var tray = [];
    $('.checkboxes:checked').each(function(){
        tray.push($(this).val());
    });

    var traycount = tray.length;
    var myData = {tray:tray,traycount:traycount};
    $.ajax({
            url:"{{ url('/packingDelete') }}",
            method:'get',
            data:myData
                 
    }).done(function(data, textStatus, jqXHR){
        window.location.href="{{ url('/packinginspection') }}";
       /* alert(data);*/
    }).fail(function(jqXHR, textStatus,errorThrown){
        console.log(errorThrown+'|'+textStatus);
    });
}

function rcpackingDelete(){
    var tray = [];
    $('.rccheckboxes:checked').each(function(){
        tray.push($(this).val());
    });
    var pono = $('#po_no').val();
    var cartonno = $('#carton_no').val();
    var traycount = tray.length;
    var myData = {tray:tray,traycount:traycount,pono:pono,cartonno:cartonno};
    $('#tblforruncard').html("");
    $.ajax({
            url:"{{ url('/rcpackingDelete') }}",
            method:'get',
            data:myData            
    }).done(function(data, textStatus, jqXHR){
        console.log(data);
        $.each(data,function(i,val){
                var tblrow = '<tr>'+
                                '<td style="width: 2%">'+
                                    '<input type="checkbox" class="form-control input-sm rccheckboxes" value="'+val.id+'" name="rccheckitem" id="rccheckitem"></input> '+
                                '</td> '+                       
                                '<td style="width: 5%"> '+          
                                    '<button type="button" name="rcedit-task" value="'+val.id+'|'+val.pono+ '|' +val.carton_no+'|'+val.runcard_no+'|'+val.runcard_qty+'|'+val.runcard_remarks+'" class="btn btn-sm btn-primary rcedit-task">'+
                                           '<i class="fa fa-edit"></i> '+
                                    '</button>'+
                                '</td>'+
                                '<td>'+val.pono+'</td>'+
                                '<td>'+val.runcard_no+'</td>'+
                                '<td>'+val.runcard_qty+'</td>'+
                                '<td>'+val.runcard_remarks+'</td>'+
                            '</tr>';
                $('#tblforruncard').append(tblrow);
                $('#rc_no').val("");
                $('#rc_qty').val("");
                $('#rc_remarks').val("");
                $('#rc_status').val("ADD");

                $('.rcedit-task').on('click', function(e) {
                    $('#rc_status').val("EDIT");
                    var edittext = $(this).val().split('|');
                    var editid = edittext[0];
                    $.ajax({
                        url:"{{ url('/packing_runcard_edit') }}",
                        method:'get',
                        data:{
                            id:editid
                        }
                    }).done(function(data, textStatus, jqXHR){
                        $('#rc_id').val(data[0]['id']);
                        $('#rc_packcode').val(data[0]['packing_code']);
                        $('#rc_no').val(data[0]['runcard_no']);
                        $('#rc_qty').val(data[0]['runcard_qty']);
                        $('#rc_remarks').val(data[0]['runcard_remarks']);
                    }).fail(function(jqXHR,textStatus,errorThrown){
                        console.log(errorThrown+'|'+textStatus)
                    });
                });
            });  
    }).fail(function(jqXHR, textStatus, errorThrown){
        console.log(errorThrown+'|'+textStatus);
    });
}

function groupby(){
    var datefrom= $('#groupby_datefrom').val();
    var dateto = $('#groupby_dateto').val();
    var g1 = $('#group1').val();
    var g2 = $('#group2').val();
    var g3 = $('#group3').val();
    var g1content = $('#group1content').val();
    var g2content = $('#group2content').val();
    var g3content = $('#group3content').val();

    var urls = "{{ url('/packgetrows') }}"+"?_token="+"{{Session::token()}}"+"&&mode=group&&datefrom="+datefrom+
                "&&dateto="+dateto+"&&g1="+g1+"&&g2="+g2+"&&g3="+g3+"&&g1content="+g1content+"&&g2content="+g2content+
                "&&g3content="+g3content;

    //alert(urls);
    loadPackdata(urls);
}

function searchby(){
    var tempdatefrom = $('#search_from').val().split('/');
    var tempdateto = $('#search_to').val().split('/');
    var pono = $('#search_pono').val();
    var datefrom = tempdatefrom[0]+'/'+tempdatefrom[1]+'/'+tempdatefrom[2];
    var dateto = tempdateto[0]+'/'+tempdateto[1]+'/'+tempdateto[2];
    var urls = "{{ url('/packgetrows') }}"+"?_token="+"{{Session::token()}}"+"&mode=search&&datefrom="+datefrom+
                        "&&dateto="+dateto+"&&pono="+pono;

    loadPackdata(urls);
}

function lot_accepted() {
    $('#judgement').change(function(){
        if($(this).val() == 'Reject') {
            $('#no_of_defects').show();
            $('#btn_no_of_defectss').show();
            $('#no_defects_label').show();
            $('#mode_defects_label').show();
            $('#btn_packmod').show();
            totalmod()

        } 
        if($(this).val() == 'Accept') {
            $('#no_of_defects').show();
            $('#btn_no_of_defectss').hide();
            $('#no_defects_label').show();
            $('#mode_defects_label').hide();
            $('#btn_packmod').hide();
            $('#no_of_defects').val("NDF");
        }
    });
}

function display_packmod(){
    var pono = $('input[name=po_no]').val();
    $('#packmod_Modal').modal('show');
    $('#AddNewModal').modal('hide');
    $('#status_inspection').val("ADD")
    $('#tblformodinspection').html("");
    $.ajax({
        url:"{{ url('/displaypackmod') }}",
        method:'get',
        data:{
            pono:pono,
        },
    }).done(function(data, textStatus,jqXHR){
        console.log(data);
        $.each(data,function(i,val){
            var tblrow = '<tr>'+                   
                            '<td style="width: 2%">'+
                                '<input type="checkbox" class="form-control input-sm checkboxesinspection" value="'+val.id+'" name="checkiteminspection" id="checkiteminspection"></input> '+
                            '</td>'+                        
                            '<td style="width: 5%">'+           
                                '<button type="button" name="edit-taskinspection" class="btn btn-sm btn-primary edit-taskinspection" value="'+val.id+ '|' +val.mod+ '|' +val.qty+'">'+
                                       '<i class="fa fa-edit"></i> '+
                                '</button>'+
                            '</td>'+
                            '<td>'+val.id+'</td>'+
                            '<td>'+val.mod+'</td>'+
                            '<td>'+val.qty+'</td>'+
                        '</tr>';
            $('#tblformodinspection').append(tblrow);
            $('#mod_inspection').val("");
            $('#qty_inspection').val("");
            $('#id_inspection').val("");
            $('#status_inspection').val("ADD");
            $('.edit-taskinspection').on('click',function(){
            var field = $(this).val().split('|');
                var id = field[0];
                $.ajax({
                    url:"{{ url('/packmod_edit') }}",
                    method:'get',
                    data:{
                        id:id
                    },
                }).done(function(data, textStatus, jqXHR){
                    console.log(data);
                    $('#mod_inspection').val(data[0]['mod']);
                    $('#qty_inspection').val(data[0]['qty']);
                    $('#id_inspection').val(id);
                    $('#status_inspection').val("EDIT");
                }).fail(function(jqXHR, textStatus, errorThrown){
                    console.log(errorThrown+'|'+textStatus);
                });
            });     
        });
    }).fail(function(jqXHR, textStatus, errorThrown){
        console.log(errorThrown+'|'+textStatus);
    });
}

function packmod_save(){
    var mod = $('select[name=mod_inspection]').val();
    var qty = $('#qty_inspection').val();
    var status = $('#status_inspection').val();
    var id = $('#id_inspection').val();
    var pono = $('input[name=po_no]').val();
    if(mod == ""){
        $('#er_mod').html("Mode of Defect field is empty");
        $('#er_mod').css('color','red');
        return false;
    }
    if(qty == ""){ 
        $('#er_qty').html("Quantity field is empty");
        $('#er_qty').css('color','red');
        return false;
    }
    $('#tblformodinspection').html("");
    $.post("{{ url('/packmod_save')}}",
    {
        _token:$('meta[name=csrf-token]').attr('content'),
        mod:mod,
        qty:qty,
        status:status,
        id:id,
        pono:pono
    }).done(function(data, textStatus, jqXHR){
        console.log(data);
        $.each(data,function(i,val){
            var tblrow = '<tr>'+                   
                            '<td style="width: 2%">'+
                                '<input type="checkbox" class="form-control input-sm checkboxesinspection" value="'+val.id+'" name="checkiteminspection" id="checkiteminspection"></input> '+
                            '</td>'+                        
                            '<td style="width: 5%">'+           
                                '<button type="button" name="edit-taskinspection" class="btn btn-sm btn-primary edit-taskinspection" value="'+val.id+ '|' +val.mod+ '|' +val.qty+'">'+
                                       '<i class="fa fa-edit"></i> '+
                                '</button>'+
                            '</td>'+
                            '<td>'+val.id+'</td>'+
                            '<td>'+val.mod+'</td>'+
                            '<td>'+val.qty+'</td>'+
                        '</tr>';
            $('#tblformodinspection').append(tblrow);
            $('#mod_inspection').val("");
            $('#qty_inspection').val("");
            $('#id_inspection').val("");
            $('#status_inspection').val("ADD");
            $('.edit-taskinspection').on('click',function(){
            var field = $(this).val().split('|');
                var id = field[0];
                $.ajax({
                    url:"{{ url('/packmod_edit') }}",
                    method:'get',
                    data:{
                        id:id
                    },
                }).done(function(data, textStatus, jqXHR){
                    console.log(data);
                    $('#mod_inspection').val(data[0]['mod']);
                    $('#qty_inspection').val(data[0]['qty']);
                    $('#id_inspection').val(id);
                    $('#status_inspection').val("EDIT");
                }).fail(function(jqXHR, textStatus, errorThrown){
                    console.log(errorThrown+'|'+textStatus);
                });
            });     
        });
        lot_accepted();
    }).fail(function(jqXHR, textStatus, errorThrown){
        console.log(errorThrown+'|'+textStatus);
    });
}

function deleteAllcheckedinspection(){
    var pono = $('input[name=po_no]').val();
    var tray = [];
    $('.checkboxesinspection:checked').each(function(){
        tray.push($(this).val());
    });
    var traycount = tray.length;
    var myData = {tray:tray,traycount:traycount,pono:pono};
    $('#tblformodinspection').html("");
    $.ajax({
            url:"{{ url('/packmod_delete') }}",
            method:'get',
            data:myData            
    }).done(function(data, textStatus, jqXHR){
        console.log(data);
        $.each(data,function(i,val){
            var tblrow = '<tr>'+                   
                            '<td style="width: 2%">'+
                                '<input type="checkbox" class="form-control input-sm checkboxesinspection" value="'+val.id+'" name="checkiteminspection" id="checkiteminspection"></input> '+
                            '</td>'+                        
                            '<td style="width: 5%">'+           
                                '<button type="button" name="edit-taskinspection" class="btn btn-sm btn-primary edit-taskinspection" value="'+val.id+ '|' +val.mod+ '|' +val.qty+'">'+
                                       '<i class="fa fa-edit"></i> '+
                                '</button>'+
                            '</td>'+
                            '<td>'+val.id+'</td>'+
                            '<td>'+val.mod+'</td>'+
                            '<td>'+val.qty+'</td>'+
                        '</tr>';
            $('#tblformodinspection').append(tblrow);   
            $('.edit-taskinspection').on('click',function(){
                var field = $(this).val().split('|');
                var id = field[0];
                $.ajax({
                    url:"{{ url('/packmod_edit') }}",
                    method:'get',
                    data:{
                        id:id
                    },
                }).done(function(data, textStatus, jqXHR){
                    console.log(data);
                    $('#mod_inspection').val(data[0]['mod']);
                    $('#qty_inspection').val(data[0]['qty']);
                    $('#id_inspection').val(id);
                    $('#status_inspection').val("EDIT");
                }).fail(function(jqXHR, textStatus, errorThrown){
                    console.log(errorThrown+'|'+textStatus);
                });
            });      
        });
        lot_accepted();
    }).fail(function(jqXHR, textStatus,errorThrown){
        console.log(errorThrown+'|'+textStatus);
    });
}

function loadPackdata(urls){
    getDatatable('packingdatatable',urls,dataColumn,[],0);
}

function PackgetDataTable(data) {
    var cnt = 0;
    $.each(data,function(i,val){
        cnt++;
        var report_status = $('#hd_report_status').val();
        var qty = '';
        if(val.qty == null){
            qty = 0;
        }else{
            qty = val.qty;
        }
        var tblrow = '<tr>'+
                        '<td width="2.25%">'+
                            '<input type="checkbox" class="input-sm checkboxes" value="'+val.id+'" name="checkitem" id="checkitem"></input> '+
                        '</td>'+                        
                        '<td width="4.25%">'+           
                            '<button type="button" name="edit-task" class="btn input-sm btn-primary edit-task" value="'+val.id+ '|' +val.date_inspected+ '|' +val.shipment_date+ '|' +val.device_name+ '|' +val.inspector+ '|' +val.packing_type+ '|' +val.unit_condition+ '|' +val.packing_operator+ '|' +val.remarks+ '|' +val.packing_code_series+ '|' +val.carton_num+ '|' +val.packing_code+ '|' +val.judgement+ '|' +val.total_qty+ '|' +val.mode_of_defect+ '|' +val.dbcon+ '|' +val.po_num+'">'+
                            
                                '   <i class="fa fa-edit"></i> '+
                            '</button>'+
                        '</td>'+
                        '<td width="6.25%">'+val.date_inspected+'<input type="hidden" id="hd_date_inspected" value="'+val.date_inspected+'" name="hd_date_inspected[]"></td>'+
                        '<td width="6.25%">'+val.shipment_date+'<input type="hidden" id="hd_shipment_date" value="'+val.shipment_date+'" name="hd_shipment_date[]"></td>'+
                        '<td width="13.25%">'+val.device_name+'<input type="hidden" id="hd_device_name" value="'+val.device_name+'" name="hd_device_name[]"></td>'+
                        '<td width="8.25%">'+val.po_num+'<input type="hidden" id="hd_po_num" value="'+val.po_num+'" name="hd_po_num[]"></td>'+
                        '<td width="5.25%">'+val.packing_operator+'<input type="hidden" id="hd_packing_operator" value="'+val.packing_operator+'" name="hd_packing_operator[]"></td>'+
                        '<td width="5.25%">'+val.inspector+'<input type="hidden" id="hd_inspector" value="'+val.inspector+'" name="hd_inspector[]"></td>'+
                        '<td width="6.25%">'+val.packing_type+'<input type="hidden" id="hd_packing_type" value="'+val.packing_type+'" name="hd_packing_type[]"></td>'+
                        '<td width="6.25%">'+val.unit_condition+'<input type="hidden" id="hd_unit_condition" value="'+val.unit_condition+'" name="hd_unit_condition[]"></td>'+
                        '<td width="6.25%">'+val.packing_code_series+'<input type="hidden" id="hd_packing_code_series" value="'+val.packing_code_series+'" name="hd_packing_code_series[]"></td>'+
                        '<td width="6.25%">'+val.carton_num+'<input type="hidden" id="hd_carton_num" value="'+val.carton_num+'" name="hd_carton_num[]"></td>'+
                        '<td width="6.25%">'+val.packing_code+'<input type="hidden" id="hd_packing_code" value="'+val.packing_code+'" name="hd_packing_code[]"></td>'+
                        '<td width="3.25%">'+val.total_qty+'<input type="hidden" id="hd_total_qty" value="'+val.total_qty+'" name="hd_total_qty[]"></td>'+
                        '<td width="6.25%">'+val.judgement+'<input type="hidden" id="hd_judgement" value="'+val.judgement+'" name="hd_judgement[]"></td>'+
                        '<td width="8.25%">'+val.remarks+'<input type="hidden" id="hd_remarks" value="'+val.remarks+'" name="hd_remarks[]"></td> '+       
                    '</tr>';    
        $('#tblforpacking').append(tblrow);
    });
}

function initData() {
    $.ajax({
        url: "{{url('/packinginspection-initdata')}}",
        type: 'GET',
        dataType: 'JSON',
        data: {_token: "{{Session::token()}}"},
    }).done(function(data,textStatus,jqXHR) {
        console.log(data);

        $.each(data.packingtypes, function(i, x) {
            $('#packing_type').append('<option value="'+x.description+'">'+x.description+'</option>');
        });

        $.each(data.unitconditions, function(i, x) {
            $('#unit_condition').append('<option value="'+x.description+'">'+x.description+'</option>');
        });

        $.each(data.packingcodeperseries, function(i, x) {
            $('#pack_code_per_series').append('<option value="'+x.description+'">'+x.description+'</option>');
        });

        $.each(data.packingoperators, function(i, x) {
            $('#packing_operator').append('<option value="'+x.description+'">'+x.description+'</option>');
        });

        $.each(data.mods, function(i, x) {
            $('#mod_inspection').append('<option value="'+x.description+'">'+x.description+'</option>');
        });
    }).fail(function(data,textStatus,jqXHR) {
        console.log("error");
    });
}

function getStampCode() {
    $.ajax({
        url: getStampCodeURL,
        type: 'GET',
        dataType: 'JSON',
        data: {_token: token},
    }).done(function(data, textStatus, xhr) {
        var x = data[1];
        _stamp = x.replace('OQC','');
        return _stamp;
        // $('#pack_code').val(_packingcode);
    }).fail(function(xhr, textStatus, errorThrown) {
        console.log("error");
    });
}