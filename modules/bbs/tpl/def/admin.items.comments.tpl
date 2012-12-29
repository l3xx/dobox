<script type="text/javascript">
{literal}
//<![CDATA[    
var oComments;           
$(function() {      
    oComments = new bbsItemsCommentsAdmTreeClass({
        path: app.themeUrl+'/img/admin/',
        {/literal}itemID: '{$itemID}', module: '{$class}'{literal}
    });
});     
//]]> 
{/literal}
</script>  

<div class="blueblock lightblock hidden">    
    <div class="title">
        <span class="leftC"></span>
        <span class="left">Информация об объявлении</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
              
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>      
 
<div class="blueblock whiteblock">    
    <div class="title">                  
        <span class="leftC"></span>
        <span class="left">Комментарии к объявлению # {$itemID} <span id="progress-comments" style="display:none;" class="progress"></span></span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">
        
            <div class="actionBar" style="height:13px; margin-bottom:5px;">
                <div class="left"></div>
                <div class="right desc">   
                    <a onclick="oComments.collapseNodeAll(); return false;" class="ajax" href="#">свернуть</a> /
                    <a onclick="oComments.expandNodeAll(); return false;" class="ajax" href="#">развернуть</a>
                </div>
                <div class="clear"></div>
            </div>
            
            <div class="comments">  
                <div class="update" id="update" style="display:none;">
                    <div class="tl"></div>
                    <div class="wrapper">
                        <div class="refresh">
                            <img class="update-comments" id="update-comments" alt="" src="{$theme_url}/img/admin/comment-update.gif" onclick="oComments.responseNewComment(this); return false;"/>
                        </div>
                        <div class="new-comments" id="new-comments" style="display: none;" onclick="oComments.goNextComment();"></div>
                    </div>
                    <div class="bl"></div>
                </div>  
                <script type="text/javascript">
                    $(function(){ldelim} oComments.setIdCommentLast({$aData.comments.nMaxIdComment});{rdelim});                    
                </script> 
            {assign var="nesting" value="-1"}
            {foreach from=$aData.comments.aComments item=v name=rublist}
                {assign var="cmtlevel" value=$v.level}                    
                {if $nesting < $cmtlevel}        
                {elseif $nesting > $cmtlevel}        
                    {section name=closelist1 loop=`$nesting-$cmtlevel+1`}</div></div>{/section}
                {elseif not $smarty.foreach.rublist.first}
                    </div></div>
                {/if}    
                <div class="comment" id="comment_id_{$v.id}">
                        <img src="{$theme_url}/img/admin/comment-close.gif" alt="+" title="свернуть/развернуть" class="folding" />
                        <a name="comment{$v.id}" ></a>                            
                        <div id="comment_content_id_{$v.id}" class="ccontent{if $v.user_id == $aData.curuid} self{/if}">                                
                            <div class="tb"><div class="tl"><div class="tr"></div></div></div>                                
                            <div class="ctext">
                                {if $v.deleted>0}
                                    <span class="desc">
                                    {if $v.deleted == 1}Комментарий удален владельцем объявления
                                    {elseif $v.deleted == 2}Комментарий удален модератором
                                    {elseif $v.deleted == 3}Комментарий удален автором комментария{/if}
                                    </span>
                                {elseif $v.user_blocked}<span class="desc">Комментарий от заблокированного пользователя</span>
                                {else}{$v.comment}{/if}                                                        
                            </div>                
                            <div class="bl"><div class="bb"><div class="br"></div></div></div>
                        </div>                            
                        <div class="info">
                            
                            <ul>
                                <li><p>{if $v.user_id}<a class="userlink" href="index.php?s=users&ev=user_ajax&action=user-info&rec={$v.user_id}">{$v.name}</a>{else}<span class="author" title="{$v.ip}">{$v.name}</span>{/if}</p></li>
                                <li class="date">{$v.created|date_format2:true}</li>
                                {if $v.pid}
                                    <li class="goto-comment-parent"><a href="#comment{$v.pid}" title="Ответ на">&uarr;</a></li>
                                {/if}                                                                                       
                                <li>
                                    {if $v.deleted==0} 
                                    <a href="#" class="delete ajax" onclick="oComments.deleteComment(this,{$v.id}); return false;">Удалить</a></li>
                                    {/if}
                                </li>
                            </ul>
                        </div>        
       
                        <div class="comment-children" id="comment-children-{$v.id}">    
                {assign var="nesting" value=$cmtlevel}    
                {if $smarty.foreach.rublist.last}
                    {section name=closelist2 loop=`$nesting+1`}</div></div>{/section}    
                {/if}
            {foreachelse}
                 <div class="alignCenter valignMiddle" id="comment-no" style="height:30px; padding-top:15px;">
                    <span class="desc">нет комментариев</span>
                 </div>            
            {/foreach}
            
                <span id="comment-children-0"></span> 
            </div>
        
        </div>
        <div class="bottom">
            <span class="left"></span>
            <span class="right"></span>
        </div>
    </div>                                    
</div>  
