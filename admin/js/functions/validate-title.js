(function($) {

    $(document).ready(function(){
        var input = $("input#title");
        acf.add_filter('validation_complete', function( json, $form ){
			
            // if errors?
            // console.log(json);
            if( !input.val().length ) {
            	
                var temp = new Object();
                temp["input"] = "_post_title";
                temp["message"] = "A Title is required";
                error();

                // if no error
                if(json.errors == 0){
                	// set a new array
                	json.errors = new Array();
                }
                // set valid to 0 instead of 1
                json.valid = 0;
                // push the error
                json.errors.push(temp);
            }
            // console.log($form);
            
            // return
            return json;
                    
        });
        function error() {
        	// setTimeout(function () {
                input.addClass('error');
                // $('html, body').animate({
                //     scrollTop: $('body').offset().top
                // }, 400);
            // }, 100);
        }
        input.focus(function(event) {
        	$(this).removeClass('error');
        });
        
    });
    
})(jQuery); 







