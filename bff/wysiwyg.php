<?php

class CWysiwyg
{
	var $type = 'FCK';
	var $isSecond = false;

	function setType($sTypeWYSIWYG = 'FCK')
	{
		$this->type = mb_strtoupper($sTypeWYSIWYG); 
	}

	function init($sFieldName, $sContent, $sWidth = '800px', $sHeight = '300px', $sToolbarMode = 'full', $sTheme = 'silver'  )
	{
	    switch ($this->type)
	    {
		    case 'FCK': {
			    if(!$this->isSecond) {
				    require_once PATH_CORE.'external/wysiwyg/FCKeditor2/fckeditor.php' ;
				    $this->isSecond = true;
			    }
                
			    $oFCKeditor = new FCKeditor($sFieldName);
			    $oFCKeditor->BasePath = '../bff/external/wysiwyg/FCKeditor2/';
                
			    if($sWidth)
				    $oFCKeditor->Width = $sWidth;
			    if($sHeight)
				    $oFCKeditor->Height = $sHeight;
                    
			    $sToolbarMode = strtolower($sToolbarMode);
			    switch($sToolbarMode)
			    {
				    case 'basic':   $oFCKeditor->ToolbarSet = 'Basic';   break;
				    case 'mini':    $oFCKeditor->ToolbarSet = 'Mini';    break;
				    case 'medium':  $oFCKeditor->ToolbarSet = 'Medium';  break;
				    case 'average': $oFCKeditor->ToolbarSet = 'Average'; break;
				    default:        $oFCKeditor->ToolbarSet = 'Default'; break;

			    }
                
                $oFCKeditor->Config['SkinPath'] = "/bff/external/wysiwyg/FCKeditor2/editor/skins/$sTheme/";
			    $oFCKeditor->Value = $sContent;
			    return $oFCKeditor->CreateHTML();

            } break;
		    case 'TEXTAREA': {
			    return '<textarea name="'.$sFieldName.'" style="width:'.$sWidth.';height:'.$sHeight.'">'.$sContent.'</textarea>';
            } break;
	    }
	}
}
