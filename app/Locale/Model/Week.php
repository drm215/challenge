<?php
	class Week extends AppModel {
		
		public $hasMany = array("Userentry", "Playerentry");
	}
?>