<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>SliceME</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- LOAD BOOTSTRAP CSS -->
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/2.3.2/css/bootstrap.min.css">
        <!-- LOAD JQUERY -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
        <!-- LOAD ANGULAR -->
        <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.0/angular.min.js"></script>
        <!-- PROCESS FORM WITH AJAX (NEW) -->
        <script>
        // define angular module/app
            var formApp = angular.module('formApp', []);
        // create angular controller and pass in $scope and $http
            function formController($scope, $http) {
        // create a blank object to hold our form information
        // $scope will allow this to pass between controller and view
                $scope.formData = {};
        // process the form
                $scope.processForm = function () {
                    $http({
                        method: 'POST',
                        url: 'process.php',
                        data: $.param($scope.formData), // pass in data as strings
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'} // set the headers so angular passing info as form data (not request payload)
                    })
                            .success(function (data) {
                                console.log(data);
                                if (!data.success) {
        // if not successful, bind errors to error variables
                                    $scope.errorYear = data.errors.year;
                                    $scope.errorMonth = data.errors.month;
                                } else {
        // if successful, bind success message to message
                                    $scope.errorYear = '';
                                    $scope.errorMonth = '';
                                    
                                    var d1 = document.getElementById('messages');
                                    angular.forEach(data.message, function(value, key) {
                                        var myTR = document.getElementById(value.file);
                                        myTR.className = myTR.className + " info";
                              //      d1.insertAdjacentHTML('beforeend', value.file);
                                    });
                                    $scope.message = data.message[0]['file'];

                                }
                            });
                };
            }
        </script>
    </head>
    <!-- apply the module and controller to our body so angular is applied to that -->
    <body ng-app="formApp" ng-controller="formController">
        <div class="container">

            <div class="row">
            <div class="span4">
                
       
                
                <!-- PAGE TITLE -->
                <div class="page-header">
                    <h1>Go cut'em</h1>
                </div>
                <!-- FORM -->
                <form ng-submit="processForm()">
                    <!-- YEAR -->
                    <div id="year-group" class="form-group" ng-class="{ 'has-error' : errorName }">
                        <label>Year</label>
                        <input type="text" name="year" class="form-control" placeholder="Year" ng-model="formData.year">
                        <span class="help-block" ng-show="errorYear">{{ errorYear}}</span>
                    </div>
                    <!-- MONTH -->
                    <div id="month-group" class="form-group" ng-class="{ 'has-error' : errorMonth }">
                        <label>Month</label>
                        <input type="text" name="month" class="form-control" placeholder="Month" ng-model="formData.month">
                        <span class="help-block" ng-show="errorMonth">{{ errorMonth}}</span>
                    </div>
                    <!-- LIMIT -->
                    <div id="limit-group" class="form-group" >
                        <label>Limit</label>
                        <input type="text" name="limit" class="form-control" placeholder="Limit (50 on default)" ng-model="formData.limit">
                    </div>
                    <!-- SUBMIT BUTTON -->
                    <button type="submit" class="btn btn-primary btn-lg">
                        Submit
                    </button>
                </form>
                <!-- SHOW DATA FROM INPUTS AS THEY ARE BEING TYPED 
                <pre>
{{ formData}}
                </pre>-->
                
                <!-- SHOW ERROR/SUCCESS MESSAGES -->
                <div id="messages"></div>
                
            </div> 
                
<?php           /*  2nd block with table  */ ?>      
            
                <div class="span8">
                    <!-- PAGE TITLE -->
                    <div class="page-header">
                        <h1>Working tree</h1>
                    </div>
<?php          

// start path
                    $path = ".";


// table output
                    echo '<table class="table table-condensed">';
                    readDirs($path);
                    echo '</table>';
?>                

                </div>
            </div>
            <?php /* debug div
                     	<div id="messages" class="well" ng-show="message">{{ message }}</div>
            */ ?>
        </div>
    </body>
</html>

<?php  
    
/* Read DIRs and build table */
function readDirs($path) {

    
    $width = 570;
    $height = 300;
    
  
    $dirHandle = opendir($path);
    while ($item = readdir($dirHandle)) {
        $newPath = $path . "/" . $item;
        if (is_dir($newPath) && $item != '.' && $item != '..' && is_numeric($item)) {
            if (mb_strlen($item) > 2 ) {
                echo '<tr><td>' . $item . '</td><td></td><td></td></tr>';
            } else {
                echo '<tr><td></td><td>' . $item . '</td><td></td><td></td></tr>';
            }

            readDirs($newPath);
        } else {
            $imageInfo = getimagesize($path . "/" . $item);
            $curr_width = $imageInfo[0];
            $curr_height = $imageInfo[1];
            if (file_exists($path . "/normal/ldpi/" . $item)) {
                $tr_class= 'info';
            }else{
                $tr_class ='';
            }
            if ($curr_width == $width && $curr_height == $height) {
                echo '<tr class="'.$tr_class.'" id="'.$item.'"><td></td><td></td><td>' . $item . '</td></tr>';
            }
        }
    }

}

?>