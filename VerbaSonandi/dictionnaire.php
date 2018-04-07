<?php

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>Dictionnaire</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mdb.min.css" rel="stylesheet">
    <link href="css/style_table.css" rel="stylesheet">
    
    
    <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-90767668-1', 'auto');
  ga('send', 'pageview');

    </script>
	

	
</head>

<body>

    <!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter42305059 = new Ya.Metrika({
                    id:42305059,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/42305059" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
      

    <div class="container">
        <br><br><br><br>
    <div class="container flex-center">
	<ul><li>
			<svg xmlns="http://www.w3.org/2000/svg"
				version="1.1"
				width="630" 
				height="150" >
				<text x="275" y="35" dy="0">
					<tspan x="300" dy=".6em" font-size="60">Verba sonandi</tspan>
					<tspan x="290" dy="2.1em" font-size="22" style="stroke-width: 0px;">dictionnaire bilingue </tspan>
					<tspan x="290" dy="1.1em" font-size="22" style="stroke-width: 0px;">recherche via examples choisis et leurs traductions</tspan>
				</text>
			</svg>
		</li>
		<li>
			<form class="form-inline" action="dictionnaires.php" method="get">
				<div class="form-group ">
					<select name="langs" id="langs" onchange="$('#langPair').submit()" class="selectpicker form-control" style="background-color: white; width: 100%;">
					<option value="français-serbe" selected='selected'>français -&gt; serbe</option>
					<option value="serbe-français">serbe -&gt; français</option>
					<option value="serbe-russe">serbe -&gt; russe</option>
					</select>
					</div>
					<br><br>
					<div class="form-group">
						<!--<a href="#" onclick="$('#waord').val($('#waord').val() + 'à');">à</a>-->
						<input type="text" id="waord" class="form-control" name="word" placeholder="Search for..." style="color:white; font-size: 1.25em;">
					</div>
					<div class="form-group">
						<button class="btn btn-default" type="submit">
							<i class="fa fa-search" aria-hidden="true"></i>
						</button>
					</div>
				</form>
		</li></ul>
        </div>
    </div>

    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="js/tether.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/mdb.min.js"></script>

    <!-- Animations init-->
    <script>
        new WOW().init();
    </script>
    

</body>
</html>