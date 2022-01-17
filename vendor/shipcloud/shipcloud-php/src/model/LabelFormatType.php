<?php

namespace shipcloud\phpclient\model;

use shipcloud\phpclient\model\Enum;

/**
 * LabelFormatType enumeration class defines the format that the returned label should have.
 *
 * @category 	Enum
 * @package  	shipcloud\phpclient\model
 * @author   	Daniel Muenter <info@msltns.com>
 * @version  	0.0.1
 * @since   	0.0.1
 * @license 	GPL 3
 *          	This program is free software; you can redistribute it and/or modify
 *          	it under the terms of the GNU General Public License, version 3, as
 *          	published by the Free Software Foundation.
 *          	This program is distributed in the hope that it will be useful,
 *          	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          	GNU General Public License for more details.
 *          	You should have received a copy of the GNU General Public License
 *          	along with this program; if not, write to the Free Software
 *          	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
class LabelFormatType extends Enum {
	
	const PDF_100_X_70_MM 				= 'pdf_100x70mm';
	
	const PDF_103_X_199_MM 				= 'pdf_103x199mm';
	
	const PDF_A5 						= 'pdf_a5';
	
	const PDF_A6 						= 'pdf_a6';
	
	const PDF_A7 						= 'pdf_a7';
	
	const ZPL_2_100_X_70_MM_203_DPI 	= 'zpl2_100x70mm_203dpi';
	
	const ZPL_2_103_X_199_MM_203_DPI 	= 'zpl2_103x199mm_203dpi';
	
	const ZPL_2_4_X_6_IN_203_DPI 		= 'zpl2_4x6in_203dpi';
	
	const ZPL_2_4_X_6_IN_300_DPI 		= 'zpl2_4x6in_300dpi';
	
	public static function get_class_name() : string {
		return '\\' . __CLASS__;
	}
	
}