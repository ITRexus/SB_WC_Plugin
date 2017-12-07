<?php
	class Counter{
		var $counter;
		const DOCPATH = "/home/strongbo/docs/";
		function Counter(){
			if(isset($_POST['type'])){
				$this->increment($_POST['type']);
			}
		}
		function increment($typeInc){
			$counter = file_get_contents(self::DOCPATH . $typeInc);
			if(!$counter){$counter = '0';}
			$counter += 1;
			file_put_contents(self::DOCPATH . $typeInc, $counter);
			$this->counter = $counter;
			return $counter;
		}
		function getCounter($typeInc) {
			$counter = file_get_contents(self::DOCPATH . $typeInc);
			if(!$counter){$counter = '1';}
			file_put_contents(self::DOCPATH . $typeInc, $counter);
			$this->counter = $counter;
			return $counter;
		}
	}

$counter = new Counter;
?>