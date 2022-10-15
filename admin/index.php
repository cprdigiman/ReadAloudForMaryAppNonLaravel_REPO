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
  <body id="admin-products" class="bg-dots loading">
  	
<div class="row">
    <div class="col-md-12"><h2 class="text-center" style="margin:50px;">Secret Surprise Sacks Administration</h2></div>
</div>
<div class="row">&nbsp;</div>
<div class="row justify-content-md-center">
    <div class="col-md-offset-2 col-md-2" style="text-align:center;"><a href="schools.php" class="btn btn-info">
        <i class="fa fa-school fa-2x"></i></a><br />Schools
    </div>
    
    <div class="col-md-2" style="text-align:center;"><a href="products.php" class="btn btn-info">
        <i class="fa fa-box-open fa-2x" style="color:white"></i></a><br />Prizes
    </div>
    <div class="col-md-2" style="text-align:center;"><a href="logout.php" class="btn btn-danger">
        <i class="fa fa-sign-out-alt fa-2x" style="color:white"></i></a><br />Log Out
    </div>
</div>

  	<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="addConsentModalLabel">New Product</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <form id="upload-item" method="post" action="/item/add">
	          <div class="form-group">
	            <label for="classname" class="col-form-label">Name</label>
	            <input type="text" class="form-control" id="new-name" name="new-name">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="itemsku" class="col-form-label">SKU/Item #</label>
	            <input type="text" class="form-control" id="new-sku" name="new-sku">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="classname" class="col-form-label">Points</label>
	            <input type="number" class="form-control" id="new-points" name="new-points">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="startdate" class="col-form-label">Quantity</label>
	            <input type="number" class="form-control" id="new-avail" name="new-avail">
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="form-group">
	            <label for="new-description" class="col-form-label">Description</label>
	            <textarea class="form-control" id="new-description" name="new-description"></textarea>
	            <div class="invalid-feedback">Required</div>
	          </div>
	          <div class="custom-file">
	          	<label for="new-image" class="custom-file-label">Choose Image File</label>
	          	<input type="file"  class="custom-file-input" name="new-image" id="new-image">
	          	<div class="invalid-feedback">Required</div>
	          </div>
	        </form>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-primary saveitem">Save Product</button>
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
 
  	</style>
	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
  	<script src="../js/scripts.js"></script>
  	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
  	<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
    <script>
      var dt;
      $(document).ready(function(){
      	
      });
  	</script>
  </body>
  </html>