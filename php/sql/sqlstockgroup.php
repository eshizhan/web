<?php
require_once('sqltable.php');
require_once('sqlstocktransaction.php');

// ****************************** StockGroupSql class *******************************************************
class StockGroupSql extends MemberTableSql
{
    function StockGroupSql($strMemberId) 
    {
        parent::MemberTableSql($strMemberId, TABLE_STOCK_GROUP);
    }
    
    function Get($strGroupName)
    {
    	return $this->GetSingleData($this->BuildWhere_id_extra('groupname', $strGroupName));
    }
    
    function GetTableId($strGroupName)
    {
		return $this->GetTableIdCallback($strGroupName, 'Get');
    }
    
    function Insert($strGroupName)
    {
    	$strMemberId = $this->GetId(); 
    	return TableSql::Insert("(id, member_id, groupname) VALUES('0', '$strMemberId', '$strGroupName')");
    }
    
    function Update($strId, $strGroupName)
    {
		return TableSql::Update("groupname = '$strGroupName' WHERE "._SqlBuildWhere_id($strId));
    }
}

// ****************************** Stock Group table *******************************************************
/*
function SqlCreateStockGroupTable()
{
    $str = 'CREATE TABLE IF NOT EXISTS `camman`.`stockgroup` ('
         . ' `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,'
         . ' `member_id` INT UNSIGNED NOT NULL ,'
         . ' `groupname` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,'
         . ' FOREIGN KEY (`member_id`) REFERENCES `member`(`id`) ON DELETE CASCADE ,'
         . ' UNIQUE ( `groupname`, `member_id` )'
         . ' ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci '; 
	$result = @mysql_query($str);
	if (!$result)	die('Create stockgroup table failed');
}
*/

function SqlGetStockGroupName($strStockGroupId)
{
    if ($stockgroup = SqlGetTableDataById(TABLE_STOCK_GROUP, $strStockGroupId))
	{
		return $stockgroup['groupname'];
	}
	return false;
}

function SqlGetStockGroupById($strGroupId)
{
    return SqlGetTableDataById(TABLE_STOCK_GROUP, $strGroupId);
}

function SqlGetStockGroupMemberId($strGroupId)
{
    if ($group = SqlGetStockGroupById($strGroupId))
    {
        return $group['member_id'];
    }
    return false;
}

function SqlGetStockGroupByMemberId($strMemberId)
{
    return SqlGetTableData(TABLE_STOCK_GROUP, _SqlBuildWhere_member($strMemberId), '`groupname` ASC');
}

// ****************************** StockGroupItemSql class *******************************************************
class StockGroupItemSql extends StockGroupTableSql
{
	var $trans_sql;	// TableSql
	
    function StockGroupItemSql($strStockGroupId) 
    {
        parent::StockGroupTableSql($strStockGroupId, TABLE_STOCK_GROUP_ITEM);
        $this->trans_sql = new TableSql(TABLE_STOCK_TRANSACTION);
    }

    function Get($strStockId)
    {
    	return $this->GetSingleData($this->BuildWhere_id_stock($strStockId));
    }
    
    function GetTableId($strStockId)
    {
		return $this->GetTableIdCallback($strStockId, 'Get');
    }

    function Insert($strStockId)
    {
    	$strGroupId = $this->GetId(); 
    	return TableSql::Insert("(id, group_id, stock_id, quantity, cost, record) VALUES('0', '$strGroupId', '$strStockId', '0', '0.0', '0')");
    }
    
    function DeleteStockTransaction($strGroupItemId)
    {
    	$this->trans_sql->Delete(_SqlBuildWhere('groupitem_id', $strGroupItemId));
    }
}    

// ****************************** Stock Group Item table *******************************************************
/*
function SqlCreateStockGroupItemTable()
{
    $str = 'CREATE TABLE IF NOT EXISTS `camman`.`stockgroupitem` ('
         . ' `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,'
         . ' `group_id` INT UNSIGNED NOT NULL ,'
         . ' `stock_id` INT UNSIGNED NOT NULL ,'
         . ' `quantity` INT NOT NULL ,'
         . ' `cost` DOUBLE(10,3) NOT NULL ,'
         . ' `record` INT NOT NULL ,'
         . ' INDEX ( `record` ),'
         . ' FOREIGN KEY (`group_id`) REFERENCES `stockgroup`(`id`) ON DELETE CASCADE ,'
         . ' UNIQUE ( `stock_id`, `group_id` )'
         . ' ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci '; 
	$result = @mysql_query($str);
	if (!$result)	die('Create stockgroupitem table failed');
}

function SqlAlterStockGroupItemTable()
{    
    $str = 'ALTER TABLE `camman`.`stockgroupitem` ADD '
         . ' INDEX ( `record` )';
//         . ' `quantity` INT DEFAULT \'0\'';
//         . ' `cost` DOUBLE(10,3) DEFAULT \'0.0\'';
//         . ' `filled` DATETIME DEFAULT \'2014-11-13 08:55:00\'';
//         . ' `record` INT DEFAULT \'0\'';
	$result = @mysql_query($str);
	if (!$result)	die('Alter stockgroupitem table failed');
}
*/

function SqlUpdateStockGroupItem($strStockGroupItemId, $strQuantity, $strCost, $strRecord)
{
	$strQry = "UPDATE stockgroupitem SET quantity = '$strQuantity', cost = '$strCost', record = '$strRecord' WHERE id = '$strStockGroupItemId' LIMIT 1";
	return SqlDieByQuery($strQry, 'Update stockgroupitem failed');
}

function SqlGetStockGroupItemId($strGroupId, $strStockId)
{
	$sql = new StockGroupItemSql($strGroupId);
	return $sql->GetTableId($strStockId);
}

function SqlGetStockGroupItemById($strGroupItemId)
{
    return SqlGetTableDataById(TABLE_STOCK_GROUP_ITEM, $strGroupItemId);
}

function SqlGetStockGroupItemByGroupId($strGroupId)
{
	$sql = new StockGroupItemSql($strGroupId);
	return $sql->GetAll();
}

function SqlCountStockGroupItemByStockId($strStockId)
{
    return SqlCountTableData(TABLE_STOCK_GROUP_ITEM, _SqlBuildWhere_stock($strStockId));
}

// ****************************** Stock Group functions *******************************************************

function SqlGetStockGroupItemArray($strGroupId)
{
	if ($result = SqlGetStockGroupItemByGroupId($strGroupId))
	{
	    $ar = array();
		while ($stockgroupitem = mysql_fetch_assoc($result)) 
		{
    		$ar[] = $stockgroupitem['id'];
		}
		@mysql_free_result($result);
        return $ar;
	}
	return false;
}

function SqlGetStockGroupItemSymbolArray($strGroupId)
{
	if ($result = SqlGetStockGroupItemByGroupId($strGroupId))
	{
	    $ar = array();
		while ($stockgroupitem = mysql_fetch_assoc($result)) 
		{
    		$ar[$stockgroupitem['id']] = SqlGetStockSymbol($stockgroupitem['stock_id']);
		}
		@mysql_free_result($result);
		asort($ar);
        return $ar;
	}
	return false;
}

function SqlGetStockGroupPrefetchSymbolArray($strGroupId)
{
    $arSymbol = array();
    if ($result = SqlGetStockGroupItemByGroupId($strGroupId))
	{
        while ($stockgroupitem = mysql_fetch_assoc($result)) 
		{
		    if (intval($stockgroupitem['record']) > 0)
		    {
		        $arSymbol[] = SqlGetStockSymbol($stockgroupitem['stock_id']);
		    }        
		}
		@mysql_free_result($result);
	}
	return $arSymbol;
}

function SqlGetStockGroupArray($sql)
{
	if ($result = $sql->GetAll())
	{
	    $ar = array();
		while ($stockgroupitem = mysql_fetch_assoc($result)) 
		{
		    $strStockId = $stockgroupitem['stock_id'];
		    if ($strSymbol = SqlGetStockSymbol($strStockId))
		    {
		    	$ar[$strStockId] = $strSymbol;
		    }
		}
		@mysql_free_result($result);
		asort($ar);
		return $ar;
	}
	return false;
}

function SqlGetStocksArray($strGroupId)
{
    $ar = array();
	$sql = new StockGroupItemSql($strGroupId);
    $arStockIdName = SqlGetStockGroupArray($sql);
    foreach ($arStockIdName as $strId => $strSymbol)
    {
        $ar[] = $strSymbol;
    }
    return $ar;
}

function SqlDeleteStockGroupByMemberId($strMemberId)
{
	if ($result = SqlGetStockGroupByMemberId($strMemberId)) 
	{
		while ($stockgroup = mysql_fetch_assoc($result)) 
		{
		    SqlDeleteStockGroup($stockgroup['id']);
		}
		@mysql_free_result($result);
	}
}

function SqlDeleteStockGroupItemByGroupId($strGroupId)
{
	$sql = new StockGroupItemSql($strGroupId);
	if ($result = $sql->GetAll())
	{
		while ($stockgroupitem = mysql_fetch_assoc($result)) 
		{
            $sql->DeleteStockTransaction($stockgroupitem['id']);
		}
		@mysql_free_result($result);
		$sql->DeleteAll();
	}
}

function SqlDeleteStockGroup($strGroupId)
{
    SqlDeleteStockGroupItemByGroupId($strGroupId);
    SqlDeleteTableDataById(TABLE_STOCK_GROUP, $strGroupId);
}

function SqlDeleteStockGroupByGroupName($strGroupName)
{
	$sql = new TableSql(TABLE_STOCK_GROUP);
    $strWhere = _SqlBuildWhere('groupname', $strGroupName);
    $iCount = $sql->Count($strWhere);
    DebugVal($iCount, 'GroupName: '.$strGroupName.' total');
    if ($iCount == 0)   return true;
    
    if ($result = $sql->GetData($strWhere))
    {
        while ($stockgroup = mysql_fetch_assoc($result)) 
        {
            SqlDeleteStockGroupItemByGroupId($stockgroup['id']);
        }
        @mysql_free_result($result);
    }
    return $sql->Delete($strWhere);
}

function SqlUpdateStockGroup($strGroupId, $arNew)
{
	$sql = new StockGroupItemSql($strGroupId);
    $arOld = SqlGetStockGroupArray($sql);
    foreach ($arNew as $strStockId => $strSymbol)
	{
	    if (array_key_exists($strStockId, $arOld) == false)
	    {
	    	$sql->Insert($strStockId);
	    }
	}
	
    foreach ($arOld as $strStockId => $strSymbol)
	{
	    if (array_key_exists($strStockId, $arNew) == false)
	    {
            $strId = $sql->GetTableId($strStockId);
            $sql->DeleteStockTransaction($strId);
            $sql->DeleteById($strId);
	    }
	}
}

?>
