<?php

	namespace dc\sorting;

	abstract class URL_KEY
	{
		// If these constant values are changed, make sure to update
		// the mutator methods as well.
		const ORDER		= 'order';
		const FILTER 	= 'field';
	}
	
	abstract class ORDER_MARKUP
	{
		const ASCENDING	= '<span class="glyphicon glyphicon glyphicon-sort-by-alphabet"></span>';
		const DECENDING	= '<span class="glyphicon glyphicon glyphicon-sort-by-alphabet-alt"></span>';
		const NONE 		= '<span class="glyphicon glyphicon glyphicon-sort"></span>';
	}
	
	abstract class ORDER_TYPE
	{
		const ASCENDING = 0;
		const DECENDING	= 1;			
	}

	abstract class FIELD
	{
		const REVISION	= 1;
		const LABEL 	= 2;
	}

?>