<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIKENDIS - Sistem Informasi Kegiatan Dinas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <style>
        :root {
            --teal-primary: #008080;
            --teal-dark: #006666;
            --teal-light: #33adad;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background-color: var(--teal-primary) !important;
            position: fixed;
            width: 100%;
            z-index: 1000;
        }
        .navbar-light .navbar-brand,
        .navbar-light .nav-link {
            color: white !important;
        }
        .hero {
            position: relative;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            color: white;
        }
        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 128, 128, 0.7), rgba(0, 128, 128, 0.7)), url('https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -1;
        }
        .hero-content {
            z-index: 1;
        }
        .btn-primary {
            background-color: var(--teal-dark);
            border-color: var(--white-dark);
            border-width: medium;
        }
        .btn-primary:hover {
            background-color: var(--teal-light);
            border-color: var(--teal-light);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--teal-primary);
        }
        .bg-light {
            background-color: #e6f3f3 !important;
        }
        .bg-dark {
            background-color: var(--teal-dark) !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">SIKENDIS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tentang">Tentang</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero text-center" id="beranda">
        <div class="container hero-content">
            <h1 class="display-4" data-aos="fade-up">SIKENDIS</h1>
            <p class="lead" data-aos="fade-up">Sistem Informasi Kegiatan Dinas BPS Kabupaten Mempawah</p>
            <a href="/a" class="btn btn-primary btn-lg" data-aos="fade-up">Mulai Sekarang</a>
        </div>
    </header>

    <section class="py-5" id="fitur">
        <div class="container">
            <h2 class="text-center mb-5">Fitur Utama</h2>
            <div class="row">
                <div class="col-md-4 text-center mb-4" data-aos="fade-up">
                    <i class="fas fa-file-alt feature-icon"></i>
                    <h3>Pembuatan Surat Tugas</h3>
                    <p>Buat surat tugas dengan mudah dan cepat sesuai kebutuhan Anda.</p>
                </div>
                <div class="col-md-4 text-center mb-4" data-aos="fade-up">
                    <i class="fas fa-plane feature-icon"></i>
                    <h3>Perjalanan Dinas</h3>
                    <p>Kelola surat perjalanan dinas dengan efisien dan terorganisir.</p>
                </div>
                <div class="col-md-4 text-center mb-4" data-aos="fade-up">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <h3>Monitoring</h3>
                    <p>Pantau status surat tugas dan perjalanan dinas dari awal hingga akhir.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-light py-5" id="tentang">
        <div class="container">
            <h2 class="text-center mb-5">Tentang SIKENDIS</h2>
            <div class="row">
                <div class="col-md-6" data-aos="fade-up">
                    <p>SIKENDIS adalah aplikasi yang dikembangkan oleh Pranata Komputer BPS Kabupaten Mempawah untuk mengakomodasi berbagai kebutuhan di satuan kerja yang belum terpenuhi oleh aplikasi lain.</p>
                </div>
                <div class="col-md-6" data-aos="fade-up">
                    <p>Dengan SIKENDIS, proses pembuatan surat tugas dan perjalanan dinas menjadi lebih efisien, terorganisir, dan mudah dimonitor. Aplikasi ini memastikan setiap langkah dalam proses administrasi berjalan lancar dan sesuai prosedur.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2024 SIKENDIS - BPS Kabupaten Mempawah. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
        });
        $(document).ready(function() {
            // Smooth scrolling for navigation links
            $('a.nav-link').on('click', function(event) {
                if (this.hash !== "") {
                    event.preventDefault();
                    var hash = this.hash;
                    $('html, body').animate({
                        scrollTop: $(hash).offset().top
                    }, 800, function(){
                        window.location.hash = hash;
                    });
                }
            });

            // Parallax effect
            $(window).scroll(function() {
                var scrollTop = $(this).scrollTop();
                $('.hero::before').css('background-position', 'center ' + (scrollTop * 0.5) + 'px');
            });
        });
    </script>
</body>
</html>
