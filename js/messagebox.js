/** 
 * Message box
 **/
   
function MessageBox(options) {
    var defaults = {
        title: "Alert",
        width: "410px",
        height: "auto",
        bodyStyle: "",
        block: true,       
        progress: false,
        topPadding: false,
        container: '<div class="mbox blueblock lightblock">\
        <div class="title"><span class="leftC"></span><span class="left mbox_title"></span><span class="rightC"></span></div>\
            <div class="content clear">\
                <div class="text"><div class="mbox_body"></div><div class="mbox_controls"></div></div>\
                <div class="bottom"><span class="left"></span><span class="right"></span></div>\
            </div>\
        </div>'                                
    //page
//    boxContainer.innerHTML = '<div class="page mbox">\
//        <div class="top"><div class="left"></div><div class="right"></div></div>\
//        <div class="content">\
//            <div class="text">\
//                <h4 class="mbox_title"></h4>\
//                <div class="mbox_body"></div>\
//                <div class="mbox_controls"></div>\
//                <div class="clear-all"></div>\
//            </div>\
//        </div>\
//        <div class="bottom"><div class="left"></div><div class="right"></div></div>\
//    </div>'; 
    };
    options = $.extend(defaults, options);

    var buttonsCount = 0, body = document.getElementsByTagName('body')[0],
        transparentBG, boxContainer, boxLayout, boxTitle, boxBody, boxControls, boxProgress,
        isVisible = false;
    
    if(options.block) {
        transparentBG = document.createElement('div');
        transparentBG.className = 'mbox_transparent_bg';
        transparentBG.style.display = 'none';
    }
            
    boxContainer = document.createElement('div');
    boxContainer.className = 'mbox_container';
    boxContainer.style.display = 'none';
    boxContainer.innerHTML = options.container; 
        
    boxTitle    = $('.mbox_title', boxContainer)[0];
    boxBody     = $('.mbox_body', boxContainer)[0];
    boxControls = $('.mbox_controls', boxContainer)[0];

    if (options.progress) {
        boxControls.innerHTML = '<div class="right"><img src="/img/progress-mini2.gif" style="display: ;" /></div>';
        boxProgress = boxControls.firstChild;
    } else {
        boxProgress = null;
    }

    if(options.block)
        transparentBG.style.height = $(document).height() + 'px';
  
    // Add button
    this.addButton = function(options) {
        if (typeof options != 'object') options = {};
        options = $.extend({
            title: 'Button',   
            width: '85px',
            pos: 'right'
        }, options);
        
        if(buttonsCount) {
            //add button separator
            var buttonSeparator = document.createElement('div');
            buttonSeparator.className = "button-separator " + options.pos;
            boxControls.appendChild(buttonSeparator);
        }

        buttonsCount++;

        var buttonWrap = document.createElement('div');
        buttonWrap.className = "button " + options.pos;
        buttonWrap.innerHTML = '<span class="left"></span><input type="button" value="'+options.title+'" style="width: '+options.width+';"/>';

//        if (boxProgress) {
//          boxControls.insertBefore(buttonWrap, boxProgress);
//        } else
         {
          boxControls.appendChild(buttonWrap);
        }
        if (options.onClick) {
          $(buttonWrap).click(options.onClick);
        }
        return buttonWrap;
    };
  
  // Add custom controls text
//  this.addControlsText = function(text) {
//    var textWrap = document.createElement('div');
//    textWrap.className = "controls_wrap";
//    textWrap.innerHTML = text;
//    boxControls.appendChild(textWrap);
//    return textWrap;
//  };
  
    this.content = function(html) {
        boxBody.innerHTML = html;
        this.refreshCoords();
    };
    
    this.ge = function(s) {
        return $(s, boxContainer);
    };

    // Remove buttons
    this.removeButtons = function() {
        buttonsCount = 0;
        boxControls.innerHTML = '';
    };

    // Refresh
    this.refresh = function() {
        boxTitle.innerHTML = options.title;
        boxContainer.style.width = typeof(options.width) == 'string' ? options.width : options.width + 'px';
        boxContainer.style.height = typeof(options.height) == 'string' ? options.height : options.height + 'px';
    };
    this.refreshCoords = function() {
        boxContainer.style.left = ($(window).width()/2-$(boxContainer).width()/2) + 'px';
        if(options.topPadding == false)
            boxContainer.style.top = ( $(window).height()/2.3-$(boxContainer).height()/2 + $(window).scrollTop() ) + 'px'; 
        else
            boxContainer.style.top = $(window).scrollTop() + options.topPadding + 'px'; 
        
        //boxContainer.style.top = $(window).scrollTop() + options.topPadding + 'px';
        //boxContainer.style.marginLeft = - ($(boxContainer).width()/2) + 'px';
    };

    // Show box
    this.show = function() {  
        if (isVisible) return;
        isVisible = true;

        // Show blocking background
        if(options.block)
            transparentBG.style.display = 'block';
                             
        this.refreshCoords();
            
        $(boxContainer).fadeIn(600); 

        if (options.onShow) {
          options.onShow();
        }
    };
  
    // Hide box
    this.hide = function(speed) {
        if (!isVisible) return;
        isVisible = false;

        var onHide = function () {
          boxContainer.style.display = 'none';
          if(options.block) 
            transparentBG.style.display = 'none';

          if (options.onHide) options.onHide();
        }
        if (speed > 0) {
          $(boxContainer).fadeOut(speed, function(){
            onHide(); 
          });
        } else {
          onHide();
        }
    };
  
    //Set options
    this.setOptions = function(newOptions) {
      options = $.extend(options, newOptions);
      this.refresh();
    };
  
    var me = this;      
    $(document).ready(function(){ 
        if(options.block) {
            body.appendChild(transparentBG);
            $(this).keydown(function(e) {
                if (e.keyCode == 27 && $(transparentBG).is(':visible')) { 
                    nothing(e);
                    me.hide(); 
                }
            });
        }
        body.appendChild(boxContainer); 
        

        if(options.hideOnClick)
            $(boxContainer).click(function(){ 
                me.hide(options.hideOnClick);
            }); 
        
        me.refresh(); 
    });

};

function MessageBoxAlert(options)
{
    var defaults0 = {
        title: "Сообщение", 
        block: true
    };

    var mb = new MessageBox( $.extend(defaults0, options) );
    mb.addButton({title: 'Отмена', onClick: function(){ mb.hide(); } });
    return mb;
};