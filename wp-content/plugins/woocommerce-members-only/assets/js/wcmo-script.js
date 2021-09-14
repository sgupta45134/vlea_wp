(function($){
  $(document).ready(function(){
    $('body').on('change','.checkout .input-radio',function(){
      $('body').trigger('update_checkout');
    });
  });
})(jQuery);
