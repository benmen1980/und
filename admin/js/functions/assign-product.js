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
    
    $(window).on('load', function() {
        event.preventDefault();

        $('.js-product-assign-wrapper').each(function(index, el) {
            var parent = $(el);
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

                if ($(this).hasClass('first-page')) {
                    i = fist_last_page(1);
                }

                input.val(i);


                prevStep(i, cnt, post);
            });

            parent.on('click', '.next-page', function(event) {

                event.preventDefault();
                if (maxPage(i,cnt_page)) return;
                i++;
                if ($(this).hasClass('last-page')) {
                    i = fist_last_page(cnt_page);
                }
                input.val(i);

                
                nextStep(i, cnt, post);

            });


            input.on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                  if (keyCode === 13) { 
                    if (maxPage(Number($(this).val()), cnt_page)){ $(this).val(cnt_page)}; 
                    if (Number($(this).val() <= 1)) $(this).val(1);

                    i = $(this).val();

                    nextStep(i, cnt, post);

                    input.val(i);
                    e.preventDefault();
                    return false;

                  }
                
            });


        });

        // function disabled(btn) {
        //     btn.addClass('disabled');
        // }
        // function undisabled(btn) {
        //     btn.removeClass('disabled');
        // };

        function maxPage($i,$cnt_page) {
            if ($i >= $cnt_page) {return true};
        }
        function fist_last_page($num) {
            return $num;
        }

        function nextStep(i, cnt, post) {
            post.addClass('hidden');
            var j = 0;
            var i = i - 1;
            for (var k = (cnt * i); k < ((cnt * i) + cnt); k++) {
                if (j >= cnt) break;
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



        function pagination(event) {
          
          // var num_ = id.substr(4);
          // var data_page = +target.dataset.page;
          // main_page.classList.remove("paginator_active");
          // main_page = document.getElementById(id);
          // main_page.classList.add("paginator_active");

          // var j = 0;
          // for (var i = 0; i < div_num.length; i++) {
          //   var data_num = div_num[i].dataset.num;
          //   if (data_num <= data_page || data_num >= data_page)
          //     div_num[i].style.display = "none";

          // }
          // for (var i = data_page; i < div_num.length; i++) {
          //   if (j >= cnt) break;
          //   div_num[i].style.display = "block";
          //   j++;
          // }
        }
        





        
    });

    
    
})(jQuery); 








