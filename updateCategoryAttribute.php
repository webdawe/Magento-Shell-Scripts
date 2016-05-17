<?php
require_once 'abstract.php';

class updateCategoryAttribute extends Mage_Shell_Abstract
{
	/**
	 * Run the script.
	 * 
	 * @return void
	 */
	public function run()
	{
		if (!$this->getArg('file'))
		{
			echo 'File Name not  Provided';
			exit;
		}
		else if(!file_exists($this->getArg('file')))
		{
			echo 'File not exists';
			exit;
		}
		if (!$this->getArg('storeId'))
		{
			echo 'Store ID Provided';
			exit;
		}

		$fileName = $this->getArg('file');
		$storeId = $this->getArg('storeId');

		$categories = $this->getAsArray($fileName);

		foreach ($categories as $category)
		{
			extract($category);
			$categorySingleton = Mage::getSingleton('catalog/category');
			$categorySingleton->load($category_id);

			if ($categoryId = $categorySingleton->getId())
			{

				//update name
				if (trim($name) != '')
				{
					$categorySingleton->setId($category_id);
					$categorySingleton->setStoreId($storeId);
					$categorySingleton->setName(utf8_encode($name));
					$url_key = Mage::getModel('catalog/product_url')->formatUrlKey($name);
					try
					{
						Mage::getModel('catalog/category')->getResource()->saveAttribute($categorySingleton, 'name');

					}
					catch (Exception $error)
					{
						echo 'ERROR NAME:' . $category_id . '-' . $error->getMessage();
					}
				}

				//update description
				if (trim($description)!= '')
				{
					$categorySingleton->setDescription(utf8_encode($description));
					$categorySingleton->setId($category_id);
					$categorySingleton->setStoreId($storeId);
					try
					{
						Mage::getModel('catalog/category')->getResource()->saveAttribute($categorySingleton, 'description');

					}
					catch (Exception $error)
					{
						echo 'ERROR:' . $category_id . '-' . $error->getMessage();
					}

				}
				//update meta title
				if (trim($meta_title)!= '')
				{
					$categorySingleton->setMetaTitle(utf8_encode($meta_title));
					$categorySingleton->setId($category_id);
					$categorySingleton->setStoreId($storeId);
					try
					{
						Mage::getModel('catalog/category')->getResource()->saveAttribute($categorySingleton, 'meta_title');

					}
					catch (Exception $error)
					{
						echo 'ERROR:' . $category_id . '-' . $error->getMessage();
					}

				}

				//update meta description
				if (trim($meta_description)!= '')
				{
					$categorySingleton->setMetaDescription(utf8_encode($meta_description));
					$categorySingleton->setId($category_id);
					$categorySingleton->setStoreId($storeId);
					try
					{
						Mage::getModel('catalog/category')->getResource()->saveAttribute($categorySingleton, 'meta_description');

					}
					catch (Exception $error)
					{
						echo 'ERROR:' . $category_id . '-' . $error->getMessage();
					}

				}
				if (trim($url_key)!= '')
				{
					$categorySingleton->setUrlKey($url_key);
					$categorySingleton->setId($category_id);
					$categorySingleton->setStoreId($storeId);
					try
					{
						Mage::getModel('catalog/category')->getResource()->saveAttribute($categorySingleton, 'url_key');

					}
					catch (Exception $error)
					{
						echo 'ERROR:' . $category_id . '-' . $error->getMessage();
					}

				}
			}
		}

	}


	/**
	 * Retrieve Date Model
	 * @return Mage_Core_Model_Abstract
	 */
	public function getDateHelper()
	{
		return Mage::getSingleton('core/date');
	}

	/**
	 * Retrieve Locale
	 * @return Mage_Core_Model_Locale
	 */
	public function getLocale(){
		return Mage::app()->getLocale();
	}
	/**
	 * 
	 * @return Mage_Core_Model_Resource
	 */
	public function getResource()
	{
		return Mage::getSingleton('core/resource');
	}

	/**
	 * Retrieve Read Connection
	 * @return Varien_Db_Adapter_Interface
	 */
	public function getReadConnection()
	{
		return $this->getResource()->getConnection('core_read');
	}

	/**
	 * Retrieve Write Connection
	 * @return Varien_Db_Adapter_Interface
	 */
	public function getWriteConnection()
	{
		return $this->getResource()->getConnection('core_write');
	}
	

	/**
    * Retrieve Usage Help Message
    */
    public function usageHelp()
    {
     		return '
			Usage:  php updateCategoryAttribute.php -- [options]
			 -- storeId <store ID> : Store ID
			 -- fileName <filename.csv> : CSV file
			';
    }

	/**
	 * Retireve csv file as array
	 * @param $file
	 * @return array
	 */
	public function getAsArray($file)
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
}

$shell = new updateCategoryAttribute();
$shell->run();

?>