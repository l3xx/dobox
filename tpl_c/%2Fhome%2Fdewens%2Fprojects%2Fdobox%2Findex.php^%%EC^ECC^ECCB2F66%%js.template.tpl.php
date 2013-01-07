<?php /* Smarty version 2.6.7, created on 2012-12-29 12:25:28
         compiled from js.template.tpl */ ?>
<script type="text/javascript">window.locDomain = '<?php echo @SITEHOST; ?>
'; window.bff_table = <?php global $oSecurity; echo $oSecurity->getUID(); ?></script>                 
<?php if (count($_from = (array)$this->_tpl_vars['tplJSIncludes'])):
    foreach ($_from as $this->_tpl_vars['v']):
?>
    <script src="<?php echo $this->_tpl_vars['v']; ?>
" type="text/javascript" charset="utf-8"></script>
<?php endforeach; endif; unset($_from); ?>
<script type="text/javascript">app.m=<?php if ($this->_tpl_vars['userLogined']): ?>1<?php else: ?>0<?php endif; ?>;
<?php if ($this->_tpl_vars['event'] == 'search' && ! $_GET['quick']):  echo 'if(window.History.emulated.pushState) {
    var hash = window.History.getHash();
    if(hash && hash.length>0) {
        if(/search\\?/i.test(hash)) document.location = \'/\'+hash;
    }
}';  endif; ?> 
</script>