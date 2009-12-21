$(function()
{
  $('#sympal_toggle_editor').css('height', $('#sympal_editor').css('height'));

  if ($.cookie('sympal_editor_open') === null)
  {
    $.cookie('sympal_editor_open', 'false');
  }

  if ($.cookie('sympal_editor_open') == 'true')
  {
    $('div#sympal_editor').css({marginLeft:'-20px'});
  }

  hiConfig = {
      sensitivity: 1, // number = sensitivity threshold (must be 1 or higher)
      interval: 100, // number = milliseconds for onMouseOver polling interval
      timeout: 100, // number = milliseconds delay before onMouseOut
      over: function() {
          if ($.cookie('sympal_editor_open') == 'true')
          {
            $.cookie('sympal_editor_open', 'false');
            $('div#sympal_editor').animate({marginLeft:'-500px'}, 'slow');
          }
          else
          {
            $.cookie('sympal_editor_open', 'true');
            $('div#sympal_editor').animate({marginLeft:'-20px'}, 'slow');
          }
          
      }
  }
  $('#sympal_toggle_editor').hoverIntent(hiConfig)
});