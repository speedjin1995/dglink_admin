<?php
require_once 'php/languageSetting.php';

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $language = $_SESSION['language'];
  $user = $_SESSION['userID'];
  $_SESSION['page']='farms';
  $suppliers = $db->query("SELECT * FROM supplies WHERE deleted = '0'");
  $states = $db->query("SELECT * FROM states");
}
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark"><?=$languageArray['farm_code'][$language] ?></h1>
			</div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
        <div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
                        <div class="row">
                          <div class="col-5"></div>
                            <div class="col-2">
                                <input type="file" id="fileInput" accept=".xlsx, .xls" />
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" id="importExcelbtn"><?=$languageArray['import_excel_code'][$language] ?></button>
                            </div>                            
                            <div class="col-3">
                                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" id="addPackages"><?=$languageArray['add_farms_code'][$language] ?></button>
                            </div>
                        </div>
                    </div>
					<div class="card-body">
						<table id="packageTable" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No.</th>
                  <th>Code</th>
                  <th>States</th>
									<th>Farm</th>
									<th>Actions</th>
								</tr>
							</thead>
						</table>
					</div><!-- /.card-body -->
				</div><!-- /.card -->
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</section><!-- /.content -->

<div class="modal fade" id="packagesModal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form role="form" id="packageForm">
            <div class="modal-header">
              <h4 class="modal-title"><?=$languageArray['add_farms_code'][$language] ?></h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="card-body">
                <div class="form-group">
                  <input type="hidden" class="form-control" id="id" name="id">
                </div>
                <div class="form-group">
                  <label for="code"><?=$languageArray['farm_code_code'][$language] ?> *</label>
                  <input type="text" class="form-control" name="code" id="code" placeholder="Enter Farm Code" maxlength="10" required>
                </div>
                <div class="form-group">
                  <label for="packages"><?=$languageArray['farm_code'][$language] ?>*</label>
                  <input type="text" class="form-control" name="packages" id="packages" placeholder="Enter Farm Name" required>
                </div>
                <div class="form-group"> 
                  <label for="address"><?=$languageArray['address_code'][$language] ?> *</label>
                  <input type="text" class="form-control" name="address" id="address" placeholder="Enter  Address" required>
                </div>
                <div class="form-group"> 
                  <label for="address"><?=$languageArray['address_code'][$language] ?> 2</label>
                  <input type="text" class="form-control" name="address2" id="address2" placeholder="Enter Address 2">
                </div>
                <div class="form-group"> 
                  <label for="address"><?=$languageArray['address_code'][$language] ?> 3</label>
                  <input type="text" class="form-control" name="address3" id="address3" placeholder="Enter Address 3">
                </div>
                <div class="form-group"> 
                  <label for="address"><?=$languageArray['address_code'][$language] ?> 4</label>
                  <input type="text" class="form-control" name="address4" id="address4" placeholder="Enter Address 4">
                </div>
                <div class="form-group">
                  <label><?=$languageArray['states_code'][$language] ?> *</label>
                  <select class="form-control" style="width: 100%;" id="states" name="states" required>
                    <?php while($rowCustomer2=mysqli_fetch_assoc($states)){ ?>
                      <option value="<?=$rowCustomer2['id'] ?>"><?=$rowCustomer2['states'] ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="form-group" style="display:none;">
                  <label><?=$languageArray['supplier_code'][$language] ?></label>
                  <select class="form-control" style="width: 100%;" id="supplier" name="supplier">
                    <option selected="selected">-</option>
                    <?php while($rowCustomer2=mysqli_fetch_assoc($suppliers)){ ?>
                      <option value="<?=$rowCustomer2['id'] ?>"><?=$rowCustomer2['supplier_name'] ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-danger" data-dismiss="modal"><?=$languageArray['close_code'][$language] ?></button>
              <button type="submit" class="btn btn-primary" name="submit" id="submitLot"><?=$languageArray['save_code'][$language] ?></button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script>
$(function () {
    $("#packageTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'order': [[ 1, 'asc' ]],
        'columnDefs': [ { orderable: false, targets: [0] }],
        'ajax': {
            'url':'php/loadFarms.php'
        },
        'columns': [
            { data: 'counter' },
            { data: 'farms_code' },
            { data: 'states' },
            { data: 'name' },
            { 
              data: 'deleted',
              render: function (data, type, row) {
                if (data == 0) {
                  return '<div class="row"><div class="col-3"><button type="button" id="edit' + row.id + '" onclick="edit(' + row.id + ')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" id="delete' + row.id + '" onclick="deactivate(' + row.id + ')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
                } 
                else{
                  return '<button type="button" id="reactivate' + row.id + '" onclick="reactivate(' + row.id + ')" class="btn btn-warning btn-sm">Reactivate</button>';
                }
              }
            }
        ],
        "rowCallback": function( row, data, index ) {

            $('td', row).css('background-color', '#E6E6FA');
        },        
    });
    
    $.validator.setDefaults({
        submitHandler: function () {
            $('#spinnerLoading').show();
            $.post('php/farms.php', $('#packageForm').serialize(), function(data){
                var obj = JSON.parse(data); 
                
                if(obj.status === 'success'){
                    $('#packagesModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                    $('#packageTable').DataTable().ajax.reload();
                    $('#spinnerLoading').hide();
                }
                else if(obj.status === 'failed'){
                    toastr["error"](obj.message, "Failed:");
                    $('#spinnerLoading').hide();
                }
                else{
                    toastr["error"]("Something wrong when edit", "Failed:");
                    $('#spinnerLoading').hide();
                }
            });
        }
    });

    $('#addPackages').on('click', function(){
        $('#packagesModal').find('#id').val("");
        $('#packagesModal').find('#code').val("");
        $('#packagesModal').find('#packages').val("");
        $('#packagesModal').find('#address').val("");
        $('#packagesModal').find('#address2').val("");
        $('#packagesModal').find('#address3').val("");
        $('#packagesModal').find('#address4').val("");
        $('#packagesModal').find('#states').val("");
        $('#packagesModal').find('#supplier').val("");
        $('#packagesModal').modal('show');
        
        $('#packageForm').validate({
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
    });

    document.getElementById('fileInput').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        
        //const sheetName = workbook.SheetNames['Farms'];
        const sheet = workbook.Sheets['Farms'];
        jsonData = XLSX.utils.sheet_to_json(sheet);
        console.log(jsonData);
        };
        reader.readAsArrayBuffer(file);
    });

    $('#importExcelbtn').on('click', function(){
        jsonData.forEach(function(row) {
            $.ajax({
                url: 'php/importExcelPackages.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(row),
                success: function(response) {
                    var obj = JSON.parse(response); 
                    
                    if(obj.status === 'success'){
                        $('#addModal').modal('hide');
                        toastr["success"](obj.message, "Success:");
                        $('#packageTable').DataTable().ajax.reload();
                        $('#spinnerLoading').hide();
                    }
                    else if(obj.status === 'failed'){
                        toastr["error"](obj.message, "Failed:");
                        $('#spinnerLoading').hide();
                    }
                    else{
                        toastr["error"]("Something wrong when import", "Failed:");
                        $('#spinnerLoading').hide();
                    }
                },
                error: function(error) {
                    toastr["error"](obj.message, "Failed:");
                    $('#spinnerLoading').hide();
                }
            })
        })
    });
});

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/getFarms.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            $('#packagesModal').find('#id').val(obj.message.id);
            $('#packagesModal').find('#code').val(obj.message.packages_code);
            $('#packagesModal').find('#packages').val(obj.message.packages);
            $('#packagesModal').find('#address').val(obj.message.address);
            $('#packagesModal').find('#address2').val(obj.message.address2);
            $('#packagesModal').find('#address3').val(obj.message.address3);
            $('#packagesModal').find('#address4').val(obj.message.address4);
            $('#packagesModal').find('#states').val(obj.message.states);
            $('#packagesModal').find('#supplier').val(obj.message.suppliers);
            $('#packagesModal').modal('show');
            
            $('#packageForm').validate({
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
            toastr["error"]("Something wrong when activate", "Failed:");
        }
        $('#spinnerLoading').hide();
    });
}

function deactivate(id){
    if (confirm('Are you sure you want to delete this items?')) {
        $('#spinnerLoading').show();
        $.post('php/deleteFarms.php', {userID: id}, function(data){
            var obj = JSON.parse(data);
            
            if(obj.status === 'success'){
                toastr["success"](obj.message, "Success:");
                $('#packageTable').DataTable().ajax.reload();
                $('#spinnerLoading').hide();
            }
            else if(obj.status === 'failed'){
                toastr["error"](obj.message, "Failed:");
                $('#spinnerLoading').hide();
            }
            else{
                toastr["error"]("Something wrong when activate", "Failed:");
                $('#spinnerLoading').hide();
            }
        });
    }
}

function reactivate(id){
    if (confirm('Are you sure you want to reactivate this items?')) {
        $('#spinnerLoading').show();
        $.post('php/reactivateFarms.php', {userID: id}, function(data){
            var obj = JSON.parse(data);
            
            if(obj.status === 'success'){
                toastr["success"](obj.message, "Success:");
                $('#packageTable').DataTable().ajax.reload();
                $('#spinnerLoading').hide();
            }
            else if(obj.status === 'failed'){
                toastr["error"](obj.message, "Failed:");
                $('#spinnerLoading').hide();
            }
            else{
                toastr["error"]("Something wrong when activate", "Failed:");
                $('#spinnerLoading').hide();
            }
        });
    }
}
</script>