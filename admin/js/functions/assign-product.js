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
                    temp["message"] = "requihidden field";
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


    
    // // SORTING 
    // $('.js-choices-list-order').sortable({
    //   items: 'tr',
    //   cursor: 'move',
    //   placeholder: "ui-state-highlight",
    //   axis: 'y',
    //   update: function(e, ui) {
    //     // var test = $(this);
    //     var sorted = $(this).sortable( "serialize");
    //     // var params = {};
    //     // params = $(this).find('.js-assign-product-id').serializeArray(),
    //     console.log(sorted);
    //     var data = { "action": "unid_product_sorted_assign_product", "order" : sorted};
    //     //send the data through ajax
    //     jQuery.ajax({
    //       type: 'POST',
    //       url: ajaxurl,
    //       data: data,
    //       cache: false,
    //       dataType: "html",
    //       success: function(data){
    //         console.log(data);
    //         $('#campaign_shipping_option').html(data);
    //       },
    //       error: function(html){

    //       }
    //     });
    //   }
    // });


    // PAGINATION UN1-T128
    


    
    
})(jQuery); 


jQuery(window).on('load', function() {
    event.preventDefault();
    paginationAssignProduct();
});




function paginationAssignProduct(event) {
     jQuery('.js-product-assign-wrapper').each(function(index, el) {
        var parent = jQuery(el);
        var post = parent.find('.js-product-assign-tr');
        var pagination = parent.find('.tablenav-pages');
        var input = pagination.find('input');
        var displayingNum = pagination.find('.displaying-num span');
        var totalPages = pagination.find('.total-pages');

        var prev = parent.find('.prev-page');
        var next = parent.find('.next-page');

        var countPost = post.length; //всего записей
        var cnt = dataAssign.per_page; // сколько отображаем сначала
        var cnt_page = Math.ceil(countPost / cnt); //кол-во страниц

        hidePaginationAssignProduct(cnt_page,pagination);

        displayingNum.html(countPost);
        totalPages.html(cnt_page);

        // console.log(post);

        //выводим первые записи {cnt}
        for (var t = 0; t < countPost; t++) {
          if (t < cnt) {
            post[t].classList.remove('hidden');
          }
        }

        var i = 1;
        var valueInput =input.val(i);


        parent.on('click', '.prev-page', function(event) {
            event.preventDefault();
            if (i <= 1) return;
            i--;

            if (jQuery(this).hasClass('first-page')) {
                i = fist_last_page(1);
            }

            input.val(i);


            prevStep(i, cnt, post);
        });

        parent.on('click', '.next-page', function(event) {

            event.preventDefault();
            if (maxPage(i,cnt_page)) return;
            i++;
            if (jQuery(this).hasClass('last-page')) {
                i = fist_last_page(cnt_page);
            }
            input.val(i);

            
            nextStep(i, cnt, post);

        });


        input.on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
              if (keyCode === 13) { 
                if (maxPage(Number(jQuery(this).val()), cnt_page)){ jQuery(this).val(cnt_page)}; 
                if (Number(jQuery(this).val() <= 1)) jQuery(this).val(1);

                i = jQuery(this).val();

                nextStep(i, cnt, post);

                input.val(i);
                e.preventDefault();
                return false;

              }
            
        });


    });
}

function hidePaginationAssignProduct(page, pagination) {
    if (page === 1) 
        pagination.hide(0);
    else
        pagination.show(0);
}

function maxPage($i,$cnt_page) {
    if ($i >= $cnt_page) {return true};
}

function fist_last_page($num) {
    return $num;
}

function nextStep(i, cnt, post) {
    post.addClass('hidden');
    console.log(post);
    var j = 0;
    var i = i - 1;
    for (var k = (cnt * i); k < ((cnt * i) + cnt); k++) {
        if (j >= cnt || post[k] == undefined) break;
        console.log(post[k]);
        post[k].classList.remove('hidden');
        j++;
    }
}

function prevStep(i, cnt, post) {
    post.addClass('hidden');
    var j = cnt;
    var i = i - 1;
    for (var k = (cnt * i); k < ((cnt * i) + cnt); k++) {
        if (j <= 0) break;
        post[k].classList.remove('hidden');
        j--;
    }
}








