function smallScreen() {
  if($(window).width()<993){
    //Student Favorites Page
    var $storeGridItem = $('#page-student-favorites').find('.store-grid-item');
    $storeGridItem.addClass('d-flex flex-column');
    $storeGridItem.find('.grid-item').removeClass('store-item');
    var $storeGridContent = $storeGridItem.find('.grid-content');
    $storeGridContent.removeClass('favorite-item-icon flex-row');
    $storeGridContent.addClass('flex-column');
    // Student Checkout Page
    var $checkoutGridItem = $('#page-student-checkout').find('.store-grid-item');
    $checkoutGridItem.addClass('d-flex flex-column');
    $checkoutGridItem.find('.grid-item').removeClass('store-item');
    var $checkoutGridContent = $checkoutGridItem.find('.grid-content');
    $checkoutGridContent.removeClass('favorite-item-icon flex-row');
    $checkoutGridContent.addClass('flex-column');
    var $footerGridItem = $('#page-student-checkout').find('.store-footer');
    $footerGridItem.removeClass('gutter-20');
    $footerGridItem.find('.yellow-grid').removeClass('favorite-item-icon');
  }
}
/*
$(document).ready(function() {
  smallScreen();
  // Store modal
  var $modal = $('#student-store-modal');
  var $product = $('#page-student-store').find('.store-grid-item');
  var $closeModal = $modal.find('.close-modal');
  $modal.css('display', 'none');
  $product.click(function() {
    $modal.css('display', 'block');
  });
  $closeModal.click(function() {
    $modal.css('display', 'none');
  });

});
$(window).resize(function(){
  smallScreen();

  var $modal = $('#student-store-modal');
  var $product = $('#page-student-store').find('.store-grid-item');
  var $closeModal = $modal.find('.close-modal');
  $modal.css('display', 'none');
  $product.click(function() {
    $modal.css('display', 'block');
  });
  $closeModal.click(function() {
    $modal.css('display', 'none');
  });
});

// Event Handlers
$('.jsStudentStoreBackButton').click(function() {
  console.log("submit clicked");
});
*/