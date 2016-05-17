# Magento Shell Scripts

1) Update Product Attribute from a CSV File

Usage 
 php updateProductAttribute.php -- [options]<br>
 -- attribute <attribute_code> : Attribute Code ( example url_key) - mandatory <br>
 -- file <filename.csv> : CSV file name - mandatory<br>
 -- store_id <store_id> : Store Id , if not provided added to the default store.<br>
 csv file should have sku and the attribute value with header row sku,attribute_code.<br>

 2) Update Category Attributes from a CSV File
 
 Usage:  php updateCategoryAttribute.php -- [options]<br>
			 -- storeId <store ID> : Store ID - Mandatory<br>
			 -- file <filename.csv> : CSV file - Mandatory <br>
 csv file should have category_id field and any / all of the fields - name, description, meta_title, meta_description.<br>
 a header row with field names is a must.<br>
 			 
