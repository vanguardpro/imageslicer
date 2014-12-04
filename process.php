<?php
$width = 570;
$height = 300;
$errors = array(); // array to hold validation errors
$data = array(); // array to pass back data
$limit=(!empty($_POST['limit']))?$_POST['limit']:50;
// validate the variables ======================================================
if (empty($_POST['year']))
    $errors['year'] = 'year is required.';
if (empty($_POST['month']))
    $errors['month'] = 'month is required.';
// return a response ===========================================================
// response if there are errors
if (!empty($errors)) {
// if there are items in our errors array, return those errors
    $data['success'] = false;
    $data['errors'] = $errors;
} else {
// if there are no errors, return a message

   
    
    include_once 'UploadImageHelper.php';
if (strlen($_POST['month'])<2){$month='0'.$_POST['month'];}else{$month=$_POST['month'];}
// return all our data to an AJAX call
//echo json_encode($data);
    $dir = $_POST['year'] . "/" . $month ;
// Open a known directory, and proceed to read its contents
//$dir = "2014/01";
    $dh = opendir($dir);
    $upload = new UploadImageHelper;
    $counter = 1;
    $x=1;
    while ((false !== ($filename = readdir($dh)))) {

        $imageInfo = getimagesize($dir.'/'.$filename);
        $curr_width = $imageInfo[0];
        $curr_height = $imageInfo[1];
   
        if ($curr_width == $width && $curr_height == $height) {
            
            if ($x <= $limit && !file_exists($dir . "/normal/ldpi/" . $filename)) {
                $files[] = array("num" => $counter, "file" => $filename);
                $x++;
            $upload->add($dir, $filename);
            }
        }

        $counter++;
        
}
      //  print_r($files);
    $data['success'] = true;
    $data['message'] = $files;
    
//    $images = preg_grep('/\.jpg$/i', $files);


}
// return all our data to an AJAX call
echo json_encode($data);
