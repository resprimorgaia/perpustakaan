<?php
session_start();
//------------------------------::::::::::::::::::::------------------------------\\
// Dibuat oleh FA Team di PT. Pacifica Raya Technology \\
//------------------------------::::::::::::::::::::------------------------------\\
include "../../../../config/koneksi.php";

if ($_GET['aksi'] == "pinjam") {

    if ($_POST['judulBuku'] == NULL) {
        $_SESSION['gagal'] = "Peminjaman buku gagal, Kamu belum memilih buku yang akan dipinjam !";
        header("location: " . $_SERVER['HTTP_REFERER']);
    } elseif ($_POST['kondisiBukuSaatDipinjam'] == NULL) {
        $_SESSION['gagal'] = "Peminjaman buku gagal, Kamu belum memilih kondisi buku yang akan dipinjam !";
        header("location: " . $_SERVER['HTTP_REFERER']);
    } else {

        include "Pemberitahuan.php";

        $nama_anggota = $_POST['namaAnggota'];
        $judul_buku = $_POST['judulBuku'];
        $tanggal_peminjaman = $_POST['tanggalPeminjaman'];
        $kondisi_buku_saat_dipinjam = $_POST['kondisiBukuSaatDipinjam'];

        $query = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE nama_anggota = '$nama_anggota' AND judul_buku = '$judul_buku' AND tanggal_pengembalian = ''");
        $cek = mysqli_num_rows($query);

        if ($cek > 0) {
            $_SESSION['gagal'] = "Peminjaman buku gagal, Kamu telah meminjam buku ini sebelumnya !";
            header("location: " . $_SERVER['HTTP_REFERER']);
        } else {
            $sql = "INSERT INTO peminjaman(nama_anggota,judul_buku,tanggal_peminjaman,kondisi_buku_saat_dipinjam)
            VALUES('" . $nama_anggota . "','" . $judul_buku . "','" . $tanggal_peminjaman . "','" . $kondisi_buku_saat_dipinjam . "')";
            $sql .= mysqli_query($koneksi, $sql);

            // Send notif to admin
            InsertPemberitahuanPeminjaman();
            //

            if ($sql) {
                $_SESSION['berhasil'] = "Peminjaman buku berhasil !";
                header("location: " . $_SERVER['HTTP_REFERER']);
            } else {
                $_SESSION['gagal'] = "Terjadi masalah dalam pengiriman data peminjaman !";
                header("location: " . $_SERVER['HTTP_REFERER']);
            }
        }
    }
} elseif ($_GET['aksi'] == "pengembalian") {

    include "Pemberitahuan.php";

    $judul_buku = $_POST['judulBuku'];
    $tanggal_pengembalian = $_POST['tanggalPengembalian'];
    $kondisiBukuSaatDikembalikan = $_POST['kondisiBukuSaatDikembalikan'];

    $ambil_id = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE judul_buku = '$judul_buku'");
    $row = mysqli_fetch_assoc($ambil_id);

    $id_peminjaman = $row['id_peminjaman'];
    $tanggal_peminjaman = $row['tanggal_peminjaman'];
    
    $tanggal_kembali = new DateTime($tanggal_pengembalian);
    $tanggal_peminjaman = new DateTime($tanggal_peminjaman);
    $selisih_hari = $tanggal_kembali->diff($tanggal_peminjaman)->days;

    $denda = 0;

    if ($selisih_hari > 7) {
        // If returned more than 7 days from the borrowing date
        $denda += 1000;
        if ($selisih_hari > 8) {
            // Additional 500 per day for every day beyond 8 days
            $denda += 500 * ($selisih_hari - 8);
        }
    }

    if ($kondisiBukuSaatDikembalikan == "Sama" && $selisih_hari <= 7) {
        $denda = 0; // If returned within 7 days and in good condition, no fine
    } elseif ($kondisiBukuSaatDikembalikan == "Rusak") {
        $denda += 20000; // Fine for damaged book
    } elseif ($kondisiBukuSaatDikembalikan == "Hilang") {
        $denda += 50000; // Fine for lost book
    }

    $query = "UPDATE peminjaman SET tanggal_pengembalian = '$tanggal_pengembalian', kondisi_buku_saat_dikembalikan = '$kondisiBukuSaatDikembalikan', denda = '$denda'";
    $query .= " WHERE id_peminjaman = $id_peminjaman";

    $sql = mysqli_query($koneksi, $query);

    if ($sql) {
        // Send notif to admin
        InsertPemberitahuanPengembalian();

        $_SESSION['berhasil'] = "Pengembalian buku berhasil !";
        header("location: " . $_SERVER['HTTP_REFERER']);
    } else {
        $_SESSION['gagal'] = "Pengembalian buku gagal !";
        header("location: " . $_SERVER['HTTP_REFERER']);
    }
}

function UpdateDataPeminjaman()
{
    include "../../../../config/koneksi.php";

    $nama_lama = $_SESSION['fullname'];
    $nama_anggota = $_POST['Fullname'];

    // Mencari nama dalam database berdasarkan session nama lengkap
    $query1 = mysqli_query($koneksi, "SELECT * FROM user WHERE fullname = '$nama_lama'");
    $row = mysqli_fetch_assoc($query1);

    // membuat variable dari hasil query1
    $nama_lama = $row['fullname'];

    // Fungsi update nama anggota pada table peminjaman
    $query = "UPDATE peminjaman SET nama_anggota = '$nama_anggota'";
    $query .= "WHERE nama_anggota = '$nama_lama'";

    $sql = mysqli_query($koneksi, $query);
}
