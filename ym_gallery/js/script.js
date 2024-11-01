jQuery(function(){
    jQuery('#tabmenu').tabs();

    jQuery('#showSet').on('click',function(){
    	jQuery('.setTable').toggle('fast');	
    });

    jQuery('#showShort').on('click',function(){
    	jQuery('.setShort').toggle('fast');	
    });

});

