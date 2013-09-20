<?php
/**
 * @author Krunal
 * Builder class to build a room. 
 */
require_once 'Room.php';

class RoomBuilder {
	private $x;
	private $y;
	private $z;
	private $transparency;
	
	public function setX($x) {
		$this->x = $x;
		return $this;
	}
	
	public function setY($y) {
		$this->y = $y;
		return $this;
	}
	
	public function setZ($z) {
		$this->z = $z;
		return $this;
	}
	
	public function buildRoom() {
		$this->transparency = Room::checkOpacity($this->x, $this->y, $this->z);	
		$room = new Room($this->x, $this->y, $this->z);
		return $room;
	}
}