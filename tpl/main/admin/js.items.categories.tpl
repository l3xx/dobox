<script type="text/javascript">      
//<![CDATA[
{literal}

function ItemsCategoriesSelect(tree, r1, r2, r3, b1, b2, result, limit, selected)
{
    var h = [{
        i : 'all', t : 'Все',
        tree : function () {
            var o = [];
            for(var k = 0, j = tree.length; k < j; k++) {
                for (var n = 0, p = tree[k].tree.length; n < p; n++) {
                    o.push( tree[k].tree[n] );
                }
            }
            o.sort(function(a, b){
                a = String(a.t); b = String(b.t);
                return (a > b ? 1 : (a < b ? -1 : 0) );
            });
            return $.unique(o);
        }()
    }];
    this.pSelect = r1;
    this.cSelect = r2;
    this.rSelect = r3;
    this.bAdd    = b1;
    this.bRemove = b2;
    this.result  = result;
    this.limit = limit || 3;
    this.tree = (h.concat(tree));  
    this.fill(this.tree, this.pSelect);
    this.fill(this.tree[0].tree, this.cSelect);
    this.init();
    
    if(selected){
        this.rSelect.innerHTML = '';
        var s = selected.split(',');
        var opts = '';  
        var o = []; 
        for(var i = 0, p = this.tree[0].tree.length; i < p; i++) {
            if( $.inArray( String(this.tree[0].tree[i].i), s)!=-1 )
                o.push( this.tree[0].tree[i] );                                                               
        }
        o.sort(function(a, b){
            a = String(a.t); b = String(b.t);
            return (a > b ? 1 : (a < b ? -1 : 0) );
        });
        for(var i=0; i < o.length; i++)
            opts += '<option value="'+o[i].i+'">'+o[i].t+'</option>';

        this.rSelect.innerHTML = opts;
        this.bRemove.disabled = false;
    }
}
ItemsCategoriesSelect.prototype = 
{
    init : function ()
    {
        var t = this;
        this.pSelect.onchange = function(){
            t.fill(t.tree[this.selectedIndex].tree, t.cSelect);
        };
        this.bAdd.onclick = function(){
            t.add(); t.save();
        };
        this.bRemove.onclick = function(){
            t.remove(); t.save();
        };
        this.checkCategoryLimit();
    },
    add : function ()
    {
        if ($('#r3 option[value=' + $('#r2 option:selected').val() + ']').length > 0) {
            return;
        }
        $('#r3 option[value = -1]').remove();
        $('#r2 option:selected').clone().appendTo("#r3");
        this.checkCategoryLimit();
    },
    remove : function ()
    {
        if($('#r3 option[value!=-1]').length>0){
            $('#r3 option:selected').remove();
        }
        if ($('#r3 option').length == 0) {
            $('#r3').html('<option value="-1">&nbsp;</option>')
        }
        this.checkCategoryLimit(); 
    },
    fill : function (tree, sel)
    {
        var opts = '';
        for (var i = 0; i < tree.length; i++)
            opts += '<option value="'+tree[i].i+'" title="'+tree[i].t+'">'+tree[i].t+'</option>';

        sel.innerHTML = opts; 
    },
    save : function ()
    {
        var b;
        if (this.result) {
            this.result.value = ""
        }
        b = this.rSelect.getElementsByTagName('option');
        for (var c = 0, a = b.length; c < a; c++)
        {
            var d = b.item(c);
            if (d.value !=- 1 && this.result) {
                this.result.value += ((c != 0) ? ',' : '') + (d.value)
            }
        }
    },
    checkCategoryLimit : function ()
    {
        if ($('#r3 option[value!=-1]').length >= this.limit && !this.bAdd.disabled) {
            this.bAdd.disabled = true
        }
        else
        {
            if ($('#r3 option[value!=-1]').length <= this.limit && this.bAdd.disabled) {
                this.bAdd.disabled = false;
            }
        }
        if ($('#r3 option[value=-1]').length == 1 && $('#r3 option[value!=-1]').length == 0) {
            this.bRemove.disabled = true
        }
        else {
            this.bRemove.disabled = false;
        }
    }
};
{/literal}

var ItemsCategoriesTree = [
{foreach from=$aCategories item=v name=mains}
{ldelim}i:{$v.id},t:'{$v.title}',tree:[{foreach from=$v.subcats item=v2 name=subs}{ldelim}i:{$v2.id},t:'{$v2.title}'{rdelim}{if !$smarty.foreach.subs.last},{/if}{/foreach}]
{rdelim}{if !$smarty.foreach.mains.last},{/if}
{/foreach}
];
//]]> 
</script>