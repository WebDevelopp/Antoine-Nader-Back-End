<?php
final class Cart {
	private $products = array();
   
  	public function __construct() {
		$this->config = Registry::get('config');
		$this->session = Registry::get('session');
		$this->db = Registry::get('db');
		$this->language = Registry::get('language');
		$this->tax = Registry::get('tax');
		$this->weight = Registry::get('weight');

		if (!isset($this->session->data['cart'])) {
      		$this->session->data['cart'] = array();
    	}
 
    	foreach ($this->session->data['cart'] as $key => $value) {
      		$array      = explode(':', $key);
      		$product_id = $array[0];
      		$quantity   = $value;

      		if (isset($array[1])) {
        		$options = explode('.', $array[1]);
      		} else {
        		$options = array();
      		} 
	 
      		$product = $this->db->query("SELECT * FROM product p LEFT JOIN product_description pd ON (p.product_id = pd.product_id) LEFT JOIN image i ON (p.image_id = i.image_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->language->getId() . "' AND p.date_available < NOW() AND p.status = '1'");
      	  	
			if ($product->num_rows) {
      			$option_price = 0;

      			$option_data = array();
      
      			foreach ($options as $product_option_value_id) {
        		 	$option_value = $this->db->query("SELECT pov.product_option_id, povd.name, pov.price, pov.prefix 
											   FROM product_option_value pov 
											   LEFT JOIN product_option_value_description povd ON (pov.product_option_value_id = povd.product_option_value_id) 
											   WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' 
											   AND pov.product_id = '" . (int)$product_id . "' 
											   AND povd.language_id = '" . (int)$this->language->getId() . "' ORDER BY pov.sort_order");
					
					if ($option_value->num_rows) {
						$option = $this->db->query("SELECT pod.name FROM product_option po 
											   LEFT JOIN product_option_description pod ON (po.product_option_id = pod.product_option_id) 
											   WHERE po.product_option_id = '" . (int)$option_value->row['product_option_id'] . "'  
											   AND po.product_id = '" . (int)$product_id . "' 
											   AND pod.language_id = '" . (int)$this->language->getId() . "' ORDER BY po.sort_order");
						
        				if ($option_value->row['prefix'] == '+') {
          					$option_price = $option_price + $option_value->row['price'];
        				} elseif ($option_value->row['prefix'] == '-') {
          					$option_price = $option_price - $option_value->row['price'];
        				}
        
        				$option_data[] = array(
          					'product_option_value_id' => $product_option_value_id,
          					'name'                    => $option->row['name'],
          					'value'                   => $option_value->row['name'],
          					'prefix'                  => $option_value->row['prefix'],
          					'price'                   => $option_value->row['price']
        				);
					}
      			}
				
				$product_discount = $this->db->query("SELECT * FROM product_discount WHERE product_id = '" . (int)$product->row['product_id'] . "' AND quantity <= '" . (int)$quantity . "' ORDER BY quantity DESC LIMIT 1");

				if (!$product_discount->num_rows) {
          			$discount = 0;
				} else {
					$discount = $product_discount->row['discount'];
				}
				 
				$download_data = array();     		
				
				$query = $this->db->query("SELECT * FROM product_to_download p2d LEFT JOIN download d ON (p2d.download_id = d.download_id) LEFT JOIN download_description dd ON (d.download_id = dd.download_id) WHERE p2d.product_id = '" . (int)$product_id . "' AND dd.language_id = '" . (int)$this->language->getId() . "'");
			
				foreach ($query->rows as $download) {
        			$download_data[] = array(
          				'download_id' => $download['download_id'],
						'name'        => $download['name'],
						'filename'    => $download['filename'],
						'mask'        => $download['mask'],
						'remaining'   => $download['remaining']
        			);
				}
				
      			$this->products[$key] = array(
        			'key'             => $key,
        			'product_id'      => $product->row['product_id'],
        			'name'            => $product->row['name'],
        			'model'           => $product->row['model'],
					'shipping'        => $product->row['shipping'],
        			'image'           => $product->row['filename'],
        			'option'          => $option_data,
					'download'        => $download_data,
        			'quantity'        => $quantity,
					'stock'           => ($quantity <= $product->row['quantity']),
        			'price'           => ($product->row['price'] + $option_price),
					'discount'        => $discount,
        			'total'           => (($product->row['price'] + $option_price) - $discount) * $quantity,
        			'tax_class_id'    => $product->row['tax_class_id'],
        			'weight'          => $product->row['weight'],
        			'weight_class_id' => $product->row['weight_class_id']
      			);
			} else {
				$this->remove($key);
			}
    	}
	}
		  
  	public function add($product_id, $qty = 1, $options = array()) {
    	if (!$options) {
      		$key = $product_id;
    	} else {
      		$key = $product_id . ':' . implode('.', $options);
    	}
    
    	if (!isset($this->session->data['cart'][$key])) {
      		$this->session->data['cart'][$key] = $qty;
    	} else {
      		$this->session->data['cart'][$key] += $qty;
    	}
  	}

  	public function update($key, $qty) {
    	if ($qty) {
      		$this->session->data['cart'][$key] = $qty;
    	} else {
	  		$this->remove($key);
		}
  	}

  	public function remove($key) {
		if (isset($this->session->data['cart'][$key])) {
     		unset($this->session->data['cart'][$key]);
  		}
	}

  	public function clear() {
		$this->session->data['cart'] = array();
  	}
	      
  	public function getProducts() {
    	return $this->products;
  	}
  	
  	public function getWeight() {
		$weight = 0;
	
    	foreach ($this->products as $product) {
      		$weight += $this->weight->convert($product['weight'] * $product['quantity'], $product['weight_class_id'], $this->config->get('config_weight_class_id'));
    	}
	
		return $weight;
	}
  
  	public function getSubTotal() { 
		$sub_total = 0;
		
		foreach ($this->products as $product) {
 	  		$sub_total += $this->tax->calculate($product['total'], $product['tax_class_id'], $this->config->get('config_tax'));
		}
		
		return $sub_total;
  	}
  	
	public function getTaxes() {
		$taxes = array();
		
		foreach ($this->products as $product) {
			if ($product['tax_class_id']) {
				if (!isset($taxes[$product['tax_class_id']])) {
					$taxes[$product['tax_class_id']] = $product['total'] / 100 * $this->tax->getRate($product['tax_class_id']);
				} else {
					$taxes[$product['tax_class_id']] += $product['total'] / 100 * $this->tax->getRate($product['tax_class_id']);
				}
			}
		}
		
		return $taxes;
  	}
	
  	public function getTotal() {
		$total = 0;
		
		foreach ($this->products as $product) {
			$total += $this->tax->calculate($product['total'], $product['tax_class_id']);
		}

		return $total;
  	}
  	
  	public function countProducts() {
		$total = 0;
		
		foreach ($this->session->data['cart'] as $value) {
			$total += $value;
		}
		
    	return $total;
  	}
	  
  	public function hasProducts() {
    	return count($this->session->data['cart']);
  	}
  
  	public function hasStock() {
		$stock = TRUE;
		
		foreach ($this->products as $product) {
			if (!$product['stock']) {
	    		$stock = FALSE;
			}
		}
		
    	return $stock;
  	}
  
  	public function hasShipping() {
		$shipping = FALSE;
		
		foreach ($this->products as $product) {
	  		if ($product['shipping']) {
	    		$shipping = TRUE;
	  		}		
		}
		
		return $shipping;
	}
}
?>