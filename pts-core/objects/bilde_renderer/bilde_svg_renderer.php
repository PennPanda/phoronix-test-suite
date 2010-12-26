<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2010, Phoronix Media
	Copyright (C) 2008 - 2010, Michael Larabel
	bilde_svg_renderer: The SVG rendering implementation for bilde_renderer

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class bilde_svg_renderer extends bilde_renderer
{
	public $renderer = 'SVG';
	private $svg = null;

	public function __construct($width, $height, $embed_identifiers = null)
	{
		$this->image_width = $width;
		$this->image_height = $height;
		$this->embed_identifiers = $embed_identifiers;

		$dom = new DOMImplementation();
		$dtd = $dom->createDocumentType('svg', '-//W3C//DTD SVG 1.1//EN', 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd');
		$this->image = $dom->createDocument(null, null, $dtd);
		$this->image->formatOutput = PTS_IS_CLIENT;

		$pts_comment = $this->image->createComment(pts_title(true) . ' [ http://www.phoronix-test-suite.com/ ]');
		$this->image->appendChild($pts_comment);

		$this->svg = $this->image->createElementNS('http://www.w3.org/2000/svg', 'svg');
		$this->svg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
		$this->svg->setAttribute('version', '1.1');
		$this->svg->setAttribute('font-family', 'sans-serif');
		$this->image->appendChild($this->svg);
	}
	public static function renderer_supported()
	{
		return true;
	}
	public function html_embed_code($file_name, $attributes = null, $is_xsl = false)
	{
		$file_name = str_replace('BILDE_EXTENSION', 'svg', $file_name);
		$attributes = pts_arrays::to_array($attributes);
		$attributes['data'] = $file_name;

		if($is_xsl)
		{
			$html = '<object type="image/svg+xml">';

			foreach($attributes as $option => $value)
			{
				$html .= '<xsl:attribute name="' . $option . '">' . $value . '</xsl:attribute>';
			}
			$html .= '</object>';
		}
		else
		{
			$html = '<object type="image/svg+xml"';

			foreach($attributes as $option => $value)
			{
				$html .= $option . '="' . $value . '" ';
			}
			$html .= '/>';
		}

		return $html;
	}
	public function resize_image($width, $height)
	{
		$this->image_width = $width;
		$this->image_height = $height;
	}
	public function render_image($output_file = null, $quality = 100)
	{
		if($this->image == null)
		{
			return false;
		}

		$this->svg->setAttribute('viewbox', '0 0 ' . $this->image_width . ' ' . $this->image_height);
		$this->svg->setAttribute('width', $this->image_width);
		$this->svg->setAttribute('height', $this->image_height);

		$svg_image = $this->image->saveXML();

		return $output_file != null ? @file_put_contents($output_file, $svg_image) : $svg_image;
	}
	public function destroy_image()
	{
		$this->image = null;
	}
	public function write_text_left($text, $font_type, $font_size, $font_color, $bound_x1, $bound_y1, $bound_x2, $bound_y2, $rotate = false)
	{
		$font_size += 1;
		$text_element = $this->image->createElement('text');
		$text_element->setAttribute('x', round($bound_x1));
		$text_element->setAttribute('y', round($bound_y1));
		$text_element->setAttribute('font-size', $font_size);

		if($rotate != false)
		{
			$rotate = ($rotate === true ? 90 : $rotate);
			$text_element->setAttribute('transform', "rotate($rotate $bound_x1 $bound_y1)");
		}

		$text_element->setAttribute('text-anchor', 'start');
		$text_element->setAttribute('dominant-baseline', 'middle');
		$text_element->setAttribute('fill', $font_color);
		$string = $this->image->createTextNode($text);
		$text_element->appendChild($string);

		if($text instanceof pts_graph_ir_value)
		{
			if($text->get_attribute('title') != null)
			{
				$text_element->setAttribute('xlink:title', $text->get_attribute('title'));
			}
			if($text->get_attribute('font-weight') != null)
			{
				$text_element->setAttribute('font-weight', $text->get_attribute('font-weight'));
			}

			if($text->get_attribute('href') != null)
			{
				$link = $this->image->createElement('a');
				$link->setAttribute('xlink:href', $text->get_attribute('href'));
				$link->setAttribute('xlink:show', 'new');
				$link->appendChild($text_element);
				$this->svg->appendChild($link);
				return;
			}
		}

		$this->svg->appendChild($text_element);
	}
	public function write_text_right($text, $font_type, $font_size, $font_color, $bound_x1, $bound_y1, $bound_x2, $bound_y2, $rotate = false)
	{
		$font_size += 1;
		$text_element = $this->image->createElement('text');
		$text_element->setAttribute('x', round($bound_x2));
		$text_element->setAttribute('y', round($bound_y2));
		$text_element->setAttribute('font-size', $font_size);

		if($rotate != false)
		{
			$rotate = ($rotate === true ? 90 : $rotate);
			$text_element->setAttribute('transform', "rotate($rotate $bound_x1 $bound_y1)");
		}

		$text_element->setAttribute('text-anchor', 'end');
		$text_element->setAttribute('dominant-baseline', 'middle');
		$text_element->setAttribute('fill', $font_color);
		$string = $this->image->createTextNode($text);
		$text_element->appendChild($string);

		if($text instanceof pts_graph_ir_value)
		{
			if($text->get_attribute('title') != null)
			{
				$text_element->setAttribute('xlink:title', $text->get_attribute('title'));
			}
			if($text->get_attribute('font-weight') != null)
			{
				$text_element->setAttribute('font-weight', $text->get_attribute('font-weight'));
			}

			if($text->get_attribute('href') != null)
			{
				$link = $this->image->createElement('a');
				$link->setAttribute('xlink:href', $text->get_attribute('href'));
				$link->setAttribute('xlink:show', 'new');
				$link->appendChild($text_element);
				$this->svg->appendChild($link);
				return;
			}
		}

		$this->svg->appendChild($text_element);
	}
	public function write_text_center($text, $font_type, $font_size, $font_color, $bound_x1, $bound_y1, $bound_x2, $bound_y2, $rotate = false)
	{
		$font_size += 1;
		$bound_x1 = round(($bound_x1 != $bound_x2) ? abs($bound_x2 - $bound_x1) / 2 + $bound_x1 : $bound_x1);
		$bound_y1 = round(($bound_y1 != $bound_y2) ? abs($bound_y2 - $bound_y1) / 2 + $bound_y1 : $bound_y1);

		$text_element = $this->image->createElement('text');
		$text_element->setAttribute('x', $bound_x1);
		$text_element->setAttribute('y', $bound_y1);
		$text_element->setAttribute('font-size', $font_size);
		$text_element->setAttribute('text-anchor', 'middle');

		if($rotate != false)
		{
			$rotate = ($rotate === true ? 90 : $rotate);
			$text_element->setAttribute('transform', "rotate($rotate $bound_x1 $bound_y1)");
		}

		$text_element->setAttribute('dominant-baseline', 'text-before-edge');
		$text_element->setAttribute('fill', $font_color);
		$string = $this->image->createTextNode($text);
		$text_element->appendChild($string);

		if($text instanceof pts_graph_ir_value)
		{
			if($text->get_attribute('title') != null)
			{
				$text_element->setAttribute('xlink:title', $text->get_attribute('title'));
			}
			if($text->get_attribute('font-weight') != null)
			{
				$text_element->setAttribute('font-weight', $text->get_attribute('font-weight'));
			}

			if($text->get_attribute('href') != null)
			{
				$link = $this->image->createElement('a');
				$link->setAttribute('xlink:href', $text->get_attribute('href'));
				$link->setAttribute('xlink:show', 'new');
				$link->appendChild($text_element);
				$this->svg->appendChild($link);
				return;
			}
		}

		$this->svg->appendChild($text_element);
	}
	public function draw_rectangle_with_border($x1, $y1, $width, $height, $background_color, $border_color, $title = null)
	{
		$width = $width - $x1;
		$height = $height - $y1;
		$x1 += $width < 0 ? $width : 0;
		$y1 += $height < 0 ? $height : 0;

		$rect = $this->image->createElement('rect');
		$rect->setAttribute('x', $x1);
		$rect->setAttribute('y', $y1);
		$rect->setAttribute('width', $width);
		$rect->setAttribute('height', $height);
		$rect->setAttribute('fill', $background_color);
		$rect->setAttribute('stroke', $border_color);
		$rect->setAttribute('stroke-width', 1);

		if($title != null)
		{
			$rect->setAttribute('xlink:title', $title);
		}

		$this->svg->appendChild($rect);
	}
	public function draw_rectangle($x1, $y1, $width, $height, $background_color)
	{
		$width = $width - $x1;
		$height = $height - $y1;
		$x1 += $width < 0 ? $width : 0;
		$y1 += $height < 0 ? $height : 0;

		$rect = $this->image->createElement('rect');
		$rect->setAttribute('x', $x1);
		$rect->setAttribute('y', $y1);
		$rect->setAttribute('width', $width);
		$rect->setAttribute('height', $height);
		$rect->setAttribute('fill', $background_color);

		$this->svg->appendChild($rect);
	}
	public function draw_rectangle_border($x1, $y1, $width, $height, $border_color)
	{
		$width = $width - $x1;
		$height = $height - $y1;
		$x1 += $width < 0 ? $width : 0;
		$y1 += $height < 0 ? $height : 0;

		$rect = $this->image->createElement('rect');
		$rect->setAttribute('x', $x1);
		$rect->setAttribute('y', $y1);
		$rect->setAttribute('width', $width);
		$rect->setAttribute('height', $height);
		$rect->setAttribute('fill', 'none');
		$rect->setAttribute('stroke', $border_color);
		$rect->setAttribute('stroke-width', 1);

		$this->svg->appendChild($rect);
	}
	public function draw_arc($center_x, $center_y, $radius, $offset_percent, $percent, $body_color, $border_color = null, $border_width = 1, $title = null)
	{
		$deg = ($percent * 360);
		$offset_deg = ($offset_percent * 360);
		$arc = $percent > 0.5 ? 1 : 0;

		$p1_x = round(cos(deg2rad($offset_deg)) * $radius) + $center_x;
		$p1_y = round(sin(deg2rad($offset_deg)) * $radius) + $center_y;
		$p2_x = round(cos(deg2rad($offset_deg + $deg)) * $radius) + $center_x;
		$p2_y = round(sin(deg2rad($offset_deg + $deg)) * $radius) + $center_y;

		$path = $this->image->createElement('path');
		$path->setAttribute('d', "M$center_x,$center_y L$p1_x,$p1_y A$radius,$radius 0 $arc,1 $p2_x,$p2_y Z");
		$path->setAttribute('fill', $body_color);
		$path->setAttribute('stroke', $border_color);
		$path->setAttribute('stroke-width', $border_width);
		$path->setAttribute('stroke-linejoin', 'round');

		if($title != null)
		{
			$path->setAttribute('xlink:title', $title);
		}

		$this->svg->appendChild($path);
	}
	public function draw_polygon($points, $body_color, $border_color = null, $border_width = 0)
	{
		$point_pairs = array();
		$this_pair = array();

		foreach($points as $one_point)
		{
			array_push($this_pair, $one_point);

			if(count($this_pair) == 2)
			{
				$pair = implode(',', $this_pair);
				array_push($point_pairs, $pair);
				$this_pair = array();
			} 
		}

		$polygon = $this->image->createElement('polygon');
		$polygon->setAttribute('fill', $body_color);

		if($border_width > 0)
		{
			$polygon->setAttribute('stroke', $border_color);
			$polygon->setAttribute('stroke-width', $border_width);
			$polygon->setAttribute('points', implode(' ', $point_pairs));
		}

		$this->svg->appendChild($polygon);
	}
	public function draw_ellipse($center_x, $center_y, $width, $height, $body_color, $border_color = null, $border_width = 0, $default_hide = false, $title = null)
	{

		$ellipse = $this->image->createElement('ellipse');
		$ellipse->setAttribute('cx', $center_x);
		$ellipse->setAttribute('cy', $center_y);
		$ellipse->setAttribute('rx', floor($width / 2));
		$ellipse->setAttribute('ry', floor($height / 2));
		$ellipse->setAttribute('stroke', $border_color);
		$ellipse->setAttribute('fill', $body_color);
		$ellipse->setAttribute('stroke-width', $border_width);

		if($title != null)
		{
			$ellipse->setAttribute('xlink:title', $title);
		}

		if($default_hide)
		{ return; // TODO: get working correctly
			$in = $this->image->createElement('set');
			$in->setAttribute('attributeName', 'stroke-opacity');
			$in->setAttribute('from', 0);
			$in->setAttribute('to', '1');
			$in->setAttribute('begin', 'mouseover');
			$in->setAttribute('end', 'mouseout');
			$ellipse->appendChild($in);

			$out = $this->image->createElement('set');
			$out->setAttribute('attributeName', 'fill-opacity');
			$out->setAttribute('from', 0);
			$out->setAttribute('to', 1);
			$out->setAttribute('begin', 'mouseover');
			$out->setAttribute('end', 'mouseout');
			$ellipse->appendChild($out);
		}

		$this->svg->appendChild($ellipse);
	}
	public function draw_line($start_x, $start_y, $end_x, $end_y, $color, $line_width = 1, $title = null)
	{
		$line = $this->image->createElement('line');
		$line->setAttribute('x1', $start_x);
		$line->setAttribute('y1', $start_y);
		$line->setAttribute('x2', $end_x);
		$line->setAttribute('y2', $end_y);
		$line->setAttribute('stroke', $color);
		$line->setAttribute('stroke-width', $line_width);

		if($title != null)
		{
			$line->setAttribute('xlink:title', $title);
		}

		$this->svg->appendChild($line);
	}
	public function draw_dashed_line($start_x, $start_y, $end_x, $end_y, $color, $line_width, $dash_length, $blank_length)
	{
		$line = $this->image->createElement('line');
		$line->setAttribute('x1', round($start_x));
		$line->setAttribute('y1', round($start_y));
		$line->setAttribute('x2', round($end_x));
		$line->setAttribute('y2', round($end_y));
		$line->setAttribute('stroke', $color);
		$line->setAttribute('stroke-width', $line_width);
		$line->setAttribute('stroke-dasharray', $dash_length . ',' . $blank_length);

		$this->svg->appendChild($line);
	}
	public function draw_poly_line($x_y_pair_array, $color, $line_width = 1)
	{
		foreach($x_y_pair_array as &$x_y)
		{
			$x_y = round($x_y[0]) . ',' . round($x_y[1]);
		}
		$poly_points = implode(' ', $x_y_pair_array);

		$polyline = $this->image->createElement('polyline');
		$polyline->setAttribute('stroke', $color);
		$polyline->setAttribute('stroke-width', $line_width);
		$polyline->setAttribute('fill', 'none');
		$polyline->setAttribute('points', implode(' ', $x_y_pair_array));

		$this->svg->appendChild($polyline);
	}
	public function png_image_to_type($file)
	{
		return $file;
	}
	public function jpg_image_to_type($file)
	{
		return $file;
	}
	public function image_copy_merge($source_image_object, $to_x, $to_y, $source_x = 0, $source_y = 0, $width = -1, $height = -1)
	{
		$image = $this->image->createElement('image');
		$image->setAttribute('x', $to_x);
		$image->setAttribute('y', $to_y);
		$image->setAttribute('width', $width);
		$image->setAttribute('height', $height);
		$image->setAttribute('xlink:href', $source_image_object);


		if($source_image_object instanceof pts_graph_ir_value && $source_image_object->get_attribute('href') != null)
		{
			$link = $this->image->createElement('a');
			$link->setAttribute('xlink:href', $source_image_object->get_attribute('href'));
			$link->setAttribute('xlink:show', 'new');
			$link->appendChild($image);
			$this->svg->appendChild($link);
		}
		else
		{
			$this->svg->appendChild($image);
		}
	}
	public function convert_hex_to_type($hex)
	{
		if(($short = substr($hex, 1, 3)) == substr($hex, 4, 3))
		{
			// very basic shortening, but could do it more properly to find #XXYYZZ collapsing to #XYZ
			$hex = '#' . $short;
		}

		return $hex;
	}
	public function convert_type_to_hex($type)
	{
		if(strlen($type) == 4)
		{
			$type .= substr($type, 1);
		}

		return $type;
	}
	public function text_string_dimensions($string, $font_type, $font_size, $predefined_string = false)
	{
		return array(0, 0); // TODO: implement, though seems to do fine without it for the SVG renderer
	}
}

?>
