<?php
require_once 'php/db_connect.php';

session_start();

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $_SESSION['page']='message';
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
        <h1 class="m-0 text-dark">Message Resources</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item">Home</li>
          <li class="breadcrumb-item active">Message Resources</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"></h3>
            <button type="button" class="btn btn-block btn-primary btn-sm" id="addMessage" style="width: 10%;float: right;">Add</button>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <table id="messageTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Message Code</th>
                  <th>English</th>
                  <th>Chinese</th>
                  <th>Malay</th>
                  <th>Nepali</th>
                  <th>Action</th>
                </tr>
              </thead>
            </table>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
  
<div class="modal fade" id="messageModal">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form role="form" id="messageForm">
          <div class="modal-header">
            <h4 class="modal-title">Message Resource Details</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">
              <div class="card-body">
                  <div class="form-group">
            <input type="hidden" class="form-control" id="keyId" name="keyId">
          </div>
          <div class="form-group">
            <label for="keyCode">Message Key Code *</label>
            <input class="form-control" name="keyCode" id="keyCode" placeholder="Message Key Code" required>
          </div>
          <div class="form-group">
            <label for="englishDecs">English</label>
            <textarea class="form-control" name="englishDecs" id="englishDecs" rows="3" placeholder="English" required></textarea>
          </div>
          <div class="form-group">
            <label for="chineseDecs">中文</label>
            <textarea class="form-control" name="chineseDecs" id="chineseDecs" rows="3" placeholder="中文" required></textarea>
          </div>
          <div class="form-group">
            <label for="malayDecs">Bahasa Malaysia</label>
            <textarea class="form-control" name="malayDecs" id="malayDecs" rows="3" placeholder="Bahasa" required></textarea>
          </div>
          <div class="form-group">
            <label for="nepaliDecs">नेपाली</label>
            <textarea class="form-control" name="nepaliDecs" id="nepaliDecs" rows="3" placeholder="नेपाली" required></textarea>
          </div>
        </div>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" name="submit" id="submitMessage">Submit</button>
          </div>
      </form>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>

<script>
$(function () {
    $("#messageTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'order': [[ 1, 'asc' ]],
        'columnDefs': [ { orderable: false, targets: [0] }],
        'ajax': {
            'url':'php/loadMessages.php'
        },
        'columns': [
            { data: 'counter' },
            { data: 'message_key_code' },
            { data: 'en' },
            { data: 'zh' },
            { data: 'my' },
            { data: 'ne' },
            { 
              data: 'id',
              render: function (data, type, row) {
                return '<div class="row"><div class="col-3"><button type="button" id="edit' + row.id + '" onclick="edit(' + row.id + ')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" id="delete' + row.id + '" onclick="deletes(' + row.id + ')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
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
            $.post('php/message.php', $('#messageForm').serialize(), function(data){
                var obj = JSON.parse(data); 
                
                if(obj.status === 'success'){
                    $('#messageModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                    $('#messageTable').DataTable().ajax.reload();
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
    
    $('#addMessage').on('click', function(){
        $('#messageModal').find('#keyId').val('');
        $('#messageModal').find('#keyCode').val('');
        $('#messageModal').find('#englishDecs').val('');
        $('#messageModal').find('#chineseDecs').val('');
        $('#messageModal').find('#malayDecs').val('');
        $('#messageModal').find('#nepaliDecs').val('');
        $('#messageModal').modal('show');
        
        $('#messageForm').validate({
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
});

function edit(id){
    $.post( "php/getmessage.php", { messageId: id}, function( data ) {
        var decode = JSON.parse(data)
        
        if(decode.status === 'success'){
            $('#messageModal').find('#keyId').val(decode.message.id);
            $('#messageModal').find('#keyCode').val(decode.message.message_key_code);
            $('#messageModal').find('#englishDecs').val(decode.message.en);
            $('#messageModal').find('#chineseDecs').val(decode.message.zh);
            $('#messageModal').find('#malayDecs').val(decode.message.my);
            $('#messageModal').find('#nepaliDecs').val(decode.message.ne);
            $('#messageModal').modal('show');
            
            $('#messageForm').validate({
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
    });
}

function deletes(id){
    $.post( "php/deletemessage.php", { messageId: id}, function( data ) {
      var obj = JSON.parse(data);
            
      if(obj.status === 'success'){
          toastr["success"](obj.message, "Success:");
          $('#messageTable').DataTable().ajax.reload();
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
</script>
</body>
</html>
