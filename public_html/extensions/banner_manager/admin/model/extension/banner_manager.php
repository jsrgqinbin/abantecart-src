<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE')) {
    header('Location: static_pages/');
}

class ModelExtensionBannerManager extends Model {
    public function addBanner($data = array()) {

		if ( isset($data['start_date']) ) {
        	$data['start_date'] = "DATE('" . date('Y-m-d', strtotime($data['start_date'])) . "')";
		} else {
			$data['start_date'] = "DATE('')";
		} 
		
		if ( isset($data['end_date']) ) {
	        $data['end_date'] =  "DATE('" . date('Y-m-d', strtotime($data['end_date'])) . "')";
		} else {
			$data['end_date'] = "DATE('')";
		} 

        $sql = "INSERT INTO `" . DB_PREFIX . "banners`
				(`status`,`banner_type`,`banner_group_name`,`start_date`,`end_date`,`blank`,`sort_order`,`target_url`,`date_added`)
				VALUES ('" . (int)$data['status'] . "',
						'" . (int)$data['banner_type'] . "',
						'" . $this->db->escape($data['banner_group_name']) . "',
						" . $data['start_date'] . ",
						" . $data['end_date'] . ",
						'" . (int)$data['blank'] . "',
						'" . (int)$data['sort_order'] . "',
						'" . $this->db->escape($data['target_url']) . "', NOW() )";
        $this->db->query($sql);
        $banner_id = $this->db->getLastId();
        // for graphic banners remap resources
        if ((int)$data['banner_type'] == 1) {
            $sql = "UPDATE `" . DB_PREFIX . "resource_map` SET object_id='" . $banner_id . "' WHERE object_name='banners' AND object_id='-1'";
            $this->db->query($sql);
        }
		$this->language->replaceDescriptions('banner_descriptions',
											 array('banner_id' => (int)$banner_id),
											 array((int)$this->session->data['content_language_id'] => array(
																		'name' => $data['name'],
																		'meta' => $data['meta'],
																		'description' => $data['description']
											 )) );
        $this->db->query($sql);

        return $banner_id;
    }

    public function getBanner($banner_id, $language_id = '') {
        $banner_id = (int)$banner_id;
        $language_id = (int)$language_id;
        if (!$language_id) {
            $language_id = (int)$this->session->data['content_language_id'];
        }
        // check is description presents
        $sql = "SELECT DISTINCT language_id
				FROM `" . DB_PREFIX . "banner_descriptions`
				WHERE banner_id='" . $banner_id . "'
				ORDER BY language_id ASC";
        $result = $this->db->query($sql);
        $counts = array();
        foreach ($result->rows as $row) {
            $counts[] = $row['language_id'];
        }
        if (!in_array($language_id, $counts)) {
            $language_id = $counts[0];
        }

        $sql = "SELECT  bd.*, b.*
				FROM `" . DB_PREFIX . "banners` b
				LEFT JOIN `" . DB_PREFIX . "banner_descriptions` bd ON (bd.banner_id = b.banner_id AND bd.language_id = '" . $language_id . "')
				WHERE b.banner_id='" . $banner_id . "'";
        $result = $this->db->query($sql);
        return $result->row;
    }


    public function getBannerGroups() {
        // check is description presents
        $sql = "SELECT DISTINCT TRIM(banner_group_name) as banner_group_name
				FROM `" . DB_PREFIX . "banners`
				ORDER BY TRIM(banner_group_name) ASC";
        $result = $this->db->query($sql);
        return $result->rows;
    }


    public function editBanner($banner_id, $data) {
        $banner_id = (int)$banner_id;
        $language_id = (int)$this->session->data['content_language_id'];
        if (isset($data['start_date'])) {
            $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
		} 
        if (isset($data['end_date'])) {
            $data['end_date'] = date('Y-m-d', strtotime($data['end_date']));
		} 

		$flds = array('name', 'description', 'meta');
		foreach ($flds as $field_name) {
			if (isset($data[$field_name])) {
				$update[$field_name] = $data[$field_name];
			}
		}

		if( count($update) ) {
			$this->language->replaceDescriptions('banner_descriptions',
											 array('banner_id' => (int)$banner_id),
											 array($language_id => $update) );		
		}


        $flds = array(  'status' => 'int',
					    'banner_type' => 'int',
						'banner_group_name' => '',
						'start_date' => '',
						'end_date' => '',
						'blank' => 'int',
						'sort_order' => 'int',
						'target_url' => '');
        $sql = "UPDATE `" . DB_PREFIX . "banners`
				SET ";
        $tmp = array();
        foreach (array_keys($flds) as $field_name) {
            if (isset($data[$field_name])) {
                $tmp[] = "`" . $field_name . "` = '" . ($flds[$field_name] == 'int' ? (int)$data[$field_name] : $this->db->escape($data[$field_name])) . "'\n";
            }
        }
        $sql .= implode(', ', $tmp);
        $sql .= " WHERE banner_id='" . $banner_id . "'";
        if ($tmp) {
            $this->db->query($sql);
        }
    }

    public function deleteBanner($banner_id) {
        $banner_id = (int)$banner_id;
        if (!$banner_id) return false;

        $sql[] = "DELETE FROM `" . DB_PREFIX . "banners` WHERE banner_id = '" . $banner_id . "'";
        $sql[] = "DELETE FROM `" . DB_PREFIX . "banner_descriptions` WHERE banner_id = '" . $banner_id . "'";
        $sql[] = "DELETE FROM `" . DB_PREFIX . "resource_map` WHERE object_name = 'banners'  AND object_id = '" . $banner_id . "'";
        foreach ($sql as $s) {
            $this->db->query($s);
        }
        return true;
    }


    public function getBanners($filter, $mode = '') {
        if (!empty($data['content_language_id'])) {
            $language_id = ( int )$data['content_language_id'];
        } else {
            $language_id = (int)$this->config->get('storefront_language_id');
        }

        if ($mode == 'total_only') {
            $sql = "SELECT COUNT(*) as total
						FROM " . DB_PREFIX . "banners b
						LEFT JOIN " . DB_PREFIX . "banner_descriptions bd ON (b.banner_id = bd.banner_id AND bd.language_id = '" . $language_id . "')";
        } else {
            $sql = "SELECT bd.*, b.*
						FROM " . DB_PREFIX . "banners b
						LEFT JOIN " . DB_PREFIX . "banner_descriptions bd ON (b.banner_id = bd.banner_id AND bd.language_id = '" . $language_id . "')";
        }

        if (!empty($filter['subsql_filter'])) {
            $sql .= " WHERE " . $filter['subsql_filter'];
        }


        $sort_data = array(
            'name' => 'bd.name',
            'status' => 'b.status',
            'sort_order' => 'b.sort_order',
            'update_date' => 'b.update_date'
        );

        if (isset($filter['sort']) && in_array($filter['sort'], array_keys($sort_data))) {
            $sql .= " ORDER BY " . $sort_data[$filter['sort']];
        } else {
            $sql .= " ORDER BY bd.name";
        }

        if (isset($filter['order']) && ($filter['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($filter['start']) || isset($filter['limit'])) {
            if ($filter['start'] < 0) {
                $filter['start'] = 0;
            }
            if ($filter['limit'] < 1) {
                $filter['limit'] = 20;
            }
            $sql .= $mode != 'total_only' ? " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'] : '';
        }
        $result = $this->db->query($sql);

        $output = array();
        if ($mode == 'total_only') {
            $output = $result->row['total'];
        } else {
            foreach ($result->rows as $row) {
                if ($row['name']) {
                    $output[] = $row;
                } else {
                    $output[] = $this->getBanner($row['banner_id'], 1);
                }
            }
        }
        return $output;
    }

    public function getBannersStat($filter, $mode = '') {
        if (!empty($data['content_language_id'])) {
            $language_id = ( int )$data['content_language_id'];
        } else {
            $language_id = (int)$this->config->get('storefront_language_id');
        }

        if ($mode == 'total_only') {
            $sql = "SELECT COUNT(*) as total
						FROM " . DB_PREFIX . "banners b
						LEFT JOIN " . DB_PREFIX . "banner_descriptions bd ON (b.banner_id = bd.banner_id)";
        } else {

            $sql = "SELECT `banner_id`, `type`, count(`type`) as cnt
						FROM " . DB_PREFIX . "banner_stat
						GROUP BY `banner_id`, `type`";
            $result = $this->db->query($sql);
            $stats = array();
            foreach ($result->rows as $row) {
                $type = $row['type'] == '1' ? 'viewed' : 'clicked';
                $stats[$row['banner_id']][$type] = $row['cnt'];
            }

            $sql = "SELECT b.banner_id,
								bd.name,
								b.banner_group_name
						FROM " . DB_PREFIX . "banners b
						LEFT JOIN " . DB_PREFIX . "banner_descriptions bd ON (b.banner_id = bd.banner_id) ";
        }

        $sql .= " WHERE bd.language_id = '" . $language_id . "'";
        if (!empty($filter['subsql_filter'])) {
            $sql .= " AND " . $filter['subsql_filter'];
        }

        /*$sort_data = array(
                  'name' => 'bd.name',
                  'banner_group_name' => 'b.banner_group_name',
                  'viewed' => 'viewed',
                  'clicked' => 'clicked'
              );*/
        // TODO need to think about sorting by columns
        /*if (isset($filter['sort']) && in_array($filter['sort'], array_keys($sort_data)) ) {
                  $sql .= " ORDER BY " . $sort_data[$filter['sort']];
              } else {
                  $sql .= " ORDER BY bd.name";
              }

              if (isset($filter['order']) && ($filter['order'] == 'DESC')) {
                  $sql .= " DESC";
              } else {
                  $sql .= " ASC";
              }
              if($mode!='total_only'){
                  if (isset($filter['start']) || isset($filter['limit'])) {
                      if ($filter['start'] < 0) {
                          $filter['start'] = 0;
                      }

                      if ($filter['limit'] < 1) {
                          $filter['limit'] = 20;
                      }

                      $sql .= " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'];
                  }
              }*/


        $result = $this->db->query($sql);
		$index = array();
        if ($mode != 'total_only') {
            foreach ($result->rows as &$row) {
                $row['clicked'] = isset($stats[$row['banner_id']]['clicked']) ? $stats[$row['banner_id']]['clicked'] : 0;
                $row['viewed'] = isset($stats[$row['banner_id']]['viewed']) ? $stats[$row['banner_id']]['viewed'] : 0;
                $row['percent'] = round($row['clicked'] * 100 / $row['viewed'], 2);
                $index[] = $row['percent'];
            }
            unset($row);
            $output = $result->rows;
            // resort by percents
            array_multisort($index, SORT_DESC, $output);
        } else {
            $output = $result->row['total'];
        }

        return $output;
    }
}