<?php
require_once 'php/languageSetting.php';

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $user = $_SESSION['userID'];
  $language = $_SESSION['language'];
  $_SESSION['page']='weight';
  $stmt = $db->prepare("SELECT * from users where id = ?");
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if(($row = $result->fetch_assoc()) !== null){
        $role = $row['role_code'];
    }

  $vehicles = $db->query("SELECT * FROM vehicles WHERE deleted = '0'"); // Vehicles
  $products = $db->query("SELECT * FROM products WHERE deleted = '0'"); // Products
  $farms = $db->query("SELECT * FROM farms WHERE deleted = '0' ORDER BY name");
  $farms2 = $db->query("SELECT * FROM farms WHERE deleted = '0' ORDER BY name");
  $customers = $db->query("SELECT * FROM customers WHERE deleted = '0' ORDER BY customer_name"); // Customers
  $customers2 = $db->query("SELECT * FROM customers WHERE deleted = '0' ORDER BY customer_name"); // Customers
  $users = $db->query("SELECT * FROM users WHERE deleted = '0'"); // Users
  $transporters = $db->query("SELECT * FROM transporters WHERE deleted = '0'"); // Drivers
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
        <h1 class="m-0 text-dark"><?=$languageArray['weight_weighing_code'][$language] ?></h1>
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
              <div class="form-group col-3">
                <label><?=$languageArray['from_date_code'][$language] ?>:</label>
                <div class="input-group date" id="fromDatePicker" data-target-input="nearest">
                  <input type="text" class="form-control datetimepicker-input" data-target="#fromDatePicker" id="fromDate"/>
                  <div class="input-group-append" data-target="#fromDatePicker" data-toggle="datetimepicker">
                  <div class="input-group-text"><i class="fa fa-calendar"></i></div></div>
                </div>
              </div>

              <div class="form-group col-3">
                <label><?=$languageArray['to_date_code'][$language] ?>:</label>
                <div class="input-group date" id="toDatePicker" data-target-input="nearest">
                  <input type="text" class="form-control datetimepicker-input" data-target="#toDatePicker" id="toDate"/>
                  <div class="input-group-append" data-target="#toDatePicker" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                  </div>
                </div>
              </div>

              <div class="col-3">
                <div class="form-group">
                  <label><?=$languageArray['farm_code'][$language] ?></label>
                  <select class="form-control select2" id="farmFilter" name="farmFilter" style="width: 100%;">
                    <option selected="selected">-</option>
                    <?php while($rowStatus2=mysqli_fetch_assoc($farms2)){ ?>
                      <option value="<?=$rowStatus2['id'] ?>"><?=$rowStatus2['name'] ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="col-3">
                <div class="form-group">
                  <label><?=$languageArray['customer_code'][$language] ?></label>
                  <select class="form-control select2" style="width: 100%;" id="customerFilter" name="customerFilter" style="display: none;">
                    <option selected="selected">-</option>
                    <?php while($rowCustomer2=mysqli_fetch_assoc($customers2)){ ?>
                      <option value="<?=$rowCustomer2['customer_name'] ?>"><?=$rowCustomer2['customer_name'] ?></option>
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
    <div class="row">
      <div class="col-lg-12">
        <div class="card card-primary">
          <div class="card-header">
            <div class="row">
              <div class="col-9"></div>
              <?php 
                if($role == "ADMIN" || $role == "MANAGER"){
                  echo '<div class="col-3">
                  <button type="button" class="btn btn-block bg-gradient-warning btn-sm" onclick="newEntry()">'.$languageArray['add_new_weight_code'][$language].'</button>
                </div>';
                }
                else{
                  echo '<div class="col-3"></div>';
                }
              ?>
            </div>
          </div>

          <div class="card-body">
            <table id="weightTable" class="table table-bordered table-striped display">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Booking Datetime</th>
                  <th>Order No</th>
                  <th>PO No</th>
                  <th>Customers</th>
                  <th>Product</th>
                  <th>Vehicle No</th>
                  <th>Driver Name</th>
                  <th>Farm Id</th>
                  <th></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="extendModal">
  <div class="modal-dialog modal-xl" style="max-width: 90%;">
    <div class="modal-content">
      <form role="form" id="extendForm">
        <div class="modal-header bg-gray-dark color-palette">
          <h4 class="modal-title" id="modalTitle"><?=$languageArray['add_new_job_code'][$language] ?></h4>
          <button type="button" class="close bg-gray-dark color-palette" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <input type="hidden" class="form-control" id="id" name="id">
          <div class="row">
            <div class="form-group col-4">
              <label>PO No.</label>
              <input class="form-control" type="text" placeholder="PO No." id="poNo" name="poNo">
            </div>
            <div class="form-group col-4">
              <label><?=$languageArray['booking_date_code'][$language] ?> *</label>
              <div class="input-group date" id="bookingDatePicker" data-target-input="nearest">
                <input type="text" class="form-control datetimepicker-input" data-target="#bookingDatePicker" id="bookingDate" name="bookingDate" required/>
                <div class="input-group-append" data-target="#bookingDatePicker" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div></div>
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label class="labelStatus"><?=$languageArray['customer_code'][$language] ?> *</label>
                <select class="form-control select2" style="width: 100%;" id="customerNo" name="customerNo" required>
                  <option value="" selected disabled hidden>Please Select</option>
                  <?php while($rowCustomer=mysqli_fetch_assoc($customers)){ ?>
                    <option value="<?=$rowCustomer['customer_name'] ?>"><?=$rowCustomer['customer_name'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label><?=$languageArray['product_code'][$language] ?> *</label>
                <select class="form-control select2" style="width: 100%;" id="product" name="product" required>
                  <option selected="selected">-</option>
                  <?php while($row5=mysqli_fetch_assoc($products)){ ?>
                    <option value="<?=$row5['product_name'] ?>"><?=$row5['product_name'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label class="vehicleNo"><?=$languageArray['vehicle_no_code'][$language] ?></label>
                <select class="form-control select2" id="vehicleNo" name="vehicleNo">
                  <option selected="selected">-</option>
                  <?php while($row2=mysqli_fetch_assoc($vehicles)){ ?>
                    <option value="<?=$row2['veh_number'] ?>" data-driver="<?=$row2['driver'] ?>"><?=$row2['veh_number'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="form-group col-4">
              <label><?=$languageArray['driver_code'][$language] ?></label>
              <select class="form-control select2" style="width: 100%;" id="driver" name="driver">
                  <option selected="selected">-</option>
                  <?php while($row5=mysqli_fetch_assoc($transporters)){ ?>
                    <option value="<?=$row5['transporter_name'] ?>" data-id="<?=$row5['id'] ?>"><?=$row5['transporter_name'] ?></option>
                  <?php } ?>
              </select>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label><?=$languageArray['farm_code'][$language] ?> *</label>
                <select class="form-control select2" style="width: 100%;" id="farm" name="farm" required>
                  <option selected="selected">-</option>
                  <?php while($row6=mysqli_fetch_assoc($farms)){ ?>
                    <option value="<?=$row6['id'] ?>"><?=$row6['name'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="form-group col-4">
              <label><?=$languageArray['avg_bird_weight_code'][$language] ?></label>
              <input class="form-control" type="number" placeholder="Average Bird Weight" id="aveBird" name="aveBird">
            </div>
            <div class="form-group col-4">
              <label><?=$languageArray['min_average_weight_code'][$language] ?> </label>
              <input class="form-control" type="number" placeholder="Min Weight" id="minWeight" name="minWeight">
            </div>
            <div class="form-group col-4">
              <label><?=$languageArray['max_average_weight_code'][$language] ?> </label>
              <input class="form-control" type="number" placeholder="Max Weight" id="maxWeight" name="maxWeight">
            </div>
            <div class="form-group col-4">
              <label><?=$languageArray['min_crate_code'][$language] ?> </label>
              <input class="form-control" type="number" placeholder="Min Crate" id="minCrate" name="minCrate">
            </div>
            <div class="form-group col-4">
              <label><?=$languageArray['max_crate_code'][$language] ?> </label>
              <input class="form-control" type="number" placeholder="Max Crate" id="maxCrate" name="maxCrate">
            </div>
            <div class="col-4">
              <div class="form-group">
                <label><?=$languageArray['assigned_to_code'][$language] ?></label>
                <select class="select2" style="width: 100%;" id="assignTo" name="assignTo[]" multiple="multiple"> 
                  <?php while($rowusers=mysqli_fetch_assoc($users)){ ?>
                    <option value="<?=$rowusers['id'] ?>"><?=$rowusers['name'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="col-8">
              <div class="form-group">
                <label><?=$languageArray['remark_code'][$language] ?></label>
                <textarea class="form-control" rows="1" placeholder="Enter ..." id="remark" name="remark"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between bg-gray-dark color-palette">
          <button type="button" class="btn btn-primary" data-dismiss="modal"><?=$languageArray['close_code'][$language] ?></button>
          <button type="submit" class="btn btn-primary" id="saveButton"><?=$languageArray['save_code'][$language] ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Values
var controlflow = "None";
var indicatorUnit = "kg";
var weightUnit = "1";
var rate = 1;
var currency = "1";

$(function () {
  $('#customerNoHidden').hide();
  $('#supplierNoHidden').hide();
  const today = new Date();
  const sevenDaysAgo = new Date(today);
  sevenDaysAgo.setDate(today.getDate() - 7);
  var started = formatDate(today) + " 00:00:00";
  var ended = formatDate(sevenDaysAgo) + " 23:59:59";

  $('.select2').select2({
    allowClear: true,
    placeholder: "Please select"
  })

  var table = $("#weightTable").DataTable({
    "responsive": true,
    "autoWidth": false,
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'searching': true,
    'order': [[ 1, 'asc' ]],
    'columnDefs': [ { orderable: false, targets: [0] }],
    'ajax': {
        'url':'php/loadWeights.php'
    },
    'columns': [
      { data: 'no' },
      { data: 'booking_date' },
      { data: 'serial_no' },
      { data: 'po_no' },
      { data: 'customer' },
      { data: 'product' },
      { data: 'lorry_no' },
      { data: 'driver_name' },
      { data: 'farm_id' },
      {
            className: 'dt-control',
            orderable: false,
            data: null,
            defaultContent: '<i class="fas fa-angle-down"></i>',
            responsivePriority: 1
        }
    ],
    "rowCallback": function( row, data, index ) {
      $('td', row).css('background-color', '#E6E6FA');
    },
    "drawCallback": function(settings) {
      $('#spinnerLoading').hide();
    }
  });

  // Add event listener for opening and closing details
  $('#weightTable tbody').on('click', 'td.dt-control', function () {
    var tr = $(this).closest('tr');
    var row = table.row( tr );

    if ( row.child.isShown() ) {
      // This row is already open - close it
      row.child.hide();
      tr.removeClass('shown');
    }
    else {
      // Open this row
      <?php 
        if($role == "ADMIN"){
          echo 'row.child( format(row.data()) ).show();tr.addClass("shown");';
        }
        else if($role == "MANAGER"){
          echo 'row.child( formatNormal(row.data()) ).show();tr.addClass("shown");';
        }
        else{
          echo 'row.child( formatNormal2(row.data()) ).show();tr.addClass("shown");';
        }
      ?>
    }
  });
  
  //Date picker
  $('#fromDatePicker').datetimepicker({
      icons: { time: 'far fa-clock' },
      format: 'DD/MM/YYYY',
      defaultDate: new Date
  });

  $('#toDatePicker').datetimepicker({
    icons: { time: 'far fa-clock' },
    format: 'DD/MM/YYYY',
    defaultDate: new Date
  });

  $('#bookingDatePicker').datetimepicker({
    icons: { time: 'far fa-clock' },
    format: 'DD/MM/YYYY',
    minDate: new Date,
    defaultDate: new Date
  });

  $('#filterSearch').on('click', function(){
    $('#spinnerLoading').show();

    var fromDateValue = $('#fromDate').val();
    var toDateValue = $('#toDate').val();
    var statusFilter = $('#statusFilter').val() ? $('#statusFilter').val() : '';
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
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          farm: statusFilter,
          customer: customerNoFilter,
        }
      },
      'columns': [
        { data: 'no' },
        { data: 'booking_date' },
        { data: 'serial_no' },
        { data: 'po_no' },
        { data: 'customer' },
        { data: 'product' },
        { data: 'lorry_no' },
        { data: 'driver_name' },
        { data: 'farm_id' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      },
      "drawCallback": function(settings) {
        $('#spinnerLoading').hide();
      }
    });
  });

  $.validator.setDefaults({
    submitHandler: function () {
      if($('#extendModal').hasClass('show')){
        $('#spinnerLoading').show();

        $.post('php/insertWeight.php', $('#extendForm').serialize(), function(data){
          var obj = JSON.parse(data); 
          if(obj.status === 'success'){
            $('#extendModal').modal('hide');
            toastr["success"](obj.message, "Success:");
            $('#weightTable').DataTable().ajax.reload();
          }
          else if(obj.status === 'failed'){
            toastr["error"](obj.message, "Failed:");
          }
          else{
            toastr["error"]("Something wrong when edit", "Failed:");
          }

          $('#spinnerLoading').hide();
        });
      }
    }
  });

  $('#refreshBtn').on('click', function(){
    var fromDateValue = '';
    var toDateValue = '';
    var statusFilter = '';
    var customerNoFilter = '';
    var vehicleFilter = '';
    var invoiceFilter = '';
    var batchFilter = '';
    var productFilter = '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
          'url':'php/loadWeights.php'
      },
      'columns': [
        { data: 'no' },
        { data: 'serial_no' },
        { data: 'po_no' },
        { data: 'customer' },
        { data: 'product' },
        { data: 'lorry_no' },
        { data: 'driver_name' },
        { data: 'farm_id' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      },
      "drawCallback": function(settings) {
        
      }
    });

    //Create new Datatable
    /*table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          status: statusFilter,
          customer: customerNoFilter,
          vehicle: vehicleFilter,
          invoice: invoiceFilter,
          batch: batchFilter,
          product: productFilter,
        } 
      },
      'columns': [
        { data: 'no' },
        { data: 'pStatus' },
        { data: 'status' },
        { data: 'serialNo' },
        { data: 'veh_number' },
        { data: 'product_name' },
        { data: 'currentWeight' },
        { data: 'inCDateTime' },
        { data: 'tare' },
        { data: 'outGDateTime' },
        { data: 'totalWeight' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      },
      "drawCallback": function(settings) {
        $('#salesInfo').text(settings.json.salesTotal);
        $('#purchaseInfo').text(settings.json.purchaseTotal);
        $('#localInfo').text(settings.json.localTotal);
      }
    });*/
  });

  $('#vehicleNo').on('change', function(){
    var dataId = $(this).find('option:selected').attr('data-driver');
    
    if (dataId) {
      $('#driver').find('option[data-id="' + dataId + '"]').prop('selected', true);
      $('#driver').trigger('change');
    }
  });
});

function updatePrices(isFromCurrency, rat){
  var totalPrice;
  var unitPrice = $('#unitPrice').val();
  var totalWeight = $('#totalWeight').val();

  if(isFromCurrency == 'Y'){
    unitPrice = (unitPrice / rate) * parseFloat(rat);
    $('#extendModal').find('#unitPrice').val(unitPrice.toFixed(2));
    rate = parseFloat(rat).toFixed(2);
  }
  else{
    unitPrice = unitPrice * parseFloat(rat);
    $('#extendModal').find('#unitPrice').val(unitPrice.toFixed(2));
    rate = parseFloat(rat).toFixed(2);
  }
  

  if(unitPrice != '' &&  moq != '' && totalWeight != ''){
    totalPrice = unitPrice * totalWeight;
    $('#totalPrice').val(totalPrice.toFixed(2));
  }
  else(
    $('#totalPrice').val((0).toFixed(2))
  )
}

function updateWeights(){
  var tareWeight =  0;
  var currentWeight =  0;
  var reduceWeight = 0;
  var moq = $('#moq').val();
  var totalWeight = 0;
  var actualWeight = 0;

  if($('#currentWeight').val()){
    currentWeight =  $('#currentWeight').val();
  }

  if($('#tareWeight').val()){
    tareWeight =  $('#tareWeight').val();
  }

  if($('#reduceWeight').val()){
    reduceWeight =  $('#reduceWeight').val();
  }

  if(tareWeight == 0){
    actualWeight = currentWeight - reduceWeight;
    actualWeight = Math.abs(actualWeight);
    $('#actualWeight').val(actualWeight.toFixed(2));
  }
  else{
    actualWeight = tareWeight - currentWeight - reduceWeight;
    actualWeight = Math.abs(actualWeight);
    $('#actualWeight').val(actualWeight.toFixed(2));
  }

  if(actualWeight != '' &&  moq != ''){
    totalWeight = actualWeight * moq;
    $('#totalWeight').val(totalWeight.toFixed(2));
  }
  else{
    $('#totalWeight').val((0).toFixed(2))
  };
}

function format (row) {
  var returnString = '<div class="row"><div class="col-md-3"><p>Average Cage Weight: '+row.average_cage+
  ' kg</p></div><div class="col-md-3"><p>Average Bird Weight: '+row.average_bird+
  ' kg</p></div><div class="col-md-3"><p>Minimum Weight: '+row.minimum_weight+
  ' kg</p></div><div class="col-md-3"><p>Maximum Weight: '+row.maximum_weight+
  ' kg</p></div></div>';
  
  if(row.status == 'Pending'){
    returnString += '<div class="row"><div class="col-3"><button type="button" onclick="edit('+row.id+
  ')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" class="btn btn-danger btn-sm" onclick="deactivate('+row.id+
  ')"><i class="fas fa-trash"></i></button></div></div></div></div>'+
  '</div>';
  }
  else{
    returnString += '<div class="row"><div class="col-3"></div><div class="col-3"><button type="button" class="btn btn-danger btn-sm" onclick="deactivate('+row.id+
  ')"><i class="fas fa-trash"></i></button></div></div></div></div>'+
  '</div>';
  }

  return returnString;
}

function formatNormal (row) {
  var returnString = '<div class="row"><div class="col-md-3"><p>Average Cage Weight: '+row.average_cage+
  ' kg</p></div><div class="col-md-3"><p>Average Bird Weight: '+row.average_bird+
  ' kg</p></div><div class="col-md-3"><p>Minimum Weight: '+row.minimum_weight+
  ' kg</p></div><div class="col-md-3"><p>Maximum Weight: '+row.maximum_weight+
  ' kg</p></div></div>';
  
  if(row.status == 'Pending'){
    returnString += '<div class="row"><div class="col-3"><button type="button" onclick="edit('+row.id+
  ')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"></div></div></div></div>'+
  '</div>';
  }
  else{
    returnString += '<div class="row"><div class="col-3"></div><div class="col-3"></div></div></div></div>'+
  '</div>';
  }

  return returnString;
}

function formatNormal2 (row) {
  return '<div class="row"><div class="col-md-3"><p>Average Cage Weight: '+row.average_cage+
  ' kg</p></div><div class="col-md-3"><p>Average Bird Weight: '+row.average_bird+
  ' kg</p></div><div class="col-md-3"><p>Minimum Weight: '+row.minimum_weight+
  ' kg</p></div><div class="col-md-3"><p>Maximum Weight: '+row.maximum_weight+
  ' kg</p></div></div><div class="row"><div class="col-3"></div><div class="col-3"></div></div></div></div>'+
  '</div>';
}

function newEntry(){
  var currentDate = moment().format('DD/MM/YYYY');
  $('#extendModal').find('#id').val("");
  $('#extendModal').find('#customerNo').select2('destroy').val('').select2();
  $('#extendModal').find('#product').select2('destroy').val('').select2();
  $('#extendModal').find('#vehicleNo').select2('destroy').val('').select2();
  $('#extendModal').find('#driver').select2('destroy').val('').select2();
  $('#extendModal').find('#farm').select2('destroy').val('').select2();
  $('#extendModal').find('#aveBird').val("");
  $('#extendModal').find('#aveCage').val('');
  $('#extendModal').find('#poNo').val('');
  $('#extendModal').find('#minCrate').val('');
  $('#extendModal').find('#maxCrate').val('');
  $('#extendModal').find('#minWeight').val('');
  $('#extendModal').find('#bookingDate').val(currentDate);
  $('#extendModal').find('#maxWeight').val("");
  $('#extendModal').find('#assignTo').select2('destroy').val('').select2();
  $('#extendModal').find('#remark').val("");
  $('#extendModal').find('#modalTitle').text("Add New Order");
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

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function edit(id) {
  $('#spinnerLoading').show();
  $.post('php/getWeights.php', {userID: id}, function(data){
    var obj = JSON.parse(data);
    
    if(obj.status === 'success'){
      var momentDate = moment(obj.message.booking_date, 'YYYY-MM-DD HH:mm:ss');
      var formattedDate = momentDate.format('DD/MM/YYYY hh:mm:ss A');

      $('#extendModal').find('#id').val(obj.message.id);
      $('#extendModal').find('#poNo').val(obj.message.po_no);
      $('#extendModal').find('#customerNo').val(obj.message.customer).trigger('change');
      $('#extendModal').find('#product').val(obj.message.product).trigger('change');
      $('#extendModal').find('#vehicleNo').val(obj.message.lorry_no).trigger('change');
      $('#extendModal').find('#driver').val(obj.message.driver_name).trigger('change');
      $('#extendModal').find('#farm').val(obj.message.farm_id).trigger('change');
      $('#extendModal').find('#aveBird').val(obj.message.average_bird);
      $('#extendModal').find('#aveCage').val(obj.message.average_cage);
      $('#extendModal').find('#minWeight').val(obj.message.minimum_weight);
      $('#extendModal').find('#maxWeight').val(obj.message.maximum_weight);
      $('#extendModal').find('#bookingDate').val(formattedDate);
      $('#extendModal').find('#maxCrate').val(obj.message.max_crate);
      $('#extendModal').find("select[name='assignTo[]']").val(obj.message.weighted_by).trigger('change');
      $('#extendModal').find('#remark').val(obj.message.remark);
      $('#extendModal').find('#modalTitle').text("Edit Order");

      /*if($('#extendModal').find('#status').val() == 'Sales'){
        $('#extendModal').find('#customerNo').html($('select#customerNoHidden').html());
        $('#extendModal').find('.labelStatus').text('Customer *');
        $('#extendModal').find('#customerNo').val(obj.message.customer);
      }
      else{
        $('#extendModal').find('#customerNo').html($('select#supplierNoHidden').html());
        $('#extendModal').find('.labelStatus').text('Supplier *');
        $('#extendModal').find('#customerNo').val(obj.message.supplier);
      }*/

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
        /*$.get('weightPage.php', function(data) {
          $('#mainContents').html(data);
        });*/
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
      }, 500);

      /*$.get('weightPage.php', function(data) {
        $('#mainContents').html(data);
      });*/
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