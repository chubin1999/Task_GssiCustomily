<?php
namespace AHT\GssiCustomily\Helper;

use AHT\GssiCustomily\Model\ResourceModel\GssiCustomily\CollectionFactory;

class Data
{
	private $_price;

    private $_collection;

    protected $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

	public function getPriceSkuPersonalizationCodeQty(array $array)
    {
        $this->_price = $array;

        /*echo "<pre>";
        var_dump($this->_price = $array);
        die();*/
        $collection = $this->collectionFactory->create();

        $this->_collection = $collection;
        $this->toFilterEq('sku');
        $this->toFilterEq('personalizationcode');
        $this->toFilterEq('qty');

        foreach ($collection as $key => $value) {
            $price = $value->getPrice();
            /*echo "<pre>";
            var_dump($price);*/
        }
        // echo "<pre>";
        // var_dump($price);
        return $price;
    }

    public function toFilterLike(String $field)
    {
        /*echo "<pre>";
        var_dump($this->_price[$field]);
        die();*/
        if (isset($this->_price[$field])) {
            $this->_collection->addFieldToFilter($field, ['like' => '%' . $this->_price[$field] . '%']);
        }
    }
    public function toFilterEq(String $field)
    {
        if (isset($this->_price[$field])) {
            $this->_collection->addFieldToFilter($field, ['eq' => $this->_price[$field]]);
        }
    }
}