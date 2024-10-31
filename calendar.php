<div id="ob-cal">
<?php
	setlocale (LC_TIME, 'fr_FR.UTF-8');
	$txt_free="Libre";
	$txt_busy="Occupé";
	//print_r($booked);
	function affiche_mois($mois, $an)
	{
		global $booked;
		$affiche = strftime("%B %Y", mktime(0, 0, 0, $mois, 1, $an));
		$cal  = '<div class="ob-cal-month"><table>';
		$cal .= '<thead><tr><th colspan="7">'.$affiche.'</th></tr></thead>';
		$cal .= '<tbody><tr>
					<th>L</th>
					<th>M</th>
					<th>M</th>
					<th>J</th>
					<th>V</th>
					<th>S</th>
					<th>D</th>
				</tr>
				<tr>';
		// Total number of days in the month...
	       $total = cal_days_in_month(CAL_GREGORIAN, $mois, $an);
		// Numerical value of start day...
	       $starts = date('N', mktime(0, 0, 0, $mois, 1, $an));
		// Create month grid...
	       for ($grid = 1; $grid <= 42; $grid++)
	       {
		       $available = $class = null;
		       if ($grid < $starts || $grid > ($total + $starts - 1)) {
			       $day = null;
			       $class = 'ob-cal-disabled';
		       } else {
			       $day = ($grid - $starts) + 1;
			$day_of_year = date("z", mktime(0, 0, 0, $mois, $day, $an))+1;	
			   if (isset($booked[$an]) && array_key_exists($day_of_year, $booked[$an])) {
				       $available = false;
				       $class = 'ob-cal-booked';
			       }
		       }
			if ($day)
				$cal .= '<td class="'.$class.'">'.$day.'</td>';
			else
					$cal .= '<td class="'.$class.'">&nbsp;</td>';
		
			if ($grid % 7 == 0 && $grid < 42){
				$cal .= "</tr><tr>";
			}
	       }
		$cal .= '</tr></tbody></table></div>';
		echo $cal;

	} // end affiche_mois

	$mois_courant = date(n);
	for ($month = $mois_courant; $month <= 12; $month++)
	{
		affiche_mois($month, date(Y));
	}
	for ($month = 1; $month <=$mois_courant-1; $month++)
	{
		affiche_mois($month, date(Y)+1);
	}
?>
</div>
<div class="ob-cal-legende"><table><tbody><tr><td class="ob-cal-booked" style="width:15px; border:1px solid #f5f5f5">&nbsp;</td><td style="width=50px"><?php echo $txt_busy; ?></td><td style="width:15px">&nbsp;</td><td style="width:15px; border:1px solid #f5f5f5;background: #f5f5f5">&nbsp;</td><td style="width=50px"><?php echo $txt_free; ?></td></tr></tbody></table></div>
