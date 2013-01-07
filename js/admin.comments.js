var bffCommentsTreeClass = function(options){ this.initialize(options); } 
bffCommentsTreeClass.prototype = 
{
    options: {
        path: 'images/',
        img: {          
            open:  'comment-open.gif', 
            close: 'comment-close.gif'
        },
        classes: {
            visible:  'visible',
            hidden:   'hidden',            
            open:     'open',            
            close:    'close'            
        }        
    },
    
    initialize: function(options){        
        this.options = $.extend(this.options, options || {});                 
        this.make();        
        this.aCommentNew = [];
        this.iCurrentShowFormComment = 0;    
        this.iCommentIdLastView = null;    
        this.countNewComment  =0;
        //this.docScroller = new Fx.Scroll(document.getDocument());    
        this.hideCommentForm( this.iCurrentShowFormComment );
    },

    make: function(){
        var thisObj = this;
        var aImgFolding=$('img.folding');
        aImgFolding.each(function(i,img){
            var divComment = $($(img).parent('div').children('div.comment-children')[0]);
            if (divComment && divComment.children('div.comment')[0]) {
                thisObj.makeImg( $(img) );
            } else {
                $(img).css('display','none');
            }
        });        
    },
    
    makeImg: function(img) {
        var thisObj = this;
        img.css('cursor', 'pointer').css('display','inline').
            addClass(this.options.classes.close).
            unbind('click').click(function(){ thisObj.toggleNode(img); });
    },
    
    toggleNode: function(img) {    
        if ( img.hasClass(this.options.classes.close) ) {                
            this.collapseNode(img);
        } else {                    
            this.expandNode(img);
        }
    },
    
    expandNode: function(img) {                
        img.attr('src', this.options.path+this.options.img.close).
            removeClass(this.options.classes.open).
            addClass(this.options.classes.close);          
        $(img.parent('div').children('div.comment-children')[0]).
                removeClass(this.options.classes.hidden).
                addClass(this.options.classes.visible);        
    },
    
    collapseNode: function(img) {
        img.attr('src', this.options.path + this.options.img.open).
            removeClass(this.options.classes.close).
            addClass(this.options.classes.open);                   
        $(img.parent('div').children('div.comment-children')[0]).
            removeClass(this.options.classes.visible).
            addClass(this.options.classes.hidden);        
    },
    
    expandNodeAll: function() {
        var thisObj = this;
        $('img.'+this.options.classes.open).each(function(i,img){            
            thisObj.expandNode($(img));
        });
    },
    
    collapseNodeAll: function() {
        var thisObj = this;      
        $('img.'+this.options.classes.close).each(function(i,img){            
            thisObj.collapseNode($(img));
        });
    },
    
    injectComment: function(idCommentParent,idComment,sHtml) {        
        var newComment = $(document.createElement('div'));
        newComment.addClass('comment').attr('id','comment_id_'+idComment).html(sHtml);        
        if (idCommentParent) {
            this.expandNodeAll();    
            var divChildren = $('#comment-children-'+idCommentParent);        
            this.makeImg( $(divChildren.parent('div').children('img.folding')[0]) );
            divChildren.append(newComment);
        } else {
            newComment.insertBefore( $('#comment-children-0') );
        }    
    },    
    
    responseNewComment: function(objImg, selfIdComment, bNotFlushNew) {
        var thisObj=this;        
        if (!bNotFlushNew) {
            $('.comment').each(function(i,item){
                var divContent= $($(item).children('div.content')[0]);
                if (divContent) divContent.removeClass('new view'); 
            });
        }                   
        objImg = $(objImg);
        objImg.attr('src', this.options.path+'comment-update-act.gif');   
        setTimeout(function(){ 
        bff.ajax('index.php?s='+this.options.module+'&ev=ajax&act=comment-response', 
            {'comment_id_last': this.idCommentLast, 'item_id': this.options.itemID },
            function(result) {                
                objImg.attr('src',this.options.path+'comment-update.gif'); 
                if (result) { 
                    var aCmt = result.aComments;                     
                    if (aCmt.length>0 && result.nMaxIdComment) {
                        this.setIdCommentLast(result.nMaxIdComment);
                        var countComments = $('#count-comments');
                        countComments.text(parseInt(countComments.text())+aCmt.length);
                    }            
                    var iCountOld=0;
                    if (bNotFlushNew) {                                                        
                        iCountOld = this.countNewComment;                        
                    } else {
                        this.aCommentNew = [];
                    }
                    if (selfIdComment) {
                        this.setCountNewComment(aCmt.length-1+iCountOld);      
                        this.hideCommentForm(this.iCurrentShowFormComment); 
                    } else {
                        this.setCountNewComment(aCmt.length+iCountOld);
                    }                    
                    $.each(aCmt, function(i,comm) {   
                        if (!(selfIdComment && selfIdComment == comm.id)) {
                            thisObj.aCommentNew.push(comm.id);
                        }                                         
                        thisObj.injectComment(comm.pid,comm.id,comm.html);
                    });
                    
                    $('#comment-no').slideUp();
                }                           
            }.bind(this)
       ); }.bind(this), 1000 );
    },
    
    setIdCommentLast: function(id) {
        this.idCommentLast = id;
    },
    
    setCountNewComment: function(count) {
        this.countNewComment = count;        
        var divCountNew = $('#new-comments');
        if(this.countNewComment>0) {
            divCountNew.text(this.countNewComment).css('display','block');            
        }else{
            this.countNewComment = 0;
            divCountNew.text(0).css('display','none');
        }
    },
    
    goNextComment: function() {        
        if(this.aCommentNew[0]) {
            this.scrollToComment(this.aCommentNew[0]);
            this.aCommentNew.splice(0,1);
        }        
        this.setCountNewComment(this.countNewComment-1);
    },
    
    scrollToComment: function(idComment) {
        var cmt=$('#comment_content_id_'+idComment);
        $.scrollTo(cmt, 500);
        if (this.iCommentIdLastView) {
            $('#comment_content_id_'+this.iCommentIdLastView).removeClass('view');
        }                
        cmt.addClass('view');
        this.iCommentIdLastView = idComment;
    },
    
    addComment: function(formObj) {              
        bff.ajax('index.php?s='+this.options.module+'&ev=ajax&act=comment-add', $(formObj).serializeArray(),
            function(data) {      
                $('#form_comment_text').attr('disabled','disabled');             
                this.responseNewComment($('#update-comments'), data.comment_id, true);
            }.bind(this));
          $('#form_comment_text').addClass('loader');        
    },
    
    enableFormComment: function() {
        $('#form_comment_text').removeClass('loader').removeAttr('disabled'); 
    },
    
    addCommentScroll: function(commentId) {
        this.aCommentNew.push(commentId);
        this.setCountNewComment(this.countNewComment+1);
    },
    
    deleteComment: function(link, commentId) {
        var divContent=$('#comment_content_id_'+commentId);
        if (!divContent) {
            return false;
        }
        
        if(!confirm('Продолжить?'))
            return false;
        
        link = $(link);            
        bff.ajax('index.php?s='+this.options.module+'&ev=ajax&act=comment-delete', 
        {'comment_id': commentId, 'item_id': this.options.itemID}, function (data) {
            if(data){                  
                var del = link.hasClass('delete'); link.removeClass('delete repair');
                if ( divContent.hasClass('del') && !del ) {
                    divContent.removeClass('del');
                    link.addClass('delete').text('Удалить');     
                } else {
                    divContent.addClass('del');
                    link.addClass('repair').text('Восстановить (удален модератором)');
                }
            }
        });
    },
    
    toggleCommentForm: function(idComment, show) {
        if (!$('#reply_'+this.iCurrentShowFormComment) || !$('#reply_'+idComment)) {
            return;
        } 
        var divCurrentForm = $('#reply_'+this.iCurrentShowFormComment);
        var divNextForm    = $('#reply_'+idComment);
        
        $('#comment_preview_'+this.iCurrentShowFormComment).html('').css('display','none');
        if (this.iCurrentShowFormComment == idComment) {
            if(show) divCurrentForm.show();
            else divCurrentForm.toggle();
            $('#form_comment_text').focus();
            return;
        }
        
        divCurrentForm.hide();
        divNextForm.html(divCurrentForm.html());
        divCurrentForm.html('');        
        divNextForm.css('display','block').show();
                
        $('#form_comment_text').focus().attr('value', '');
        $('#form_comment_reply').attr('value', idComment);
        this.iCurrentShowFormComment = idComment;
    },
    
    hideCommentForm: function(idComment) {
        var comm = $('#reply_'+idComment);
        if(comm) {
            this.enableFormComment();
            $('#comment_preview_'+this.iCurrentShowFormComment).html('').css('display','none');
            comm.hide();
        }
    }
};


var bffCmtTree = null;
