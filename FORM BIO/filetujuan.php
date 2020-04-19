<?php
echo $action = $_REQUEST['action'];

parse_str($_REQUEST['dataform'], $hasil); 
echo 'username: ' . $hasil['username']; 
echo 'firstname : ' . $hasil['firstname'];
echo 'lastname  : ' . $hasil['lastname'];
echo 'email  : ' . $hasil['email'];
echo 'address  : ' . $hasil['address'];
echo "CC Number: ".$hasil['cc-number']."<br/>";
echo "Billing :".implode(',',$hasil['billing'])."<br/>";

$gambarku = $_FILES["fotoku"];	//fotoku adalah record yang dikirim dari html (sync) dan js (async)

/* Error File Handling */
$ccnumber = trim($hasil['cc-number']);
if (!empty($gambarku["name"]) and !empty($ccnumber)){
	$namafile = $gambarku["name"];		//nama filenya
	preg_match("/([^\.]+$)/", $namafile, $ext);		//Regex: mencari string sesudah titik terakhir, saved in array ext
	$file_ext = strtolower($ext[1]);
	$namafilebaru = $hasil['cc-number'].".".$ext[1];	//nama file barunya [ccnumber].png
    $file = $gambarku["tmp_name"];						//source filenya 
    //perform the upload operation
	$extensions= array("jpeg","jpg","png");				//extensi file yang diijinkan
	//Kirim pesan error jika extensi file yang diunggah tidak termasuk dalam extensions
	$errors = array();
	if(in_array($file_ext,$extensions) === false)
	 $errors[] = "Extensi yang diperbolehkan jpeg atau png.";
	
	//Kirim pesan error jika ukuran file > 500kB
	$file_size = $gambarku['size'];
	if($file_size > 2097152)
	 $errors[] = "Ukuran file harus lebih kecil dari 2MB.";
    
	//Upload file
	if(empty($errors)){
		if(move_uploaded_file($file, "uploads/" . $namafilebaru))
			echo "Uploaded dengan nama $namafilebaru";
	}
}else echo $errors[] = "Lengkapi nomor kartu kredit dan gambarnya. ";
echo "<br/>";

if(!empty($errors)){
	echo "Error : ";
	foreach ($errors as $val)
		echo $val;

if($action == 'create')
	$syntaxsql = "insert into tbl_user values (null,'$hasil[username]','$hasil[firstname]', '$hasil[lastname]', '$hasil[email]', 
	'$hasil[address]',now())";
elseif($action == 'update')
	$syntaxsql = "update tbl_user set username = '$hasil[username]', firstname = '$hasil[firstname]', lastname = '$hasil[lastname]', 
	email = '$hasil[email]', address ='$hasil[adress]'";
elseif($action == 'delete')
	$syntaxsql = "delete from tbl_user where username = '$hasil[username]'";
elseif($action == 'read')
	$syntaxsql = "select * from tbl_user";
	
//eksekusi syntaxsql 
$conn = new mysqli("localhost","root","","coba"); //dbhost, dbuser, dbpass, dbname
if ($conn->connect_errno) {
  echo "Failed to connect to MySQL: " . $conn -> connect_error;
  exit();
}else{
  echo "Database connected. ";
}
//create, update, delete query($syntaxsql) -> true false
if ($conn->query($syntaxsql) === TRUE) {
	echo "Query $action with syntax $syntaxsql suceeded !";
}
elseif ($conn->query($syntaxsql) === FALSE){
	echo "Error: $syntaxsql" .$conn->error;
}
//khusus read query($syntaxsql) -> semua associated array
else{
	$result = $conn->query($syntaxsql); //bukan true false tapi data array asossiasi
	if($result->num_rows > 0){
		echo "<table id='tresult' class='table table-striped table-bordered'>";
		echo "<thead><th>username</th><th>firstname</th><th>lastname</th><th>email</th><th>address</th></thead>";
		echo "<tbody>";
		while($row = $result->fetch_assoc()) {
			echo "<tr><td>".$row['username']."</td><td>". $row['firstname']."</td><td>". $row['lastname']."</td><td>". $row['email']."</td>
			<td>". $row['address']."</td></tr>";
		}
		echo "</tbody>";
		echo "</table>";
	}
}
$conn->close();

?>