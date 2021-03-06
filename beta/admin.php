<?php
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past


include_once($docRoot.'backend/Mobile_Detect.php');
include_once($docRoot."backend/common.php");
$common = new Common();
$detect = new Mobile_Detect();

if (isset($_POST['getTable'])) {
  getOrdersTable($common);
  exit;
}



?>
<!DOCTYPE html> 
<html>
<head>
	<base target="_blank"/>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<title>&lt;<?php echo $common::guildName;?>&gt; - Admin</title> 
  <link rel="favicon" type="image/x-icon" href="/favicon.ico">
	<link rel="stylesheet" href="/css/style.css" />
	<link rel="stylesheet" href="/css/ui-darkness/jquery-ui-1.10.1.custom.min.css" />
  <?php echo $common->outputCommonJS();?>
  <script>
  window.filter_functions_list = {
      3 : {
      <?php
      $output = "";
      foreach ($common->levelRanges as $range=>$limits) {
        $start = $limits['start'];
        $end = $limits['end'];
        $output .= "'$range'      : function(e, n, f, i) { return n>=$start && n<=$end; },";
      }
      echo substr_replace($output ,"",-1);
      ?>     
      },
      4 : {
      <?php
      $output = "";
      foreach ($common->levelRanges as $range=>$limits) {
        $start = $limits['start'];
        $end = $limits['end'];
        $output .= "'$range'      : function(e, n, f, i) { return n>=$start && n<=$end; },";
      }
      echo substr_replace($output ,"",-1);
      ?>     
      }
    }
  </script>
  
	<script type="text/javascript" src="/js/power.js"></script>
  <?php include_once($docRoot."backend/analyticstracking.php") ?>
	<script src="/js/jquery-1.9.1.js"></script>
  <script src="/js/jquery.md5.min.js"></script>
  <script src="/js/jquery-ui-1.10.1.custom.min.js"></script>  
  <script src="/js/jquery.tablesorter.js"></script>	
	<script src="/js/jquery.tablesorter.widgets.js"></script>	
  <script src="/js/oocharts.js"></script>	
  <script>
    $(document).ready(function() {
    // oocharts
      // oo.setOOId("a5dd9d4126874b97b2dec74513847e33");
      // oo.load(doCharts);
 

    
      $(function() {
        <?php echo $common->getMemberNamesJS();?> 
        $( "#name" ).autocomplete({
          source: memberNames
        });
      });
      $("#orders_table").tablesorter({
        cssInfoBlock : "tablesorter-no-sort", 		
        widgets: ['zebra', 'stickyHeaders', 'filter']   
      });
       setupDoneBtn();
      $( "#dialog-form" ).dialog({
      autoOpen: false,
      height: 600,
      maxHeight: 600,
      resizable: false,
      width: 560,
      modal: true,
      buttons: {
        "Close": function() {
          $( this ).dialog( "close" );
        }
      }
    });
      $("#motd-prompt").dialog({
        autoOpen: false,
        height: 400,
        maxHeight: 600,
        resizable: false,
        width: 560,
        modal: true,
        buttons: {
            "Save": function() {
                var motd = $("#motdInput").val();
                // console.log(motd);
                // var text2 = $("#txt2");
                //Do your code here
                // text1.val(text2.val().substr(1,9));
                 $(".ui-dialog-titlebar-close").hide();
                  $('#motdSaveStatus').html("Submitting data...<img src='/images/ajax-loader.gif'></br><em>(May take a few moments)</em>");
                  $('#motdSaveStatus').show();
                  $(".ui-dialog-buttonpane button:contains('Close')").button("disable");
                  $(".ui-dialog-buttonpane button:contains('Save')").button("disable");
                  $.ajax({			
                    url: '/backend/backend.php',
                    type: 'POST',
                    data: {"MOTD" :motd},
                    timeout : 20000,
                    tryCount : 0,
                    retryLimit : 3,
                    dataType: "json",
                    success: function(data) {
                      console.log(data);
                      $(".ui-dialog-titlebar-close").show();
                      $(".ui-dialog-buttonpane button:contains('Close')").button("enable");
                      $(".ui-dialog-buttonpane button:contains('Save')").button("enable");
                      $('#motdSaveStatus').html("Updating done!(?)"+data.msg);
                      if (data.status == -1) {
                        window.loggedIn = false;
                        alert(data.msg);
                        console.log("window.loggedIn: "+window.loggedIn);
                        loginCallback = 'updateMotd';
                        openLoginForm();
                      } else {
                        $('#motdDisplayDiv').html("MOTD: "+motd);
                        $('#motdSaveStatus').html("Updating done!"+data.msg);
                      } 
                      // $("#motd-prompt").dialog("close");
                    },
                    error:  function(xhr, textStatus, errorThrown ) {
                      if (textStatus == 'timeout') {
                        this.tryCount++;
                        if (this.tryCount <= this.retryLimit) {
                            //try again
                            $.ajax(this);
                            return;
                        }
                          $(".ui-dialog-titlebar-close").show();
                          $(".ui-dialog-buttonpane button:contains('Close')").button("enable");
                          $(".ui-dialog-buttonpane button:contains('Save')").button("enable");
                          $('#motdSaveStatus').html("Have tried to submit your order " + this.retryLimit + " times and it is still not working. We give in. Sorry. Please try again. If issues persist please contact <a href='mailto:webmaster@DOMAIN'>webmaster@DOMAIN</a>.");                      
                          return;
                        }              
                      $('#motdSaveStatus').html("Submission failed! Server responded with code: "+xhr.status+". Please try again. If issues persist please contact <a href='mailto:webmaster@DOMAIN'>webmaster@DOMAIN</a>.");
                      $(".ui-dialog-titlebar-close").show();
                      $(".ui-dialog-buttonpane button:contains('Close')").button("enable");
                      $(".ui-dialog-buttonpane button:contains('Save')").button("enable");
                      // $("#motd-prompt").dialog("close");
                    }                
                  });
                
            },
            "Close": function() {
                $(this).dialog("close");
            }
        }
    });


  $('#updateMotdBtn').click(updateMotd());
  function updateMotd() {
    if (!window.loggedIn) {
      alert("You're currently not logged in, please log in now.");
      console.log("window.loggedIn: "+window.loggedIn);
      loginCallback = 'updateMotd';
      openLoginForm();
      return;
    }
      $('#motdSaveStatus').hide();
      $("#motd-prompt").dialog("open");
    });
  });
   function saveChanges() {
    if (!window.loggedIn) {
      alert("You're currently not logged in, please log in now.");
      console.log("window.loggedIn: "+window.loggedIn);
      loginCallback = 'saveChanges';
      openLoginForm();
      return;
    }
    $( "#dialog-form" ).dialog( "open" );
      $(".ui-dialog-titlebar-close").hide();
      $('#saveStatus').html("Submitting data...<img src='/images/ajax-loader.gif'></br><em>(May take a few moments)</em>");
      $('#saveStatus').show();
      $(".ui-dialog-buttonpane button:contains('Close')").button("disable");
      var orders = new Array();
      $('tr.itemRow').each(function() {
        var lineItem = new Object();
        lineItem.id = $(this).attr('id');
        lineItem.note = $('#'+lineItem.id +'-note').text();
        lineItem.postID = $('#'+lineItem.id +'-postID').text();
        // console.log($('#'+lineItem.id +'-postID').text());
        lineItem.status = 0;
        if ($('#'+lineItem.id +'-done').text() == "X") {
          lineItem.status = 1;
        } 
        orders.push(JSON.stringify(lineItem));
      });
      $.ajax({			
        url: '/backend/updateOrders.php',
        type: 'POST',
        data: {"orders[]" :orders, "name": $('#forumName').text()},
        timeout : 20000,
        tryCount : 0,
        retryLimit : 3,
        dataType: "json",
        success: function(data) {
          if (data.status == -1) {
            window.loggedIn = false;
            alert(data.msg);
            console.log("window.loggedIn: "+window.loggedIn);
            loginCallback = 'saveChanges';
            openLoginForm();
          } else if (data.status == -2) {
            $('#saveStatus').html(data.msg);
          } else {
            $('#saveStatus').html(data.msg+"<br>Updating table...<img src='/images/ajax-loader.gif'>" );
            
            $.ajax({			
              url: '/admin.php',
              type: 'POST',
              data: {"getTable":1},
              success: function(tableData) {
                // console.log("admin getTable data: "+tableData);
                $("#orders_table > tbody").html("");
                // $('#myTable > thead:last').append(tableData);
                $(tableData).appendTo("#orders_table");
                // $("#orders_table").trigger("update");
                // $("#orders_table").trigger("appendCache");
                $("table")
                  .trigger("update")
                  .trigger("appendCache");
                  setupDoneBtn();
                $('#saveStatus').html(data+"<br>Table updated! Good job dude/dudette!" );
                $(".ui-dialog-titlebar-close").show();
            $(".ui-dialog-buttonpane button:contains('Close')").button("enable");
                // $('#saveStatus').html("Data saved successfully?!");
              }
            });
          }
        },
        error:  function(xhr, textStatus, errorThrown ) {
          if (textStatus == 'timeout') {
            this.tryCount++;
            if (this.tryCount <= this.retryLimit) {
                //try again
                $.ajax(this);
                return;
            }
              $(".ui-dialog-titlebar-close").show();
              $(".ui-dialog-buttonpane button:contains('Close')").button("enable");
              $('#saveStatus').html("Have tried to submit your order " + this.retryLimit + " times and it is still not working. We give in. Sorry. Please try again. If issues persist please contact <a href='mailto:webmaster@DOMAIN'>webmaster@DOMAIN</a>.");                      
              return;
            }              
          $('#saveStatus').html("Submission failed! Server responded with code: "+xhr.status+". Please try again. If issues persist please contact <a href='mailto:webmaster@DOMAIN'>webmaster@DOMAIN</a>.");
          $(".ui-dialog-titlebar-close").show();
          $(".ui-dialog-buttonpane button:contains('Close')").button("enable");
        }                
      });
      
    }
    
    function setupDoneBtn() {
      $('.doneBtn').click(function () {
      var currentText = $(this).html();
      if (currentText == "Done!") {
        $(this).html("X");
        $(this).closest('tr').toggleClass('hilite');
      } else {
        $(this).html("Done!");
        $(this).closest('tr').toggleClass('hilite');
      }
      });
    }
    
    function checkAll() {
      $('.doneBtn').each(function() {
      var currentText = $(this).html();
      if (currentText == "Done!") {
        $(this).click();
      }
    });
    }
    function uncheckAll() {
      $('.doneBtn').each(function() {
      var currentText = $(this).html();
      if (currentText == "X") {
        $(this).click();
      }
    });
    }
      // var currentText = $('#checkAllLink').text();
      // if (currentText == "Check All") {
        // $('#checkAllLink').text("Uncheck All");
      // } else {
        // $('#checkAllLink').text("Check All");
      // }
    function doCharts(){
      //Now I can start drawing charts.
      var startDate = new Date();
      var endDate = new Date();
      startDate.setMonth(startDate.getMonth()-1)
      // var m = new oo.Metric("1", startDate, endDate); 
      // m.setMetric('ga:pageviews');
      var tl = new oo.Timeline("38923956", startDate, endDate); 
      tl.addMetric('ga:visitors', 'Visits');
      // tl.addMetric('ga:newVisits', 'New Visits');
      tl.setOption('title', 'Visits Chart');
      tl.setOption('colors', ['red', 'black', '#0072c6']);
      tl.draw("timeline-div");
    }
  </script>
  <script src="./js/loginFunctions.js"></script>
</head>
</head> 
<body> 
<div id="page">
<h1 style="text-align:center;background-color: #333;">
<?php echo $common->makeFabulous("<".$common::guildName."> - Admin");?>
</h1>
<h3 style="text-align:center;background-color: #333;">
<?php
$common->outputMenu();
?>
</h3>
<br>
<?php echo $common->getMotd();?>
<br>
<div id="main-content">
<?php 
$common->outputGreeting();
?>
<br>
  <h1 style='text-align:right;'><button id='updateMotdBtn' class='cmdBtn'>Update Motd</button> <button id='uncheckAllLinkTop' onclick="uncheckAll();" class='cmdBtn'>Uncheck All</button><button id='checkAllLinkTop' class='cmdBtn' onclick="checkAll();">Check All</button> <button id='checkAllLinkTop' class='cmdBtn' onclick="saveChanges();" style="color:#0F0;">Save Changes</button>
</h1>
<br>
	<table class="tablesorter" id="orders_table">
	<thead>
	<tr>    
    <th class="filter-select" style="width: 10%;">Requester</th>
    <th class="filter-false" style="width: 2%;">Count</th>		
    <th style="width: 45%;">Item</th>
    
    <th class="filter-select" style="width: 10%;">Banker</th>
    <th class="filter-select" style="width: 5%;">Post ID</th>  
    <th style="width: 5%;">Location</th>    
    <th style="width: 20%;">Note</th>
    <th class="filter-false" style="width: 3%;">Status</th>
	</tr>
	</thead>
  <tbody>
<?php
getOrdersTable($common);
?>
</tbody>
</table>
<br><br>
<div id="timeline-div"></div>
<br><br>
</div>
</div>
<div id="floatingLink" style='background-color:#000; left:0; position:fixed; text-align:center; bottom:0; width:100%;'>
<a href="javascript:void(0)" id="uncheckAllLink" onclick="uncheckAll();" target="_self" title="Save">Uncheck All</a> - 
<a href="javascript:void(0)" id="checkAllLink" onclick="checkAll();" target="_self" title="Save">Check All</a> -
<a href="#" target="_self" title="Back to Top"><img style="border: none;" src="./images/top.gif"/>Back to Top</a> - 
<a href="javascript:void(0)" onclick="saveChanges();" target="_self" title="Save">Save Changes</a> 
</div>
</div>
<div id="dialog-form" title="Save Data" style='height:600px;'>
<div id="test">
  <div id="saveStatus" style='display:none;'>
  Submitting data...<img src='/images/ajax-loader.gif'></br><em>(May take a few moments)</em>
  </div>
  </div>
</div>
<div id="motd-prompt" title="Update MOTD">       
    <textarea name="motdInput" id="motdInput" rows="2" cols="60"><?php echo trim($common->queryMotd());?></textarea>
    
    <div id="motdSaveStatus" style='display:none;'>
  Submitting data...<img src='/images/ajax-loader.gif'></br><em>(May take a few moments)</em>
  </div>
</div>
<?php $common->outputDialogs(); ?>
</body>
</html>
<?php

function getOrdersTable($common) {

  
  $query = "SELECT * from orders ".
    "WHERE isDone=0 ".
    "ORDER BY banker ASC";
  $result = $common->query($query);
  $o = "";
  if (mysqli_num_rows($result) <= 0) {
    // echo '<tbody class="tablesorter-no-sort">';
    echo "<tr><td colspan='9'>No pending orders! Way to go, bro!</td></tr>";
  } else {
    // echo "<tbody>";
    while(list($id, $postID, $requester, $itemID, $itemName, $icon, $color, $count, $banker, $location, $isDone, $note) = $result->fetch_row()) {
      switch ($color) {
      case "1eff00":
        $quality = "Uncommon";
        break;
      case "ffffff":
        $quality = "Common";
        break;
      case "0070dd":
        $quality = "Rare";
        break;
      default:
        $quality = "It'sAMystery";
      }
      $iconImage = strtolower(str_replace("Interface\\Icons\\","",$icon));
      
      $o .= "<tr id='$id' class='itemRow'>";
      $o .= "<td>$requester</td>";
      $o .= "<td id='$id-itemCount-$count'>$count</td>";
      $o .= "<td><img src='/images/icons/".$iconImage.".png' style='width:25px;height:25px;'>&nbsp;&nbsp;<a href='".$common::aowowURL."?item=$itemID' class='itemLink'><font class='itemName' color='#$color'>$itemName</a></font></td>";
      
      $o .= "<td id='$id-banker'>$banker</td>";//<td><img src='/images/icons/".$iconImage.".png' style='width:25px;height:25px;'></td>";      
      $o .= "<td><a id='$id-postID' href='/forums/viewtopic.php?f=2&t=11&p=$postID#p$postID'>$postID</a></td>";
      $o .= "<td>$location</td>";
      $o .= "<td><div id='$id-note' style='width:100%; height:100%; min-width:125px;min-height:25px;border:2px solid;border-color:#DDDDDD;' contenteditable='true'>$note</div></td>";
      $o .= "<td valign='middle'><button id='$id-done' class='doneBtn'>Done!</button></td>";
      $o .= "</tr>";
    }
  }



	echo $o;

}
?>
