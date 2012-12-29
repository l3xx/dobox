<?php /* Smarty version 2.6.7, created on 2012-12-26 00:53:49
         compiled from js.template.tpl */ ?>
<?php if (count($_from = (array)$this->_tpl_vars['tplJSIncludes'])):
    foreach ($_from as $this->_tpl_vars['v']):
?>
<script src="<?php echo $this->_tpl_vars['v']; ?>
" type="text/javascript" charset="utf-8"></script>
<?php endforeach; endif; unset($_from); ?>                                                                                    

<script type="text/javascript" charset="utf-8">
app.themeUrl = '<?php echo $this->_tpl_vars['theme_url']; ?>
';
app.adm = true; 
app.yCountry = {title: '<?php echo @GEO_YMAPS_COUNTRY_TITLE; ?>
', coords: '<?php echo @GEO_YMAPS_COUNTRY_COORDS; ?>
', bounds: '<?php echo @GEO_YMAPS_COUNTRY_BOUNDS; ?>
'.split(';')};
<?php echo '
$(document).ready(function(){          
    $(\'a.userlink\').fancybox({type: \'ajax\', scrolling: \'no\'});
    $(\'a.itemlink\').fancybox({type: \'ajax\', scrolling: \'no\'});
});
'; ?>

</script>
