<?php

session_start();
if($_SESSION['user']=='' || !isset($_SESSION['user'])) {
	header('location:login.php');
}
?>
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css" integrity="sha384-KA6wR/X5RY4zFAHpv/CnoG2UW1uogYfdnP67Uv7eULvTveboZJg0qUpmJZb5VqzN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="css/style.css">
  </head>
  <body id="admin-schools" class="bg-dots loading">
  	
  	<div class="table-wrapper">
  		<h1>Schools <button class="btn btn-info addschool">Add New</button></h1>
	  	<table id="schoolsdb" class="table table-striped table-hover table-bordered">
	  		<thead>
	  			<tr><th>Name</th><th>Year</th><th>Classes</th><th>Status</th><th>&nbsp;</th></tr>
	  		</thead>
	  		<tbody>
	  		</tbody>
	  	</table>
  	</div>

  	<div class="modal fade" id="addSchoolModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="addConsentModalLabel">Add School</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <form id="upload-item" method="post" action="/item/add">
	          <div class="form-group">
	            <label for="name" class="col-form-label">School Name</label>
	            <input type="text" class="form-control" id="name" name="name">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="year" class="col-form-label">Read-a-Thon Year</label>
	            <input type="text" class="form-control" id="year" name="year">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="ptalogin" class="col-form-label">PTA Login</label>
	            <input type="text" class="form-control" id="ptalogin" name="ptalogin">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="ptapassword" class="col-form-label">PTA Password</label>
	            <input type="text" class="form-control" id="ptapassword" name="ptapassword">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="studentlogin" class="col-form-label">Student Login</label>
	            <input type="text" class="form-control" id="studentlogin" name="studentlogin">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="studentpassword" class="col-form-label">Student Password</label>
	            <input type="text" class="form-control" id="studentpassword" name="studentpassword">
	            <div class="invalid-feedback">Required</div>
	          </div>
	        </form>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-primary savenewschool">Save New School</button>
	        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="addConsentModalLabel">Edit Product</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <form id="edit-item" method="post" action="/item/update">
	          <div class="form-group">
	            <label for="classname" class="col-form-label">Name</label>
	            <input type="text" class="form-control" id="itemname" name="itemname">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="itemsku" class="col-form-label">SKU/Item #</label>
	            <input type="text" class="form-control" id="itemsku" name="itemsku">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="classname" class="col-form-label">Points</label>
	            <input type="number" class="form-control" id="itempoints" name="itempoints">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="startdate" class="col-form-label">Quantity</label>
	            <input type="number" class="form-control" id="itemavail" name="itemavail">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="new-description" class="col-form-label">Description</label>
	            <textarea class="form-control" id="itemdescription" name="itemdescription"></textarea>
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="custom-file">
	          	<label for="new-image" class="custom-file-label">Choose New Image File</label>
	          	<input type="file"  class="custom-file-input" name="itemimage" id="itemimage">
	          	<p>(Leave blank to keep same image)</p>
	          	<div class="invalid-feedback">Required</div>
	          </div>
	          <input type="hidden" name="itemid" id="itemid" value="0">
	        </form>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-primary saveitemchanges">Save Changes</button>
	        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div class="modal fade" id="deleteVerifyModal" tabindex="-1" role="dialog" aria-labelledby="deleteVerifyModal" aria-hidden="true">
		<div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="addConsentModalLabel">Delete <span class="productname"></span>?</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	      	<p>Are you sure you want to delete this product?  This cannot be undone.</p>
	      </div>
	      <div class="modal-footer">
	      	<button type="button" class="btn btn-danger deleteitem">Yes, Delete Product</button>
	        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
	      </div>
	  </div>
	  </div>
	</div>

  	<style>
  		#productsdb img {
  			width:100px;
  		}
  		table {
  			width:100%;
  		}
  		.table-wrapper {
  			width:80%;
  			margin:50px auto;
  		}
  		span.fa.fa-trash-alt { 
  			width:18px;
  		}
  		.actions {
  			width:100px;
  		}
  		input[type="file"]+img{
  			width:100px;
  		}
  		.modal-body {
  			padding-bottom:50px;
  		}
  	</style>
	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
  	<script src="../js/scripts.js"></script>
  	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
  	<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
    <script>
      var dt;
      $(document).ready(function(){
      	$.ajax({
              type:'POST',
                  url: "/schools",
                  dataType:'json'
              }).done(function(response) {
                console.log(response);
                $.each(response.schools,function(k,v){
                  var item='<tr data-sid="'+v.id+'"><td><a href="school.php?id='+v.id+'">'+v.name+'</a></td><td>'+v.year+'</td><td>'+v.classes+'</td><td>'+v.status+'</td><td><div class="actions"><button data-sid="'+v.id+'" class="btn btn-primary editschool"><span class="fa fa-edit"></span></button> <button data-sid="'+v.id+'" class="btn btn-danger archivecheck" data-name="'+v.name+'"><span class="fa fa-trash-alt"></span></button></div></td></tr>';
                  $('#schoolsdb tbody').append(item);
                });
                dt=$("#schoolsdb").dataTable({
			      "pageLength": 25
			    });
              });
        
        $('.addschool').click(function(){
        	$('#addschoolmodal input').val('');
        	$('.savenewschool').data('sid','');
        	$('#addSchoolModal .modal-title').text('Add School');
        	$('#addSchoolModal').modal('show');
        });
        $('.savenewschool').click(function(){
        	var data={
        		name:$('#name').val(),
        		year:$('#year').val(),
        		ptalogin:$('#ptalogin').val(),
        		ptapassword:$('#ptapassword').val(),
        		studentlogin:$('#studentlogin').val(),
        		studentpassword:$('#studentpassword').val()
        	}
        	console.log(data);
        	var url='/schools/add';
        	if($(this).data('sid')!=='') {
        		url='/schools/edit';
        		data.sid=$('.savenewschool').data('sid');
        	}
        	$.ajax({
        		type:'POST',
        		url:url,
        		data:data
        	}).done(function(response) {
        		console.log(response);
        		window.location.href='schools.php';
        	});
        });
        $('body').on('click','.editschool',function(){
        	console.log('edit school');
        	var data={
        		id: $(this).data('sid')
        	};
        	$.ajax({
        		type:'POST',
        		url:'/school',
        		data:data
        	}).done(function(r) {
        		var v=r.schools[0];
        		$('#name').val(v.name);
        		$('#year').val(v.year);
        		$('#ptalogin').val(v.login);
        		$('#ptapassword').val(v.password);
        		$('#studentlogin').val(v.student_login);
        		$('#studentpassword').val(v.student_password);
        		$('#addSchoolModal .modal-title').text('Edit School');
        		$('.savenewschool').data('sid',v.id);
        		$('#addSchoolModal').modal('show');
        	});
        });

      });
  	</script>
  </body>
  </html>