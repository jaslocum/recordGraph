<?php
require("dbconfig.php");
require("makeSelect.php");
?>
<head lang="us">
    <meta charset="utf-8">
    <title>Record Graph</title>
    <link href="css/ui-lightness/jquery-ui-1.10.0.custom.css" rel="stylesheet"></link>
    <link href="css/jquery-ui-timepicker-addon.css" rel="stylesheet"></link>
    <script src="js/jquery-1.9.0.js"></script>
    <script src="js/jquery-ui-1.10.0.custom.js"></script>
    <script src="js/jquery-ui-timepicker-addon.js"></script>
    <script src="js/date.format.js"></script>
    <style>
        body{
                font: 62.5% "Trebuchet MS", sans-serif;
                margin: 50px;
        }
        .headers {
                margin-top: 2em;
        }
        #dialog-link {
                padding: .4em 1em .4em 20px;
                text-decoration: none;
                position: relative;
        }
        #dialog-link span.ui-icon {
                margin: 0 5px 0 0;
                position: absolute;
                left: .2em;
                top: 50%;
                margin-top: -8px;
        }
        #icons {
                margin: 0;
                padding: 0;
        }
        #icons li {
                margin: 2px;
                position: relative;
                padding: 4px 0;
                cursor: pointer;
                float: left;
                list-style: none;
        }
        #icons span.ui-icon {
                float: left;
                margin: 0 4px;
        }
        .fakewindowcontain .ui-widget-overlay {
                position: absolute;
        }
    </style>
    <script>
	$(function() {
            
                endDate=new Date();
                //- 12 hours/
                begDate=new Date((endDate-(12*60*60*1000)));
                document.getElementById('begDatepicker').value=dateFormat(begDate,"yyyy/mm/dd hh:00tt");
                $( "#begDatepicker" ).datetimepicker({
                        dateFormat: "yy/mm/dd",
                        timeFormat: "hh:mmtt",
                        showMinute: false,
			inline: true
		});
                
                
                document.getElementById('endDatepicker').value=dateFormat(endDate,"yyyy/mm/dd hh:00tt");
		$( "#endDatepicker" ).datetimepicker({
                        dateFormat: "yy/mm/dd",
                        timeFormat: "hh:mmtt",
                        showMinute: false,
			inline: true
		});
	});
    </script>
    </meta>
</head>
<body>
<form action="recordGraph.php" method="post" target="_blank">
    <table>
        <tbody>
            <tr>
                <td colspan="1" >
                    <font size = +2>
                        Record Charting
                    </font>						
                </td>
            </tr>
            <tr>
                <td colspan="1">
                    Starting Date and Time: 
                </td>
                <td colspan="2">
                    <!-- Datepicker -->
                    <input type="text" id="begDatepicker" name="BegTime" value="">
                </td>
            </tr>
            <tr>
                <td colspan="1">
                    Ending Date and Time:
                </td>
                <td colspan="2">
                    <input type="text" id="endDatepicker" name="EndTime" value=""><br>
                </td>
            </tr>
            <tr>
                <td colspan="1">
                    Device:
                </td>
                <td colspan="2">
                    <?php
                       echo makeSelect("Device", "id", "name", "description", "records", "", "name ASC", "");
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
    <input type="Submit">
</form>
</body>
  