<?php

class Sitemap extends SitemapBase
{
	function main()
	{
        return '';
	}

	/**
	 * @param string $sInternalKeyword
	 * @param string $sNumLevel 'null|all|next|number'
	 * @return unknown
	 */
	function getmenu($sInternalKeyword = null, $sNumLevel = 'next')
	{
        $sWhere = '';
        
		if(isset($sInternalKeyword))
		{
			$sQuery = "SELECT I.*, '0' as active, T.numlevel, T.numright, T.numleft
                       FROM ".DB_PREFIX."sitemap_tree T,
                    	    ".DB_PREFIX."sitemap I
                       WHERE I.node_id = T.id
                    	     AND T.pid!=0
                    	     AND T.enabled=1
                    	     AND I.keyword='".$sInternalKeyword."'
                    	    ";
			$aFrom = $this->db->one_array($sQuery);

			if($aFrom)
			{
				$sWhere = ' AND numleft>'.$aFrom['numleft'].' AND numright<'.$aFrom['numright'].' ';
				if(isset($sNumLevel))
				{
					if(is_numeric($sNumLevel))
					{
						$sWhere .= ' AND numlevel = '.$sNumLevel.' ';
					}
					else
					{
						switch($sNumLevel)
						{
							case 'next':
								$sWhere .= 'AND numlevel='.($aFrom['numlevel']+1).' ';
								break;
                            case 'all-sub':
                                $sWhere .= 'AND numlevel>'.($aFrom['numlevel']).' ';
                                break;
                        }
					}
				}
			}
		}

		$aData = $this->db->select("SELECT I.keyword, T.id, T.pid, I.menu_link, I.menu_title, '0' as active, T.numlevel, T.numright, T.numleft
                   FROM ".DB_PREFIX.'sitemap_tree T,
                        '.DB_PREFIX.'sitemap I
                   WHERE I.node_id = T.id
                        AND T.pid!=0
                        AND T.enabled=1
                        '.$sWhere .'
                   ORDER BY numleft');

		$sCurrentLink = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		for ($i=0; $i<count($aData); $i++)
		{
			$aData[$i]['active'] = 0;
			$aData[$i]['node'] = ($aData[$i]['numright']-$aData[$i]['numleft']!=1 ? true : false );
            $aData[$i]['tab'] =  $aData[$i]['numlevel']*20-20;
            
			if(strpos($aData[$i]['menu_link'], 'http://')===false)
			{
				$aData[$i]['menu_link'] = ltrim($aData[$i]['menu_link'],'/\\ ');
				$aData[$i]['menu_link'] = SITEURL.'/'.$aData[$i]['menu_link'];
			}

			if(	$aData[$i]['menu_link'] == $sCurrentLink
			    || SITEURL.'/'.$aData[$i]['menu_link'] == $sCurrentLink
			    || strstr($sCurrentLink, $aData[$i]['menu_link'])
			    )
			{
				$aData[$i]['active'] = 1;
			}
		}
        
        if($sNumLevel == 'all-sub') {
            return $this->db->transformRowsToTree($aData, 'id', 'pid', 'subs');
        }

		return $aData;

	}
    
}