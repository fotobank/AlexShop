<?php

/**
 * Simpla CMS
 *
 * @copyright	2011 Denis Pikusov
 * @link		http://simplacms.ru
 * @author		Denis Pikusov
 *
 */
 
require_once('Simpla.php');

/**
 * Class Features
 */
class Features extends Simpla
{

    /**
     * @param array $filter
     *
     * @return array|bool
     */
    public function get_features($filter = array())
	{
		$category_id_filter = '';	
		if(isset($filter['category_id']))
			$category_id_filter = $this->db->placehold('AND id in(SELECT feature_id FROM __categories_features AS cf WHERE cf.category_id in(?@))', (array)$filter['category_id']);
		
		$in_filter_filter = '';	
		if(isset($filter['in_filter']))
			$in_filter_filter = $this->db->placehold('AND f.in_filter=?', (int)$filter['in_filter']);
		
		$id_filter = '';	
		if(!empty($filter['id']))
			$id_filter = $this->db->placehold('AND f.id in(?@)', (array)$filter['id']);
		
		// Выбираем свойства
		$query = $this->db->placehold("SELECT id, name, position, in_filter FROM __features AS f
									WHERE 1
									$category_id_filter $in_filter_filter $id_filter ORDER BY f.position");
		$this->db->query($query);
		return $this->db->results();
	}

    /**
     * @param $id
     *
     * @return bool|int
     */
    public function get_feature($id)
	{
		// Выбираем свойство
		$query = $this->db->placehold('SELECT id, name, position, in_filter FROM __features WHERE id=? LIMIT 1', $id);
		$this->db->query($query);

        return $this->db->result();
	}

    /**
     * @param $id
     *
     * @return array|bool
     */
    public function get_feature_categories($id)
	{
		$query = $this->db->placehold('SELECT cf.category_id as category_id FROM __categories_features cf
										WHERE cf.feature_id = ?', $id);
		$this->db->query($query);
		return $this->db->results('category_id');	
	}

    /**
     * @param $feature
     *
     * @return mixed
     */
    public function add_feature($feature)
	{
		$query = $this->db->placehold('INSERT INTO __features SET ?%', $feature);
		$this->db->query($query);
		$id = $this->db->insert_id();
		$query = $this->db->placehold('UPDATE __features SET position=id WHERE id=? LIMIT 1', $id);
		$this->db->query($query);
		return $id;
	}

    /**
     * @param $id
     * @param $feature
     *
     * @return mixed
     */
    public function update_feature($id, $feature)
	{
		$query = $this->db->placehold('UPDATE __features SET ?% WHERE id in(?@) LIMIT ?', (array)$feature, (array)$id, count((array)$id));
		$this->db->query($query);
		return $id;
	}

    /**
     * @param array $id
     */
    public function delete_feature($id = array())
	{
		if(!empty($id))
		{
			$query = $this->db->placehold('DELETE FROM __features WHERE id=? LIMIT 1', (int)$id);
			$this->db->query($query);
			$query = $this->db->placehold('DELETE FROM __options WHERE feature_id=?', (int)$id);
			$this->db->query($query);	
			$query = $this->db->placehold('DELETE FROM __categories_features WHERE feature_id=?', (int)$id);
			$this->db->query($query);	
		}
	}


    /**
     * @param $product_id
     * @param $feature_id
     */
    public function delete_option($product_id, $feature_id)
	{
		$query = $this->db->placehold("DELETE FROM __options WHERE product_id=? AND feature_id=? LIMIT 1", (int)$product_id, (int)$feature_id);
		$this->db->query($query);
	}


    /**
     * @param $product_id
     * @param $feature_id
     * @param $value
     *
     * @return mixed
     */public function update_option($product_id, $feature_id, $value)
	{	 
		if($value != '')
			$query = $this->db->placehold('REPLACE INTO __options SET value=?, product_id=?, feature_id=?', $value, (int)$product_id, (int)$feature_id);
		else
			$query = $this->db->placehold('DELETE FROM __options WHERE feature_id=? AND product_id=?', (int)$feature_id, (int)$product_id);
		return $this->db->query($query);
	}


    /**
     * @param $id
     * @param $category_id
     */
    public function add_feature_category($id, $category_id)
	{
		$query = $this->db->placehold('INSERT IGNORE INTO __categories_features SET feature_id=?, category_id=?', $id, $category_id);
		$this->db->query($query);
	}

    /**
     * @param $id
     * @param $categories
     */
    public function update_feature_categories($id, $categories)
	{
		$id = (int)$id;
		$query = $this->db->placehold('DELETE FROM __categories_features WHERE feature_id=?', $id);
		$this->db->query($query);
		
		
		if(is_array($categories))
		{
			$values = array();
			foreach($categories as $category)
				$values[] = "($id , ". (int)$category .")";
	
			$query = $this->db->placehold("INSERT INTO __categories_features (feature_id, category_id) VALUES ".implode(', ', $values));
			$this->db->query($query);

			// Удалим значения из options 
			$query = $this->db->placehold('DELETE o FROM __options o
			                               LEFT JOIN __products_categories pc ON pc.product_id=o.product_id
			                               WHERE o.feature_id=? AND pc.position=(SELECT MIN(pc2.position) FROM __products_categories pc2 WHERE pc.product_id=pc2.product_id) AND pc.category_id not in(?@)', $id, $categories);
			$this->db->query($query);
		}
		else
		{
			// Удалим значения из options 
			$query = $this->db->placehold('DELETE o FROM __options o WHERE o.feature_id=?', $id);
			$this->db->query($query);
		}
	}


    /**
     * @param array $filter
     *
     * @return array|bool
     */
    public function get_options(array $filter = [])
    {
        $feature_id_filter = '';
		$product_id_filter = '';
		$category_id_filter = '';
		$visible_filter = '';
		$brand_id_filter = '';
		$features_filter = '';

		if(empty($filter['feature_id']) && empty($filter['product_id']))
			return array();
		
		$group_by = '';
		if(isset($filter['feature_id']))
			$group_by = 'GROUP BY feature_id, value';
			
		if(isset($filter['feature_id']))
			$feature_id_filter = $this->db->placehold('AND po.feature_id in(?@)', (array)$filter['feature_id']);

		if(isset($filter['product_id']))
			$product_id_filter = $this->db->placehold('AND po.product_id in(?@)', (array)$filter['product_id']);

		if(isset($filter['category_id']))
			$category_id_filter = $this->db->placehold('INNER JOIN __products_categories pc ON pc.product_id=po.product_id AND pc.category_id in(?@)', (array)$filter['category_id']);

		if(isset($filter['visible']))
			$visible_filter = $this->db->placehold('INNER JOIN __products p ON p.id=po.product_id AND visible=?', (int)$filter['visible']);

		if(isset($filter['brand_id']))
			$brand_id_filter = $this->db->placehold('AND po.product_id in(SELECT id FROM __products WHERE brand_id in(?@))', (array)$filter['brand_id']);

		if(isset($filter['features']))
			foreach($filter['features'] as $feature=>$value)
			{
				$features_filter .= $this->db->placehold('AND (po.feature_id=? OR po.product_id in (SELECT product_id FROM __options WHERE feature_id=? AND value=? )) ', $feature, $feature, $value);
			}


		$query = $this->db->placehold("
            SELECT po.product_id, po.feature_id, po.value, count(po.product_id) as count
		    FROM __options po $visible_filter $category_id_filter
			WHERE 1 $feature_id_filter $product_id_filter $brand_id_filter $features_filter 
			GROUP BY po.feature_id, po.value 
			ORDER BY value=0, -value DESC, value");

		$this->db->query($query);

        return $this->db->results();
	}

    /**
     * @param $product_id
     *
     * @return array|bool
     */public function get_product_options($product_id)
	{
		$query = $this->db->placehold("SELECT f.id as feature_id, f.name, po.value, po.product_id FROM __options po LEFT JOIN __features f ON f.id=po.feature_id
										WHERE po.product_id in(?@) ORDER BY f.position", (array)$product_id);

		$this->db->query($query);

        return $this->db->results();
	}

}