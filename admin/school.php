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
  	
  	

  	<div class="row">
	  	<div class="table-wrapper">
	  		<div class="row">
		  		<h1><span class="schoolname">School Name</span> - <span class="schoolyear">School Year</span></h1>
		  	</div>
		  	<div class="row" style="margin-top:50px;margin-bottom:50px;background: #eee;
    padding: 10px;">
		  		<div class="col-md-4 text-center">
		  			<button class="btn btn-info schoolreport">School Order Summary Report</button>
		  		</div>
		  		<div class="col-md-4 text-center">
		  			<button class="btn btn-info classreport">Orders By Class Report</button>
		  		</div>
		  		<div class="col-md-4 text-center">
		  			<button class="btn btn-info labelscsv">Download Labels CSV</button>
		  		</div>
		  	</div>
		  	<div class="row">
		  		<div class="form-group col-md-4">
		            <label for="name" class="col-form-label">School Name</label>
		            <input type="text" class="form-control" id="name" name="name">
		            <div class="invalid-feedback">Required</div>
		          </div>
		          <div class="form-group col-md-4">
		            <label for="year" class="col-form-label">Read-a-Thon Year</label>
		            <input type="text" class="form-control" id="year" name="year">
		            <div class="invalid-feedback">Required</div>
		          </div>
		          <div class="form-group col-md-4">
		            <label for="status" class="col-form-label">Status</label>
		            <select class="form-control" name="status" id="status">
		            	<option value="Pending">Pending</option>
		            	<option value="Points">Points</option>
		            	<option value="Ordering">Ordering</option>
		            	<option value="Fulfillment">Fulfillment</option>
		            	<option value="Completed">Completed</option>
		            </select>
		            <div class="invalid-feedback">Required</div>
		          </div>
		          <div class="form-group col-md-4" style="border-top:1px solid #ddd;">
		            <label for="ptalogin" class="col-form-label">PTA Login</label>
		            <input type="text" class="form-control" id="ptalogin" name="ptalogin">
		            <div class="invalid-feedback">Required</div>
		            <label for="ptapassword" class="col-form-label">PTA Password</label>
		            <input type="text" class="form-control" id="ptapassword" name="ptapassword">
		            <div class="invalid-feedback">Required</div>
		          </div>

		          <div class="form-group col-md-4" style="border-left:1px solid #ddd;border-top:1px solid #ddd;">
		            <label for="studentlogin" class="col-form-label">Student Login</label>
		            <input type="text" class="form-control" id="studentlogin" name="studentlogin">
		            <div class="invalid-feedback">Required</div>
		            <label for="studentpassword" class="col-form-label">Student Password</label>
		            <input type="text" class="form-control" id="studentpassword" name="studentpassword">
		            <div class="invalid-feedback">Required</div>
		          </div>


		  		<div class="statuses col-md-4">
		  			<p><strong>Pending</strong> - the default status. Indicates a school is in the database but their Read-a-Thon has not yet started.</p>
		  			<p><strong>Points</strong> - PTA, teachers, and parents may enter donation points, class reading points, and home reading points.  The prize store is available for adding favorites, but orders may not be submitted yet.</p>
		  			<p><strong>Ordering</strong> - The prize store is open and students may submit their prize orders. Points cannot be changed.</p>
		  			<p><strong>Fulfillment</strong> - The prize store is closed and ordering is completed.  Reports are being pulled and orders are being fulfilled.</p>
		  			<p><strong>Completed</strong> - Prizes have been delivered to the school. The Read-a-Thon process is completed.</p>
		  		</div>
		  		<div class="col-md-12 text-center" style="padding:10px;background:#eee;">
		  			<button class="btn btn-primary updateschool">Save School Changes</button>
		  		</div>
		  	</div>
		  	<table id="classesdb" class="table table-striped table-hover table-bordered">
		  		<thead>
		  			<tr><th>Teacher</th><th>Grade</th><th>Students</th><th>&nbsp;</th></tr>
		  		</thead>
		  		<tbody>
		  		</tbody>
		  	</table>
		  	
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
  		#classesdb_wrapper {
  			margin-top:50px;
  		}
  		.statuses {
  			font-size:10px;
  		}
  		label {
  			font-weight:600;
  		}
  	</style>
	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
  	<script src="../js/scripts.js"></script>
  	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
  	<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
    <script>
      	var dt;
    	function getVar(variable){
	       var query = window.location.search.substring(1);
	       var vars = query.split("&");
	       for (var i=0;i<vars.length;i++) {
	               var pair = vars[i].split("=");
	               if(pair[0] == variable){return pair[1];}
	       }
	       return(false);
		}
      $(document).ready(function(){
      	var data={
      		school_id: getVar('id')
      	};
      	$.ajax({
              type:'POST',
                  url: "/classes",
                  data:data,
                  dataType:'json'
              }).done(function(response) {
                console.log(response);
                $('.schoolname').text(response.school_name);
                $('.schoolyear').text(response.year);
                $('#name').val(response.school_name);
                $('#year').val(response.year);
                $('#status').children('[value="'+response.status+'"]').prop('selected',true);
                console.log('[value="'+response.status+'"]');
                console.log($('#status').children('[value="'+response.status+'"]'));
                $('#ptalogin').val(response.ptalogin);
                $('#ptapassword').val(response.ptapassword);
                $('#studentlogin').val(response.studentlogin);
                $('#studentpassword').val(response.studentpassword);
                $.each(response.classes,function(k,v){
                	var grade=v.grade;
                	if(v.grade=='0') {
                		grade='K';
                	}
                  var item='<tr data-sid="'+v.id+'"><td>'+v.name+'</td><td>'+grade+'</td><td>'+v.students+'</td><td><div class="actions"><button data-sid="'+v.id+'" class="btn btn-primary editschool"><span class="fa fa-edit"></span></button> <button data-sid="'+v.id+'" class="btn btn-danger archivecheck" data-name="'+v.name+'"><span class="fa fa-trash-alt"></span></button></div></td></tr>';
                  $('#classesdb tbody').append(item);
                });
                dt=$("#classesdb").dataTable({
			      "pageLength": 25
			    });
              });
        
        $('.schoolreport').click(function(){
        	window.open('/reports/schoollist?sid='+getVar('id'),'_blank');
        });
        $('.classreport').click(function(){
        	window.open('/reports/classlist?sid='+getVar('id'),'_blank');
        });
        $('.labelscsv').click(function(){
        	window.open('/reports/csv?sid='+getVar('id'),'_blank');
        });
        $('.updateschool').click(function(){
        	var data={
        		sid: getVar('id'),
        		name:$('#name').val(),
        		year:$('#year').val(),
        		ptalogin:$('#ptalogin').val(),
        		ptapassword:$('#ptapassword').val(),
        		studentlogin:$('#studentlogin').val(),
        		studentpassword:$('#studentpassword').val(),
        		status:$('#status').val()
        	};
        	$.ajax({
        		type:'POST',
        		url:'/schools/editwithstatus',
        		data:data,
        		dataType:'json'
        	}).done(function(response) {
        		window.location.href='school.php?id='+getVar('id');
        	});
        });
      });
  	</script>
  </body>
  </html>