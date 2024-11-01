function changeImg(){
  jQuery('.ym_thumP li img').on('click',function(){
  var imgSrc = jQuery(this).attr('src'),
      num = jQuery('.ym_thumP li img').index(this);

  jQuery(".ym_thumP li").addClass('opacity');
  jQuery('.ym_thumP li').eq(num).removeClass('opacity');
  jQuery('.ym_mainP img').attr({src:imgSrc});
  jQuery('.ym_mainP img').stop().fadeOut('fast');
  jQuery('.ym_mainP img').fadeIn('slow');
  });
}