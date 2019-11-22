<?php

class Player{
	public $name, $health, $strength, $defence, $speed, $luck, $lucky_number;

	function set_property($property, $min, $max){
		$this->$property = rand($min, $max);
	}

	function get_property($property){
		return $this->$property;
	}

	function set_initial_properties($name, $health_min, $health_max, $strength_min, $strength_max, $defence_min, $defence_max, $speed_min, $speed_max, $luck_min, $luck_max){
		$this->name = $name;
		$this->set_property('health', $health_min, $health_max);
		$this->set_property('strength', $strength_min, $strength_max);
		$this->set_property('defence', $defence_min, $defence_max);
		$this->set_property('speed', $speed_min, $speed_max);
		$this->set_property('luck', $luck_min, $luck_max);
		// Player can make at most 10 attacks.
		// number = x% of 10 attacks = x*10/100 
		// number = the number of times when player gets lucky
		$luck = $this->get_property('luck');
		$this->lucky_number = (int) ($luck * 10 / 100);
	}

	function show_initial_properties(){
		echo($this->get_property('name') . "<br>");
		echo('Health: ' . $this->get_property('health') . "<br>");
		echo('Strength: ' . $this->get_property('strength') . "<br>");
		echo('Defence: ' . $this->get_property('defence') . "<br>");
		echo('Speed: ' . $this->get_property('speed') . "<br>");
		echo('Luck: ' . $this->get_property('luck') . "<br><br>");
	}

	function get_initial_properties_array(){
		$array = (object)[
			'Name' => $this->name,
			'Health' => $this->health,
			'Strength' => $this->strength,
			'Defence' => $this->defence,
			'Speed' => $this->speed,
			'Luck' => $this->luck,
		];
		return $array;
	}

}

class Orderus extends Player{
	// Orderus can make at most 10 attacks.
	// number = x% of 10 attacks = x*10/100 
	// number = the number of times when magic happens (rapid strike/ magic shield)
	public $strike_number = 1, $shield_number = 2;
}


function check_magic($player, $magic_number, $message){
	$chances = $player->get_property($magic_number);
	if($chances > 0 and rand(0,1)){
		$player->$magic_number= $chances - 1;
		return true;
	}	
	return false;
}


function show_stats($defender, $damage){
	echo($defender->get_property('name') . " was attacked. <br>");
	echo("Damage: " . $damage . "<br>");
	echo($defender->name . "'s left health: " . $defender->get_property('health') . "<br><br>");
}


function set_damage($attacker, $defender, $active_strike, $active_shield){
	$round_stats = array();
	array_push($round_stats, "Attacker: " . $attacker->name);
	array_push($round_stats, "Defender: " . $defender->name);
	if($active_strike)
		array_push($round_stats, "Rapid strike: yes");
	else
		array_push($round_stats, "Rapid strike: no");
	if($active_shield)
		array_push($round_stats, "Magic shield: yes");
	else
		array_push($round_stats, "Magic shield: no");
	
	$message = $defender->name . " was lucky.";
	$bad_message = $defender->name . " was NOT lucky.";
	
	if(check_magic($defender, 'lucky_number', $message)){
		array_push($round_stats, $message);
		$damage = 0;
	}else{
		array_push($round_stats, $bad_message);
		$damage = $attacker->get_property('strength') - $defender->get_property('defence');
		if($active_strike)
			$damage = $damage * 2;
		if($active_shield)
			$damage = $damage / 2;
	}
	$defender->health = $defender->health - $damage;

	// show_stats($defender, $damage);
	array_push($round_stats, "Damage: " . $damage);
	array_push($round_stats, $defender->name . "'s left health: " . $defender->get_property('health'));

	return $round_stats;
}



function order_first_round($orderus, $beast){
	$orderus_speed = $orderus->get_property('speed');
	$beast_speed =  $beast->get_property('speed');
	$orderus_luck = $orderus->get_property('luck');
	$beast_luck = $beast->get_property('luck');

	// Orderus attacks
	if($orderus_speed > $beast_speed){ 			
		return [$orderus, $beast];
	}
	// Beast attacks
	else if ($orderus_speed < $beast_speed){ 	
		return [$beast, $orderus];
	}
	// Orderus attacks
	else if ($orderus_luck > $beast_luck){ 	
		return [$orderus, $beast];
	}
	// Beast attacks
	else{ 										
		return [$beast, $orderus];
	}

}

function attack($attacker, $defender){
	$active_strike = false;
	$active_shield = false;
	$message_strike = "Orderus uses rapid strike<br>";
	$message_shield = "Orderus uses magic shield<br>";
	
	// Orderus attacks
	if($attacker->get_property('name') == 'Orderus'){ 			
		$active_strike = check_magic($attacker, 'strike_number', $message_strike);
	}
	// Beast attacks
	else{ 	
		$active_shield = check_magic($defender, 'shield_number', $message_shield);
	}

	return set_damage($attacker, $defender, $active_strike, $active_shield); 
	
}


function game_over($orderus, $beast, $battle){
	if($beast->get_property('health') < 1){
		// echo("Game over.<br>The winner is ". $orderus->get_property('name'));
		return "Game over.<br>The winner is ". $orderus->get_property('name');
	}
	if($orderus->get_property('health') < 1){
		// echo("Game over.<br>The winner is ". $beast->get_property('name'));
		return "Game over.<br>The winner is ". $beast->get_property('name');
	}
	return "";
}


function start_game(){
	$orderus = new Orderus();
	$orderus->set_initial_properties("Orderus", 70, 100, 70, 80, 45, 55, 40, 50, 10, 30);

	$beast = new Player();
	$beast->set_initial_properties("Beast", 60, 90, 60, 90, 40, 60, 40, 60, 25, 40);

	$players_order = order_first_round($orderus, $beast);
	$attacker = $players_order[0];
	$defender = $players_order[1];

	$battle = array();

	for($round = 1; $round <= 20; $round++){

		$result = attack($attacker, $defender);	
		$battle["Round-" . $round] = $result;

		if (game_over($orderus, $beast, $battle)!=""){
			$battle["Winner"] = game_over($orderus, $beast, $battle);
			return $battle;
		}
		// Switch roles between attacker and defender
		$temp = $attacker;
		$attacker = $defender;
		$defender = $temp;
	}

	if($round == 21){
		echo("Game over. No one won");
		$battle["Winner"] = "Game over. No one won";
	}

	return $battle;
}

if (isset($_GET['start'])) {
	$orderus = new Orderus();
	$orderus->set_initial_properties("Orderus", 70, 100, 70, 80, 45, 55, 40, 50, 10, 30);

	$beast = new Player();
	$beast->set_initial_properties("Beast", 60, 90, 60, 90, 40, 60, 40, 60, 25, 40);

	$characters = (object)[
		'orderus' => $orderus->get_initial_properties_array(),
		'beast' => $beast->get_initial_properties_array(), 
	];
	$battle = start_game();
	$game = (object)[
		'characters' => $characters,
		'battle' => $battle,
	];
	echo json_encode($game);
}



?>