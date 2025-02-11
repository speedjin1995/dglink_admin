<?php
require_once 'php/languageSetting.php';

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $user = $_SESSION['userID'];
  $language = $_SESSION['language'];
  $_SESSION['page']='otherDashboard';
  $stmt = $db->prepare("SELECT * from users where id = ?");
	$stmt->bind_param('s', $user);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if(($row = $result->fetch_assoc()) !== null){
    $role = $row['role_code'];
  }

  $packages = $db->query("SELECT * FROM farms WHERE deleted = '0'");
  $customers = $db->query("SELECT * FROM customers WHERE deleted = '0'");
}
?>

<style>
  @media screen and (min-width: 676px) {
    .modal-dialog {
      max-width: 1800px; /* New width for default modal */
    }
  }
</style>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark"><?=$languageArray['dashboard_code'][$language] ?></h1>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="form-group col-4">
                <label>Date range: <span id="range"></span></label>

                <div class="input-group">
                  <button type="button" class="btn btn-default float-right" id="daterange-btn">
                    <i class="far fa-calendar-alt"></i> Date range picker
                    <i class="fas fa-caret-down"></i>
                  </button>
                </div>
              </div>

              <div class="col-4">
                <div class="form-group">
                  <label><?=$languageArray['farm_code'][$language] ?></label>
                  <select class="form-control select2" id="farmFilter" name="farmFilter" style="width: 100%;">
                    <option selected="selected">-</option>
                    <?php while($rowStatus=mysqli_fetch_assoc($packages)){ ?>
                      <option value="<?=$rowStatus['id'] ?>"><?=$rowStatus['name'] ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="col-4">
                <div class="form-group">
                  <label><?=$languageArray['customer_code'][$language] ?></label>
                  <select class="form-control select2" style="width: 100%;" id="customerFilter" name="customerFilter" style="display: none;">
                    <option selected="selected">-</option>
                    <?php while($rowCustomer=mysqli_fetch_assoc($customers)){ ?>
                      <option value="<?=$rowCustomer['customer_name'] ?>"><?=$rowCustomer['customer_name'] ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-9"></div>
              <div class="col-3">
                <button type="button" class="btn btn-block bg-gradient-warning btn-sm"  id="filterSearch">
                  <i class="fas fa-search"></i>
                  <?=$languageArray['search_code'][$language] ?>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--div class="row">
      <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box">
          <span class="info-box-icon bg-info">
            <i class="fas fa-shopping-cart"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Done</span>
            <span class="info-box-number" id="salesInfo">0</span>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box">
          <span class="info-box-icon bg-success">
            <i class="fas fa-shopping-basket"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">In Progress</span>
            <span class="info-box-number" id="purchaseInfo">0</span>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box">
          <span class="info-box-icon bg-warning">
            <i class="fas fa-warehouse" style="color: white;"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Total</span>
            <span class="info-box-number" id="localInfo">0</span>
          </div>
        </div>
      </div>
    </div-->

    <div class="row">
      <div class="col-lg-12">
        <div class="card card-primary">
          <div class="card-header">
            <div class="row">
              <div class="col-12"><?=$languageArray['dashboard_code'][$language] ?></div>
              <!--div class="col-2">
                <button type="button" class="btn btn-block bg-gradient-info btn-sm"  id="officeSearch">
                  <i class="fas fa-newspaper"></i>
                </button>
              </div>
              <div class="col-2">
                <button type="button" class="btn btn-block bg-gradient-warning btn-sm"  id="farmSearch">
                  <i class="fas fa-file"></i>
                </button>
              </div>
              <div class="col-2">
                <button type="button" class="btn btn-block bg-gradient-success btn-sm"  id="excelSearch">
                  <i class="fas fa-file-excel"></i>
                </button>
              </div-->
            </div>
          </div>

          <div class="card-body">
            <table id="weightTable" class="table table-bordered table-striped display">
              <thead>
                <tr>
                  <th>Serial No</th>
                  <th>Customer</th>
                  <th>Farm</th>
                  <th>Number of <br>Cages</th>
                  <th>Number of <br>Birds</th>
                  <th>Average Birds <br>Weight</th>
                </tr>
              </thead>
              <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(function () {
  const today = new Date();
  const sevenDaysAgo = new Date(today);
  sevenDaysAgo.setDate(today.getDate() - 7);
  var started = formatDate(today) + " 00:00:00";
  var ended = formatDate(today) + " 23:59:59";
  var dateRange = formatDate(today) + ' - ' + formatDate(today);
  $('#range').html(dateRange);
  
  $('.select2').select2({
    allowClear: true,
    placeholder: "Please Select"
  })

  $('#daterange-btn').daterangepicker(
    {
      ranges   : {
        'Today'       : [moment(), moment()],
        'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month'  : [moment().startOf('month'), moment().endOf('month')],
        'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      startDate: moment(),
      endDate  : moment()
    },
    function (start, end) {
      var startFormatted = formatDate(start) + " 00:00:00";
      var endFormatted = formatDate(end) + " 23:59:59";
      var dateRange = start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY');
      $('#range').html(dateRange);
      started = startFormatted;
      ended = endFormatted;
      var statusFilter = $('#farmFilter').val() ? $('#farmFilter').val() : '';
      var customerNoFilter = $('#customerFilter').val() ? $('#customerFilter').val() : '';

      //Destroy the old Datatable
      $("#weightTable").DataTable().clear().destroy();

      //Create new Datatable
      table = $("#weightTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'searching': false,
        'order': [[ 0, 'asc' ]],
        'columnDefs': [ { orderable: false, targets: [0] }],
        'ajax': {
          'type': 'POST',
          'url':'php/filterCountOther.php',
          'data': {
            fromDate: startFormatted,
            toDate: endFormatted,
            farm: statusFilter,
            customer: customerNoFilter
          } 
        },
        'columns': [
            {
                data: 'serial_no',
                render: function(data, type, row) {
                    var userId = row.id; // Assuming 'id' is the user ID from the server data
                    return '<a href="https://ccb.syncweigh.com/printportrait.php?userID=' + userId + '" target="_blank">' + data + '</a>';
                }
            },
            { data: 'customer' },
            { data: 'farm_id' },
            { data: 'total_cages' },
            { data: 'total_birds' },
            { data: 'average_bird' }
        ],
        "rowCallback": function( row, data, index ) {
          $('td', row).css('background-color', '#E6E6FA');
        },
        "drawCallback": function(settings) {
          $('#spinnerLoading').hide();
          /*$('#salesInfo').html(settings.json.done);
          $('#purchaseInfo').html(settings.json.inprogress);
          $('#localInfo').html(settings.json.total);*/
        },
        "footerCallback": function(row, data, start, end, display) {
            var api = this.api();

            // Calculate total for 'total_cages' column
            var totalCages = api
                .column(3, { page: 'current' })
                .data()
                .reduce(function(a, b) {
                    return a + parseInt(b, 10);
                }, 0);

            // Calculate total for 'total_birds' column
            var totalBirds = api
                .column(4, { page: 'current' })
                .data()
                .reduce(function(a, b) {
                    return a + parseInt(b, 10);
                }, 0);

            // Update footer with the total
            $(api.column(3).footer()).html(totalCages);
            $(api.column(4).footer()).html(totalBirds);
        }
      });
    }
  );

  var table = $("#weightTable").DataTable({
    "responsive": true,
    "autoWidth": false,
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'searching': false,
    'order': [[ 0, 'asc' ]],
    'columnDefs': [ { orderable: false, targets: [0] }],
    'ajax': {
      'type': 'POST',
      'url':'php/filterCountOther.php',
      'data': {
        fromDate: started,
        toDate: ended,
        farm: '',
        customer: ''
      } 
    },
    'columns': [
      {
        data: 'serial_no',
        render: function(data, type, row) {
            var userId = row.id; // Assuming 'id' is the user ID from the server data
            return '<a href="https://ccb.syncweigh.com/printportrait.php?userID=' + userId + '" target="_blank">' + data + '</a>';
        }
      },
      { data: 'customer' },
      { data: 'farm_id' },
      { data: 'total_cages' },
      { data: 'total_birds' },
      { data: 'average_bird' }
    ],
    "rowCallback": function( row, data, index ) {
      $('td', row).css('background-color', '#E6E6FA');
    },
    "drawCallback": function(settings) {
      $('#spinnerLoading').hide();
      /*$('#salesInfo').html(settings.json.done);
      $('#purchaseInfo').html(settings.json.inprogress);
      $('#localInfo').html(settings.json.total);*/
    },
    "footerCallback": function(row, data, start, end, display) {
        var api = this.api();

        // Calculate total for 'total_cages' column
        var totalCages = api
            .column(3, { page: 'current' })
            .data()
            .reduce(function(a, b) {
                return a + parseInt(b, 10);
            }, 0);

        // Calculate total for 'total_birds' column
        var totalBirds = api
            .column(4, { page: 'current' })
            .data()
            .reduce(function(a, b) {
                return a + parseInt(b, 10);
            }, 0);

        // Update footer with the total
        $(api.column(3).footer()).html(totalCages);
        $(api.column(4).footer()).html(totalBirds);
    }
  });

  $('#filterSearch').on('click', function(){
    $('#spinnerLoading').show();

    var fromDateValue = started;
    var toDateValue = ended;
    var statusFilter = $('#farmFilter').val() ? $('#farmFilter').val() : '';
    var customerNoFilter = $('#customerFilter').val() ? $('#customerFilter').val() : '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    //Create new Datatable
    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': false,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterCountOther.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          farm: statusFilter,
          customer: customerNoFilter,
        }
      },
      'columns': [
        {
            data: 'serial_no',
            render: function(data, type, row) {
                var userId = row.id; // Assuming 'id' is the user ID from the server data
                return '<a href="https://ccb.syncweigh.com/printportrait.php?userID=' + userId + '" target="_blank">' + data + '</a>';
            }
        },
        { data: 'customer' },
        { data: 'farm_id' },
        { data: 'total_cages' },
        { data: 'total_birds' },
        { data: 'average_bird' }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      },
      "drawCallback": function(settings) {
        $('#spinnerLoading').hide();
        /*$('#salesInfo').html('Total Transaction: ' + settings.json.salesTotal + '<br>Total Incoming: ' + settings.json.salesWeight + ' kg<br>Total Outgoing: ' + settings.json.salesTare + ' kg<br>Total Net Weight: ' +settings.json.salesNet+ ' kg');
        $('#purchaseInfo').html('Total Transaction: ' + settings.json.purchaseTotal + '<br>Total Incoming: ' + settings.json.purchaseWeight + ' kg<br>Total Outgoing: ' + settings.json.purchaseTare + ' kg<br>Total Net Weight: ' +settings.json.purchaseNet+ ' kg');
        $('#localInfo').html('Total Transaction: ' + settings.json.localTotal + '<br>Total Incoming: ' + settings.json.localWeight + ' kg<br>Total Outgoing: ' + settings.json.localTare + ' kg<br>Total Net Weight: ' +settings.json.localNet+ ' kg');*/
      },
      "footerCallback": function(row, data, start, end, display) {
            var api = this.api();

            // Calculate total for 'total_cages' column
            var totalCages = api
                .column(3, { page: 'current' })
                .data()
                .reduce(function(a, b) {
                    return a + parseInt(b, 10);
                }, 0);

            // Calculate total for 'total_birds' column
            var totalBirds = api
                .column(4, { page: 'current' })
                .data()
                .reduce(function(a, b) {
                    return a + parseInt(b, 10);
                }, 0);

            // Update footer with the total
            $(api.column(3).footer()).html(totalCages);
            $(api.column(4).footer()).html(totalBirds);
        }
    });
  });
});

function format (row) {
  return '<div class="row"><div class="col-md-3"><p>Customer Name: '+row.customer_name+
  '</p></div><div class="col-md-3"><p>Unit Weight: '+row.unit+
  '</p></div><div class="col-md-3"><p>Weight Status: '+row.status+
  '</p></div><div class="col-md-3"><p>MOQ: '+row.moq+
  '</p></div></div><div class="row"><div class="col-md-3"><p>Address: '+row.customer_address+
  '</p></div><div class="col-md-3"><p>Batch No: '+row.batchNo+
  '</p></div><div class="col-md-3"><p>Weight By: '+row.userName+
  '</p></div><div class="col-md-3"><p>Package: '+row.packages+
  '</p></div></div><div class="row"><div class="col-md-3">'+
  '</div><div class="col-md-3"><p>Lot No: '+row.lots_no+
  '</p></div><div class="col-md-3"><p>Invoice No: '+row.invoiceNo+
  '</p></div><div class="col-md-3"><p>Unit Price: '+row.unitPrice+
  '</p></div></div><div class="row"><div class="col-md-3">'+
  '</div><div class="col-md-3"><p>Order Weight: '+row.supplyWeight+
  '</p></div><div class="col-md-3"><p>Delivery No: '+row.deliveryNo+
  '</p></div><div class="col-md-3"><p>Total Weight: '+row.totalPrice+
  '</p></div></div><div class="row"><div class="col-md-3"><p>Contact No: '+row.customer_phone+
  '</p></div><div class="col-md-3"><p>Variance Weight: '+row.varianceWeight+
  '</p></div><div class="col-md-3"><p>Purchase No: '+row.purchaseNo+
  '</p></div><div class="col-md-3"><div class="row"><div class="col-3"><button type="button" class="btn btn-warning btn-sm" onclick="edit('+row.id+
  ')"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" class="btn btn-danger btn-sm" onclick="deactivate('+row.id+
  ')"><i class="fas fa-trash"></i></button></div><div class="col-3"><button type="button" class="btn btn-info btn-sm" onclick="print('+row.id+
  ')"><i class="fas fa-print"></i></button></div><div class="col-3"><button type="button" class="btn btn-success btn-sm" onclick="portrait('+row.id+
  ')"><i class="fas fa-receipt"></i></button></div></div></div></div>'+
  '</div><div class="row"><div class="col-md-3"><p>Remark: '+row.remark+
  '</p></div><div class="col-md-3"><p>% Variance: '+row.variancePerc+
  '</p></div><div class="col-md-3"><p>Transporter: '+row.transporter_name+
  '</p></div></div>';
  ;
}

function formatNormal (row) {
  return '<div class="row"><div class="col-md-3"><p>Customer Name: '+row.customer_name+
  '</p></div><div class="col-md-3"><p>Unit Weight: '+row.unit+
  '</p></div><div class="col-md-3"><p>Weight Status: '+row.status+
  '</p></div><div class="col-md-3"><p>MOQ: '+row.moq+
  '</p></div></div><div class="row"><div class="col-md-3"><p>Address: '+row.customer_address+
  '</p></div><div class="col-md-3"><p>Batch No: '+row.batchNo+
  '</p></div><div class="col-md-3"><p>Weight By: '+row.userName+
  '</p></div><div class="col-md-3"><p>Package: '+row.packages+
  '</p></div></div><div class="row"><div class="col-md-3">'+
  '</div><div class="col-md-3"><p>Lot No: '+row.lots_no+
  '</p></div><div class="col-md-3"><p>Invoice No: '+row.invoiceNo+
  '</p></div><div class="col-md-3"><p>Unit Price: '+row.unitPrice+
  '</p></div></div><div class="row"><div class="col-md-3">'+
  '</div><div class="col-md-3"><p>Order Weight: '+row.supplyWeight+
  '</p></div><div class="col-md-3"><p>Delivery No: '+row.deliveryNo+
  '</p></div><div class="col-md-3"><p>Total Weight: '+row.totalPrice+
  '</p></div></div><div class="row"><div class="col-md-3"><p>Contact No: '+row.customer_phone+
  '</p></div><div class="col-md-3"><p>Variance Weight: '+row.varianceWeight+
  '</p></div><div class="col-md-3"><p>Purchase No: '+row.purchaseNo+
  '</p></div><div class="col-md-3"><div class="row"><div class="col-3"><button type="button" class="btn btn-info btn-sm" onclick="print('+row.id+
  ')"><i class="fas fa-print"></i></button></div><div class="col-3"><button type="button" class="btn btn-success btn-sm" onclick="portrait('+row.id+
  ')"><i class="fas fa-receipt"></i></button></div></div></div></div>'+
  '</div><div class="row"><div class="col-md-3"><p>Remark: '+row.remark+
  '</p></div><div class="col-md-3"><p>% Variance: '+row.variancePerc+
  '</p></div><div class="col-md-3"><p>Transporter: '+row.transporter_name+
  '</p></div></div>';
  ;
}

function edit(id) {
  $('#spinnerLoading').show();
  $.post('php/getWeights.php', {userID: id}, function(data){
    var obj = JSON.parse(data);
    
    if(obj.status === 'success'){
      $('#extendModal').find('#id').val(obj.message.id);
      $('#extendModal').find('#serialNumber').val(obj.message.serialNo);
      $('#extendModal').find('#unitWeight').val(obj.message.unitWeight);
      $('#extendModal').find('#invoiceNo').val(obj.message.invoiceNo);
      $('#extendModal').find('#status').val(obj.message.status);
      $('#extendModal').find('#lotNo').val(obj.message.lotNo);
      $('#extendModal').find('#deliveryNo').val(obj.message.deliveryNo);
      $('#extendModal').find('#batchNo').val(obj.message.batchNo);
      $('#extendModal').find('#purchaseNo').val(obj.message.purchaseNo);
      $('#extendModal').find('#currentWeight').val(obj.message.currentWeight);
      $('#extendModal').find('#product').val(obj.message.productName);
      $('#extendModal').find('#moq').val(obj.message.moq);
      $('#extendModal').find('#transporter').val(obj.message.transporter);
      $('#extendModal').find('#tareWeight').val(obj.message.tare);
      $('#extendModal').find('#package').val(obj.message.package);
      $('#extendModal').find('#actualWeight').val(obj.message.actualWeight);
      $('#extendModal').find('#supplyWeight').val(obj.message.supplyWeight);
      $('#extendModal').find('#varianceWeight').val(obj.message.varianceWeight);
      $('#extendModal').find('#remark').val(obj.message.remark);
      $('#extendModal').find('#totalPrice').val(obj.message.totalPrice);
      $('#extendModal').find('#unitPrice').val(obj.message.unitPrice);
      $('#extendModal').find('#totalWeight').val(obj.message.totalWeight);
      $('#extendModal').find('#reduceWeight').val(obj.message.reduceWeight);
      $('#extendModal').find('#pStatus').val(obj.message.pStatus);
      $('#extendModal').find('#outGDateTime').val(obj.message.outGDateTime);
      $('#extendModal').find('#inCDateTime').val(obj.message.inCDateTime);
      $('#extendModal').find('#variancePerc').val(obj.message.variancePerc);

      $('#extendModal').find('#toDatePicker').datetimepicker({
        icons: { time: 'far fa-clock' },
        format: 'DD/MM/YYYY HH:mm:ss A'
      });

      $('#extendModal').find('#dateTime').val(obj.message.dateTime);
    
      if($('#extendModal').find('#status').val() == '1'){
        $('#extendModal').find('#customerNo').html($('select#customerNoHidden').html()).append($('#extendModal').find('#status').val());
        $('#extendModal').find('.labelStatus').text('Customer No');
        $('#extendModal').find('.labelOrder').text('Order Weight');
        $('#extendModal').find('#customerNo').val(obj.message.customer);
        
      }
      else if($('#extendModal').find('#status').val() == '2'){
        $('#extendModal').find('#customerNo').html($('select#supplierNoHidden').html()).append($('#extendModal').find('#status').val());
        $('#extendModal').find('.labelStatus').text('Supplier No');
        $('#extendModal').find('.labelOrder').text('Supply Weight');
        $('#extendModal').find('#customerNo').val(obj.message.customer);
      }

      if(obj.message.manualVehicle === 1){
        $('#extendModal').find('#manualVehicle').prop('checked', true);
        $('#extendModal').find('#vehicleNoTct').removeAttr('hidden');
        $('#extendModal').find('#vehicleNo').attr('hidden', 'hidden');
        $('#extendModal').find('#vehicleNoTct').val(obj.message.vehicleNo);
      }
      else{
        $('#extendModal').find('#manualVehicle').prop('checked', false);
        $('#extendModal').find('#vehicleNo').removeAttr('hidden');
        $('#extendModal').find('#vehicleNoTct').attr('hidden', 'hidden');
        $('#extendModal').find('#vehicleNo').val(obj.message.vehicleNo);
      }

            ///still need do some changes
      if(obj.message.manual === 1){
        $('#extendModal').find('#manual').prop('checked', true);
        $('#extendModal').find('#currentWeight').attr('readonly', false);
      }

      if(obj.message.manualOutgoing === 1){
        $('#extendModal').find('#manualOutgoing').prop('checked', true);
        $('#extendModal').find('#tareWeight').attr('readonly', false);
      }

      $('#extendModal').modal('show');
      $('#extendForm').validate({
        errorElement: 'span',
        errorPlacement: function (error, element) {
          error.addClass('invalid-feedback');
          element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
          $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
          $(element).removeClass('is-invalid');
        }
      });
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when pull data", "Failed:");
    }
    $('#spinnerLoading').hide();
  });
}

function deactivate(id) {
  if (confirm('Are you sure you want to delete this items?')) {
    $('#spinnerLoading').show();
    $.post('php/deleteWeight.php', {userID: id}, function(data){
      var obj = JSON.parse(data);

      if(obj.status === 'success'){
        toastr["success"](obj.message, "Success:");
        $('#weightTable').DataTable().ajax.reload();
      }
      else if(obj.status === 'failed'){
        toastr["error"](obj.message, "Failed:");
      }
      else{
        toastr["error"]("Something wrong when activate", "Failed:");
      }
      $('#spinnerLoading').hide();
    });
  }
}

function print(id) {
  $.post('php/print.php', {userID: id, file: 'weight'}, function(data){
    var obj = JSON.parse(data);

    if(obj.status === 'success'){
      var printWindow = window.open('', '', 'height=400,width=800');
      printWindow.document.write(obj.message);
      printWindow.document.close();
      setTimeout(function(){
        printWindow.print();
        printWindow.close();
      }, 1000);
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when activate", "Failed:");
    }
  });
}

function print2(id) {
  $.post('php/printportrait.php', {userID: id, file: 'weight'}, function(data){
    var obj = JSON.parse(data);

    if(obj.status === 'success'){
      var printWindow = window.open('', '', 'height=400,width=800');
      printWindow.document.write(obj.message);
      printWindow.document.close();
      setTimeout(function(){
        printWindow.print();
        printWindow.close();
      }, 1000);
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when activate", "Failed:");
    }
  });
}

function portrait(id) {
  $.post('php/printportrait.php', {userID: id, file: 'weight'}, function(data){
    var obj = JSON.parse(data);

    if(obj.status === 'success'){
      var printWindow = window.open('', '', 'height=400,width=800');
      printWindow.document.write(obj.message);
      printWindow.document.close();
      setTimeout(function(){
        printWindow.print();
        printWindow.close();
      }, 500);
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when activate", "Failed:");
    }
  });
}
</script>