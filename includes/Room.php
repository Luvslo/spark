<?php
/**
 * 
 * @author Krunal
 * Room class for the rooms of the world. 
 *
 */
class Room {
	private $x;
	private $y;
	private $z;
	private $transparency;
	private $description;
	private $name;
	private $descriptions = array(
			'You are in a beautiful garden. Enjoy the roses!',
			'You are in a dining room. Enjoy some food!',
			'You are in a living room. Enjoy watching TV!',
			'You are in the bedroom. Go to sleep!',
			'You are in the class room. This class room has 45 students'
	);
	
		
	public function __construct($x, $y, $z) {
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->transparency = Room::checkOpacity($x, $y, $z);
		$this->name = $x.$y.$z;
		$rand = rand(0, count($this->descriptions) - 1);
		$this->description = $this->descriptions[$rand];
	}
	
	public function getX() {
		return $this->x;
	}
	
	public function getY() {
		return $this->y;
	}
	
	public function getZ() {
		return $this->z;
	}

	public function getDescription() {
		return $this->description;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getOpacity() {
		return $this->transparency;
	}
	
	public static function checkOpacity($x, $y, $z) {
		$transparency = TRUE;
		if($z % 2 == 0 && $y % 2 == 0 && $x % 2 == 0)
				$transparency = FALSE;
		elseif($z % 2 != 0 && $y % 2 != 0 && $x % 2 != 0)
				$transparency = FALSE;
		
		return $transparency;
	}
}