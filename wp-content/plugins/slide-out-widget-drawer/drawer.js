jQuery(document).ready(function($){

  var wrapper = '.slide-out-widget-wrapper',
      title = wrapper + '.closed .slide-out-widget:first-child .slide-out-widget-title';

  //add default title if none is present
  if( !$(title).length ){
    var defaultTitle = $(wrapper).data('default');
    $(wrapper).prepend('<div class="slide-out-widget"><h2 class="slide-out-widget-title">' + defaultTitle + '</h2></div>');
  }

  //title click toggle
  $(title).click(function(){
    $('.slide-out-widget-wrapper').toggleClass('open');
  });

  //handle click outside
  $(document).click(function(event) {
    if(!$(event.target).closest(wrapper).length && $(wrapper).hasClass("open")) {
      $('.slide-out-widget-wrapper').removeClass('open');
    }
  });
});