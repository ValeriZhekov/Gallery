<?php
$serverName = "DESKTOP-AQ35H71\SQLEXPRESS";


$connectionOptions = [
    "Database" => "GalleryProject",
    "TrustServerCertificate" => true, // windows authentication /not pass
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
?>