
(function ($) {
	$(function () {
		var form = $('#login-form');
			
		if (form.length > 0) {
			form.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em"
			});
		}
		
		var groups = $('.group', form).filter(function(){
			return !$(this).hasClass('submit');
		}).click(function(){
			$(groups).removeClass('active');
			$(this).addClass('active');
		});
		$('#name').trigger('click').trigger('focus');
		
	});
})(jQuery);

$('#spinner').hide();
$('#status').html('Systems ready' );

function doLogin() {
    
    $("body").css('cursor','wait');
    $('#status').html('loading');
    $('#spinner').show();
    $("#btnSubmit").attr("disabled", true);
    
    $.ajax({
          type: "POST",
          url: "login.php",
          data: { "mobileNumber":$('#mobileNumber').val(), "idnumber":$('#idnumber').val()  },
          error: function (jqXHR, exception) {
            $('#status').html('error occured');
            $("#btnSubmit").attr("disabled", false);
          },
          success: function(data){
               $('#status').html('data.result:' + data.result);
              if(data.result === 1) {
                  $('#spinner').hide();
                  window.location.replace("learningStatement-mobile.php");
              } else {
                  $('#spinner').hide();
                  $("body").css('cursor','pointer');
                  $('#status').html(data.error);
                  $("#btnSubmit").attr("disabled", false);
                  
              }
          }
          
        });
    
}