j(document).ready(function(){
  var hideDelay = 500;  
  var currentID;
  var hideTimer = null;
  // One instance that's reused to show info for the current person
  var container = j('<div id="profilePreviewContainer">'
      + '</div>');

  j('body').append(container);
  j('.grid_view ul.item-list li').live('mouseover', function(){
      if (hideTimer)
          clearTimeout(hideTimer);
      var pos = j(this).offset();
      var width = j(this).width();
      container.css({
          left: (pos.left + width - 140) + 'px',
          top: pos.top - 145 + 'px',
          position: 'absolute'
      });
      var user_id = j(this).find('img.avatar').attr('id');
      j('#profilePreviewContainer').html('&nbsp;');
      j.post(ajaxurl,{'action': 'buatp_profile_preview' , 'user_id' : user_id  }, function(response){
          j('#profilePreviewContainer').html(response);
      })
      container.css('display', 'block');
  });

  j('.grid_view ul.item-list li').live('mouseout', function()
  {
      container.css('display', 'none');
  });

  // Allow mouse over of details without hiding details
  j('#profilePreviewContainer, #profilePreviewContainer div, #buatp_profile_proview, #buatp_profile_proview a, #buatp_profile_proview img,').live('mouseover',function()
  {
      container.css('display', 'block');
  });

  // Hide after mouseout
  j('#profilePreviewContainer').mouseout(function()
  {
      if (hideTimer)
          clearTimeout(hideTimer);
      hideTimer = setTimeout(function()
      {
          container.css('display', 'none');
      }, hideDelay);
  });
});