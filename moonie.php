<?php

/*
Plugin Name: MoonPhase plugin
Plugin URI: http://www.ryumaou.com/hoffman/netgeek/?page_id=1027
Description: A WP plugin to let you record the MoonPhase of a post, with libraries and code "borrowed" from Dan Sheeler and Kumar McMillan 
Version: 1.0a
Author: J. K. Hoffman
Author URI: http://www.ryumaou.com/hoffman/netgeek/
* June 2003 updated July 2010
* authors:
* 	dan sheeler <dsheeler@cs.uchicago.edu>
* 	kumar mcmillan <kumar@chicagomodular.com>
*
* description:
* 	do moon phase calculation stuff
*   You will see a slight drift in the cycle if you compare the results to other phase calculations...
*   this is probably because of different degrees of precision among phase periods used, as well as 
*   float precision from computer to computer.  It is more or less accurate and seems to always report the
*   correct phase name.  Please let us know if you have any improvements, suggestions
*   or questions.  Do not make modifications to this class in case the source changes (see moon-phase.php 
*   for an example of usage). 
*/
require_once(dirname(__FILE__).'/../../wp-config.php');
$moonimgloc = get_settings('siteurl').'/wp-content/plugins/';
define('MP_NEW_MOON_NAME','a New Moon');
define('MP_NEW_MOON_ID',0);
define('MP_WAXING_CRESCENT_NAME','Waxing Crescent');
define('MP_WAXING_CRESCENT_ID',1);
define('MP_FIRST_QUARTER_NAME','a First Quarter Moon');
define('MP_FIRST_QUARTER_ID',2);
define('MP_WAXING_GIBBOUS_NAME','Waxing Gibbous');
define('MP_WAXING_GIBBOUS_ID',3);
define('MP_FULL_MOON_NAME','a Full Moon');
define('MP_FULL_MOON_ID',4);
define('MP_WANING_GIBBOUS_NAME','Waning Gibbous');
define('MP_WANING_GIBBOUS_ID',5);
define('MP_THIRD_QUARTER_MOON_NAME','a Third Quarter Moon');
define('MP_THIRD_QUARTER_MOON_ID',6);
define('MP_WANING_CRESCENT_NAME','Waning Crescent');
define('MP_WANING_CRESCENT_ID',7);
define('MP_DAY_IN_SECONDS', 60 * 60 * 24);

class moonPhase {

var $allMoonPhases = array();
var $dateAsTimeStamp;
var $moonPhaseIDforDate;
var $moonPhaseNameForDate;
var $periodInDays = 29.53058867; 
var $periodInSeconds = -1; 
var $someFullMoonDate;


function moonPhase($timeStamp = -1) {
	$this->allMoonPhases = array(
		MP_NEW_MOON_NAME,
		MP_WAXING_CRESCENT_NAME,
		MP_FIRST_QUARTER_NAME,
		MP_WAXING_GIBBOUS_NAME,
		MP_FULL_MOON_NAME,
		MP_WANING_GIBBOUS_NAME,
		MP_THIRD_QUARTER_MOON_NAME,
		MP_WANING_CRESCENT_NAME);

	$this->someFullMoonDate = strtotime("August 22 2002 22:29 UTC");
	if($timeStamp == '' or $timeStamp == -1) $timeStamp = time();
	$this->setDate($timeStamp);
} 


function calcMoonPhase() {
	$position = $this->getPositionInCycle();
	if($position >= 0.474 && $position <= 0.53)
		$phaseInfoForCurrentDate = array(MP_NEW_MOON_ID, MP_NEW_MOON_NAME);
	else if ($position >= 0.53 && $position <= 0.724)
		$phaseInfoForCurrentDate = array(MP_WAXING_CRESCENT_ID, MP_WAXING_CRESCENT_NAME);
	else if ($position >= 0.724 && $position <= 0.776)
		$phaseInfoForCurrentDate = array(MP_FIRST_QUARTER_ID, MP_FIRST_QUARTER_NAME);
	else if ($position >= 0.776 && $position <= 0.974)
		$phaseInfoForCurrentDate = array(MP_WAXING_GIBBOUS_ID, MP_WAXING_GIBBOUS_NAME);
	else if ($position >= 0.974 || $position <= 0.026)
		$phaseInfoForCurrentDate = array(MP_FULL_MOON_ID, MP_FULL_MOON_NAME);
	else if ($position >= 0.026 && $position <= 0.234)
		$phaseInfoForCurrentDate = array(MP_WANING_GIBBOUS_ID, MP_WANING_GIBBOUS_NAME);
	else if ($position >= 0.234 && $position <= 0.295)
		$phaseInfoForCurrentDate = array(MP_THIRD_QUARTER_MOON_ID, MP_THIRD_QUARTER_MOON_NAME);
	else if ($position >= 0.295 && $position <= 0.4739)
		$phaseInfoForCurrentDate = array(MP_WANING_CRESCENT_ID, MP_WANING_CRESCENT_NAME);
	list($this->moonPhaseIDforDate,$this->moonPhaseNameForDate) = $phaseInfoForCurrentDate;
} 


function getAllMoonPhases() {
	return $this->allMoonPhases;
} 


function getBaseFullMoonDate() {
	return $this->someFullMoonDate;
} 


function getDateAsTimeStamp() {
	return $this->dateAsTimeStamp;
} 


function getDaysUntilNextFullMoon() {
	$position = $this->getPositionInCycle();
	return round((1 - $position) * $this->getPeriodInDays(), 2);
} 


function getDaysUntilNextLastQuarterMoon() {
	$days = 0;
	$position = $this->getPositionInCycle();
	if ($position < 0.25)
		$days = (0.25 - $position) * $this->getPeriodInDays();
	else if ($position >= 0.25)
		$days = (1.25 - $position) * $this->getPeriodInDays();
	return round($days, 1);
} 


function getDaysUntilNextFirstQuarterMoon() {
	$days = 0;
	$position = $this->getPositionInCycle();
	if ($position < 0.75)
		$days = (0.75 - $position) * $this->getPeriodInDays();
	else if ($position >= 0.75)
		$days = (1.75 - $position) * $this->getPeriodInDays();
	return round($days,1);
} 


function getDaysUntilNextNewMoon() {
	$days = 0;
	$position = $this->getPositionInCycle();
	if ($position < 0.5)
		$days = (0.5 - $position) * $this->getPeriodInDays();
	else if ($position >= 0.5)
		$days = (1.5 - $position) * $this->getPeriodInDays();
	return round($days, 1);
} 


function getPercentOfIllumination() {
	
	$percentage = (1.0 + cos(2.0 * M_PI * $this->getPositionInCycle())) / 2.0;
	$percentage *= 100;
	$percentage = round($percentage,1) . '%';
	return $percentage;
} 


function getPeriodInDays() {
	return $this->periodInDays;
} 


function getPeriodInSeconds() {
	if($this->periodInSeconds > -1) return $this->periodInSeconds; 
	$this->periodInSeconds = $this->getPeriodInDays() * MP_DAY_IN_SECONDS;
	return $this->periodInSeconds;
} 


function getPhaseID() {
	return $this->moonPhaseIDforDate;
} 


function getPhaseName($ID = -1) {
	if($ID <= -1) 
		return $this->moonPhaseNameForDate; 
	return $this->allMoonPhases[$ID]; 
} 


function getPositionInCycle() {
	$diff = $this->getDateAsTimeStamp() - $this->getBaseFullMoonDate();
	$periodInSeconds = $this->getPeriodInSeconds();
	$position = ($diff % $periodInSeconds) / $periodInSeconds; 
	if ($position < 0)
		$position = 1 + $position;
	return $position;
} 


function getUpcomingWeekArray($newStartingDateAsTimeStamp = -1) {
	$newStartingDateAsTimeStamp = ($newStartingDateAsTimeStamp > -1)
		? $newStartingDateAsTimeStamp
		: $this->getDateAsTimeStamp();
	$moonPhaseObj = get_class($this);
	$weeklyPhase = new $moonPhaseObj($newStartingDateAsTimeStamp);
	$upcomingWeekArray = array();
	for(	$day = 0, $thisTimeStamp = $weeklyPhase->getDateAsTimeStamp(); 
				$day < 7; $day++, $thisTimeStamp += MP_DAY_IN_SECONDS) {
		$weeklyPhase->setDate($thisTimeStamp);
		$upcomingWeekArray[$thisTimeStamp] = $weeklyPhase->getPhaseID();
	} 
	unset($weeklyPhase);
	return $upcomingWeekArray;
} 


function setDate($timeStamp = -1) {
	if($timeStamp == '' or $timeStamp == -1) $timeStamp = time();
	$this->dateAsTimeStamp = $timeStamp;
	$this->calcMoonPhase();
} 

} 

function moonphase($post_id){
        global $wpdb;
	$request = "SELECT UNIX_TIMESTAMP(post_date) AS post_date FROM $wpdb->posts WHERE id = $post_id ";
	$posts = $wpdb->get_results($request);
	
	foreach ($posts as $post) {
		$moondate=get_the_date('F d Y H:i:s',$post->pdate);
		$dateAsTS=strtotime($moondate);

	}

$mp = new moonPhase($dateAsTS);
//echo "<b>On this date: ", strftime ("%b %d %Y %H:%M:%S", $mp->getDateAsTimeStamp()), ":</b>";
//echo "<br />\n";
//echo "The moon date is ".$moondate;
echo "The moon is ", $mp->getPhaseName();
}

function get_moonphase(){
         global $moonimgloc;
	//$request = "SELECT UNIX_TIMESTAMP(post_date) AS post_date FROM $wpdb->posts WHERE id = $post_id ";
	//$posts = $wpdb->get_results($request);
	
//	foreach ($posts as $post) {
		$moondate2=date("F d Y H:i:s");
		$dateAsTS2=strtotime($moondate2);

//	}

$mp2 = new moonPhase($dateAsTS2);
//echo "<b>On this date: ", strftime ("%b %d %Y %H:%M:%S", $mp->getDateAsTimeStamp()), ":</b>";
//echo "<br />\n";
//echo "The moon date is ".$moondate;
$mymoon=$mp2->getPhaseName();
$mymoonpercent=$mp2->getPercentOfIllumination();
$myfullmoondays=$mp2->getDaysUntilNextFullMoon();

switch($mymoon){
	case "a New Moon":
	         $imageurl=$moonimgloc.'/moonimg/new.gif';
	         break;
	case "Waxing Crescent":
	         $imageurl=$moonimgloc.'moonimg/waxingcresent.gif';
	         break;
                  case "a First Quarter Moon":
                          $imageurl=$moonimgloc.'moonimg/1quarter.gif';
                          break;
                  case "Waxing Gibbous":
                          $imageurl=$moonimgloc.'moonimg/waxinggibbous.gif';
                          break;
                  case "a Full Moon":
                          $imageurl=$moonimgloc.'moonimg/full.gif';
                          break;
                  case "Waning Gibbous":
                           $imageurl=$moonimgloc.'moonimg/waninggibbous.gif';
                           break;
                  case "a Third Quarter Moon":
                           $imageurl=$moonimgloc.'moonimg/lastquarter.gif';
                           break;
                  case "Waning Crescent":
                           $imageurl=$moonimgloc.'moonimg/waningcresent.gif';
                           break;
                }
 echo "<img src='$imageurl' alt='The moon is $mymoon' title='The moon is $mymoon, $mymoonpercent full, and the next full moon is in $myfullmoondays days ' />";

}

?>
