(function($) {
    $(document).ready(function(){
        var input = $(".js-assign-product-warehouse");
        acf.add_filter('validation_complete', function( json, $form ){
			
            // if errors?
            // console.log(json);
            input.each(function(index, el) {
                var $this = $(el);
                if( !$this.val().length ) {
                
                    var temp = new Object();
                    temp["input"] = $this.attr('name');
                    // temp["input"] = $this.attr('name');
                    temp["message"] = "required field";
                    error($this);

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
            });
            
            // return
            return json;
                    
        });
        function error($this) {
        	// setTimeout(function () {
                $this.addClass('error');
                // $('html, body').animate({
                //     scrollTop: $('body').offset().top
                // }, 400);
            // }, 100);
        }
        input.focus(function(event) {
        	$(this).removeClass('error');
        });
        
    });

    // $('.choices-list').sortable({
    //   cursor: 'move',
    //   axis: 'y',
    //   update: function(e, ui) {
    //     var test = $(this).sortable('refresh');
    //     console.log(test);
    //     var params = {};
    //     params = $(this).find('input').serializeArray(),
    //     console.log(params);
    //       // $.ajax({
    //       //   type: 'POST',
    //       //   data: {
    //       //     id : params
    //       //   },
    //       //   url: 'Your url',
    //       //   success: function(msg) {
    //       //     // Your sorted data.
    //       //   }
    //       // });
    //   }
    // });
    
    
})(jQuery); 








