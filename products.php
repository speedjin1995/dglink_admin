<?php
require_once 'php/languageSetting.php';

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "login.html";</script>';
}
else{
    $language = $_SESSION['language'];
    $user = $_SESSION['userID'];
    $_SESSION['page']='products';
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark"><?=$languageArray['product_code'][$language] ?></h1>
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
                                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" id="addProducts"><?=$languageArray['add_products_code'][$language] ?></button>
                            </div>
                        </div>
                    </div>
					<div class="card-body">
						<table id="productTable" class="table table-bordered table-striped">
							<thead>
								<tr>
                                    <th>Code</th>
									<th>Chicken Description</th>
                                    <th>Remark</th>
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

<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form role="form" id="productForm">
            <div class="modal-header">
              <h4 class="modal-title"><?=$languageArray['add_products_code'][$language] ?></h4>
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
                  <label for="code"><?=$languageArray['product_code_code'][$language] ?> *</label>
                  <input type="text" class="form-control" name="code" id="code" placeholder="Enter Product Code" maxlength="10" required>
                </div>
                <div class="form-group">
                  <label for="product"><?=$languageArray['product_code'][$language] ?> *</label>
                  <input type="text" class="form-control" name="product" id="product" placeholder="Enter Product Name" required>
                </div>
                <div class="form-group"> 
                  <label for="remark"><?=$languageArray['description_code'][$language] ?> </label>
                  <textarea class="form-control" id="remark" name="remark" placeholder="Enter your remark"></textarea>
                </div>
              </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-danger" data-dismiss="modal"><?=$languageArray['close_code'][$language] ?></button>
              <button type="submit" class="btn btn-primary" name="submit" id="submitMember"><?=$languageArray['save_code'][$language] ?></button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script>
$(function () {
    $("#productTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url':'php/loadProducts.php'
        },
        'columns': [
            { data: 'product_code' },
            { data: 'product_name' },
            { data: 'remark' },
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
            $.post('php/products.php', $('#productForm').serialize(), function(data){
                var obj = JSON.parse(data); 
                
                if(obj.status === 'success'){
                    $('#addModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                    $('#productTable').DataTable().ajax.reload();
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

    $('#addProducts').on('click', function(){
        $('#addModal').find('#id').val("");
        $('#addModal').find('#code').val("");
        $('#addModal').find('#product').val("");
        $('#addModal').find('#remark').val("");
        $('#addModal').modal('show');
        
        $('#productForm').validate({
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

        const sheetName = workbook.SheetNames[3];
        const sheet = workbook.Sheets[sheetName];
        jsonData = XLSX.utils.sheet_to_json(sheet);
        console.log(jsonData);
        };
        reader.readAsArrayBuffer(file);
    });

    $('#importExcelbtn').on('click', function(){
        jsonData.forEach(function(row) {
            $.ajax({
                url: 'php/importExcelProduct.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(row),
                success: function(response) {
                    var obj = JSON.parse(response); 
                    
                    if(obj.status === 'success'){
                        $('#addModal').modal('hide');
                        toastr["success"](obj.message, "Success:");
                        $('#productTable').DataTable().ajax.reload();
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
    $.post('php/getProduct.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            $('#addModal').find('#id').val(obj.message.id);
            $('#addModal').find('#code').val(obj.message.product_code);
            $('#addModal').find('#product').val(obj.message.product_name);
            $('#addModal').find('#remark').val(obj.message.remark);
            $('#addModal').modal('show');
            
            $('#productForm').validate({
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
        $.post('php/deleteProduct.php', {userID: id}, function(data){
            var obj = JSON.parse(data);
            
            if(obj.status === 'success'){
                toastr["success"](obj.message, "Success:");
                $('#productTable').DataTable().ajax.reload();
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
        $.post('php/reactivateProduct.php', {userID: id}, function(data){
            var obj = JSON.parse(data);
            
            if(obj.status === 'success'){
                toastr["success"](obj.message, "Success:");
                $('#productTable').DataTable().ajax.reload();
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