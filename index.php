<?php 
	include 'wh_helper.php';
	$conversations=json_decode(getAllConv(),true);
?>

<!DOCTYPE html>
<html>
<head>
	<title>HVBN - Control room dashboard</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/css/materialize.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.7/js/materialize.min.js"></script>
</head>
<body>
	<script>
	  window.fbAsyncInit = function() {
	    FB.init({
	      appId      : '',
	      xfbml      : true,
	      version    : 'v2.7'
	    });
	  };

	  (function(d, s, id){
	     var js, fjs = d.getElementsByTagName(s)[0];
	     if (d.getElementById(id)) {return;}
	     js = d.createElement(s); js.id = id;
	     js.src = "//connect.facebook.net/en_US/sdk.js";
	     fjs.parentNode.insertBefore(js, fjs);
	   }(document, 'script', 'facebook-jssdk'));
	</script>
	<div class="container">
		<div class="row">
			<div class="center-align">
				<h2>HVBN - Safety Bot</h2>
			 </div>
		</div>
		<div class="row">
			<div class="center-align">
				<!-- <b>Github Source:</b> https://github.com -->
			 </div>
			<div class="center-align">
				<b>Note: </b>Data has been masked (xx) to prevent privacy issues due to public usage. Actual data will be unmasked after authentication.
			 </div>
		</div>
		<div class="row">
			<div class="center-align">
				<div class="fb-messengermessageus" 
				  messenger_app_id="" 
				  page_id=""
				  data-ref="Help" 
				  color="blue" 
				  size="xlarge"></div>
			 </div>
		</div>
		<div class="row">
			<h4 class="center-align">List of emergency cases</h4>
			<table class="striped">
				<thead>
					<tr>
						<th>S No.</th>
						<th>Emergency Type</th>
						<th>Latitude</th>
						<th>Longitude</th>
						<th>Mobile Number</th>
						<th>Timestamp</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$tbody='';
						$i=1;
						foreach($conversations as $conversation){
							$mobile=$conversation['mob_num'];
							if(!empty($mobile))
								$mobile=substr_replace($mobile, 'xxx', 4, -3);
							$latitude=$conversation['latitude'];
							if(!empty($latitude))
								$latitude=substr_replace($latitude, 'x', 1, 1);
							$longitude=$conversation['longitude'];
							if(!empty($longitude))
								$longitude=substr_replace($longitude, 'x', 1, 1);
							$em_type=$conversation['em_type'];
							if(!empty($em_type))
								$em_type=str_replace('ALERT_', '', $em_type);
							$tbody .= '<tr>';
							$tbody .= '<td>'.$i.'</td>';
							$tbody .= '<td>'.$em_type.'</td>';
							$tbody .= '<td>'.$latitude.'</td>';
							$tbody .= '<td>'.$longitude.'</td>';
							$tbody .= '<td>'.$mobile.'</td>';
							$tbody .= '<td>'.date('d-m-Y h:m:s a',strtotime($conversation['created_at'])).'</td>';
							$tbody .= '</tr>';
							$i=$i+1;
						}
						echo $tbody;
					?>
				</tbody>
			</table>
		</div>
	</div>
</body>
</html>