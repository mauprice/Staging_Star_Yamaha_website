$(document).ready(function(){cost()
    check_session();
    //get_register('login','','');
    header();
    get_records('last100');
    $('#status').val('new');

});

function cost(){
    "use strict";
    let cost = Number($('#purchase_price').val()) + Number($('#rego').val()) + Number($('#revs').val()) + Number($('#stamp_duty').val()) + Number($('#parts').val()) + Number($('#labour').val()) + Number($('#transport').val()) + Number($('#misc').val()) + Number($('#writeback').val()) + Number($('#gst_payable').val()) + Number($('#acc_purchased').val());
    return cost;
};
function sold(){
    "use strict";
    let sold = Number($('#bal_paid').val()) + Number($('#trade_price').val());
    return sold;
};


function check_session() {
    "use strict";
    var id = sessionStorage.getItem("id");
    var session = sessionStorage.getItem("session");
    var url = 'php/register.php';
    var http = new XMLHttpRequest();
    var params = 'type=session&session=' + session;
    http.open('POST', url, true);
    http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    http.onreadystatechange = function () { 
       if (http.readyState == 4 && http.status == 200) {
         // console.log(JSON.parse(http.responseText));
          var arr = JSON.parse(http.responseText);
          if (arr.success === true) {            
            $('#register_container').html(arr.register_container);  
            functions();
          } else if (arr.success === false) {
            // sessionStorage.removeItem('session');            
             get_register('login','','');
 
          }
 
       }
    }
    http.send(params);
 }

function get_register(type,username,password){
    "use strict";
    $('.loading').css("display","block");
    
    $.ajax({
        type: "POST",
        dataType: "json",
        data:{'type':type,'username':username,'password':password},  
        beforeSend: function(x) {
            if (x && x.overrideMimeType) {
                x.overrideMimeType("application/json;charset=UTF-8");
            }
        },
        url: "php/register.php",
        success: function(data) {
          if (data.success === true) {
                $('#loginModal').css("display","none");
                sessionStorage.setItem('session',data.session)
                $('#register_container').html(data.register_container);
                get_records('last100');

                functions();
                  
                

          }else{              
            $('#loginModal').css('display','block');
            $('.login-btn').on("click",function(){
                get_register('login',$('#username').val(),$('#password').val())
            });
          }
        }
    });

}

function functions(){
    "use strict";

    $( "#sold_date" ).datepicker({
        dateFormat: 'dd/mm/yy'
    });
    $( "#comm_paid" ).datepicker({
        dateFormat: 'dd/mm/yy'
    });
    $( "#purchase_date" ).datepicker({
        dateFormat: 'dd/mm/yy'
    });
    $( "#date_banked" ).datepicker({
        dateFormat: 'dd/mm/yy'
    });

    $('.poskey').on("click",function(){
          change_password();
    });

    $('.form-field').on("change",function(){  
          
        $('#sold_price').val(number_format (sold(), 2, '.', ''));
 
        let profit = Number(sold()) - Number(cost());
        $('#profit').val(number_format (profit, 2, '.', ''));

    });

    $('.exit').on("click", function(){
        $('.modal-body').html('<h5>Close the Register?.</h5>')
         //window.close();
         $('#myModal').modal('show');
         $('.yesno').on("click",function(){
            if(this.id === 'yes'){
              window.close();
            }else{
              $('#myModal').modal('hide');
            }
         });
      });
      
      $('.new').on("click", function(){
          const d = new Date();
         $('.modal-body').html('<h5>Adding a new record?.</h5>');
         $('#myModal').modal('show');
         $(".yesno").unbind().click(function() {
            if(this.id === 'yes'){
              $('.data-rows').removeClass('data-row-selected'); 
              $('#status').val('new');
              $('.form-field').each(function(i, obj) {
                 if($(this).attr('type') === 'text'){
                     console.log($(this).data('type'))
                     if($(this).data('type') == 'date'){
                        let mth = d.getMonth()+1;
                        if (String(mth).length === 1){
                            mth ='0'+mth;
                        }
                        let yr = d.getFullYear();
                        let day = d.getDate();
                        if (String(day).length === 1){
                          day ='0'+day;
                        }
  
                        $('#'+this.id).val(day+'/'+mth+'/'+yr);
                     }else{
                        $('#'+this.id).val('');
                     }
                 }else{
                     $('#'+this.id).val(0);
                 }
              });
              $('#myModal').modal('hide');
            }else{
              $('#myModal').modal('hide');
            }
         });
      });
  
      var test =0; 
         $('.save').unbind().click(function() {   
         $('.modal-body').html('<h5>Save this record?.</h5>');
         $('#myModal').modal('show');
         $(".yesno").unbind().click(function() {
            if(this.id === 'yes'){
              let form_data = {
                  'desc': $('#desc').val(),
                  'stock_no': $('#stock_no').val(),
                  'purchase_date': $('#purchase_date').val(),
                  'rego_no': $('#rego_no').val(),
                  'purchase_price': $('#purchase_price').val(),
                  'before_writeback': $('#before_writeback').val(),
                  'revs': $('#revs').val(),
                  'rego': $('#rego').val(),
                  'stamp_duty': $('#stamp_duty').val(),
                  'parts': $('#parts').val(),
                  'labour': $('#labour').val(),
                  'transport': $('#transport').val(),
                  'misc': $('#misc').val(),
                  'writeback': $('#writeback').val(),
                  'acc_purchased': $('#acc_purchased').val(),
                  'gst_paid': $('#gst_paid').val(),
                  'gst_collected': $('#gst_collected').val(),
                  'bal_banked': $('#bal_banked').val(),
                  'date_banked': $('#date_banked').val(),
                  'gst_payable': $('#gst_payable').val(),
                  'sold_date': $('#sold_date').val(),
                  'sold_to': $('#sold_to').val(),
                  'inv_no': $('#inv_no').val(),
                  'vin_no': $('#vin_no').val(),
                  'ymaf_unit_no': $('#ymaf_unit_no').val(),
                  'trade_details': $('#trade_details').val(),
                  'trade_price': $('#trade_price').val(),
                  'bal_paid': $('#bal_paid').val(),
                  'sold_price': $('#sold_price').val(),
                  'salesman': $('#salesman').val(),
                  'profit': $('#profit').val(),
                  'comm_paid': $('#comm_paid').val()
              };
              
              $('#myModal').modal('hide');
  
              if($('#status').val()=== 'new'){
                  save_record(0,form_data);
                  
              }else{
                  save_record($('#status').val(),form_data);
                 return false;
              }
  
            }else{
              $('#myModal').modal('hide');
            }
           
         });
         
      });
  
  
      $('.delete').on("click", function(){
          $('.modal-body').html('<h5>Delete this record?.</h5>');
          $('#myModal').modal('show');
          $(".yesno").unbind().click(function() {
              if(this.id === 'yes'){
                 delete_record($('#status').val());
                 
              }else{
                  $('#myModal').modal('hide');
              }
          });
      });
  
      $('.print').on("click", function(){
          let print_dates = '<div class="print-date-container">'+
                                '<div class="print-left">'+
                                   '<h5>Select Dates</h5>'+
                                   '<input type="text" class="form-field date-select" id="sdate" placeholder="Start Date">'+
                                   '<input type="text" class="form-field date-select" id="edate" placeholder="End Date">'+
                                '</div>'+
                                '<div class="print-right">'+
                                   '<button class="btn print-btn">Print</button>'+
                                '</div>'+
                            '</div>';
  
         // $('.modal-title').html('<h5>Print Report</h5>');
          $('#print-body').html(print_dates);
          $('#printModal').css('display','block');
  
          $("#sdate").datepicker({       
              changeMonth: true,
              changeYear: true,           
              buttonImageOnly: true,
              buttonImage: 'images/calendar.gif',
              dateFormat: 'dd/mm/yy',
              yearRange: '2000:+0'
          });
  
          $("#edate").datepicker({       
              changeMonth: true,
              changeYear: true,           
              buttonImageOnly: true,
              buttonImage: 'images/calendar.gif',
              dateFormat: 'dd/mm/yy',
              yearRange: '2000:+0'
          });
  
  
          $(".print-btn").on("click",function() {
              print_report($('#sdate').val(),$('#edate').val());
              $('#printModal').css('display','none');
          });
          $('.btn-close').on("click",function(){
              $('#printModal').css('display','none');
          })
      });
  
      $('.radio-field').on("click", function(){
          get_records(this.id);
      });

      $('.close-report').on("click",function(){
        $('.report-container').css('display','none');
      });

      newpage();

}

function change_password(){
    "use strict";
    
    $('#passwordModal').modal('show');
    $('.modal-message').html('');
    $('.password-btn').on("click",function(){
        if(($('#change_password').val() === $('#check_password').val()) && ($('#change_password').val().length >= 8)) {
           
            let new_pw = $('#change_password').val();


            $.ajax({
                type: "POST",
                dataType: "json",
                data:{'type':'changepw','new_pw':new_pw},  
                beforeSend: function(x) {
                    if (x && x.overrideMimeType) {
                        x.overrideMimeType("application/json;charset=UTF-8");
                    }
                },
                url: "php/register.php",
                success: function(data) {
                if (data.success === true) {
                    $('#passwordModal').modal('hide');
                }

                }
            });

        }else{
            let message = '';
            if($('#change_password').val().length < 8){
                message = 'Required 8 characters or more';
            }else{               
                message = 'Passwords do not match.';   
            }
            $('.modal-message').html(message);
        }
    });
}
function header(){
    "use strict";
  //  $('.header-bar').html('<h1 class="header-name">Northstar Yamaha Register</h1>');
}

function get_records(list_type){
    "use strict";
    let decimals = 0;
    let dec_point = '.';
    let thousands_sep = ',';
    $('.loading').css("display","block");
    $.ajax({
        type: "POST",
        dataType: "json",
        data:{'type':'list','list_type':list_type},  
        beforeSend: function(x) {
            if (x && x.overrideMimeType) {
                x.overrideMimeType("application/json;charset=UTF-8");
            }
        },
        url: "php/register.php",
        success: function(data) {
          if (data.success === true) {
            var results_table = '<table class="entries">'+
                                  '<thead>'+
                                   '<tr class="data-head">'+
                                     '<th style="width:100px">ID</th>'+
                                     '<th>DESCRIPTION</th>'+
                                     '<th style="width:100px">STOCK NO</th>'+
                                     '<th style="width:150px; text-align:right; padding-right:10px;">PURCHASE&nbsp;PRICE</th>'+
                                     '<th style="width:100px; text-align:right; padding-right:10px;">PROFIT</th>'+
                                     '<th style="width:100px">SOLD&nbsp;DATE</th>'+
                                     '<th style="width:100px">INVOICE #</th>'+
                                     '<th>SOLD TO</th>'+
                                     '<th style="width:100px; text-align:right; padding-right:10px;">SOLD&nbsp;PRICE</th>'+
                                     '<th style="width:100px">REGO No</th>'+
                                     '<th style="width:100px">VIN No</th>'+
                                    '</tr>'+
                                    '</thead><tbody class="tbody">';
            $.each(data.records, function(key,value) {
                  //  console.log(key);
                   // console.log(value);
                    results_table +=  '<tr class="data-rows" id="'+value.ID+'">'+
                                        '<td style="width:100px">'+value.ID+'</td>'+
                                        '<td>'+value.description+'</td>'+
                                        '<td style="width:100px">'+value.stock_no+'</td>'+
                                        '<td style="width:150px; text-align:right; padding-right:10px;">$'+number_format (value.purchase_price, decimals, dec_point, thousands_sep)+'</td>'+
                                        '<td style="width:100px; text-align:right; padding-right:10px;">$'+number_format (value.profit, decimals, dec_point, thousands_sep)+'</td>'+
                                        '<td style="width:100px">'+value.sold_date+'</td>'+
                                        '<td style="width:100px">'+value.inv_no+'</td>'+
                                        '<td>'+value.sold_to+'</td>'+
                                        '<td style="width:100px; text-align:right; padding-right:10px;">$'+number_format (value.sold_price, decimals, dec_point, thousands_sep)+'</td>'+
                                        '<td style="width:100px">'+value.rego_no+'</td>'+
                                        '<td style="width:100px">'+value.vin_no+'</td>'+                                        
                                    '</tr>';
            });
                    results_table +='</tbody></table>';
                    $('#records_data').html(results_table);
                    $('.loading').css("display","none");

                    $('.data-rows').on("click",function(){
                        get_record(this.id);
                        $('#status').val(this.id);
                        $('.data-rows').removeClass("data-row-selected");
                        $('#'+this.id).addClass("data-row-selected");
                    });
          }
        }
    });

}

function save_record(id,form_data){
    "use strict";

    //console.log(id);
   // console.log(form_data);
    //TODO://Create Ajax to register.php save 0 for new rec number for existing
    $.ajax({
        type: "POST",
        dataType: "json",
        data:{'type':'save','id':id,'form_data':form_data},  
        beforeSend: function(x) {
            if (x && x.overrideMimeType) {
                x.overrideMimeType("application/json;charset=UTF-8");
            }
        },
        url: "php/register.php",
        success: function(data) {
            if (data.success === true) {
                get_records('last100');
            }
        }
    });

}

function delete_record(id){
    "use strict";

    $.ajax({
        type: "POST",
        dataType: "json",
        data:{'type':'delete','id':id},  
        beforeSend: function(x) {
            if (x && x.overrideMimeType) {
                x.overrideMimeType("application/json;charset=UTF-8");
            }
        },
        url: "php/register.php",
        success: function(data) {
            if (data.success === true) {
                const d = new Date();
                get_records('last100');
                $('#myModal').modal('hide');
                $('.form-field').each(function(i, obj) {
                    if($(this).attr('type') === 'text'){
                        console.log($(this).data('type'))
                        if($(this).data('type') == 'date'){
                           let mth = d.getMonth();
                           if (String(mth).length === 1){
                               mth ='0'+mth;
                           }
                           let yr = d.getFullYear();
                           let day = d.getDate();
                           if (String(day).length === 1){
                             day ='0'+day;
                           }
     
                           $('#'+this.id).val(day+'/'+mth+'/'+yr);
                        }else{
                           $('#'+this.id).val('');
                        }
                    }else{
                        $('#'+this.id).val(0);
                    }
                 });
                 $('#status').val('new');
            }
        }
    });
}

function get_record(id){
"use strict";
    let decimals = 2;
    let dec_point = '.';
    let thousands_sep = '';
    $.ajax({
        type: "POST",
        dataType: "json",
        data:{'type':'record','id':id},  
        beforeSend: function(x) {
            if (x && x.overrideMimeType) {
                x.overrideMimeType("application/json;charset=UTF-8");
            }
        },
        url: "php/register.php",
        success: function(data) {
            if (data.success === true) {
                console.log(data.record)
                let profit =0;
                $('#desc').val(data.record[0].description);
                $('#stock_no').val(data.record[0].stock_no);
                $('#purchase_date').val(data.record[0].purchase_date);
                $('#rego_no').val(data.record[0].rego_no);
                $('#purchase_price').val(number_format (data.record[0].purchase_price, decimals, dec_point, thousands_sep));
                $('#before_writeback').val(data.record[0].before_writeback);
                $('#revs').val(number_format (data.record[0].revs, decimals, dec_point, thousands_sep));
                $('#rego').val(number_format (data.record[0].rego, decimals, dec_point, thousands_sep));
                $('#stamp_duty').val(number_format (data.record[0].stamp_duty, decimals, dec_point, thousands_sep));
                $('#parts').val(number_format (data.record[0].parts, decimals, dec_point, thousands_sep));
                $('#labour').val(number_format (data.record[0].labour, decimals, dec_point, thousands_sep));
                $('#transport').val(number_format (data.record[0].transport, decimals, dec_point, thousands_sep));
                $('#misc').val(number_format (data.record[0].misc, decimals, dec_point, thousands_sep));
                $('#writeback').val(number_format (data.record[0].writeback, decimals, dec_point, thousands_sep));
                $('#acc_purchased').val(number_format (data.record[0].acc_purchased, decimals, dec_point, thousands_sep));
                $('#gst_paid').val(number_format (data.record[0].purchase_price/11, decimals, dec_point, thousands_sep));
                //$('#gst_paid').val(number_format (data.record[0].gst_paid, decimals, dec_point, thousands_sep));
                $('#gst_collected').val(number_format (data.record[0].gst_collected, decimals, dec_point, thousands_sep));
                $('#bal_banked').val(number_format (data.record[0].bal_banked, decimals, dec_point, thousands_sep));
                $('#date_banked').val(data.record[0].date_banked);
                $('#gst_payable').val(number_format (Number(data.record[0].gst_collected) - Number(data.record[0].purchase_price/11, decimals) , decimals, dec_point, thousands_sep));
                //$('#gst_payable').val(number_format (data.record[0].gst_payable, decimals, dec_point, thousands_sep));
                $('#sold_date').val(data.record[0].sold_date);
                $('#sold_to').val(data.record[0].sold_to);
                $('#inv_no').val(data.record[0].inv_no);
                $('#vin_no').val(data.record[0].vin_no);
                $('#ymaf_unit_no').val(data.record[0].ymaf_unit_no);
                $('#trade_details').val(data.record[0].trade_details);
                $('#trade_price').val(number_format (data.record[0].trade_price, decimals, dec_point, thousands_sep));
                $('#bal_paid').val(number_format (data.record[0].bal_paid, decimals, dec_point, thousands_sep));
                $('#sold_price').val(number_format (data.record[0].sold_price, decimals, dec_point, thousands_sep));
                $('#salesman').val(data.record[0].salesman);
                //profit = Number(sold()) - Number(cost());
                //console.log(Number(sold()) - Number(cost()));
                $('#profit').val(number_format ( Number(sold()) - Number(cost()), 2, '.', ''));
               //$('#profit').val(number_format (data.record[0].profit, decimals, dec_point, thousands_sep));
                $('#comm_paid').val(data.record[0].comm_paid);
            }
        }
    });


}

function print_report(sdate,edate){
    "use strict";

       

    $.ajax({
        type: "POST",
        dataType: "json",
        data:{'type':'print','sdate':sdate,'edate':edate},  
        beforeSend: function(x) {
            if (x && x.overrideMimeType) {
                x.overrideMimeType("application/json;charset=UTF-8");
            }
        },
        url: "php/register.php",
        success: function(data) {
            if (data.success === true) {
              //  console.log(data.results)
                
                let total_purchase_price = 0;
                let total_before_writeback = 0;
                let total_parts = 0;
                let total_labour = 0;
                let total_transport = 0;
                let total_misc = 0;
                let total_writeback = 0;
                let total_gst_payable = 0;
                let total_acc_purchased = 0;
                let total_rego = 0;
                let total_ymaf_rebate = 0;
                let total_bal_paid = 0;
                let total_sold_price = 0;
                let total_profit = 0;

          let report = '<table>'+
                        '<tr class="row-head">'+
                            '<th>Stock No</th>'+
                            '<th>Description</th>'+
                            '<th class="numbers">Purchase Price</th>'+
                            '<th class="numbers">Before Write&nbsp;Back</th>'+
                            '<th class="numbers">Rego</th>'+
                            '<th class="numbers">W/shop Parts</th>'+
                            '<th class="numbers">W/Shop Labour</th>'+
                            '<th class="numbers">Transport</th>'+
                            '<th class="numbers">Misc</th>'+
                            '<th class="numbers">Warranty</th>'+
                            '<th class="numbers">Write Back</th>'+
                            '<th class="numbers">GST Payable</th>'+
                            '<th>Sold To</th>'+
                        '</tr>';
                $.each(data.results, function(i, v) {
                report +='<tr class="row-color">'+
                        '<td>'+v.stock_no+'</td>'+
                        '<td>'+v.description+'</td>'+
                        '<td class="numbers">$'+number_format (v.purchase_price, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (v.before_writeback, 0, '.', ',')+'</td>'+
                        '<td class="numbers">'+number_format (v.rego, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (v.parts, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (v.labour, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (v.transport, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (v.misc, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (v.acc_purchased, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (v.writeback, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (v.gst_payable, 0, '.', ',')+'</td>'+
                        '<td>'+v.sold_to+'</td>'+
                        '</tr>';

                        total_purchase_price += Number(v.purchase_price);
                        total_before_writeback += Number(v.before_writeback);
                        total_parts += Number(v.parts);
                        total_labour += Number(v.labour);
                        total_transport += Number(v.transpor);
                        total_misc += Number(v.misc);
                        total_writeback += Number(v.writeback);
                        total_gst_payable += Number(v.gst_payable);
                        total_acc_purchased += Number(v.acc_purchased);
                        total_rego += Number(v.rego);

                        total_ymaf_rebate += Number(v.ymaf_rebate);
                        total_bal_paid += Number(v.bal_paid);
                        total_sold_price += Number(v.sold_price);
                        total_profit += Number(v.profit);
                });

                
                report +='<tr class="row-footer">'+
                        '<td></td>'+
                        '<td></td>'+
                        '<td class="numbers">$'+number_format (total_purchase_price, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_before_writeback, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_rego, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_parts, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_labour, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_transport, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_misc, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_acc_purchased, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_writeback, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_gst_payable, 0, '.', ',')+'</td>'+
                        '<td></td>'+
                        '</tr>';
                report +='</table>';


                let report1 = '<div class="page-break" style="break-after:page"></div>'+
                              '<table>'+
                                '<tr class="row-head">'+
                                    '<th>Inv No</th>'+
                                    '<th>VIN No</th>'+
                                    '<th>YMF Unit No</th>'+
                                    '<th class="numbers">YMF Rebate</th>'+
                                    '<th>Trade Details</th>'+
                                    '<th class="numbers">Bal Paid</th>'+
                                    '<th class="numbers">Sold Price</th>'+
                                    '<th>Sales Person</th>'+
                                    '<th class="numbers">Profit</th>'+
                                    '<th>Comm Paid</th>'+
                                    '<th>Purchase Date</th>'+
                                    '<th>Sell Date</th>'+
                                    '<th>Sold To</th>'+
                                '</tr>';
                $.each(data.results, function(i, v) {
                    console.log(v.trade_details)

                    let ymaf_unit_no = '';
                    if(v.ymaf_unit_no == null){
                        ymaf_unit_no = '';
                    }else{
                        ymaf_unit_no = v.ymaf_unit_no;
                    }

                    let trade_details = '';
                    if(v.trade_details == null){
                        trade_details = '';
                    }else{
                        trade_details = v.trade_details;
                    }

                    let comm_paid = '';
                    if(v.comm_paid == null){
                        comm_paid = '';
                    }else{
                        comm_paid = v.comm_paid;
                    }
                report1 +='<tr class="row-color">'+
                        '<td>'+v.inv_no+'</td>'+
                        '<td>'+v.vin_no+'</td>'+
                        '<td>'+ymaf_unit_no+'</td>'+
                        '<td class="numbers">$'+number_format (v.ymaf_rebate, 0, '.', ',')+'</td>'+
                        '<td>'+trade_details+'</td>'+
                        '<td class="numbers">$'+number_format (v.bal_paid, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (v.sold_price, 0, '.', ',')+'</td>'+
                        '<td>'+v.salesman+'</td>'+
                        '<td class="numbers">$'+number_format (v.profit, 0, '.', ',')+'</td>'+
                        '<td>'+comm_paid+'</td>'+
                        '<td>'+v.purchase_date+'</td>'+
                        '<td>'+v.sold_date+'</td>'+
                        '<td>'+v.sold_to+'</td>'+
                        '</tr>';

                       // total_ymaf_rebate += Number(v.ymaf_rebate);
                       // total_bal_paid += Number(v.bal_paid);
                       // total_sold_price += Number(v.sold_price);
                       // total_profit += Number(v.profit);
                        
                });

                
                report1 +='<tr class="row-footer">'+
                        '<td></td>'+
                        '<td></td>'+
                        '<td></td>'+
                        '<td class="numbers">$'+number_format (total_ymaf_rebate, 0, '.', ',')+'</td>'+
                        '<td></td>'+
                        '<td class="numbers">$'+number_format (total_bal_paid, 0, '.', ',')+'</td>'+
                        '<td class="numbers">$'+number_format (total_sold_price, 0, '.', ',')+'</td>'+
                        '<td></td>'+
                        '<td class="numbers">$'+number_format (total_profit, 0, '.', ',')+'</td>'+
                        '<td></td>'+
                        '<td></td>'+
                        '<td></td>'+
                        '<td></td>'+
                        '</tr>';
                report1 +='</table>';

                let report_all = report + report1;

                $('#report_body').html(report_all);
                $('.report-container').css('display','block');

                $('.close-report').on("click",function(){
                    $('.report-container').css('display','none');
                });
                $('.print-report').on("click",function(){
                  
                    
                    window.print();
                                            
                });
            }
        }
    });
}
function newpage(){
    "use strict";
    const d = new Date();
    $('.form-field').each(function(i, obj) {
        if($(this).attr('type') === 'text'){
            console.log($(this).data('type'))
            if($(this).data('type') == 'date'){
               let mth = d.getMonth()+1;
               if (String(mth).length === 1){
                   mth ='0'+mth;
               }
               let yr = d.getFullYear();
               let day = d.getDate();
               if (String(day).length === 1){
                 day ='0'+day;
               }

               $('#'+this.id).val(day+'/'+mth+'/'+yr);
            }else{
               $('#'+this.id).val('');
            }
        }else{
            $('#'+this.id).val(0);
        }
     });
}

function number_format (number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}
