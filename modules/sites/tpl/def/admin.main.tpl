
<div class="blueblock whiteblock">    
    <div class="title">
        <span class="leftC"></span>
        <span>Привет, админ!</span>
        <span class="rightC"></span>                        
    </div>
    <div class="content clear">
        <div class="text">  
            
            <div class="tabsBar">
                {foreach from=$aData.tabs key=k item=v}
                    <span class="tab {if $v.a}tab-active{/if}" onclick="bff.redirect('index.php?s=sites&ev=main&tab={$k}');">{$v.t}</span>
                {/foreach}
                <span id="progress-main" class="progress" style="display:none;"></span>
            </div>

            {if $tab == 'profile'} 

                {$aData.tabContent} 

            {elseif $tab == 'stat'}

                <table>
                   <tr>
                      <td>
                         <span style="font-size:22px; font-weight:bold; color:#A3867C;">{$aData.subscribers} - {$aData.subscribers|declension:'подписка;подписки;подписок':false}</span>
                      </td>
                   </tr> 
                </table>
            
            {/if}


        </div>
    </div>                                    
    <div class="bottom">      
        <div class="content">
            <ul>
                <li><span class="post-date">{$smarty.now|date_format:"%H:%M"}</span></li>     
            </ul>
        </div>    
    </div>   
    <div class="clear-all"></div>
</div>    
 