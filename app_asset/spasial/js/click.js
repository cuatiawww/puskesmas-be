$('a[data-scroll="true"]').click(function(e){         
    var scroll_target = $(this).data('id');
    var scroll_trigger = $(this).data('scroll');
    
    if(scroll_trigger == true && scroll_target !== undefined){
        e.preventDefault();
        
        // $("#logo_bnpb").animate({
        // 	width: 30,
        // 	top: 5,
        // }, 1000);

        $("#peta_indo").animate({
        	left: -170,
        }, 1000);
        
        if (scroll_target == "#graph_table") {
            $('html, body').animate({
                scrollTop: $(scroll_target).offset().top-20,
            }, 1000);
        } else {
            $('html, body').animate({
                scrollTop: $(scroll_target).offset().top+20
            }, 1000);
        }
        
    }
                
});