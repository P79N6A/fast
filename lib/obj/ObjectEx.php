<?php
/**
 *对象扩展类 
 * @author zengjf
 */
class ObjectEx{
	private $obj;
	/**
	 * 构建对象
	 * @param Object $obj 对象	
	 */	
	function __construct($obj){
		if(is_object($obj)) 
			$this->obj=$obj;
		else{
			$this->obj=new ValueEx();
			$this->obj->value=$obj;
		} 
	}
	/**
	 * 拷贝对象属性到目标对象
	 * @param Object $dest 目标对象
	 * @param array $propList  需要拷贝的特性数组    如果为空，拷贝源对象全部属性
	 */
	function copy($dest,$propList=array()){
		if($propList){
			foreach ($propList as $key){
				if(isset($this->obj->$key))  $dest->$key = $this->obj->$key;
			}
		}else{
			foreach ($this->obj as $key=>$val){
				$dest->$key=$val;
			}
		}
	}	
}
class ValueEx{
	public $value;
}