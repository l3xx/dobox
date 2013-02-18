<?php

/**
*   >> Create Tables, replace DB_PREFIX, TABLENAME, GROUP_ID
* 
    DROP TABLE IF EXISTS `DB_PREFIX.TABLENAME._tree`;
    CREATE TABLE `DB_PREFIX.TABLENAME._tree` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `pid` int(10) unsigned NOT NULL default '0',
      `GROUP_ID` int(10) unsigned NOT NULL default '0',
      `numleft` int(11) default NULL,
      `numright` int(11) default NULL,
      `numlevel` int(11) default NULL,
      `enabled` tinyint(1) unsigned NOT NULL default '1',
      PRIMARY KEY  (`id`),
      UNIQUE KEY `id` (`id`,`numleft`,`numright`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

*  GROUP_ID - ID "комментируемого" объекта, статьи, фотографии
* 
*/

class dbNestedSetsTree extends Component
{
    private $treeTable = '';
    private $groupField = 'group_id';
    private $groupID = 0;
    private $useGroups = false;     

    public function __construct($sTreeTable, $bUseGroups = false, $sGroupField = 'group_id')
    {
       $this->treeTable = $sTreeTable;
       $this->useGroups = $bUseGroups; 
       if($bUseGroups) {
           $this->groupField = $sGroupField;
       }
    }

    function setGroupID($nGroupID=0)
    {
       $this->groupID = (int)$nGroupID; 
    }
    
    //------------------------------------------------------------------------------------------------------------
    // _tree functions
    
    function getNodePath($nNodeID, &$aResult, $sAddWhere = ' AND numlevel>1 ')
    {
        $nParentNodeID = (int)$this->db->one_data('SELECT pid FROM '.$this->treeTable.' WHERE id='.$nNodeID.' '.$sAddWhere);
        if(!$nParentNodeID) return;
        $aResult[] = $nParentNodeID;

        $this->getNodePath($nParentNodeID, $aResult);
    }
    
    function getNodeParentsID($nNodeID, $sAddWhere = ' AND numlevel>0 ')
    {
        $aNodeInfo = $this->getNodeInfo($nNodeID);
        return $this->db->select_one_column('SELECT id FROM '.$this->treeTable.' 
                                        WHERE numleft <= '.$aNodeInfo['numleft'].' AND numright > '.$aNodeInfo['numright'].$sAddWhere.' 
                                        ORDER BY numleft');
    }

    function getNodeParentsAndChildrensID($nNodeID, $sAddWhere = ' AND numlevel>1 ')
    {
        $aNodeInfo = $this->getNodeInfo($nNodeID);
        
    }
    
	function getNodeInfo($nNodeID)
	{
		return $this->db->one_array('SELECT * FROM '.$this->treeTable.' WHERE id='.((int)$nNodeID).' LIMIT 1');
	}

    function getChildrenCount($nNodeID, $sAddWhere = '')
    {
        return (int)$this->db->one_data('SELECT (numright-numleft)-1 FROM '.$this->treeTable.' WHERE id='.((int)$nNodeID)." $sAddWhere LIMIT 1");
    }
    
    function checkRootNode(&$nRootID, $nGroupID = 0)
    {
        $nRootID = $this->getRootNodeID($nGroupID, $bJustCreated);
        return $bJustCreated;
    }
    
    function getRootNodeID($nGroupID = 0, &$bJustCreated)
    {
        if($this->useGroups)
        {
            if(empty($nGroupID)) $nGroupID = $this->groupID;
            else $nGroupID = (integer)$nGroupID;
        }

        //Check and create root
        $nInsertID = (int)$this->db->one_data('SELECT id
                       FROM '.$this->treeTable.'
                       WHERE '.($this->useGroups?$this->groupField.'="'.$nGroupID.'" AND':'').' pid=0');
        if( !$nInsertID )
        {                 
            $this->db->execute('INSERT INTO '.$this->treeTable.'
                          ('.($this->useGroups?$this->groupField.',':'').' pid, numlevel, numleft, numright)
                           VALUES('.($this->useGroups?'"'.$nGroupID.'",':'').' 0, 0, 1, 2)');
            $nInsertID = $this->db->insert_id($this->treeTable, 'id');
            $bJustCreated = true;
        }
        else
        {
            $bJustCreated = false;
        }
        
        return $nInsertID;
    }
    
    function insertNode($nParentNodeID = 0, $nGroupID = 0)
    {
        $nParentNodeID = (int)$nParentNodeID;
        if(!$nParentNodeID)
            $nParentNodeID = $this->getRootNodeID($nGroupID);

        if((int)$nGroupID==0)
            $nGroupID = $this->groupID;
            
        return $this->insertNodeByParentId($nParentNodeID);
    }
    
	function insertNodeByParentId($nParentNodeID)
	{
		$aParentInfo = $this->getNodeInfo($nParentNodeID);
		if(!$aParentInfo)
		    return false;
        
        $sQuery = 'UPDATE '.$this->treeTable.' 
                    SET numright=numright+2, numleft=IF(numleft>'.$aParentInfo['numright'].', numleft+2, numleft) 
                    WHERE numright>='.$aParentInfo['numright'].($this->useGroups?' AND '.$this->groupField.'="'.$aParentInfo[$this->groupField].'"':''); 
        $this->db->execute($sQuery);

		$sQuery = 'INSERT INTO '.$this->treeTable.' (
                    '.($this->useGroups?$this->groupField.',':'').' pid, numleft, numright, numlevel)
                  VALUES('.($this->useGroups?'"'.$aParentInfo[$this->groupField].'",':'').' 
                    '.$nParentNodeID.','.($aParentInfo['numright']).', '.($aParentInfo['numright']+1).', '.($aParentInfo['numlevel']+1).')';
		$this->db->execute($sQuery);
		return $this->db->insert_id($this->treeTable, 'id');
	}
    
    function deleteNode($nNodeID)
    {
        $aNodeInfo = $this->getNodeInfo($nNodeID);
        if(!$aNodeInfo)
            return false;
            
        $aDeleteNodeIDs = $this->db->select_one_column('SELECT id FROM '.$this->treeTable.' 
                                   WHERE numleft>='.$aNodeInfo['numleft'].' AND numright<='.$aNodeInfo['numright']. 
                                   ($this->useGroups?' AND '.$this->groupField.'="'.$aNodeInfo[$this->groupField].'"':'') );

        $this->db->execute('DELETE FROM '.$this->treeTable.' WHERE numleft>='.$aNodeInfo['numleft'].' AND numright<='.$aNodeInfo['numright'].
                      ($this->useGroups?' AND '.$this->groupField.'="'.$aNodeInfo[$this->groupField].'"':'') );
        
        $this->db->execute('UPDATE '.$this->treeTable.' 
                  SET numright=(numright-'.$aNodeInfo['numright'].'+'.$aNodeInfo['numleft'].'-1)
                  WHERE numright > '.$aNodeInfo['numright'].($this->useGroups?' AND '.$this->groupField.'="'.$aNodeInfo[$this->groupField].'"':'') );

        $this->db->execute('UPDATE '.$this->treeTable.'
                  SET numleft=(numleft-'.$aNodeInfo['numright'].'+'.$aNodeInfo['numleft'].'-1)
                  WHERE numleft > '.$aNodeInfo['numleft'].($this->useGroups?' AND '.$this->groupField.'="'.$aNodeInfo[$this->groupField].'"':'') );
        
        return $aDeleteNodeIDs;
    }

    function moveNodeUp($nNodeID)
    {
        //get node info
        $aNodeInfo = $this->getNodeInfo($nNodeID);
        if(!$aNodeInfo)
            return false;

        //get upper node info
        $sQuery = 'SELECT * FROM '.$this->treeTable.' 
                   WHERE numleft<'.$aNodeInfo['numleft'].' AND numlevel='.$aNodeInfo['numlevel'].' AND pid='.$aNodeInfo['pid'].' '. 
                         ($this->useGroups?' AND '.$this->groupField.'="'.$aNodeInfo[$this->groupField].'"':'').'
                         ORDER BY numleft DESC LIMIT 1';
        $aUpperNodeInfo = $this->db->one_array($sQuery);
        if(!$aUpperNodeInfo)
            return false;
        
        if(($aUpperNodeInfo['numright'] - $aUpperNodeInfo['numleft']) == 1 &&
           ($aNodeInfo['numright'] - $aNodeInfo['numleft']) == 1 )
            $this->changePosition($aNodeInfo, $aUpperNodeInfo);
        else
           $this->changePosiotionAll($aNodeInfo, $aUpperNodeInfo, 'before'); 
        
        return true;
    }

    function moveNodeDown($nNodeID)
    {
        //get node info
        $aNodeInfo = $this->getNodeInfo($nNodeID);
        if(!$aNodeInfo)
            return false;

        //get lower node info
        $sQuery = 'SELECT * FROM '.$this->treeTable.' 
                   WHERE numleft>'.$aNodeInfo['numleft'].' AND numlevel='.$aNodeInfo['numlevel'].' AND pid='.$aNodeInfo['pid'].' '.  
                         ($this->useGroups?' AND '.$this->groupField.'="'.$aNodeInfo[$this->groupField].'"':'').' 
                   ORDER BY numleft ASC LIMIT 1';
        $aLowerNodeInfo = $this->db->one_array($sQuery);
        if(!$aLowerNodeInfo)
            return false;

        if(($aLowerNodeInfo['numright'] - $aLowerNodeInfo['numleft']) == 1 &&
           ($aNodeInfo['numright'] - $aNodeInfo['numleft']) == 1)
            $this->changePosition($aNodeInfo, $aLowerNodeInfo);
        else
           $this->changePosiotionAll($aNodeInfo, $aLowerNodeInfo, 'after');
           
        return true;
    }
    
    function changePosition($aFirstNode, $aSecondNode)
    {
        $sQuery = 'UPDATE '.$this->treeTable.'
                    SET numleft='.$aFirstNode['numleft'].',
                        numright='.$aFirstNode['numright'].',
                        numlevel='.$aFirstNode['numlevel'].',
                        pid='.$aFirstNode['pid'].'
                    WHERE id='.$aSecondNode['id'];
        $this->db->execute($sQuery);
        
        $sQuery = 'UPDATE '.$this->treeTable.'
                    SET numleft='.$aSecondNode['numleft'].',
                        numright='.$aSecondNode['numright'].',
                        numlevel='.$aSecondNode['numlevel'].',
                        pid = '.$aSecondNode['pid'].'
                    WHERE id='.$aFirstNode['id'];
        $this->db->execute($sQuery);
    }

    function rotateTablednd($sPrefix = 'dnd-')
    {
                          
        do{
            /*
            * dragged  - перемещаемый елемент
            * target   - елемент 'до' или 'после' которого, оказался перемещаемый елемент (сосед)
            * position - новая позиция перемещаемого елемента относительно 'target' елемента
            */              
            
            $nDraggedID = intval(str_replace($sPrefix, '', (!empty($_POST['dragged'])?$_POST['dragged']:'') ));
            if($nDraggedID<=0) break;
            
            $nNeighboorID = intval(str_replace($sPrefix, '', (!empty($_POST['target'])?$_POST['target']:'') )); 
            if($nNeighboorID<=0) break;

            if(!$sPosition = func::POST('position', true))
                break;
            elseif(!in_array($sPosition, array('after', 'before')))
                break;    
            
            $FirstNode = $this->getNodeInfo($nDraggedID);
            $aSecondNode = $this->getNodeInfo($nNeighboorID);
                       
            return $this->changePosiotionAll($FirstNode, $aSecondNode, $sPosition);

        } while(false);                    
        
        return false;
    }
    
    function changePosiotionAll($aFirstNode, $aSecondNode, $sPosition='after')
    {
        $leftId1 = $aFirstNode['numleft'];
        $rightId1 = $aFirstNode['numright'];
        $level1 = $aFirstNode['numlevel'];

        $leftId2 = $aSecondNode['numleft'];
        $rightId2 = $aSecondNode['numright'];
        $level2 = $aSecondNode['numlevel'];
        
        if ('before' == $sPosition) {
            if ($leftId1 > $leftId2) {
                $sQuery = 'UPDATE '.$this->treeTable.' SET 
                numright = CASE WHEN numleft BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN numright - ' . ($leftId1 - $leftId2).'
                WHEN numleft BETWEEN ' . $leftId2 . ' AND ' . ($leftId1 - 1) . ' THEN numright +  ' . ($rightId1 - $leftId1 + 1) . ' ELSE numright END,
                numleft = CASE WHEN numleft BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN numleft - ' . ($leftId1 - $leftId2).'
                WHEN numleft BETWEEN ' . $leftId2 . ' AND ' . ($leftId1 - 1) . ' THEN numleft + ' . ($rightId1 - $leftId1 + 1) . ' ELSE numleft END 
                WHERE numleft BETWEEN ' . $leftId2 . ' AND ' . $rightId1;
            } else {
                $sQuery = 'UPDATE '.$this->treeTable.' SET 
                  numright = CASE WHEN numleft BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN numright + ' . (($leftId2 - $leftId1) - ($rightId1 - $leftId1 + 1)).'
                  WHEN numleft BETWEEN ' . ($rightId1 + 1) . ' AND ' . ($leftId2 - 1) . ' THEN numright - ' . (($rightId1 - $leftId1 + 1)) . ' ELSE numright END,
                  numleft = CASE WHEN numleft BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN numleft + ' . (($leftId2 - $leftId1) - ($rightId1 - $leftId1 + 1)).'
                  WHEN numleft BETWEEN ' . ($rightId1 + 1) . ' AND ' . ($leftId2 - 1) . ' THEN numleft - ' . ($rightId1 - $leftId1 + 1) . ' ELSE numleft END
                  WHERE numleft BETWEEN ' . $leftId1 . ' AND ' . ($leftId2 - 1);
            }
        }
        if ('after' == $sPosition) {
            if ($leftId1 > $leftId2) {
                $sQuery = 'UPDATE '.$this->treeTable.' SET 
                  numright = CASE WHEN numleft BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN numright - ' . ($leftId1 - $leftId2 - ($rightId2 - $leftId2 + 1)).'
                  WHEN numleft BETWEEN ' . ($rightId2 + 1) . ' AND ' . ($leftId1 - 1) . ' THEN numright +  ' . ($rightId1 - $leftId1 + 1) . ' ELSE numright END, 
                  numleft = CASE WHEN numleft BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN numleft - ' . ($leftId1 - $leftId2 - ($rightId2 - $leftId2 + 1)).'
                  WHEN numleft BETWEEN ' . ($rightId2 + 1) . ' AND ' . ($leftId1 - 1) . ' THEN numleft + ' . ($rightId1 - $leftId1 + 1) . ' ELSE numleft END 
                  WHERE numleft BETWEEN ' . ($rightId2 + 1) . ' AND ' . $rightId1;
            } else {
                $sQuery = 'UPDATE '.$this->treeTable.' SET  
                   numright = CASE WHEN numleft BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN numright + ' . ($rightId2 - $rightId1).'
                   WHEN numleft BETWEEN ' . ($rightId1 + 1) . ' AND ' . $rightId2 . ' THEN numright - ' . (($rightId1 - $leftId1 + 1)).' ELSE numright END,
                   numleft = CASE WHEN numleft BETWEEN ' . $leftId1 . ' AND ' . $rightId1 . ' THEN numleft + ' . ($rightId2 - $rightId1).'
                   WHEN numleft BETWEEN ' . ($rightId1 + 1) . ' AND ' . $rightId2 . ' THEN numleft - ' . ($rightId1 - $leftId1 + 1) . ' ELSE numleft END
                   WHERE numleft BETWEEN ' . $leftId1 . ' AND ' . $rightId2;
            }
        }
        
        if(isset($sQuery))
            return $this->db->execute($sQuery);
        
        return false;
    }   
    
    function toggleNodeEnabled($nNodeID, $bIntField = true, $bToggleChildNodes = false)
    {
        $aNodeInfo = $this->getNodeInfo((int)$nNodeID);
        
        if(!$aNodeInfo)
            return false;
 
        if($bToggleChildNodes)
        {             
            $this->db->execute('UPDATE '.$this->treeTable.' SET enabled='.($bIntField ? ($aNodeInfo['enabled']?1:0) : ($aNodeInfo['enabled']=='Y'?'"N"':'"Y"') ).' 
                           WHERE numleft>='.$aNodeInfo['numleft'].' AND numright<='.$aNodeInfo['numright'].
                                 ($this->useGroups?' AND '.$this->groupField.'="'.$aNodeInfo[$this->groupField].'"':'') );
        }
        else
        {
            $this->db->execute('UPDATE '.$this->treeTable.' SET enabled = '.($bIntField?' (1-enabled) ':' IF(enabled="Y","N","Y") ').'
                           WHERE id='.((int)$nNodeID).'
                           '.($this->useGroups?' AND '.$this->groupField.'="'.$aNodeInfo[$this->groupField].'"':'').' ');
        }
        
        return true;
    }
    
     /**
     * Функция проверки дерева на валидность
     * @param boolean с отчетом или без отчета
     * @return boolean прошло валидацию или нет, если отчет включен - развернутый ответ
     */
    function validate($debug = true)
    {
        $i=0;
        $aResult = array(0=>'',1=>'',2=>'',3=>'',4=>'',5=>''); 
        do{         
            switch($i)
            {
               case 0:{ #Левый ключ ВСЕГДА меньше правого
                  $aRes = $this->db->select_one_column('SELECT id FROM '.$this->treeTable.' WHERE numleft >= numright');
                 
                  if(count($aRes)>0)
                  {
                          if(!$debug) return false;
                          $aResult[0]='id: '. implode(', ',$aRes);
                  } 
               }break;
               case 1:{ #Наибольший правый ключ ВСЕГДА равен двойному числу узлов;
                        #Наименьший левый ключ ВСЕГДА равен 1
                     $aRes = $this->db->one_array('SELECT COUNT(id) as n, MIN(numleft) as min_left,MIN(id) as root_id, MAX(numright) as max_right FROM '.$this->treeTable);
                     if($aRes['min_left']!=1) 
                     {   
                            if(!$debug) return false;  
                            $aResult[1]= 'Наименьший левый ключ '.$aRes['min_left'].' != 1<br />(id: '.$aRes['root_id'].')';
                           
                     }                                                                                                
                     if($aRes['max_right'] != 2*$aRes['n'])
                     {
                            if(!$debug) return false;  
                            $aResult[2]= 'Наибольший правый ключ :'.$aRes['max_right'].'  !=  '.(2*$aRes['n']).' <br />(id: '.$aRes['root_id'].')' ;
                     }
               }break;
               case 2:{ #Разница между правым и левым ключом ВСЕГДА нечетное число;
                       $aRes = $this->db->select_one_column('SELECT mm.id 
                                                FROM '.$this->treeTable.' as mm,
                                                (SELECT '.$this->treeTable.'.id,  MOD((numright - numleft) , 2) AS ostatok 
                                                 FROM '.$this->treeTable.') as m
                                                WHERE mm.id = m.id AND m.ostatok=0' );
                       if($aRes) 
                       {
                            if(!$debug) return false;  
                            $aResult[3]='id: '.implode(', ',$aRes);
                       }
              }break;                   
              case 3:{ #Если уровень узла нечетное число то тогда левый ключ ВСЕГДА четное число, то же
                      $aRes = $this->db->select_one_column('  SELECT mm.id FROM '.$this->treeTable.' as mm, 
                            (SELECT '.$this->treeTable.'.id, MOD( ('.$this->treeTable.'.numleft - '.$this->treeTable.'.numlevel + 1) , 2) AS ostatok 
                            FROM '.$this->treeTable.')as m
                            WHERE mm.id=m.id AND m.ostatok = 1'); 
                      
                      if($aRes) 
                       {
                            if(!$debug) return false;  
                            $aResult[4]='id: '.implode(', ',$aRes);
                       }           
               }break;
               case 4:{ #Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый;  +
                        $aRes = $this->db->one_array('SELECT t1.id, COUNT(t1.id) AS rep, MAX(t3.numright) AS max_right
                               FROM '.$this->treeTable.' AS t1, '.$this->treeTable.' AS t2, '.$this->treeTable.' AS t3 
                               WHERE t1.numleft <> t2.numleft AND t1.numleft <> t2.numright AND t1.numright <> t2.numleft AND t1.numright <> t2.numright
                               GROUP BY t1.id HAVING max_right <> SQRT(4 * rep + 1) + 1'); 
                                  
                       if($aRes) 
                       {
                            if(!$debug) return false;
                            foreach($aRes as $v)
                                $aResult[5].= '[id:'.$v['id'].'  max_right:  '.$v['max_right'].']<br/>';
                       }      
               }break;             
            }
            $i++;   
        } while($i<count($aResult)-1) ;

        if($debug) {
            return tpl::fetchPHP($aResult, PATH_CORE.'database'.DIRECTORY_SEPARATOR.'nestedsets'.DIRECTORY_SEPARATOR.'validation.php');
        }  
                    
        return true;       
    }    
    
}  
