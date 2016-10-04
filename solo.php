<?php

/* SVG lib */

class Solo
{
	protected $width;
	protected $height;
	protected $elems = [];

	public function __construct($width = 300, $height = 300) {
		$this->width = $width;
		$this->height = $height;
	}

	public function render() {
		$xmlns = "xmlns:xlink='http://www.w3.org/1999/xlink'";
		$output = "<svg width='$this->width' height='$this->height' $xmlns>";
		foreach ($this->elems as $elem) {
			$output .= $elem->render();
		}
		$output .= "</svg>";
		return $output;
	}

	public function add(SoloElem $elem) {
		$this->elems[] = $elem;
		return $this;
	}

	public function duplicate() {
		$elem = clone end($this->elems);
		$this->add($elem);
		$elem->attr('id', null);
		return $elem;
	}

	public function rect($x, $y, $width, $height = null) {
		$elem = new SoloRect($x, $y, $width, $height);
		$this->add($elem);
		return $elem;
	}

	public function circle($cx, $cy, $r) {
		$elem = new SoloCircle($cx, $cy, $r);
		$this->add($elem);
		return $elem;
	}

	public function ellipse($cx, $cy, $rx, $ry) {
		$elem = new SoloEllipse($cx, $cy, $rx, $ry);
		$this->add($elem);
		return $elem;
	}

	public function polyline($points) {
		$elem = new SoloPolyline($points);
		$this->add($elem);
		return $elem;
	}

	public function text($x, $y, $text) {
		$elem = new SoloText($x, $y, $text);
		$this->add($elem);
		return $elem;
	}

	public function path($d) {
		$elem = new SoloPath($d);
		$this->add($elem);
		return $elem;
	}

	public function image($href, $x, $y, $width, $height) {
		$elem = new SoloImage($href, $x, $y, $width, $height);
		$this->add($elem);
		return $elem;
	}
}

class SoloElem 
{
	protected $tag;
	protected $fromCenter = false;
	protected $innerHTML = "";
	protected $link;
	protected $animate = [];

	protected $attr = [ // allowed attributes
		'id' => null,
		'class' => null,
		'x' => null,
		'y' => null,
		'cx' => null,
		'cy' => null,
		'dx' => null,
		'dy' => null,
		'r' => null,
		'rx' => null,
		'ry' => null,
		'width' => null,
		'height' => null,
		'text' => null,
		'd' => null,
		'style' => null,
		'stroke' => 'black',
		'stroke-width' => '1',
		'stroke-opacity' => null,
		'fill' => 'none',
		'fill-opacity' => null,
		'opacity' => null,
		'transform' => null,
		'onclick' => null,
		'onmouseenter' => null,
		'onmouseleave' => null,
	];

	public function __call($attr, $args) {
		if (in_array($attr, array_keys($this->attr))) {
			$this->attr[$attr] = reset($args);
		}
		return $this;
	}

	public function render() {
		$output = "<{$this->tag} ";
		foreach ($this->attr as $key => $value) {
			if (!is_null($value)) {
				$output .= " $key='$value' ";
			}
		}

		$output .= " " . $this->renderCustomAttr() . " ";
		$animate = "";
		foreach ($this->animate as $item) {
			$animate .= sprintf("<animate attributeType='%s' attributeName='%s' from='%s' to='%s' begin='$s' dur='%s' repeatCount='%s' />",
				$item['type'], 
				$item['name'], 
				$item['from'], 
				$item['to'], 
				$item['begin'],
				$item['dur'],
				$item['repeat']
			);
		}
		$output .= ">{$this->innerHTML}{$animate}</{$this->tag}>";
		if (!is_null($this->link)) {
			$output = "<a xlink:href='$this->link'>$output</a>";
		}
		return $output;
	}

	public function renderCustomAttr() {
		return "";
	}

	public function __toString() {
		return $this->render();
	}

	public function attr($attr, $value) {
		$this->attr[$attr] = $value;
		return $this;
	}

	public function stroke($stroke, $stroke_width = null) {
		$this->attr['stroke'] = $stroke;
		if (!is_null($stroke_width)) {
			$this->attr['stroke-width'] = $stroke_width;
		}
		return $this;
	}

	public function fromCenter($yes = true) {
		$this->fromCenter = $yes;
		return $this;
	}

	public function move($dx, $dy) {
		$attrx = !is_null($this->attr['x']) ? 'x' : (!is_null($this->attr['cx']) ? 'cx' : null);
		$attry = !is_null($this->attr['y']) ? 'y' : (!is_null($this->attr['cy']) ? 'cy' : null);
		if ($attrx) {
			$this->attr[$attrx] += $dx;
		} 
		if ($attry) {
			$this->attr[$attry] += $dy;
		}
		return $this;
	}

	public function moveTo($new_x, $new_y) {
		$this->attr['x'] = $new_x;
		$this->attr['y'] = $new_y;
		return $this;
	}

	public function link($url) {
		$this->link = $url;
		return $this;
	}

	public function animate($params = null) {
		$default = [
			'type' => 'css',
			'name' => 'opacity',
			'from' => '1',
			'to' => '0',
			'begin' => '0s',
			'dur' => '1',
			'repeat' => '1', 
		];
		if (!is_array($params)) {
			$params = $default;
		} else {
			$params += $default;
		}

		$this->animate[] = $params;
		return $this;
	}
}

class SoloCircle extends SoloElem
{
	public function __construct($cx, $cy, $r) {
		$this->tag = 'circle';
		$this->attr['cx'] = $cx;
		$this->attr['cy'] = $cy;
		$this->attr['r'] = $r;
		$this->fromCenter = true; // by default for circle
	}
}

class SoloEllipse extends SoloElem
{
	public function __construct($cx, $cy, $rx, $ry) {
		$this->tag = 'ellipse';
		$this->attr['cx'] = $cx;
		$this->attr['cy'] = $cy;
		$this->attr['rx'] = $rx;
		$this->attr['ry'] = $ry;
	}
}

class SoloRect extends SoloElem
{
	public function __construct($x, $y, $width, $height = null) {
		$this->tag = 'rect';
		$this->attr['x'] = $x;
		$this->attr['y'] = $x;
		$this->attr['width'] = $width;
		$this->attr['height'] = is_null($height) ? $width : $height;
	}

	public function round($rx, $ry = null) {
		$this->attr['ry'] = $this->attr['rx'] = $rx;
		if (!is_null($ry)) {
			$this->attr['ry'] = $ry;
		}
		return $this;
	}
}


class SoloPolyline extends SoloElem
{
	protected $points = [];
	protected $pointsStr = null;
	
	public function __construct($points) {
		$this->tag = "polyline";
		if (is_string($points)) {
			$this->pointsStr = $points;
		} elseif (is_array($points)) {
			$this->points = [];
			foreach ($points as $point) {
				$this->points[] = $point;
			}
		} else {
			$this->pointsStr = "";
		}
	}

	public function renderCustomAttr() {
		if (is_null($this->pointsStr)) {
			$pointsStr = "";
			foreach ($this->points as $point) {
				if (is_string($point)) {
					$pointsStr .= $point . " ";	
				} else {
					$pointsStr .= $point[0] . "," . $point[1] . " ";
				}
			}
		} else {
			$pointsStr = $this->pointsStr;
		}
		$pointsStr = " points='$pointsStr'";
		return $pointsStr;
	}
}

class SoloText extends SoloElem
{
	public function __construct($x, $y, $text) {
		$this->tag = "text";
		$this->attr['x'] = $x;
		$this->attr['y'] = $y;
		$this->attr['text'] = $text;
		$this->innerHTML = $text;
	}

	public function text($text) {
		$this->innerHTML = "text";
	}
}

class SoloPath extends SoloElem
{
	public function __construct($d) {
		$this->tag = "path";
		$this->attr['d'] = $d;
	}
}

class SoloImage extends SoloElem
{
	public function __construct($href, $x, $y, $width, $height) {
		$this->tag = 'image';
		$this->attr['xlink:href'] = $href;
		$this->attr['x'] = $x;
		$this->attr['y'] = $y;
		$this->attr['width'] = $width;
		$this->attr['height'] = $height;
	}
}


function solo_test()
{
	$solo = new Solo();
	$x = 10;
	$y = 10;
	$w = 100;
	
	$solo->rect($x, $y, 100, 50)->fill('green')->id('svg-main-rect')->round(5, 5);
	foreach ([1,2,3,4,5,6,7,8] as $item) {
		$solo->duplicate()->move(20, 10);
	}

	//$solo->rect($cx, $cy, $w)->fill('lightgray')->round(15)->stroke('black')->id('svg-main-rect');
	/*$solo->polyline([
		[$cx + $w/7, $cy + $w],
		[$cx + $w/2, $cy],
		[$cx + $w*6/7, $cy + $w],
		[$cx, $cy + $w/3],
		[$cx + $w, $cy + $w/3],
		[$cx + $w/7, $cy + $w],
	])->stroke('red', 2)->fill('white');
	$solo->circle($cx + $w/2, $cy + $w/2, $w/4)->fill('green')->id('green-circle');
	$solo->rect($cx + $w/2, $cy + $w/2, $w/4)->fromCenter()->round(20)->fill('lightgreen');

	$l = $w/7;
	$h = $l * 0.87;
	$solo->polyline([
		[$cx + $w/2 - $l/2, $cy + $w/2 + $h/2],
		[$cx + $w/2, $cy + $w/2 - $h/2],
		[$cx + $w/2 + $l/2, $cy + $w/2 + $h/2],
		[$cx + $w/2 - $l/2, $cy + $w/2 + $h/2],
	])->fill('lightblue');

	$solo->text($cx + 15, $cy + 25, "Светлана")->stroke('green');*/

	return $solo->render();
}
