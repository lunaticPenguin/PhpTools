<?php
namespace App\Tools;

/**
 * FormHelper
 * 
 * Class to help while form rendering, to displays default or user values.
 */
class FormHelper
{
	protected static $hashData = array();

	/**
	 * Allows to set the current request's data.
	 * @param array $hashData
	 */
	public static function loadData(array $hashData) {
		static::$hashData = $hashData;
	}
	
	/**
	 * Allows to merge the current request's data with incoming data.
	 * @param array $hashData
	 */
	public static function mergeData(array $hashData) {
		static::$hashData = array_merge(static::$hashData, $hashData);
	}
	
	/**
	 * Allows to know if a value has been registered for a field name.
	 * 
	 * @param string $strVariableName
	 * @return boolean
	 */
	public static function hasValue($strVariableName) {
		return array_key_exists($strVariableName, static::$hashData);
	}
	
	/**
	 * Allows to get the specified field's value.
	 * For all text field and textarea.
	 * 
	 * @param string $strVariableName
	 * @param string $mixedDefault value returned by default
	 * 
	 * @return string
	 */
	public static function getValue($strVariableName, $mixedDefault = '') {
		if (static::hasValue($strVariableName)) {
			return static::$hashData[$strVariableName];
		}
		return $mixedDefault;
	}
	
	/**
	 * Allows to know if a field should be selected.
	 * For select fields'
	 * 
	 * @param string $strVariableName
	 * @param string $strValue
	 * 
	 * @return boolean
	 */
	public static function isSelected($strVariableName, $strValue) {
		if (!static::hasValue($strVariableName)) {
			return false;
		}
		
		if (is_array(static::$hashData[$strVariableName])) {
			return in_array($strValue, static::$hashData[$strVariableName]);
		}
		
		return static::$hashData[$strVariableName] === $strValue;
	}
	
	/**
	 * Allows to get the specific attribute if a field should be selected.
	 * For select fields'
	 *
	 * @param string $strVariableName
	 * @param string $strValue
	 *
	 * @return string
	 */
	public static function getSelected($strVariableName, $strValue) {
		if (static::isSelected($strVariableName, $strValue)) {
			return ' selected="selected"';
		}
		return '';
	}
	
	/**
	* Allows to know if a field should be checked.
	* For radio and checkbox fields'
	 * 
	 * @param string $strVariableName
	 * @param string $strValue
	 * 
	 * @return boolean
	 */
	public static function isChecked($strVariableName, $strValue) {
		return static::isSelected($strVariableName, $strValue);
	}
	
	/**
	 * Allows to get the specific attribute if a field should be checked.
	 * For radio and checkbox fields'
	 *  
	 * @param string $strVariableName
	 * @param string $strValue
	 * @return string
	 */
	public static function getChecked($strVariableName, $strValue) {
		if (static::isSelected($strVariableName, $strValue)) {
			return ' checked="checked"';
		}
		return '';
	}
}
