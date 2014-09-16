var j = jQuery;


j(document).ready(function(){
    
/////////////////////////////////////////////////////////////////////////////////////

    j('.buat_type_field_selection').live('change',function(){
        var selected = j(this).val();
        if(selected == 'null')
            return;
        var html = '<form id="hidden_form" method="post" action=""><input type="hidden" name="buat_selected_field" value="'+selected+'" /></form>';
        j('#buat_hidden_fields').html(html);
        j('form#hidden_form').submit();
    });

/////////////////////////////////////////////////////////////////////////////////////

    j('.buat_role_to_type').parent().parent().hide();
    j('.buat_manage_existing_users').live('click',function(){
       if( j(this).val() ==  'role_to_type' )
            j('.buat_role_to_type').parent().parent().slideDown(1000);
       else
            j('.buat_role_to_type').parent().parent().slideUp(1000);
    });


/////////////////////////////////////////////////////////////////////////////////////

  j('div#buat_view_mode img').click(function(){
     var view = j(this).attr('id');
     j('#buat_members').removeClass().addClass(view); 
     j('div#buat_view_mode img').each(function(){
       j(this).removeClass();  
     })
     j(this).addClass('current_view');
  });
  
  /////////////////////////////////////////////////////////////////////////////////////
  
  
  
}) 