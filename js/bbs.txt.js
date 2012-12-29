
var bffDynpropsTextify = (function(){
    var types = {text: 1, textarea: 2, wysiwyg: 3, radioyesno: 4, checkbox: 5, 
             select: 6, multipleselect: 7, radiogroup: 8, checkboxgroup: 9, 
             number: 10, range: 11, country:12, state:13, date: 14};
        
    function getValue(type, variant, name, textReplace)
    {
        var value;

        switch (type) {

            case types.text: 
            case types.textarea: 
                value = getValueText(name, textReplace);
            break;       
            case types.number:
            case types.range: {
                var unit;
                if($(prefix+name+'_unit').length) {
                    unit = getValueSelect(name+'_unit');  
                    textReplace = ' '+unit;
                }
                if(type == types.number) {
                    value = getValueText(name, textReplace);
                } else {
                    value = getValueSelect(name, textReplace);
                }

            } break;      
            case types.select: 
            case types.multipleselect:
                value = getValueSelect(name, textReplace);
            break;
            case types.radioyesno:  
            case types.radiogroup: 
                value = getValueRadioGroup(name, type, textReplace);
            break;
            case types.checkbox: 
                value = getValueCheckbox(name, textReplace);
            break;
            case types.checkboxgroup:
                value = getValueCheckboxGroup(name, textReplace);
            break;
            case types.wysiwyg:
                
            break;
            case types.country:
            case types.state:
            case types.date: 
                
            break;                
        }
        
        return value;
    }

    function getValueText(name, textAfter)
    {
        var val = $(prefix+name, $block).val();
        return val ? val + (textAfter ? textAfter : '') : '';
    }

    function getValueSelect(name, textAfter)
    {
        var select = $(prefix+name, $block).get(0);
        var value;
        if(select.getAttribute('multiple')) 
        {
            value = '';
            var set_delimiter = ', '; 
            $(select).find(':selected').each(function() {
                value += $(this).text() + set_delimiter;
            });
        } else {
            if(select.value!=0)
                value = select.options[select.selectedIndex].text + (textAfter ? textAfter : '');
        }
        return value ? value : '';
    }

    function getValueCheckbox(name, textChecked)
    {
        var $input = $(prefix+name, $block);
        return ( $input.is(':checked') ? (textChecked ? textChecked : $input.attr('title')) : '' );
    }

    function getValueRadioGroup(name, type, textChecked)
    {
        var $input = $(prefix+name+':checked', $block);
        return ( $input.length && (type == types.radiogroup || intval($input.val())==2) ? (textChecked ? textChecked : $input.attr('title')) : '' );
    }
    
    function getValueCheckboxGroup(name)
    {
        var $input = '';
        var value = ''; 
        var set_delimiter = ', ';

        $(prefix+name+' input:checked', $block).each(function(){
            value += (value ? set_delimiter : '') + this.getAttribute('title');
        });

        return value ? value : '';
    }
    
    var $block, prefix, selector, process;
    
    return {
        init: function(o) {
            $block = $(o.block);
            prefix = o.prefix;
            selector = o.selector;
            process = o.process;
            var timeout = 100;
            $('select'+selector, $block).live('change', $.debounce( function() { 
                process();
            }, timeout));

            $('input[type="checkbox"]'+selector+',input[type="radio"]'+selector, $block).live('click', $.debounce( function() { 
                process();
            }, timeout));
            
            $('textarea'+selector+',input[type="text"]'+selector, $block).live('keyup', $.debounce( function() { 
                process();
            }, timeout));
            process();
        }, 
        clear: function(){
            if($block && selector) {
                $('select'+selector+',input[type=checkbox]'+selector+',input[type="radio"]'+selector+',textarea'+selector, $block).unbind();
            }
        },
        add: function (text, type, variant, name, delimiter, textReplace) {
            var value = getValue(type, variant, name, textReplace);
            if (value) {
                text = text + (text ? delimiter : '') + value;
            }
            return text;
        } 
    };
})();