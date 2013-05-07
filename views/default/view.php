
<?php
try {
    $this->header($editable = false);
} catch (Exception $e) {
    
}
try {
    $assetUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.GlaucomaVirtualClinic.assets.js'));
    Yii::app()->clientScript->registerScriptFile($assetUrl.'/imageLoader.js'); 
    Yii::app()->clientScript->registerScriptFile($assetUrl.'/EyeGraph.js'); 
} catch (Exception $e) {
    
}
$summaryPath = 'application.modules.GlaucomaVirtualClinic.models.GlaucomaSummaryView';
Yii::import($summaryPath, true);
Yii::import('application.modules.GlaucomaVirtualClinic.models.*', true);
$summary = 'GlaucomaSummaryView';
if (class_exists('GlaucomaSummaryView', true)) {
    $obj = new $summary($this->patient->hos_num);
}
if (!$obj) {
    return;
}

$iops_left = $obj->getIOPsLeft();
$iops_right = $obj->getIOPsRight();

$patient = $this->patient;
// OK, let's display the stereo disc images, if there are any:
Yii::import('application.modules.module_esb_mirth.models.*');
$eyeRightFiles = DiscUtils::getDiscFileList($patient, 'R');
$eyeLeftFiles = DiscUtils::getDiscFileList($patient, 'L');
$eyeRightFilesVfa = VfaUtils::getVfaFileList($patient, 'R');
$eyeLeftFilesVfa = VfaUtils::getVfaFileList($patient, 'L');
?>
<?php
if (count($iops_left) > 1 || count($iops_right) > 1) {
    ?>

    <script type="text/javascript">
                                                                                                                                                                                                
        // Runs on page load
        function init_graphs()
        {
            // Get reference to the drawing canvas
            var canvas = document.getElementById('canvasR');
                                                                                                                                                                                
            // Create a drawing linked to the canvas
            eyeGraph = new EG.Graph(canvas);
                                                                                                                                                                                
            // Set x axis details
    <?php
    echo $obj->getEyeGraphDates();
    ?>

            // Set y axis details
            eyeGraph.axisYArray = [[0,'0'], [66,'10'], [132, '20'], [198, '30'],
                [264, '40'], [330, '50'], [396,'60']];

            // Add a series of points
            eyeGraph.addSeries("LineGraph", [<?php echo $obj->getIOPsEyeRight(); ?>], {colour:'blue'});
            eyeGraph.addSeries("TimePoints", [
    <?php
    foreach ($obj->getDiagnosesRight() as $key => $diagnosis) {
        echo $diagnosis;
        if ($key < count($obj->getDiagnosesRight())) {
            echo ",";
        }
    }
    ?>], null);
                eyeGraph.addSeries("TimePoints", [
    <?php
    foreach ($obj->getMedicationsRight() as $key => $medication) {
        echo $m;
        if ($key < count($obj->getMedicationsRight())) {
            echo ",";
        }
    }
    ?>], null);

                    // Draw graph
                    eyeGraph.draw();
                    // Get reference to the drawing canvas
                    var canvas = document.getElementById('canvasL');
                                                                                                                                                                                
                    // Create a drawing linked to the canvas
                    eyeGraph = new EG.Graph(canvas);
                                                                                                                                                                                
                    // Set x axis details
    <?php
    echo $obj->getEyeGraphDates();
    ?>

                    // Set y axis details
                    eyeGraph.axisYArray = [[0,'0'], [66,'10'], [132, '20'], [198, '30'],
                        [264, '40'], [330, '50'], [396,'60']];

                    // Add a series of points
                    eyeGraph.addSeries("LineGraph", [<?php echo $obj->getIOPsEyeLeft(); ?>], {colour:'red'});
                    eyeGraph.addSeries("TimePoints", [
    <?php
    foreach ($obj->getDiagnosesLeft() as $key => $diagnosis) {
        echo $diagnosis;
        if ($key < count($obj->getDiagnosesLeft())) {
            echo ",";
        }
    }
    ?>], null);
                        //            eyeGraph.addSeries("TimeBlocks", [
    <?php
//                $meds = $obj->getMedicationsLeft($this->patient->hos_num);
//                foreach($meds as $key => $medication) {
//                    echo $medication; 
//                    if ($key < count($meds)) {
//                        echo ",";
//                    }
//                }
    ?>//], null);

                        // Draw graph
                        eyeGraph.draw();
                        // Now ad visual field graphs:
                                                          
                    }                                                                                                                           	            
    </script>
    <?php
    if (count($iops_right) > 1) {
        ?>
        <div class="section" style="float: left; width: 400px; height:480px; overflow:auto">
            <h4>Intraocular Pressures:</h4>
            <canvas id="canvasR" class="eg_graph" width="1000" height="400" tabindex="1"></canvas>
        </div>
        <?php
    }
    if (count($iops_left) > 1) {
        ?>

        <div class="section" style="float: right; width: 400px; height:480px; overflow:auto">
            <h4>Intraocular Pressures:</h4>
            <canvas id="canvasL" class="eg_graph" width="1000" height="400" tabindex="1"></canvas>
        </div>
        <?php
    }
} else if (count($iops_left) == 1 || count($iops_right) == 1) {
    // just display information about the specified IOP:
    ?>

    <div class="section" style="float: left; margin-left: 100px; ">
    <?php
    $iopLeft = "-";
    $iopRight = "-";
    if (count($iops_left)) {
        $iopLeft = $iops_left[0];
    }
    if (count($iops_right)) {
        $iopRight = $iops_right[0];
    }
    echo "There is only 1 recorded IOP for this patient (RE/LE): "
    . $iopRight . " / " . $iopLeft
    . "<br>At least two (2) IOPs must be recorded for the graph to be drawn.";
    ?>
    </div>
    <div style="clear: both"></div> 
    <?php
} else {
    ?>

    <div class="section" style="float: left; margin-bottom: 10px; margin-top: 10px; margin-left: 100px; ">
    <?php echo "There are no IOPs recorded for this patient."; ?>
    </div>
    <div style="clear: both"></div> 
    <?php
}
?>

<script type="text/javascript">
                var imagesStereoRight = new Array();
                var imagesStereoLeft = new Array();
                var imagesVfaRight = new Array();
                var imagesVfaLeft = new Array();
<?php
$imageIndex = 0;
foreach ($eyeRightFiles as $file) {
    // large-size image storage location:
    $x = $file->file->name;
    echo "\nimagesStereoRight[" . ($imageIndex++) . "] = \"" . DiscUtils::getEncodedDiscFileName($patient->hos_num, $file->file->name) . "/thumbs/" . $file->file->name . "\";";
}
$imageIndex = 0;
foreach ($eyeLeftFiles as $file) {
    // large-size image storage location:
    echo "\n imagesStereoLeft[" . ($imageIndex++) . "] = \"" . DiscUtils::getEncodedDiscFileName($patient->hos_num, $file->file->name) . "/thumbs/" . $file->file->name . "\";";
}

$imageIndex = 0;
foreach ($eyeRightFilesVfa as $file) {
   if ($file->vfa_file) {
        // large-size image storage location:
        echo "\nimagesVfaRight[" . ($imageIndex++) . "] = \"" . VfaUtils::getEncodedDiscFileName($patient->hos_num, $file->file_name) . "/thumbs/" . $file->vfa_file->file->name . "\";";
   }
}
$imageIndex = 0;
foreach ($eyeLeftFilesVfa as $file) {
    // large-size image storage location:
    if ($file->vfa_file) {
        echo "\n imagesVfaLeft[" . ($imageIndex++) . "] = \"" . VfaUtils::getEncodedDiscFileName($patient->hos_num, $file->file_name) . "/thumbs/" . $file->vfa_file->file->name . "\";";
    }
}
?>
                window.onload = function() {
                    var canvas = document.getElementById("canvasStereoRight");
                    if (canvas != null) {
                        var imageObjRight = new Image();
                        var context = canvas.getContext("2d");
                        imageObjRight.onload = function() {
                            context.drawImage(imageObjRight, 0, 0);
                        };
                        imageObjRight.src = imagesStereoRight[0];
                        canvas.addEventListener('mousemove', function(e){ev_mousemove(e,
                            "canvasStereoRight", imageObjRight, imagesStereoRight,
                            "canvasVfaRight", imagesVfaRight)}, false);
                    }
                    
                    var canvas2 = document.getElementById("canvasStereoLeft");
                    if (canvas2 != null) {
                        var imageObjLeft = new Image();
                        var context2 = canvas2.getContext("2d");
                        imageObjLeft.onload = function() {
                            context2.drawImage(imageObjLeft, 0, 0);
                        };
                        imageObjLeft.src = imagesStereoLeft[0];
                        canvas2.addEventListener('mousemove', function(e){ev_mousemove(e, 
                            "canvasStereoLeft", imageObjLeft, imagesStereoLeft,
                            "canvasVfaLeft", imagesVfaLeft)}, false);
                    }
                    
                    var canvasVfa = document.getElementById("canvasVfaRight");
                    if (canvasVfa != null) {
                        var imageObjVfaRight = new Image();
                        var contextVfa = canvasVfa.getContext("2d");
                        imageObjVfaRight.onload = function() {
                            contextVfa.drawImage(imageObjVfaRight, 0, 0);
                        };
                        imageObjVfaRight.src = imagesVfaRight[0];
                        canvasVfa.addEventListener('mousemove', function(e){ev_mousemove(e, 
                            "canvasVfaRight", imageObjVfaRight, imagesVfaRight,
                            "canvasStereoRight", imagesStereoRight)}, false);
                    }
                    
                    var canvasVfa2 = document.getElementById("canvasVfaLeft");
                    if (canvasVfa2 != null) {
                        var imageObjVfaLeft = new Image();
                        var contextVfa2 = canvasVfa2.getContext("2d");
                        imageObjVfaLeft.onload = function() {
                            contextVfa2.drawImage(imageObjVfaLeft, 0, 0);
                        };
                        imageObjVfaLeft.src = imagesVfaLeft[0];
                        canvasVfa2.addEventListener('mousemove', function(e){ev_mousemove(e, 
                            "canvasVfaLeft", imageObjVfaLeft, imagesVfaLeft,
                            "canvasStereoLeft", imagesStereoLeft)}, false);
                    }
                };
</script>

<div style="clear: both"></div> 
<div id="x" style="float:left; margin-bottom: 10px; margin-top: 10px; margin-left: 100px; ">
<?php echo "Stereo images: " . count($eyeRightFiles) ?>
</div>

<div id="x" style="float:right; margin-bottom: 10px; margin-top: 10px; margin-right: 100px; ">
<?php echo "Stereo images: " . count($eyeLeftFiles) ?>
</div>
<div style="clear: both"></div> 
<?php
if (count($eyeRightFiles) > 0) {
    ?>

    <div id="XYZ2" class="jThumbnailScroller" style="margin-left: 100px; float:left; height:225px; width:300px; ">
        <canvas id="canvasStereoRight" class="tmp" width="300" height="225" tabindex="1"></canvas>
    </div>
    <?php
}

if (count($eyeLeftFiles) > 0) {
    ?>

    <div id="XYZ" class="jThumbnailScroller" style="margin-right: 100px; float:right; height:225px; width:300px; ">
        <canvas id="canvasStereoLeft" class="tmp" width="300" height="255" tabindex="1"></canvas>
    </div>
    <?php
}
?>
<div style="clear: both"></div> 

<div id="x" style="float:left; margin-bottom: 10px; margin-top: 10px; margin-left: 100px; ">
<?php echo "VFA images: " . count($eyeRightFilesVfa) ?>
</div>

<div id="x" style="float:right; margin-bottom: 10px; margin-top: 10px; margin-right: 100px; ">
<?php echo "VFA images: " . count($eyeLeftFilesVfa) ?>
</div>
<div style="clear: both"></div> 

<?php
if (count($eyeRightFilesVfa) > 0) {
    ?>
    <div id="XYZa2" class="jThumbnailScroller" style="margin-left: 100px; float:left; height:306px; width:300px; ">
        <canvas id="canvasVfaRight" class="tmp" width="300" height="306" tabindex="1"></canvas>
    </div>
    <?php
}

if (count($eyeLeftFilesVfa) > 0) {
    ?>
    <div id="XYZa" class="jThumbnailScroller" style="margin-right: 100px; float:right; height:306px; width:300px; ">
        <canvas id="canvasVfaLeft" class="tmp" width="300" height="306" tabindex="1"></canvas>
    </div>
    <?php
}
?>

<div style="clear: both"></div> 

<?php
if (count($iops_left) > 1 || count($iops_right) > 1) {
    ?>
    <script>
                    init_graphs();
    </script>

    <?php
}
try {
    $this->footer();
} catch (Exception $e) {
    
}
?>