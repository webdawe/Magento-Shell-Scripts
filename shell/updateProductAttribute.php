<?php
require_once 'abstract.php';

class Update_Product_Attribute extends Mage_Shell_Abstract
{
	/**
	 * Run the script.
	 * 
	 * @return void
	 */
	public function run()
	{
		if ($this->getArg('attribute'))
		{
			$attribute = $this->getArg('attribute');
		}
		else
		{
			echo "Attribute Parameter Missing!!" . PHP_EOL;
			exit;
		}
		
		$query = "SELECT entity_type_id FROM  eav_entity_type WHERE entity_model = 'catalog/product'";
		$entityTypeId = $this->getReadConnection()->fetchOne($query);
		
		//retrieve Attribute ID and the Backend Model
		$attributeInfo = $this->getAttributeInfo($attribute, $entityTypeId);
		
		if (count($attributeInfo) > 0)
		{
			$attributeId = $attributeInfo[0]['attribute_id'];
			$backendType = $attributeInfo[0]['backend_type'];
			
			$backendTable =  'catalog_product_entity_' . $backendType;
		}
		else
		{
			echo "Attribute Not found" . PHP_EOL;
			exit;
		}

		if ($this->getArg('file'))
		{
			$csv = $this->getArg('file');
		}
		else
		{
			echo "File Parameter Missing!!" . PHP_EOL;
			exit;
		}
		
		$storeId = 0;
		if ($this->getArg('storeId'))
		{
			$storeId = $this->getArg('storeId');
		}
		
		if (!file_exists($csv))
		{
			echo "Error : File Not Found:" . $csv . PHP_EOL;
			exit;
		}
		$products  = $this->getCsvToArray($csv);
		$total = count($products);
		
		$updated = 0;
		$added = 0;
		$error = 0;
		
		
		foreach ($products as $product)
		{
				//get Product Id
				$productId = $this->getProductId($product['sku']);
				
				if (!$productId)
				{
					echo 'NO Product Found for Sku:' . $product['sku'] . PHP_EOL;
					$error++;
					continue;
				}
				$conditions = " entity_type_id = '" . $entityTypeId. "' 
							AND store_id = '" . $storeId . "'  
							AND entity_id = '" . $productId . "' 
							AND attribute_id = '" . $attributeId . "'";
				
				$fields = " entity_type_id = '" . $entityTypeId. "'  
							, store_id = '" . $storeId . "' 
							, entity_id = '" . $productId . "' 
							, attribute_id = '" . $attributeId . "'";			
							
				$query = "SELECT count(*) as cnt 
							FROM " . $backendTable . " 
							WHERE" . $conditions;
				
				$attributeValue = $product[$attribute];
				
				if ($attribute == 'url_key')
				{
					$attributeValue = Mage::getModel('catalog/product_url')->formatUrlKey($attributeValue);
				}
				
				if ($this->getReadConnection()->fetchOne($query) > 0)
				{
					$query = "UPDATE " . $backendTable . "  SET value = '"  . $attributeValue  . "' WHERE" . $conditions;
					
					$updated++;
				}
				else
				{
					$query = "INSERT INTO " . $backendTable . "  SET value = '"  . $attributeValue . "'," . $fields;
					
					$added++;
				}
				
				
				try
				{
					$this->getWriteConnection()->query($query);
					
				}
				catch (Exception $e)
				{
						$error++;
						echo 'Error for SKU:' . $product['sku'] .  $e->getMessage() . PHP_EOL;
						
				}
		}
		
		echo sprintf("From Total %s , %s updated , %s added , %s Error(s) ", $total, $updated , $added, $error) . PHP_EOL;
	}
	
	/**
	 * Retrieve Product ID
	 * @return int
	 */
	 
	public function getProductId($sku)
	{
		$query = "SELECT entity_id FROM catalog_product_entity WHERE sku = '" . $sku . "'";
		
		return $this->getReadConnection()->fetchOne($query);
		
	}
	
	/**
	 * Retrieve Attribute Info
	 * @return array
	 */
	public function getAttributeInfo($attribute, $entityTypeId)
	{
		
		
		//get Attribute Id
		$query = "SELECT attribute_id,backend_type FROM eav_attribute WHERE entity_type_id = '" . $entityTypeId. "' and attribute_code = '" . $attribute . "'";
		
		return $this->getReadConnection()->fetchAll($query);
	}
	
	/**
	 * Retrieve DB Resource
	 * @return Mage_Core_Model_Resource
	 */
	public function getResource()
	{
		return Mage::getSingleton('core/resource');
	}
	
	/**
	 * Retrieve Read Connection
	 * @return Mage_Core_Model_Resource
	 */
	public function getReadConnection()
	{
		return $this->getResource()->getConnection('core_read');
	}
	
	/**
	 * Retrieve Write Connection
	 * @return Mage_Core_Model_Resource
	 */
	public function getWriteConnection()
	{
		return $this->getResource()->getConnection('core_write');
	}
	
	/**
	 * Retrieve CSV Data as an Array
	 * @return Mage_Core_Model_Resource
	 */
	 
	public function getCsvToArray($file)
	{
	
		$row = 0;
		$coloumnNames = array();
		$items = array();
		
		if (($handle = fopen($file, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$num = count($data);
						
				for ($c=0; $c < $num; $c++) {
					if($row ==0){
						$coloumnNames[] =  $data[$c];
					}else{
						$rowData[$coloumnNames[$c]] =  $data[$c];
					}
					
				}
				if($row >0){
					$items[] = $rowData;
				}
				$row++;
			}
			fclose($handle);
		}
		
		return $items;
	}
	
	/**
    * Retrieve Usage Help Message
    */
    public function usageHelp()
    {
     		return '
			Usage:  php updateProductAttribute.php -- [options]
			 -- attribute <attribute_code> : Attribute Code ( example url_key) - mandatory
			 -- file <filename.csv> : CSV file name - mandatory
			 -- store_id <store_id> : Store Id , if not provided added to the default store.
			';
    }
}

$shell = new Update_Product_Attribute();
$shell->run();

?>