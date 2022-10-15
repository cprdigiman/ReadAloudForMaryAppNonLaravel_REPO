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
  	
  	<div class="table-wrapper">
  		<h1>Products Database <button class="btn btn-info addproduct">Add New</button></h1>
	  	<table id="productsdb" class="table table-striped table-hover table-bordered">
	  		<thead>
	  			<tr><th>Image</th><th>Name</th><th>Description</th><th>Points</th><th>Quantity</th><th>&nbsp;</th></tr>
	  		</thead>
	  		<tbody>
	  		</tbody>
	  	</table>
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
                  url: "/items",
                  dataType:'json'
              }).done(function(response) {
                console.log(response);
                $.each(response.items,function(k,v){
                  var item='<tr data-itemid="'+v.id+'"><td><img src="/img/items/'+v.id+'.jpg?'+new Date().getTime()+'"></td><td>'+v.name+'</td><td>'+v.description+'</td><td>'+v.points+'</td><td>'+v.avail+'</td><td><div class="actions"><button data-itemid="'+v.id+'" class="btn btn-primary edititem"><span class="fa fa-edit"></span></button> <button data-itemid="'+v.id+'" class="btn btn-danger deleteitemcheck" data-name="'+v.name.replace(/"/g, '&quot;')+'"><span class="fa fa-trash-alt"></span></button></div></td></tr>';
                  $('#productsdb tbody').append(item);
                });
                dt=$("#productsdb").dataTable({
			      "pageLength": 25
			    });
              });
        $('.addproduct').click(function(){
        	$('#addProductModal').modal('show');
        });

        $('.deleteitem').click(function(){
        	var delrow=$(this).parents('tr');
        	console.log('delrow before response:',delrow);
        	var data={
        		itemid:$(this).data('id')
        	};
        	$.ajax({
        		type:'POST',
        		url:'/items/delete',
        		data: data,
        		dataType:'json'
        	}).done(function(response) {
        		console.log(response);
        		var table = $('#productsdb').DataTable();
        		var bob=$('.deleteitemcheck[data-itemid="'+data.itemid+'"]').parents('tr');
        		table.row(bob).remove().draw();
        		$('#deleteVerifyModal').modal('hide');
        	})
        });
        $("input[type='file']").change(function(e) {

		    for (var i = 0; i < e.originalEvent.srcElement.files.length; i++) {
		        var file = e.originalEvent.srcElement.files[i];
		        var img = document.createElement("img");
		        var reader = new FileReader();
		        reader.onloadend = function() {
		             img.src = reader.result;
		             $(img).addClass('imgpreview');
		             var fn=$('#new-image').val();
		             var ar=fn.split('\\');
		             $('.custom-file-label').text(ar[2]);
		        }
		        reader.readAsDataURL(file);
		        $('input[type="file"]').after(img);
		    }
		});
		$('.saveitem').click(function(){
			$('#upload-item').submit();
		});

		$('body').on('click','.edititem',function(){
			//get item id
			var itemid=$(this).data('itemid');
			//send item id to api
			var data={
				itemid:itemid
			}
			$.ajax({
				type:'POST',
				url:'/getitem',
				data:data,
				dataType:'json'
			}).done(function(response){
				//get back data
				console.log(response);
				//fill the fields
				$('.saveitemchanges').data('itemid',response.item[0].id);
				$('#itemname').val(response.item[0].name);
				$('#itemdescription').val(response.item[0].description);
				$('#itempoints').val(response.item[0].points);
				$('#itemavail').val(response.item[0].avail);
				$('#itemid').val(response.item[0].id);
				$('#itemsku').val(response.item[0].sku);
				//show the modal
				$('#editProductModal').modal('show');
			});
		});

		$('.saveitemchanges').click(function(){
			$('#edit-item').submit();
		})

		$('#edit-item').submit(function(e){
			e.preventDefault();
			var itemid=$('#itemid').val();
			var formdata = new FormData(this);
			$.ajax({
	            url: "/updateitem",
	            type: "POST",
	            data: formdata,
	            mimeTypes:"multipart/form-data",
	            contentType: false,
	            cache: false,
	            processData: false,
	            success: function(v){
	            	console.log('Response:',v);
	            	//update row
	            	var table = $('#productsdb').DataTable();
	            	table.cell($('td:nth-child(2)','tr[data-itemid="'+itemid+'"]')).data(v.name);
	            	table.cell($('td:nth-child(3)','tr[data-itemid="'+itemid+'"]')).data(v.description);
	            	table.cell($('td:nth-child(4)','tr[data-itemid="'+itemid+'"]')).data(v.points);
	            	table.cell($('td:nth-child(5)','tr[data-itemid="'+itemid+'"]')).data(v.avail);
	            	$('#editProductModal').modal('hide');
	            	//now fix image to new one
	            	var sam=$('td:nth-child(1)','tr[data-itemid="'+itemid+'"]');
	            	var bob=table.cell(sam).nodes()[0];
	            	var fred=$('img',bob).attr('src') + '?' + new Date().getTime();
	            	$('img',bob).attr('src',fred);
	            },error: function(err){
	                console.log(err);
	            }
	         });
		});



		$('body').on('click','.deleteitemcheck',function(){
				$('.productname').text($(this).data('name'));
				$('.deleteitem').data('id',$(this).data('itemid'));
				$('#deleteVerifyModal').modal('show');
			});
		$('#upload-item').submit(function(e){
			e.preventDefault();

			var err=0;
			if($('#new-name').val()=='') {
				$('#new-name').addClass('is-invalid');
				err++;
			}
			if($('#new-description').val()=='') {
				$('#new-description').addClass('is-invalid');
				err++;
			}
			if($('#new-avail').val()=='') {
				$('#new-avail').addClass('is-invalid');
				err++;
			}
			if($('#new-points').val()=='') {
				$('#new-points').addClass('is-invalid');
				err++;
			}
			if($('#new-image').val()=='') {
				$('#new-image').addClass('is-invalid');
				err++;
			}
			if(err>0) {
				return false;
			}

			var formdata = new FormData(this);
			$.ajax({
	            url: "/items/add",
	            type: "POST",
	            data: formdata,
	            mimeTypes:"multipart/form-data",
	            contentType: false,
	            cache: false,
	            processData: false,
	            success: function(v){
	            	console.log('Response:',v);
	                var item='<tr data-itemid="'+v.id+'"><td><img src="/img/items/'+v.id+'.jpg"></td><td>'+v.name+'</td><td>'+v.description+'</td><td>'+v.points+'</td><td>'+v.avail+'</td><td><div class="actions"><button class="btn btn-primary"><span class="fa fa-edit"></span></button> <button class="btn btn-danger"><span class="fa fa-trash-alt"></span></button></div></td></tr>';
	                var table = $('#productsdb').DataTable();
 
					table.row.add( ['<img src="/img/items/'+v.id+'.jpg">',v.name,v.description,v.points,v.avail,'<div class="actions"><button data-itemid="'+v.id+'" class="btn btn-primary edititem"><span class="fa fa-edit"></span></button> <button data-itemid="'+v.id+'" class="btn btn-danger deleteitemcheck"><span class="fa fa-trash-alt" data-name="'+v.name.replace(/"/g, '&quot;')+'"></span></button></div>']
					     ).draw();
					$('#new-name').val('').removeClass('is-invalid');
					$('#new-description').val('').removeClass('is-invalid');
					$('#new-points').val('').removeClass('is-invalid');
					$('#new-avail').val('').removeClass('is-invalid');
					$('#new-image').val('').removeClass('is-invalid');
					$('.custom-file-label').text('Choose Image File');
					$('.imgpreview').detach();
	            },error: function(err){
	                console.log(err);
	            }
	         });

			
		});
      });
  	</script>
  </body>
  </html>