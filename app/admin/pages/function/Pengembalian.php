<?php
session_start();
include "../../../../config/koneksi.php";

if ($_GET['aksi'] == "hapus") {
    $id_peminjaman = $_GET['id'];

    $sql = mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman = $id_peminjaman");

    if ($sql) {
        $_SESSION['berhasil'] = "Kegiatan berhasil di hapus !";
        header("location: " . $_SERVER['HTTP_REFERER']);
    } else {
        $_SESSION['gagal'] = "Kegiatan gagal di hapus !";
        header("location: " . $_SERVER['HTTP_REFERER']);
    }
}
