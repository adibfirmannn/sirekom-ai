<?php
// Pastikan ID lomba tersedia dalam parameter URL
if (isset($_GET['id'])) {
    $lomba_id = $_GET['id'];

    // Lakukan koneksi ke database
    $conn = new mysqli('localhost', 'root', '', 'sirekom_ai');

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Query untuk mengambil data lomba berdasarkan ID
    $sql = "SELECT lombas.*, kategoris.jenisKategori
        FROM lombas
        JOIN kategoris ON lombas.idKategori = kategoris.id
        WHERE lombas.id = $lomba_id;
        ";
    $result = $conn->query($sql);

    // Periksa apakah ada data yang ditemukan
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc(); // Ambil data lomba dari hasil query
?>

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <!-- Required meta tags -->
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

            <title>Detail Lomba</title>
        </head>

        <body>
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container">
                    <a class="navbar-brand" href="#">Sirekom</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="#">Lomba</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Kategori</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Detail Lomba -->
            <div class="container my-4">
                <div class="row">
                    <div class="col-md-6">
                        <img src="img/<?php echo $row['posterLomba']; ?>" class="img-fluid rounded" alt="<?php echo $row['namaLomba']; ?>" style="max-width: 100%; max-height: 300px;">
                    </div>
                    <div class="col-md-6">
                        <h2 class="mt-4"><?php echo $row['namaLomba']; ?></h2>
                        <input type="hidden" value="<?php echo $row['idKategori']; ?>">
                        <p class="mb-4"><?php echo $row['deskripsiLomba']; ?></p>
                        <p class="mb-4"><?php echo $row['jenisKategori']; ?></p>
                        <p><strong>Tanggal Mulai:</strong> <?php echo $row['tanggalBukaPendaftaran']; ?></p>
                        <p><strong>Tanggal Selesai:</strong> <?php echo $row['tanggalTutupPendaftaran']; ?></p>
                        <a href="#" class="btn btn-primary">Daftar Sekarang</a>
                    </div>
                </div>
            </div>

            <!-- Lomba Serupa Berdasarkan Kategori-->
            <div class="container my-4">
                <h2>Lomba Serupa</h2>
                <div id="similar-lombas" class="row row-cols-1 row-cols-md-3 g-4">
                    <?php
                    // Inisialisasi cURL untuk mendapatkan lomba serupa berdasarkan kategori
                    $ch = curl_init();

                    // Set URL tujuan
                    curl_setopt($ch, CURLOPT_URL, "http://localhost:5000/recommend_category/" . $row['idKategori']);

                    // Set opsi lainnya
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Menyimpan respons dalam variabel daripada mencetaknya langsung

                    // Eksekusi cURL dan simpan respons
                    $response = curl_exec($ch);

                    // Cek apakah ada kesalahan
                    if (curl_errno($ch)) {
                        echo '<p>Error: ' . curl_error($ch) . '</p>';
                    } else {
                        // Tampilkan respons (jika berhasil)
                        $similar_lombas = json_decode($response, true);

                        // Loop melalui data dan tampilkan dalam bentuk card
                        foreach ($similar_lombas as $lomba) {
                            // Cek apakah ID lomba sama dengan ID lomba yang sedang ditampilkan, jika ya, lanjutkan ke lomba berikutnya
                            if ($lomba['id'] == $lomba_id) {
                                continue;
                            }
                            // var_dump($lomba['posterLomba']);
                    ?>
                            <div class="col">
                                <div class="card">
                                    <div class="card-body">
                                        <img src="img/<?php echo $lomba['posterLomba']; ?>" class="card-img-top" alt="<?php echo $row['namaLomba']; ?>" style="max-width: 100%; max-height: 300px;">

                                        <h5 class="card-title"><?php echo $lomba['namaLomba']; ?></h5>
                                        <p class="card-text overflow-hidden text-truncate"><?php echo $lomba['deskripsiLomba']; ?></p>
                                        <a href="detail.php?id=<?php echo $lomba['id']; ?>" class="btn btn-primary">Detail Lomba</a>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    }

                    // Tutup koneksi cURL
                    curl_close($ch);
                    ?>
                </div>
            </div>

            <!-- Lomba Serupa berdasarkan deskripsi -->
            <div class="container my-4">
                <h2>Lomba Serupa</h2>
                <div id="similar-lombas" class="row row-cols-1 row-cols-md-3 g-4">
                    <?php
                    // Inisialisasi cURL untuk mendapatkan lomba serupa berdasarkan deskripsi
                    $ch = curl_init();

                    // Set URL tujuan
                    curl_setopt($ch, CURLOPT_URL, "http://localhost:5000/recommend_similar");

                    // Set opsi lainnya
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Menyimpan respons dalam variabel daripada mencetaknya langsung

                    // Set data yang dikirim dalam permintaan POST
                    $postData = json_encode(['namaLomba' => $row['namaLomba']]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

                    // Eksekusi cURL dan simpan respons
                    $response = curl_exec($ch);
                    // var_dump($response);

                    // Cek apakah ada kesalahan
                    if (curl_errno($ch)) {
                        echo '<p>Error: ' . curl_error($ch) . '</p>';
                    } else {
                        // Tampilkan respons (jika berhasil)
                        $similar_lombas = json_decode($response, true);

                        // Loop melalui data dan tampilkan dalam bentuk card
                        foreach ($similar_lombas as $lomba) {
                            // Cek apakah ID lomba sama dengan ID lomba yang sedang ditampilkan, jika ya, lanjutkan ke lomba berikutnya
                            if ($lomba['id'] == $lomba_id) {
                                continue;
                            }
                    ?>
                            <div class="col">
                                <div class="card">
                                    <img src="img/<?php echo $lomba['posterLomba']; ?>" class="card-img-top" alt="<?php echo $row['namaLomba']; ?>" style="max-width: 100%; max-height: 300px;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $lomba['namaLomba']; ?></h5>
                                        <p class="card-text overflow-hidden text-truncate"><?php echo $lomba['deskripsiLomba']; ?></p>
                                        <a href="detail.php?id=<?php echo $lomba['id']; ?>" class="btn btn-primary">Detail Lomba</a>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    }

                    // Tutup koneksi cURL
                    curl_close($ch);
                    ?>
                </div>
            </div>




            <!-- Bootstrap Bundle with Popper -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        </body>

        </html>

<?php
    } else {
        echo "<p>Data lomba tidak ditemukan.</p>";
    }

    // Tutup koneksi database
    $conn->close();
} else {
    echo "<p>ID lomba tidak tersedia.</p>";
}
?>