<?php

namespace com\cminds\payperposts\model;

abstract class TaxonomyTerm extends Model {
	
	const TAXONOMY = '';
	
	static protected $instances;
	
	protected $term;
	
	
	static function bootstrap() {
		add_action('init', array(get_called_class(), 'init'), 3);
	}
	
	
	/**
	 * Get instance
	 * 
	 * @param object|int $term Term object or ID
	 * @return com\cminds\payperposts\model\TaxonomyTerm
	 */
	static function getInstance($term) {
		if (is_scalar($term)) {
			if (!empty(static::$instances[$term])) return static::$instances[$term];
			else if (is_numeric($term)) $term = get_term_by('term_id', $term, static::TAXONOMY);
			else $term = get_term_by('slug', $term, static::TAXONOMY);
		}
		if (!empty($term) AND is_object($term) AND $term->taxonomy == static::TAXONOMY) {
			if (empty(static::$instances[$term->term_id])) {
				static::$instances[$term->term_id] = new static($term);
			}
			return static::$instances[$term->term_id];
		}
	}
	
	
	function __construct($term) {
		$this->term = $term;
	}
	
	
	function getId() {
		return $this->term->term_id;
	}
	
	
	function getName() {
		return $this->term->name;
	}
	
	
	function getSlug() {
		return $this->term->slug;
	}
	

	public function getPermalink() {
		return get_term_link($this->term, self::TAXONOMY);
	}
	

	public function getParentInstance() {
		if ($this->term->parent) {
			return static::getInstance($this->term->parent);
		}
	}
	
	
	public function getParentId() {
		return $this->term->parent;
	}
	

    static function getTree($params = array(), $depth = 0) {
    	$params = shortcode_atts(array('orderby' => 'name', 'hide_empty' => 0, 'parent' => null), $params);
        $terms = get_terms(static::TAXONOMY, $params);
        $output = array();
        foreach ($terms as $term) {
        	if ($obj = static::getInstance($term)) {
	        	$output[$term->term_id] = str_repeat('-', $depth) .' '. $term->name;
	        	$output += static::getTree(array('parent' => $term->term_id), $depth+1);
        	}
        }
        return $output;
    }
    
    
    public static function getTreeArray($params = array(), $depth = 0) {
    	$params = shortcode_atts(array('orderby' => 'name', 'hide_empty' => 0, 'parent' => null), $params);
    	$terms = get_terms(static::TAXONOMY, $params);
    	$output = array();
    	foreach ($terms as $term) {
    		if ($obj = static::getInstance($term)) {
	    		$term->term_id = intval($term->term_id);
	    		$output[$params['parent'] ? $params['parent'] : 0][$term->term_id] = $term;
	    		$output += self::getTreeArray(array('parent' => $term->term_id), $depth+1);
    		}
    	}
    	return $output;
    }
    
	
	
	static function getAll() {
		$terms = get_terms(static::TAXONOMY);
		$output = array();
    	foreach ($terms as $term) {
    		$output[$term->term_id] = static::getInstance($term);
    	}
    	return $output;
	}
	
	
}
