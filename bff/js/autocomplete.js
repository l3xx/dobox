/**
*    Examples 
*    minChars = Minimum characters the input must have for the ajax request to be made
*	 timeOut = Number of miliseconds passed after user entered text to make the ajax request   
*    validSelection = If set to true then will invalidate (set to empty) the value field if the text is not selected (or modified) from the list of items.
*    after, before = a function that will be caled before/after the ajax request
*/
jQuery.fn.autocomplete = function(url, settings) 
{
	return this.each( function()
	{
        settings = jQuery.extend( {
            minChars : 2, timeout: 500, progress: null, 
            after : null, before : null, validate: true, onSelect: false
        } , settings);
        
		var textInput = $(this);                 
        var valueInput = $(settings.valueInput);
		//create a new hidden input that will be used for holding the return value when posting the form, then swap names with the original input
		textInput.after('<ul class="autocomplete"></ul>').attr('autocomplete','off');
        if(settings.width == undefined) {
            settings.width = textInput.width() + 10;
        }
		var list = textInput.next().css({width: settings.width});
		var oldText = false;
		var typingTimeout;
		var size = 0;
		var selected = 0;   
        
		function getData(text)
		{
			window.clearInterval(typingTimeout);
			if (text != oldText && (settings.minChars != null && text.length >= settings.minChars))
			{
				clear();
				if(typeof settings.before == 'function') 
				    settings.before(textInput,text);

                textInput.addClass('autocomplete-progress');
				bff.ajax(url,{q: text},function(data)
				{   
					var items = '';
					if (data) {   
						size = 0;
                        for(key in data) {	
	                        items += '<li value="' + key + '">' + data[key].toString().replace(new RegExp("(" + text + ")","i"),"<strong>$1</strong>") + '</li>';
                            size++;
                        }         
                        if(size)
                        {            
                            list.html(items).show();
                            list.children().
                                hover(function() { $(this).addClass("selected").siblings().removeClass("selected");}, function() { $(this).removeClass("selected") } ).
                                click(function () { 
                                    valueInput.val( $(this).attr('value') ); 
                                    textInput.val( $(this).text() ); 
                                    if (typeof settings.onSelect == 'function') 
                                        settings.onSelect( valueInput.val(), textInput.val() );
                                    clear(); 
                                });
                        }
						if (typeof settings.after == 'function') 
						    settings.after(textInput, text);
					}
                    textInput.removeClass('autocomplete-progress');
				});
				oldText = text;
			}
		}
		
		function clear()
		{
            list.hide();
            size = 0;
            selected = 0;
		}	
		
        textInput.unbind("keyup"); 
		textInput.keyup(function(e) 
		{
			window.clearInterval(typingTimeout);
                
            switch(e.which)
            {
                case 27: case 9: clear(); break; //escape, tab to next element
                case 46: case 8: { //delete, backspace
                    clear(); 
                    //invalidate previous selection
                    if (settings.validate) valueInput.val('');

                    typingTimeout = window.setTimeout(function() { getData(textInput.val()) },settings.timeout);
                    if(settings.onSelect && textInput.val() == '' && (oldText===false || oldText != '')) {
                        oldText = '';
                        settings.onSelect( 0, '' );
                    }
                    return true;
                } break; 
                case 13:{ //enter 
                    if ( list.css('display') == 'none') { //if the list is not visible then make a new request, otherwise hide the list
                         getData(textInput.val());
                    } else {
                        clear();
                        if(valueInput.val()!='' && settings.onSelect) {
                            settings.onSelect( valueInput.val(), textInput.val() );
                        }
                    }
                    e.preventDefault();
                    return false;
                } break; 
                case 40: case 38: { //move up, down 
                    switch(e.which) 
                    {
                        case 40: 
                          selected = selected >= size - 1 ? 0 : selected + 1; break;
                        case 38:
                          selected = selected <= 0 ? size - 1 : selected - 1; break;
                        default: break;
                    }
                    //set selected item and input values
                    textInput.val( list.children().removeClass('selected').eq(selected).addClass('selected').text() );            
                    valueInput.val( list.children().eq(selected).attr('value') );
                } break; 
                default:{
                    //invalidate previous selection
                    if (settings.validate) valueInput.val('');
                    typingTimeout = window.setTimeout(function() { getData(textInput.val()) },settings.timeout);
                } break; 
            }
		});
        
        textInput.blur(function(){ window.setTimeout(function(){ clear(); },200);  });
        
        ;
	});
};
